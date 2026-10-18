#!/usr/bin/env python3
"""Guard the website's single-form and empty-content rules before release."""
from html import unescape
from pathlib import Path
import re
import sys

ROOT = Path(".")
pages = sorted(ROOT.glob("*.html"))
errors = []
notes = []

FORM_RE = re.compile(r"<form\b([^>]*)>([\s\S]*?)</form\s*>", re.I)
ATTR_RE = re.compile(r"""\b([\w:-]+)\s*=\s*(["'])(.*?)\2""", re.I)
CTA_RE = re.compile(r"<a\b([^>]*\bclass\s*=\s*(?:\"[^\"]*(?:btn--primary|hero-action|nav__cta|nav__mobile-cta|footer__cta)[^\"]*\"|'[^']*(?:btn--primary|hero-action|nav__cta|nav__mobile-cta|footer__cta)[^']*')[^>]*)>", re.I)

def attrs(source):
    return {m.group(1).lower(): unescape(m.group(3)) for m in ATTR_RE.finditer(source)}

def text_content(source):
    source = re.sub(r"<!--[\s\S]*?-->", " ", source)
    source = re.sub(r"<script\b[\s\S]*?</script\s*>", " ", source, flags=re.I)
    source = re.sub(r"<style\b[\s\S]*?</style\s*>", " ", source, flags=re.I)
    source = re.sub(r"<[^>]+>", " ", source)
    return unescape(source).replace("\xa0", " ").strip()

all_forms = []
for page in pages:
    source = page.read_text(encoding="utf-8", errors="replace")
    forms = list(FORM_RE.finditer(source))
    if forms:
        all_forms.append((page.name, forms))
    if re.search(r"<section\b[^>]*\bdata-op-empty\s*=\s*['\"]true['\"]", source, re.I):
        errors.append(f"{page.name}: static HTML still contains an explicitly empty CMS section; remove the section, not just hide it with CSS.")

    # User-facing empty-state copy must not survive in released HTML. Maps may
    # remain only when their real embed/link is present; it is not an admin slot.
    if re.search(r"Switch this section on in the admin when you want to show a map\.", source, re.I):
        errors.append(f"{page.name}: visible map-admin placeholder copy remains.")
    if re.search(r"<p\b[^>]*>\s*Related reading\.\s*</p>", source, re.I):
        errors.append(f"{page.name}: generic 'Related reading.' filler remains in a related-content card.")
    for map_section in re.finditer(r"<section\b(?=[^>]*\bdata-op-source\s*=\s*['\"]map['\"])[^>]*>", source, re.I):
        if re.search(r"\bdata-hidden\s*=\s*['\"]true['\"]", map_section.group(0), re.I):
            errors.append(f"{page.name}: map section is still marked hidden although the static page contains a real map embed.")

    # Internal performance cards must not present undocumented client-book,
    # audit, uplift or benchmark numbers as proof.
    stat_card_re = re.compile(
        r'<div class="insight-stat">\s*<div class="insight-stat__value">([\s\S]*?)</div>\s*<div class="insight-stat__label">([\s\S]*?)</div>\s*</div>',
        re.I,
    )
    for stat_card in stat_card_re.finditer(source):
        label = text_content(stat_card.group(2)).lower()
        if re.search(
            r"in our \d{4}|by our \d{4}|we routinely see|we've (?:audited|worked on|seen)|"
            r"accounts we've|agencies we audit|we would describe|our best estimate|in our book|"
            r"in our scrape|in our audits|case tracking|three-year client retention in agencies|"
            r"share of a mid-market gcc clinic's visits|reported share .* from gcc capital",
            label,
            re.I,
        ):
            errors.append(f"{page.name}: unsupported first-party performance statistic remains: {label[:120]!r}.")

    # All pages must use the same two local style layers in the intended order.
    link_tags = re.findall(r"<link\b[^>]*>", source, re.I)
    stylesheet_links = [tag for tag in link_tags if attrs(tag).get("rel", "").lower() == "stylesheet"]
    local_stylesheets = [
        attrs(tag).get("href", "")
        for tag in stylesheet_links
        if attrs(tag).get("href", "").startswith("styles/")
    ]
    theme_version = "20261018a" if page.name == "index.html" else "20261016c"
    expected_styles = ["styles/glass.css?v=20261016c", f"styles/OrcaPulse.css?v={theme_version}"]
    if local_stylesheets != expected_styles:
        errors.append(f"{page.name}: expected only the shared structural and brand CSS in order {expected_styles}; found {local_stylesheets}.")
    if "styles/custom-design.css" in source or "styles/template-design.css" in source:
        errors.append(f"{page.name}: redundant empty stylesheet import remains.")

    # Empty dynamic-content anchors are allowed only when they render visible content.
    for match in re.finditer(r"<!--\s*OP-(COLLECTION|BLOG):[^>]*-->([\s\S]*?)<!--\s*/OP-\1\s*-->", source, re.I):
        body = match.group(2)
        meaningful_text = text_content(body)
        meaningful_element = re.search(r"<(?:img|svg|video|iframe|canvas|a|button|input|select|textarea)\b", body, re.I)
        if not meaningful_text and not meaningful_element:
            errors.append(f"{page.name}: empty OP-{match.group(1).upper()} content slot remains between CMS markers.")

    # All primary conversion buttons should go to a booking provider. Ordinary
    # in-page navigation links and form submit buttons are not treated as CTAs.
    for m in CTA_RE.finditer(source):
        a = attrs(m.group(1))
        href = a.get("href", "").strip()
        label = text_content(m.group(0))
        if not (href.startswith("https://calendly.com/") or href.startswith("https://cal.com/")):
            errors.append(f"{page.name}: primary CTA {label!r} points to {href!r}, not Cal.com/Calendly.")

contact_forms = [forms for name, forms in all_forms if name == "contact.html"]
other_forms = [(name, len(forms)) for name, forms in all_forms if name != "contact.html"]
if len(contact_forms) != 1 or len(contact_forms[0]) != 1:
    errors.append(f"contact.html must contain exactly one public enquiry form; found {sum(len(x) for x in contact_forms)}.")
if other_forms:
    errors.append("Only Contact Us may contain a public form; found forms on: " + ", ".join(f"{name} ({count})" for name, count in other_forms))
if not any(name == "contact.html" for name, _ in all_forms):
    errors.append("contact.html has no enquiry form.")
else:
    contact = Path("contact.html").read_text(encoding="utf-8", errors="replace")
    m = FORM_RE.search(contact)
    if m:
        a = attrs(m.group(1))
        if a.get("id") != "lead-form":
            errors.append("contact.html form must have id='lead-form'.")
        if a.get("action") != "admin/lead.php":
            errors.append("contact.html form must post to admin/lead.php.")
        if not re.search(r'name\s*=\s*["\']source["\']\s+value\s*=\s*["\']contact-page["\']', m.group(2), re.I):
            errors.append("contact.html form must record source=contact-page.")
        if 'id="lead-feedback"' not in m.group(0):
            errors.append("contact.html form must include its accessible success/error feedback region.")
home = Path("index.html").read_text(encoding="utf-8", errors="replace")
if FORM_RE.search(home):
    errors.append("index.html must not contain a public enquiry form; keep its conversion CTA booking-only.")
if "contact.html#contact-form-section" not in home:
    errors.append("Homepage must retain a working link to the Contact Us form section.")

if 'class="pill">Case studies</span>' in home:
    errors.append("Homepage must not present illustrative process cards as published case studies.")

a = Path("styles/glass.css").read_text(encoding="utf-8", errors="replace")
b = Path("styles/OrcaPulse.css").read_text(encoding="utf-8", errors="replace")
for css_name, css_text in [("styles/glass.css", a), ("styles/OrcaPulse.css", b)]:
    if re.search(r"(?m)^\s*(?:html\s*,\s*body|\.page)\s*\{[^}]*overflow-x\s*:\s*(?:hidden|clip)", css_text, re.I):
        errors.append(f"{css_name}: global overflow clipping is masking horizontal-layout defects; fix the overflowing component instead.")

if errors:
    print(f"Conversion/content regression checks FAILED: {len(errors)} issue(s).")
    for error in errors:
        print("- " + error)
    sys.exit(1)

print(f"Conversion/content regression checks passed: {len(pages)} HTML pages scanned.")
print("- Contact Us is the sole public form.")
print("- All primary CTA links point to Cal.com or Calendly.")
print("- No empty CMS sections, map-admin placeholders, generic related-copy filler or unsupported first-party metric cards remain.")

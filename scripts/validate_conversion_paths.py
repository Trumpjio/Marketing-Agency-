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

    # All pages must use the same two local style layers in the intended order.
    stylesheet_links = re.findall(r'<link\\b[^>]*rel=["\\']stylesheet["\\'][^>]*>', source, re.I)
    local_stylesheets = []
    for link in stylesheet_links:
        match = re.search(r'\\bhref=["\\']([^"\\']+)["\\']', link, re.I)
        if match and match.group(1).startswith("styles/"):
            local_stylesheets.append(match.group(1))
    expected_styles = ["styles/glass.css?v=20261016c", "styles/OrcaPulse.css?v=20261016c"]
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

if errors:
    print(f"Conversion/content regression checks FAILED: {len(errors)} issue(s).")
    for error in errors:
        print("- " + error)
    sys.exit(1)

print(f"Conversion/content regression checks passed: {len(pages)} HTML pages scanned.")
print("- Contact Us is the sole public form.")
print("- All primary CTA links point to Cal.com or Calendly.")
print("- No empty CMS sections or empty comment-delimited content slots remain.")

#!/usr/bin/env python3
"""Remove empty public content slots and normalize editorial visuals.

Only known placeholder copy and explicit empty CMS regions are removed. This
script does not fabricate reviews, client names, awards, metrics, or case
studies. Decorative SVGs are used for editorial artwork, never presented as
performance proof.
"""
from html import unescape
from pathlib import Path
import re

ROOT = Path(".")
HTML_FILES = sorted(ROOT.glob("*.html"))

SECTION_OPEN = re.compile(r"<section\b[^>]*>", re.I)
SECTION_TOKEN = re.compile(r"</?section\b[^>]*>", re.I)
EMPTY_MARKED = re.compile(r"<section\b(?=[^>]*\bdata-op-empty\s*=\s*['\"]true['\"])[^>]*>", re.I)
SLOT_PAIR = re.compile(
    r"<!--\s*OP-(COLLECTION|BLOG):[^>]*-->([\s\S]*?)<!--\s*/OP-\1\s*-->",
    re.I,
)
RELATED_READING = re.compile(r"<p\b[^>]*>\s*Related reading\.\s*</p>", re.I)
MAP_ADMIN_HINT = re.compile(
    r"<p\b[^>]*>\s*Switch this section on in the admin when you want to show a map\.\s*</p>",
    re.I,
)
SECTION_HEAD = re.compile(
    r"<div\b(?=[^>]*\bclass\s*=\s*['\"][^'\"]*\bsection-head\b[^'\"]*['\"])[^>]*>[\s\S]*?</div\s*>",
    re.I,
)
SCRIPTS_STYLES = re.compile(r"<(?:script|style)\b[\s\S]*?</(?:script|style)\s*>", re.I)
TAGS = re.compile(r"<[^>]+>")
SPACE = re.compile(r"\s+")

VISUAL_SYMBOL = re.compile(
    r"<div\b(?=[^>]*\bclass\s*=\s*['\"][^'\"]*\barticle-visual__symbol\b[^'\"]*['\"])[^>]*>[\s\S]*?</div\s*>",
    re.I,
)
VISUAL_LABEL = re.compile(
    r"<div\b(?=[^>]*\bclass\s*=\s*['\"][^'\"]*\barticle-visual__label\b[^'\"]*['\"])[^>]*>([\s\S]*?)</div\s*>",
    re.I,
)

EDITORIAL_ASSETS = {
    "playbook": ("editorial-playbook.svg", "Playbooks"),
    "field": ("editorial-field-notes.svg", "Field notes"),
    "essay": ("editorial-essay.svg", "Essays"),
    "teardown": ("editorial-teardown.svg", "Teardowns"),
    "benchmark": ("editorial-benchmark.svg", "Benchmarks"),
    "corridor": ("editorial-corridor.svg", "Corridor"),
}
EMPTY_SLOT_SOURCES = {
    "team", "teams", "award", "awards", "testimonial", "testimonials",
    "case-study", "case-studies", "client-work", "related-services",
    "popular-posts", "popular-articles", "recent-posts", "featured-posts",
    "people", "proof", "results", "blog", "services", "map",
}
PLACEHOLDER_COPY = re.compile(
    r"^(?:"
    r"switch this section on in the admin(?: when you want to show .*)?|"
    r"(?:add|choose|select|populate) (?:your |the )?(?:team|awards?|testimonials?|case studies|client logos|popular posts|related services)(?: in (?:the )?admin)?|"
    r"(?:no|there are no) (?:team members|awards?|testimonials?|case studies|related services|popular posts)(?: yet)?|"
    r"coming soon|lorem ipsum|content will appear here"
    r")\.?$",
    re.I,
)


def visible_text(source: str) -> str:
    source = re.sub(r"<!--[\s\S]*?-->", " ", source)
    source = SCRIPTS_STYLES.sub(" ", source)
    source = TAGS.sub(" ", source)
    return SPACE.sub(" ", unescape(source).replace("\xa0", " ")).strip()


def section_end(source: str, start: int) -> int:
    """Return end offset for a section, accounting for nested section tags."""
    depth = 0
    for match in SECTION_TOKEN.finditer(source, start):
        token = match.group(0)
        if token.lower().startswith("</"):
            depth -= 1
            if depth == 0:
                return match.end()
        elif not token.rstrip().endswith("/>"):
            depth += 1
    return len(source)


def remove_matching_sections(source: str, predicate):
    removed = 0
    cursor = 0
    while True:
        found = None
        for match in SECTION_OPEN.finditer(source, cursor):
            if predicate(match.group(0)):
                found = match
                break
        if found is None:
            break
        end = section_end(source, found.start())
        source = source[:found.start()] + source[end:]
        cursor = found.start()
        removed += 1
    return source, removed


def source_name(open_tag: str) -> str:
    match = re.search(r"\bdata-op-source\s*=\s*(['\"])(.*?)\1", open_tag, re.I)
    return unescape(match.group(2)).strip().lower() if match else ""


def has_content_or_media(body: str) -> bool:
    if re.search(r"<(?:img|svg|picture|video|iframe|canvas|a|button|input|select|textarea|details|table|ul|ol)\b", body, re.I):
        return True
    text = visible_text(SECTION_HEAD.sub(" ", body))
    return bool(text)


def remove_empty_source_sections(source: str):
    removed = 0
    cursor = 0
    while True:
        found = None
        for match in SECTION_OPEN.finditer(source, cursor):
            key = source_name(match.group(0))
            if key in EMPTY_SLOT_SOURCES:
                found = match
                break
        if found is None:
            break
        end = section_end(source, found.start())
        body = source[found.end():max(found.end(), end - len("</section>"))]
        # Keep legitimate content. Strip admin-facing empty-state text before
        # assessing whether there is any actual content to display.
        body_without_hint = MAP_ADMIN_HINT.sub("", body)
        body_without_hint = re.sub(
            r"<p\b[^>]*>\s*(?:Add|Choose|Select|Populate).{0,100}</p>",
            "",
            body_without_hint,
            flags=re.I,
        )
        body_without_hint = SLOT_PAIR.sub(
            lambda m: m.group(0) if has_content_or_media(m.group(2)) else "",
            body_without_hint,
        )
        if not has_content_or_media(body_without_hint):
            source = source[:found.start()] + source[end:]
            cursor = found.start()
            removed += 1
        else:
            cursor = end
    return source, removed


def editorial_asset(source: str, symbol_start: int) -> tuple[str, str]:
    # The visual label sits immediately before each symbol inside an article
    # visual, including cards in insights.html.
    before = source[max(0, symbol_start - 420):symbol_start]
    labels = list(VISUAL_LABEL.finditer(before))
    label = unescape(TAGS.sub("", labels[-1].group(1))).strip() if labels else "Playbooks"
    lower = label.lower()
    if "field" in lower:
        key = "field"
    elif "essay" in lower:
        key = "essay"
    elif "teardown" in lower:
        key = "teardown"
    elif "benchmark" in lower:
        key = "benchmark"
    elif "corridor" in lower or "city" in lower:
        key = "corridor"
    else:
        key = "playbook"
    filename, display_label = EDITORIAL_ASSETS[key]
    return f"assets/illustrations/{filename}", display_label


def normalize_editorial_art(path: Path, source: str):
    if path.name != "insights.html" and not path.name.startswith("insight-"):
        return source, 0
    matches = list(VISUAL_SYMBOL.finditer(source))
    changed = 0
    for match in reversed(matches):
        asset, label = editorial_asset(source, match.start())
        image = (
            f'<img class="article-visual__art" src="{asset}" '
            f'alt="Abstract editorial illustration for the {label} topic" '
            'loading="lazy" decoding="async">'
        )
        source = source[:match.start()] + image + source[match.end():]
        changed += 1
    return source, changed


def main():
    page_count = 0
    changed_pages = 0
    empty_marked_removed = 0
    empty_source_removed = 0
    empty_slots_removed = 0
    map_hints_removed = 0
    related_blurbs_removed = 0
    visuals_added = 0
    homepage_replacements = 0
    unsupported_claims_corrected = 0

    for path in HTML_FILES:
        original = path.read_text(encoding="utf-8", errors="replace")
        updated = original
        page_count += 1

        updated, count = remove_matching_sections(
            updated,
            lambda tag: bool(EMPTY_MARKED.search(tag)),
        )
        empty_marked_removed += count

        # Paired CMS slots that contain nothing should not reserve vertical
        # space. After removing those markers, remove their parent CMS section
        # too when it has no usable content/media left.
        def remove_if_empty(match):
            nonlocal empty_slots_removed
            if not has_content_or_media(match.group(2)):
                empty_slots_removed += 1
                return ""
            return match.group(0)

        updated = SLOT_PAIR.sub(remove_if_empty, updated)
        updated, count = remove_empty_source_sections(updated)
        empty_source_removed += count

        # Map embeds already exist in these templates. Remove the misleading
        # admin instruction and the unused hidden flag, not the live map.
        updated, count = MAP_ADMIN_HINT.subn("", updated)
        map_hints_removed += count
        def normalize_map_tag(match):
            tag = match.group(0)
            return re.sub(r"\sdata-hidden\s*=\s*(['\"])true\1", "", tag, flags=re.I)
        updated = re.sub(
            r"<section\b(?=[^>]*\bdata-op-source\s*=\s*['\"]map['\"])[^>]*>",
            normalize_map_tag,
            updated,
            flags=re.I,
        )

        updated, count = RELATED_READING.subn("", updated)
        related_blurbs_removed += count

        # The homepage cards describe methods, not disclosed client case
        # studies. Say that clearly rather than imply quantified proof.
        homepage_copy = [
            ('<span class="pill">Case studies</span>', '<span class="pill">Illustrative workstreams</span>'),
            ('<h2>Build visibility.<br>Earn trust. Drive conversions.</h2>', '<h2>Make growth work as a connected system.</h2>'),
            ('<p>How a focused search and content strategy connects your business with the buyers already looking for what you sell.</p>',
             '<p>These examples show how we structure search, content and acquisition work. They are illustrative approaches—not published client case studies or performance claims.</p>'),
            ('<div class="OrcaPulse-case__logo"><span style="font-size:37px;letter-spacing:-2px;">OrcaPulse</span></div>',
             '<div class="OrcaPulse-case__logo"><span class="OrcaPulse-case__mark">↗</span><span>Search &amp; Discovery<br><small>Intent-led acquisition</small></span></div>'),
        ]
        for old, new in homepage_copy:
            count = updated.count(old)
            if count:
                homepage_replacements += count
                updated = updated.replace(old, new)

        # Replace a broad industry benchmark with the specific diagnostic
        # work performed. Do not imply every account has the same savings.
        old_claim = "A forensic look at every account, pixel, event and conversion. Most agencies find 20–40% of spend being wasted here."
        new_claim = "A structured review of every account, pixel, event and conversion. We trace tracking gaps, waste signals and attribution blind spots before recommending any budget changes."
        count = updated.count(old_claim)
        if count:
            unsupported_claims_corrected += count
            updated = updated.replace(old_claim, new_claim)

        updated, count = normalize_editorial_art(path, updated)
        visuals_added += count

        if updated != original:
            path.write_text(updated, encoding="utf-8")
            changed_pages += 1
            print(f"Normalized {path.name}")

    print(f"Content-slot normalization scanned {page_count} HTML pages.")
    print(f"Removed {empty_marked_removed} explicitly empty CMS sections.")
    print(f"Removed {empty_source_removed} empty source-driven sections.")
    print(f"Removed {empty_slots_removed} empty comment-delimited CMS slots.")
    print(f"Removed {map_hints_removed} misleading map placeholder paragraphs.")
    print(f"Removed {related_blurbs_removed} generic 'Related reading' placeholders.")
    print(f"Added {visuals_added} category-specific editorial SVG images.")
    print(f"Corrected {homepage_replacements} homepage proof-label/copy fragments.")
    print(f"Reworded {unsupported_claims_corrected} broad spend-waste claims.")
    print(f"Content-slot normalization changed {changed_pages} HTML pages.")


if __name__ == "__main__":
    main()

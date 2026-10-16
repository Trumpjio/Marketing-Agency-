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



# These pages contained internal performance ratios without a public source,
# sample size or measurement window. Preserve the editorial point, not an
# unsupported number. Replace the entire visual stats row with measurement
# methods that a reader could actually reproduce.
METRIC_GUIDANCE = {
    "insight-arabic-seo-what-works.html": [
        ("Query set", "Compare local and global domains across the same commercial queries, language variants, market and search date."),
        ("Script variants", "Track MSA, Gulf dialect, English, Arabizi and mixed-script queries separately; do not assume they behave alike."),
        ("Local presence", "Audit the map pack, business profile and local-intent features for the same priority-query set.")
    ],
    "insight-one-metric-retention.html": [
        ("Recommendation log", "Record each evidence-backed recommendation, the rationale and whether the client acted on it."),
        ("Quarterly review", "Review documented observations alongside renewal cohorts; do not infer causation from correlation alone."),
        ("Research cadence", "Schedule external benchmarking and internal diagnostics into the account's operating rhythm.")
    ],
    "insight-linkedin-gcc-b2b-playbook.html": [
        ("Executive voice", "Compare named-executive and company-page posts using the same audience, period and content format."),
        ("Pipeline cycle", "Track sourced and assisted opportunities through the complete enterprise buying cycle."),
        ("Format test", "Compare video, captions and language using qualified conversations—not a universal reach multiple.")
    ],
    "insight-real-estate-marketing-dubai-playbook.html": [
        ("Lead quality", "Compare cost per qualified lead using one definition, attribution window and reporting period."),
        ("Creative test", "Test Reels and static creative against qualified gallery visits rather than assuming a fixed uplift."),
        ("Channel mix", "Build the mix from CRM-attributed qualified visits; separate first-touch and assisted influence.")
    ],
    "insight-dental-clinic-marketing-gcc.html": [
        ("Local discovery", "Measure local-result visibility for a defined set of service and location queries."),
        ("Review quality", "Track review recency, relevance and legitimate patient feedback alongside local visibility."),
        ("First response", "Set a working-hours response target, then compare actual response time with qualified appointment outcomes.")
    ],
    "insight-aeo-geo-in-practice.html": [
        ("Citation share", "Run a fixed set of high-intent queries and record whether the brand is cited, by engine and date."),
        ("Answer coverage", "Track which priority questions return answer surfaces and whether the content answers them directly."),
        ("Search overlap", "Measure traditional ranking and answer-engine citations separately; do not assume one predicts the other.")
    ]
}
DIV_TOKEN = re.compile(r"</?div\b[^>]*>", re.I)
STATS_OPEN = re.compile(
    r"<div\b(?=[^>]*\bclass\s*=\s*(['\"])[^'\"]*\binsight-stats\b[^'\"]*\1)[^>]*>",
    re.I
)


def div_block_end(source: str, start: int) -> int:
    """Find the matching closing </div> for a div at start, including nesting."""
    first = re.search(r"<div\b[^>]*>", source[start:], re.I)
    if not first:
        return start
    begin = start + first.start()
    depth = 0
    for match in DIV_TOKEN.finditer(source, begin):
        if match.group(0).lower().startswith("</"):
            depth -= 1
            if depth == 0:
                return match.end()
        else:
            depth += 1
    return len(source)


def replace_unverified_stats(path: Path, source: str):
    guidance = METRIC_GUIDANCE.get(path.name)
    if not guidance:
        return source, 0
    openings = list(STATS_OPEN.finditer(source))
    if not openings:
        return source, 0
    replacement = (
        '<div class="insight-stats reveal">\n'
        + "\n".join(
            f'  <div class="insight-stat"><div class="insight-stat__value">{title}</div>'
            f'<div class="insight-stat__label">{label}</div></div>'
            for title, label in guidance
        )
        + "\n</div>"
    )
    # Replace the first metric row only. Re-run is idempotent because the
    # generated cards do not contain an additional .insight-stats wrapper.
    match = openings[0]
    end = div_block_end(source, match.start())
    return source[:match.start()] + replacement + source[end:], 1


def replace_unsupported_copy(path: Path, source: str):
    replacements = {
        "insight-arabic-seo-what-works.html": [
            ("Google in Arabic now ranks GCC domains above global generalist domains for roughly 70% of commercial GCC queries we track.",
             "Arabic search strategy should account for local domains, dialect and intent; validate patterns against a dated query set for each target market."),
            ("Hosting in the GCC region, with a GCC CDN edge, and a correctly configured ccTLD (.ae, .sa, .qa, .kw, .bh), produces a measurable ranking uplift against identical content on a .com hosted in Europe.",
             "Choose hosting for reliability and measured user experience. Assess page speed, crawlability, hreflang and geographic relevance separately; a hosting region or ccTLD alone does not guarantee a ranking gain.")
        ],
        "insight-one-metric-retention.html": [
            ("The one metric that decides whether a client stays.", "A question that can strengthen client retention."),
            ("The one metric that predicts whether a client stays is not ROAS, not CPL, not brand lift — it is &quot;did we show them something unexpected this quarter?&quot;",
             "One useful retention question is whether the agency surfaced an evidence-backed observation the client had not considered that quarter; it should be evaluated alongside performance, service quality and renewal data."),
            ("The one metric that actually predicts whether a client stays is a question, not a number:",
             "A useful question for a client-retention review is not a single predictor, but:"),
            ("The relationship is predictive.", "Use the relationship as a hypothesis to test against a defined renewal cohort."),
            ("Your repeat-purchase rate on the Riyadh customer cohort is 2.3x the Dubai cohort, but you are spending 4x more in Dubai acquisition. We think you have a geography-mix problem.",
             "Compare repeat-purchase rates across Riyadh and Dubai cohorts with acquisition spend; if the observed difference holds after checking sample size and attribution, investigate the geography mix before reallocating budget."),
            ("your email conversion dropped 40% in the last two months",
             "your email conversion dropped materially over the last two months")
        ],
        "insight-linkedin-gcc-b2b-playbook.html": [
            ("The reach is 10x lower and the content reads as corporate.",
             "It can be harder to establish personal credibility from a company page alone; compare reach and qualified conversations using a consistent audience and period."),
            ("native video with Arabic captions produces a measurable engagement lift across GCC national audiences",
             "native video with Arabic captions is worth testing with GCC national audiences; compare retention, qualified responses and downstream outcomes against a consistent control")
        ],
        "insight-real-estate-marketing-dubai-playbook.html": [
            ("Static feed creative underperforms Reels by a factor of three to five in 2025 benchmarks.",
             "Compare static feed creative and Reels in controlled tests against cost per qualified lead or booked gallery visit; performance varies by audience, creative and attribution window."),
            ("A useful rule of thumb from our 2024-2025 audit work: in a healthy Dubai developer paid programme, 55-70% of qualified gallery visits are first touched by Google (branded or neighbourhood intent), 15-25% first touched by Meta or YouTube video, and 10-20% first touched by broker-driven offline. If your mix is skewed heavily to Meta, you are probably over-buying the top of the funnel and under-serving the lower.",
             "Build the channel mix from CRM-attributed qualified sales-gallery visits. Define the attribution window, distinguish first-touch from assisted influence and compare the measured contribution before changing spend."),
            ("A rival takes your top-of-funnel brand searches for roughly the price of a decent lunch.",
             "Competitors can bid on brand-related queries; review search-term reports and impression share before deciding whether defensive bidding is justified.")
        ],
        "insight-dental-clinic-marketing-gcc.html": [
            ("The three pillars that outperform triple the paid-media budget.",
             "The three pillars that complement paid media."),
            ("Three listings get almost all the clicks.",
             "The map pack can be an important local-discovery surface; confirm its prominence with the clinic's target-query set."),
            ("A clinic with 400 reviews, last one 11 months ago, loses to one with 180 reviews, last one two days ago, every single time.",
             "A high review count may not compensate for stale feedback. Assess recency, relevance, prominence and conversion quality together."),
            ("a review-collection system that produces 10-20 new reviews per month, every month, forever",
             "a review-collection process that consistently invites genuine patient feedback without incentives or pressure"),
            ("direct-billing arrangements with Daman, AXA, Oman Insurance, Bupa Arabia, Allianz and the big TPA networks drive 40-70% of a mid-market clinic's visits.",
             "direct-billing arrangements can be a meaningful acquisition and retention channel, but their contribution should be measured from verified appointment and visit data rather than assumed from partner names alone.")
        ],
        "insight-aeo-geo-in-practice.html": [
            ("A working teardown of how one B2B brand in Riyadh went from invisible to cited across Perplexity, Google AI Overviews and ChatGPT Search.",
             "A practical teardown of the technical and editorial signals that can make B2B content easier for answer engines to find, interpret and cite.")
        ],
        "insights.html": [
            ("From invisible to cited across Perplexity, AI Overviews and ChatGPT Search.",
             "A practical guide to helping answer engines find, interpret and cite B2B content.")
        ]
    }
    changes = 0
    for old, new in replacements.get(path.name, []):
        count = source.count(old)
        if count:
            source = source.replace(old, new)
            changes += count
    return source, changes


UNVERIFIED_FIRST_PARTY = re.compile(
    r"\b(?:in our \d{4}|our \d{4}[-–]\d{2} (?:audit|case|benchmark)|"
    r"by our \d{4}|we (?:routinely )?see|we've (?:audited|worked on|seen)|"
    r"accounts we've|agencies we audit|we would describe|our best estimate|"
    r"in our book|in our scrape|in our audits|in our case tracking|"
    r"categories we track|by our .*audit|our audit work|in the categories we track|"
    r"in our \d{4}[-–]\d{2} audit|in our clients|our 2024|"
    r"three-year client retention in agencies|"
    r"share of a mid-market gcc clinic's visits|"
    r"rule of thumb: \$1 of extra qualified|"
    r"hotel audit,? \d{4}|"
    r"reported share .* from gcc capital)",
    re.I,
)
STAT_CARD = re.compile(
    r'<div class="insight-stat">\s*'
    r'<div class="insight-stat__value">([\s\S]*?)</div>\s*'
    r'<div class="insight-stat__label">([\s\S]*?)</div>\s*</div>',
    re.I,
)


def replace_unverified_metric_cards(source: str):
    corrected = 0

    def rewrite(match):
        nonlocal corrected
        value = visible_text(match.group(1))
        label = visible_text(match.group(2))
        if not UNVERIFIED_FIRST_PARTY.search(label):
            return match.group(0)

        combined = (value + " " + label).lower()
        if any(term in combined for term in ("three-year client retention in agencies without", "client retention in agencies without")):
            title = "Cohort comparison"
            detail = "Compare retention cohorts only when cohort definitions, sample sizes, measurement period and confounding factors are documented."
        elif any(term in combined for term in ("retention", "client book", "client retention")):
            title = "Retention cohort"
            detail = "Define the cohort and period; compare renewals with documented recommendations and service context without implying causation."
        elif any(term in combined for term in ("reach", "engagement", "uplift", "cpm", "creative", "video", "reels")):
            title = "Matched creative test"
            detail = "Compare formats using the same audience, spend, dates and conversion definition; report a numerical difference only when the source data can be shared."
        elif any(term in combined for term in ("margin", "over-budget", "fee premium", "hourly", "cost efficiency")):
            title = "Scope comparison"
            detail = "Compare equivalent scopes, direct costs and delivery periods; disclose the sample and method before claiming a margin or pricing effect."
        elif any(term in combined for term in ("insurer", "visits", "billing", "appointment", "clinic")):
            title = "Channel contribution"
            detail = "Measure verified appointments and visits by acquisition path and period rather than inferring results from a channel's availability."
        elif any(term in combined for term in ("market", "capital flow", "trade value", "purchasing power", "population")):
            title = "Market baseline"
            detail = "Use a dated public source and define the geography and scope before publishing a market-size or cross-border-flow estimate."
        elif any(term in combined for term in ("best estimate", "when agencies", "will be", "2027", "2028")):
            title = "Scenario to test"
            detail = "Treat forward-looking timing as a hypothesis, not a forecast, until a reproducible source supports it."
        else:
            title = "Evidence standard"
            detail = "Publish numerical outcomes only when the source dataset, sample size, date range and metric definition can be substantiated."
        corrected += 1
        return (
            '<div class="insight-stat"><div class="insight-stat__value">'
            + title
            + '</div><div class="insight-stat__label">'
            + detail
            + '</div></div>'
        )

    return STAT_CARD.sub(rewrite, source), corrected


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


        updated, count = replace_unverified_stats(path, updated)
        unsupported_claims_corrected += count
        updated, count = replace_unsupported_copy(path, updated)
        unsupported_claims_corrected += count
        updated, count = replace_unverified_metric_cards(updated)
        unsupported_claims_corrected += count

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

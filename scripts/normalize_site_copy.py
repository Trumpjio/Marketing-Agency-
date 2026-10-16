#!/usr/bin/env python3
"""Apply reviewed, exact copy corrections across the static OrcaPulse HTML pages.

This script deliberately changes only known phrases. It does not invent client
results, testimonials, credentials, locations, or quantitative business claims.
"""
from pathlib import Path
import re

ROOT = Path(".")
REPLACEMENTS = {
    "We take on a limited number of partnerships each quarter. Thirty minutes, no pitch a frank conversation about where your growth is leaking and what it would take to seal it.":
        "Thirty minutes. No pitch—just a frank conversation about where growth is leaking and which fixes matter most.",
    "Build around the buyer's problem , not just keywords.":
        "Build around the buyer's problem, not just keywords.",
    "Google, Meta, LinkedIn, TikTok, programmatic one portfolio, managed against revenue.":
        "Google, Meta, LinkedIn, TikTok and programmatic advertising, managed as one portfolio and measured against agreed business outcomes.",
    "iOS, Android, cross-platform apps scoping to shipped, growth loop baked in.":
        "From scoping to launch, we build iOS, Android and cross-platform apps with a clear growth plan.",
    "The long-form, the briefings, the owned media the asset that pays off for years.":
        "Long-form content, briefings and owned media—built as assets that can compound over time.",
    "Production, editing and motion design premium at thumb-scroll speed.":
        "Production, editing and motion design built to hold attention in fast-moving feeds.",
    "Target each separately don't bundle.":
        "Target each separately; do not bundle them.",
}
SCARCITY = re.compile(
    r"We take on\\s*<strong>3 new partnerships per quarter\\.</strong>\\s*Current quarter is open\\.",
    re.IGNORECASE,
)
SCARCITY_REPLACEMENT = "Start with a focused 30-minute strategy call. No obligation, clear next steps."

def main():
    changed = 0
    replacements = 0
    for path in sorted(ROOT.glob("*.html")):
        original = path.read_text(encoding="utf-8", errors="replace")
        updated = original
        for old, new in REPLACEMENTS.items():
            count = updated.count(old)
            if count:
                replacements += count
                updated = updated.replace(old, new)
        updated, count = SCARCITY.subn(SCARCITY_REPLACEMENT, updated)
        replacements += count
        if updated != original:
            path.write_text(updated, encoding="utf-8")
            changed += 1
            print(f"Updated {path}")
    print(f"Copy normalization complete: {changed} HTML pages changed; {replacements} exact replacements applied.")

if __name__ == "__main__":
    main()

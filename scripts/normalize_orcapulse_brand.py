#!/usr/bin/env python3
"""Normalize legacy Ioka brand/domain references for the OrcaPulse production site."""
from pathlib import Path
import json
import re

ROOT = Path(".")
TEXT_SUFFIXES = {".html", ".php", ".css", ".js", ".json", ".xml", ".txt", ".svg"}
EXCLUDED_PARTS = {".git", "node_modules", "vendor", "reports", "docs", "AUDITS"}

def normalize(text: str) -> str:
    text = re.sub(r"ioka\.online", "orcapulse.io", text, flags=re.IGNORECASE)
    text = re.sub(r"\bioka\b", "OrcaPulse", text, flags=re.IGNORECASE)
    # After removing unverified email/WhatsApp links, also remove the leftover
    # visual wrapper if it contains only a separator and whitespace.
    text = re.sub(
        r'<div\b(?=[^>]*class=["\'][^"\']*\bhero__sub-links\b[^"\']*["\'])[^>]*>\s*(?:&nbsp;\s*·\s*&nbsp;)?\s*</div>',
        '',
        text,
        flags=re.IGNORECASE | re.DOTALL,
    )
    # The Ioka email and zero-filled WhatsApp number were never verified for
    # OrcaPulse. Remove their links rather than inventing replacement details.
    text = re.sub(
        r'<a\b(?=[^>]*\bhref\s*=\s*["\']mailto:hello@orcapulse\.io["\'])[^>]*>.*?</a>',
        '',
        text,
        flags=re.IGNORECASE | re.DOTALL,
    )
    text = re.sub(
        r'<a\b(?=[^>]*\bhref\s*=\s*["\'][^"\']*wa\.me/971500000000[^"\']*["\'])[^>]*>.*?</a>',
        '',
        text,
        flags=re.IGNORECASE | re.DOTALL,
    )
    text = re.sub(
        r'<a\b(?=[^>]*\bhref\s*=\s*["\']tel:\+?971[\s().-]*50[\s().-]*0{3}[\s().-]*0{4}["\'])[^>]*>.*?</a>',
        '',
        text,
        flags=re.IGNORECASE | re.DOTALL,
    )
    text = re.sub(r"hello@orcapulse\.io", "", text, flags=re.IGNORECASE)
    text = re.sub(r"https?://wa\.me/971500000000[^\s\"'<>]*", "", text, flags=re.IGNORECASE)
    text = re.sub(r"\+971\s*50\s*0{3}\s*0{4}|971500000000", "", text, flags=re.IGNORECASE)
    return text

changed = []
for path in ROOT.rglob("*"):
    if not path.is_file() or path.suffix.lower() not in TEXT_SUFFIXES:
        continue
    if any(part in EXCLUDED_PARTS for part in path.parts):
        continue
    try:
        original = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        continue
    updated = normalize(original)
    if path.as_posix() == "data/settings.json":
        data = json.loads(updated)
        data["brand_name"] = "OrcaPulse"
        data["base_url"] = "https://orcapulse.io"
        data["footer_copyright"] = "© 2026 OrcaPulse. All rights reserved."
        # Clear contact values until the owner supplies verified details.
        for key in ("email", "phone", "whatsapp", "address"):
            data[key] = ""
        updated = json.dumps(data, ensure_ascii=False, indent=2) + "\n"
    if path.as_posix() == "robots.txt":
        updated = re.sub(
            r"(?im)^sitemap:\s*https?://[^\s]+",
            "Sitemap: https://orcapulse.io/sitemap.xml",
            updated,
        )
        if not re.search(r"(?im)^sitemap:", updated):
            updated = updated.rstrip() + "\n\nSitemap: https://orcapulse.io/sitemap.xml\n"
    if updated != original:
        path.write_text(updated, encoding="utf-8")
        changed.append(path.as_posix())

leftovers = []
contact_placeholders = []
for path in ROOT.rglob("*"):
    if not path.is_file() or path.suffix.lower() not in TEXT_SUFFIXES:
        continue
    if any(part in EXCLUDED_PARTS for part in path.parts):
        continue
    try:
        text = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        continue
    if re.search(r"ioka\.online|\bioka\b", text, re.IGNORECASE):
        leftovers.append(path.as_posix())
    if re.search(r"hello@orcapulse\.io|wa\.me/971500000000|971500000000|\+971\s*50\s*0{3}\s*0{4}", text, re.IGNORECASE):
        contact_placeholders.append(path.as_posix())

print("Changed files:", len(changed))
print("\n".join(changed))
if leftovers:
    print("Legacy brand/domain references remain:", *leftovers, sep="\n")
if contact_placeholders:
    print("Unverified placeholder contact details remain:", *contact_placeholders, sep="\n")
if leftovers or contact_placeholders:
    raise SystemExit(1)
print("Validation passed: no legacy brand/domain references or known placeholder contact details remain in scanned website text files.")

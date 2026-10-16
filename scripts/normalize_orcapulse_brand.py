#!/usr/bin/env python3
"""Normalize legacy Ioka brand/domain references for the OrcaPulse production site.

This intentionally changes website source files only. It does not invent contact
details or modify audit/research reports.
"""
from pathlib import Path
import json
import re

ROOT = Path(".")
TEXT_SUFFIXES = {".html", ".php", ".css", ".js", ".json", ".xml", ".txt", ".md", ".svg"}
EXCLUDED_PARTS = {".git", "node_modules", "vendor", "reports", "docs"}

def normalize(text: str) -> str:
    text = re.sub(r"ioka\\.online", "orcapulse.io", text, flags=re.IGNORECASE)
    text = re.sub(r"\\bioka\\b", "OrcaPulse", text, flags=re.IGNORECASE)
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
        # Do not publish old/placeholder contact information.
        for key in ("email", "phone", "whatsapp", "address"):
            if not data.get(key) or "ioka" in str(data.get(key)).lower():
                data[key] = ""
        updated = json.dumps(data, ensure_ascii=False, indent=2) + "\\n"
    if path.as_posix() == "robots.txt":
        updated = re.sub(r"(?im)^sitemap:\\s*https?://[^\\s]+", "Sitemap: https://orcapulse.io/sitemap.xml", updated)
        if not re.search(r"(?im)^sitemap:", updated):
            updated = updated.rstrip() + "\\n\\nSitemap: https://orcapulse.io/sitemap.xml\\n"
    if updated != original:
        path.write_text(updated, encoding="utf-8")
        changed.append(path.as_posix())

# Validate high-risk leftovers in website files.
leftovers = []
for path in ROOT.rglob("*"):
    if not path.is_file() or path.suffix.lower() not in TEXT_SUFFIXES:
        continue
    if any(part in EXCLUDED_PARTS for part in path.parts):
        continue
    try:
        text = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        continue
    if re.search(r"ioka\\.online|\\bioka\\b", text, re.IGNORECASE):
        leftovers.append(path.as_posix())

print("Changed files:", len(changed))
print("\\n".join(changed))
if leftovers:
    print("Legacy brand/domain references remain:", *leftovers, sep="\\n")
    raise SystemExit(1)
print("Validation passed: no Ioka brand/domain references remain in scanned website text files.")

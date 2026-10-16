#!/usr/bin/env python3
"""Remove empty stylesheet includes and version the shared brand CSS consistently."""
from pathlib import Path
import re

ROOT = Path(".")
REMOVE = re.compile(
    r'<link\b(?=[^>]*\brel="stylesheet")(?=[^>]*\bhref="styles/(?:custom-design|template-design)\.css(?:\?[^"]*)?")[^>]*>\s*',
    re.I,
)
OLD_VERSION = "20261014b"
NEW_VERSION = "20261016c"

def main():
    changed = 0
    links_removed = 0
    references_updated = 0
    for page in sorted(ROOT.glob("*.html")):
        original = page.read_text(encoding="utf-8", errors="replace")
        content, removed = REMOVE.subn("", original)
        links_removed += removed
        for name in ("glass.css", "OrcaPulse.css"):
            old = f"styles/{name}?v={OLD_VERSION}"
            new = f"styles/{name}?v={NEW_VERSION}"
            count = content.count(old)
            references_updated += count
            content = content.replace(old, new)
        if content != original:
            page.write_text(content, encoding="utf-8")
            changed += 1
            print(f"Updated shared CSS links in {page.name}")
    print(
        f"CSS reference normalization complete: {changed} pages changed; "
        f"{links_removed} empty stylesheet link(s) removed; "
        f"{references_updated} cache-version reference(s) updated."
    )

if __name__ == "__main__":
    main()

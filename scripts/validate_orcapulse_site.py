#!/usr/bin/env python3
"""Validate OrcaPulse static-site structure before Hostinger release."""
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import urlparse, unquote
import json
import re
import xml.etree.ElementTree as ET

ROOT = Path(".")
PRODUCTION_ORIGIN = "https://orcapulse.io"
ASSET_SUFFIXES = {
    ".html", ".css", ".js", ".mjs", ".svg", ".png", ".jpg", ".jpeg",
    ".webp", ".gif", ".ico", ".woff", ".woff2", ".ttf", ".pdf", ".xml",
}
EXCLUDED_DIRS = {".git", "node_modules", "vendor", "reports", "docs", "AUDITS"}

class RefParser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.refs = []
        self.jsonld = []
    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        for key in ("href", "src", "action", "poster"):
            value = attrs.get(key)
            if value:
                self.refs.append((tag, key, value.strip()))
        if tag.lower() == "script" and attrs.get("type", "").lower() == "application/ld+json":
            self.jsonld.append("start")

def ignored(path: Path) -> bool:
    return any(part in EXCLUDED_DIRS for part in path.parts)

def clean_jsonld(value):
    if isinstance(value, dict):
        return {k: clean_jsonld(v) for k, v in value.items() if v != ""}
    if isinstance(value, list):
        return [clean_jsonld(v) for v in value]
    return value

def check_site():
    errors = []
    html_files = sorted(p for p in ROOT.rglob("*.html") if p.is_file() and not ignored(p))
    if not html_files:
        errors.append("No HTML pages found.")

    settings_path = ROOT / "data/settings.json"
    try:
        settings = json.loads(settings_path.read_text(encoding="utf-8"))
        if settings.get("brand_name") != "OrcaPulse":
            errors.append("data/settings.json brand_name must be OrcaPulse.")
        if settings.get("base_url") != PRODUCTION_ORIGIN:
            errors.append("data/settings.json base_url must be https://orcapulse.io.")
    except Exception as exc:
        errors.append(f"data/settings.json is invalid: {exc}")

    # Every URL in the production sitemap must use the canonical production
    # origin and map to a real file in this deployed directory.
    sitemap_path = ROOT / "sitemap.xml"
    sitemap_urls = []
    try:
        sitemap_root = ET.parse(sitemap_path).getroot()
        ns = {"sm": "http://www.sitemaps.org/schemas/sitemap/0.9"}
        sitemap_urls = [n.text.strip() for n in sitemap_root.findall(".//sm:loc", ns) if n.text]
        if not sitemap_urls:
            errors.append("sitemap.xml has no <loc> entries.")
        for url in sitemap_urls:
            parsed = urlparse(url)
            if parsed.scheme != "https" or parsed.netloc.lower() != "orcapulse.io":
                errors.append(f"Sitemap URL is not on the canonical production origin: {url}")
                continue
            rel = unquote(parsed.path.lstrip("/")) or "index.html"
            target = ROOT / rel
            if not target.is_file():
                errors.append(f"Sitemap URL does not map to a file: {url} -> {rel}")
    except Exception as exc:
        errors.append(f"sitemap.xml is invalid: {exc}")

    # Verify local links and assets do not point at nonexistent files.
    checked_refs = 0
    for html_path in html_files:
        html = html_path.read_text(encoding="utf-8", errors="replace")
        parser = RefParser()
        parser.feed(html)
        for tag, attr, raw in parser.refs:
            value = raw.strip()
            if not value or value.startswith("#"):
                continue
            parsed = urlparse(value)
            if parsed.scheme or parsed.netloc:
                if parsed.scheme.lower() in {"http", "https"} and parsed.netloc.lower() in {"ioka.online", "www.ioka.online", "orcapulse.com", "www.orcapulse.com"}:
                    errors.append(f"{html_path}: legacy host in {attr}: {value}")
                continue
            local_path = unquote(parsed.path)
            if not local_path:
                continue
            if local_path.startswith("/"):
                local_path = local_path.lstrip("/")
            else:
                local_path = (html_path.parent / local_path).as_posix()
            candidate = ROOT / local_path
            if Path(local_path).suffix.lower() not in ASSET_SUFFIXES:
                continue
            checked_refs += 1
            if not candidate.is_file():
                errors.append(f"{html_path}: missing local {attr} target: {raw} (checked {local_path})")

        # Validate every JSON-LD block and remove empty-valued properties
        # from structured data to avoid publishing blank email/address fields.
        def replace_jsonld(match):
            raw = match.group(2)
            try:
                parsed = json.loads(raw)
                return match.group(1) + json.dumps(clean_jsonld(parsed), ensure_ascii=False, separators=(",", ":")) + match.group(3)
            except Exception as exc:
                errors.append(f"{html_path}: invalid JSON-LD: {exc}")
                return match.group(0)
        cleaned = re.sub(
            r'(<script\\b[^>]*type=["\\\']application/ld\\+json["\\\'][^>]*>)(.*?)(</script\\s*>)',
            replace_jsonld,
            html,
            flags=re.IGNORECASE | re.DOTALL,
        )
        if cleaned != html:
            html_path.write_text(cleaned, encoding="utf-8")

    # robots.txt must advertise the sitemap that this build actually ships.
    robots = (ROOT / "robots.txt").read_text(encoding="utf-8")
    if "Sitemap: https://orcapulse.io/sitemap.xml" not in robots:
        errors.append("robots.txt must declare Sitemap: https://orcapulse.io/sitemap.xml.")
    if re.search(r"ioka\\.online|orcapulse\\.com", robots, re.IGNORECASE):
        errors.append("robots.txt still includes a legacy host.")

    print(f"HTML pages: {len(html_files)}")
    print(f"Sitemap URLs: {len(sitemap_urls)}")
    print(f"Local HTML/asset references checked: {checked_refs}")
    if errors:
        print("Release validation errors:")
        for err in errors:
            print(f"- {err}")
        raise SystemExit(1)
    print("Release validation passed.")

if __name__ == "__main__":
    check_site()

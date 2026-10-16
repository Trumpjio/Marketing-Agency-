#!/usr/bin/env python3
"""Validate OrcaPulse static-site structure before Hostinger release."""
from html import unescape
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
    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        for key in ("href", "src", "action", "poster"):
            value = attrs.get(key)
            if value:
                self.refs.append((tag, key, value.strip()))

def ignored(path: Path) -> bool:
    return any(part in EXCLUDED_DIRS for part in path.parts)

def attr(tag: str, name: str):
    match = re.search(r"\b" + re.escape(name) + r"\s*=\s*([\"'])(.*?)\1", tag, re.IGNORECASE | re.DOTALL)
    return match.group(2).strip() if match else ""

def plain_text(markup: str) -> str:
    return re.sub(r"\s+", " ", unescape(re.sub(r"<[^>]+>", " ", markup))).strip()

def clean_jsonld(value):
    if isinstance(value, dict):
        obj = dict(value)
        # These URLs are service-area landing pages, not verified staffed offices.
        # A Service node with areaServed is truthful without implying a local address.
        if obj.get("@type") == "LocalBusiness":
            obj["@type"] = "Service"
            service_url = obj.get("url", "")
            obj["@id"] = service_url + "#service" if service_url else "https://orcapulse.io/#service"
            obj["provider"] = obj.pop("parentOrganization", {"@id": "https://orcapulse.io/#organization"})
            obj["serviceType"] = "Marketing services"
        return {k: clean_jsonld(v) for k, v in obj.items() if v != ""}
    if isinstance(value, list):
        return [clean_jsonld(v) for v in value if v != ""]
    return value

def set_article_metadata(path: Path, html: str) -> str:
    if not path.name.startswith("insight-"):
        return html

    canonical = ""
    image_url = ""
    description = ""
    title = ""
    for tag in re.findall(r"<link\b[^>]*>", html, re.IGNORECASE):
        if attr(tag, "rel").lower() == "canonical":
            canonical = attr(tag, "href")
            break
    for tag in re.findall(r"<meta\b[^>]*>", html, re.IGNORECASE):
        name = attr(tag, "name").lower()
        prop = attr(tag, "property").lower()
        if name == "description":
            description = attr(tag, "content")
        elif prop == "og:image":
            image_url = attr(tag, "content")
    h1 = re.search(r"<h1\b[^>]*>(.*?)</h1\s*>", html, re.IGNORECASE | re.DOTALL)
    page_title = re.search(r"<title\b[^>]*>(.*?)</title\s*>", html, re.IGNORECASE | re.DOTALL)
    title = plain_text(h1.group(1)) if h1 else (plain_text(page_title.group(1)) if page_title else path.stem.replace("-", " "))

    if not canonical:
        canonical = f"{PRODUCTION_ORIGIN}/{path.as_posix()}"
    if not re.search(r'<meta\b(?=[^>]*property=["\']og:url["\'])', html, re.IGNORECASE):
        tag = f'<meta property="og:url" content="{canonical}" />\n'
        html = re.sub(r"</head\s*>", tag + "</head>", html, count=1, flags=re.IGNORECASE)

    existing_article_type = re.search(r'"@type"\s*:\s*"(?:Article|BlogPosting|NewsArticle)"', html, re.IGNORECASE)
    if not existing_article_type:
        article = {
            "@context": "https://schema.org",
            "@type": "BlogPosting",
            "headline": title,
            "mainEntityOfPage": {"@type": "WebPage", "@id": canonical},
            "url": canonical,
            "publisher": {"@type": "Organization", "@id": f"{PRODUCTION_ORIGIN}/#organization", "name": "OrcaPulse"},
        }
        if description:
            article["description"] = description
        if image_url:
            article["image"] = [image_url]
        block = '<script type="application/ld+json">' + json.dumps(article, ensure_ascii=False, separators=(",", ":")) + "</script>\n"
        html = re.sub(r"</head\s*>", block + "</head>", html, count=1, flags=re.IGNORECASE)
    return html

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
            if not (ROOT / rel).is_file():
                errors.append(f"Sitemap URL does not map to a file: {url} -> {rel}")
    except Exception as exc:
        errors.append(f"sitemap.xml is invalid: {exc}")

    checked_refs = 0
    changed_html = 0
    converted_localbusiness = 0
    article_metadata_added = 0

    for html_path in html_files:
        original = html_path.read_text(encoding="utf-8", errors="replace")
        html = set_article_metadata(html_path, original)
        if html != original:
            article_metadata_added += 1
        before_localbusiness = html.count('"@type":"LocalBusiness"') + html.count('"@type": "LocalBusiness"')

        def replace_jsonld(match):
            raw = match.group(2)
            try:
                parsed = json.loads(raw)
                return match.group(1) + json.dumps(clean_jsonld(parsed), ensure_ascii=False, separators=(",", ":")) + match.group(3)
            except Exception as exc:
                errors.append(f"{html_path}: invalid JSON-LD: {exc}")
                return match.group(0)

        html = re.sub(
            r'(<script\b[^>]*type=["\']application/ld\+json["\'][^>]*>)(.*?)(</script\s*>)',
            replace_jsonld,
            html,
            flags=re.IGNORECASE | re.DOTALL,
        )
        if before_localbusiness:
            converted_localbusiness += before_localbusiness

        parser = RefParser()
        parser.feed(html)
        for tag, key, raw in parser.refs:
            value = raw.strip()
            if not value or value.startswith("#"):
                continue
            parsed = urlparse(value)
            if parsed.scheme or parsed.netloc:
                if parsed.scheme.lower() in {"http", "https"} and parsed.netloc.lower() in {"ioka.online", "www.ioka.online", "orcapulse.com", "www.orcapulse.com"}:
                    errors.append(f"{html_path}: legacy host in {key}: {value}")
                continue
            local_path = unquote(parsed.path)
            if not local_path:
                continue
            local_path = local_path.lstrip("/") if local_path.startswith("/") else (html_path.parent / local_path).as_posix()
            if Path(local_path).suffix.lower() not in ASSET_SUFFIXES:
                continue
            checked_refs += 1
            if not (ROOT / local_path).is_file():
                errors.append(f"{html_path}: missing local {key} target: {raw} (checked {local_path})")

        if html != original:
            html_path.write_text(html, encoding="utf-8")
            changed_html += 1

    robots = (ROOT / "robots.txt").read_text(encoding="utf-8")
    if "Sitemap: https://orcapulse.io/sitemap.xml" not in robots:
        errors.append("robots.txt must declare Sitemap: https://orcapulse.io/sitemap.xml.")
    if re.search(r"ioka\.online|orcapulse\.com", robots, re.IGNORECASE):
        errors.append("robots.txt still includes a legacy host.")

    # Fail if any service-area page continues to publish a LocalBusiness type.
    for html_path in html_files:
        html = html_path.read_text(encoding="utf-8", errors="replace")
        if '"@type":"LocalBusiness"' in html or '"@type": "LocalBusiness"' in html:
            errors.append(f"{html_path}: LocalBusiness schema remains on a service-area page.")

    print(f"HTML pages: {len(html_files)}")
    print(f"Sitemap URLs: {len(sitemap_urls)}")
    print(f"Local HTML/asset references checked: {checked_refs}")
    print(f"HTML files updated with article metadata/JSON-LD cleanup: {changed_html}")
    print(f"Service-area LocalBusiness schema nodes converted: {converted_localbusiness}")
    if errors:
        print("Release validation errors:")
        for err in errors:
            print(f"- {err}")
        raise SystemExit(1)
    print("Release validation passed.")

if __name__ == "__main__":
    check_site()

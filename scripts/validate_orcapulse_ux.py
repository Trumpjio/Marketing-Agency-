#!/usr/bin/env python3
"""Static regression checks for the OrcaPulse UI/UX and conversion fixes."""
from pathlib import Path
import re
import sys

ROOT = Path(".")
failures = []
checks = []

def require(condition, message):
    if condition:
        checks.append(message)
    else:
        failures.append(message)

pages = sorted(ROOT.glob("*.html"))
require(len(pages) >= 80, f"HTML page inventory present ({len(pages)} pages)")

main_js = (ROOT / "scripts/main.js").read_text(encoding="utf-8")
css = (ROOT / "styles/OrcaPulse.css").read_text(encoding="utf-8")
home = (ROOT / "index.html").read_text(encoding="utf-8")
lead_php = (ROOT / "admin/lead.php").read_text(encoding="utf-8")
lead_viewer = (ROOT / "admin/leads.php").read_text(encoding="utf-8")
layout = (ROOT / "admin/_layout.php").read_text(encoding="utf-8")
data_htaccess = (ROOT / "data/.htaccess").read_text(encoding="utf-8")
insights = (ROOT / "insights.html").read_text(encoding="utf-8")
work = (ROOT / "work.html").read_text(encoding="utf-8")
usa = (ROOT / "country-usa.html").read_text(encoding="utf-8")
services = (ROOT / "services.html").read_text(encoding="utf-8")

require("removeEmptyContentSlots" in main_js and 'data-op-empty="true"' in main_js,
        "empty CMS sections are handled before reveal animations")
require("showLeadSubmissionFeedback" in main_js and "lead-feedback" in home,
        "homepage enquiry success/failure feedback is wired")
require("Sending your enquiry" in home and 'id="lead-status"' in home,
        "submission state is separate from the booking link")
require("if (!rate_limit('lead', 10, 600))" in lead_php,
        "lead rate-limit failures are enforced")
require("flock($lockHandle, LOCK_EX)" in lead_php and "tempnam($dir, '.lead-store-')" in lead_php and "@rename($tmp, $file)" in lead_php,
        "lead storage uses a separate lock file and atomic replacement")
require("require_login();" in lead_viewer and "Export CSV" in lead_viewer,
        "lead inbox and CSV export are admin-protected")
require("href=\"leads.php\"" in layout and "Require all denied" in data_htaccess,
        "admin navigation exists and lead data directory is blocked from direct web access")
require("max-width: 760px" in css and "max-width: 420px" in css and "[hidden]" in css,
        "responsive styles cover tablet, phone and hidden-state rendering")
require('id="op-subscribe"' not in insights and "Unsubscribe in one click" not in insights,
        "insights page no longer promises unimplemented newsletter delivery")
require("We do not invent proof." in work and "Numbers that mean something." not in work,
        "portfolio copy distinguishes methods from verified client results")
require("in the United States?" in usa,
        "USA page heading uses natural grammar")
require("Choose the capability your business needs next." in services,
        "services page has a coherent section heading")

# Shared feedback and cleanup logic must be available to every static page.
unwired = [p.name for p in pages if "scripts/main.js" not in p.read_text(encoding="utf-8", errors="replace")]
require(not unwired, "shared JavaScript loads on every HTML page" + (f" (missing: {', '.join(unwired[:10])})" if unwired else ""))

# Guard common source copy defects corrected by the site-wide normalization script.
for bad in [
    "Thirty minutes, no pitch a frank conversation",
    "the problem , not just keywords",
    "3 new partnerships per quarter.</strong> Current quarter is open",
    "in United States?",
    "scoping to shipped",
    "premium at thumb-scroll speed",
]:
    hits = [
        p.name for p in pages
        if bad.lower() in p.read_text(encoding="utf-8", errors="replace").lower()
    ]
    require(not hits, f"obsolete copy removed: {bad}" + (f" (found in: {', '.join(hits)})" if hits else ""))

if failures:
    print("UX/content regression checks FAILED:")
    for failure in failures:
        print(f"- {failure}")
    sys.exit(1)

print(f"UX/content regression checks passed: {len(checks)} checks.")
for check in checks:
    print(f"- {check}")

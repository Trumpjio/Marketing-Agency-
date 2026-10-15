# OrcaPulse Website Audit — Preliminary Findings & Execution Plan

**Audit date:** 15 October 2026  
**Scope:** Source-level audit of `Trumpjio/Marketing-Agency-`, branch `deploy`; no live changes made.  
**Production verification:** Not completed. The live domain could not be independently retrieved through the available browser interface. Findings below distinguish repository evidence from live-site assumptions.

## A. Executive Summary

The repository README describes this project as the OrcaPulse static HTML site with PHP admin, but the current `deploy` branch contains broad Ioka branding and `ioka.online` canonical URLs. At the same time, `robots.txt` points to `orcapulse.com` and the settings file uses an OrcaPulse Calendly URL. This mismatch is the highest-priority source-level issue because it can send search engines, social previews, and site visitors inconsistent brand/domain signals.

A second high-priority security concern exists in `admin/config.php`: a hardcoded initial-password fallback is present. It should be removed and the credential rotated before a release. No password value is reproduced in this report.

The source contains a sitemap with 89 URLs, all using `ioka.online`; the files corresponding to those paths exist in the branch. This verifies repository consistency only, not the status or indexability of live URLs.

## B. Audit Scope and Limitations

Reviewed source evidence includes the repository README, branch file tree, `index.html`, `robots.txt`, `sitemap.xml`, `data/settings.json`, key page metadata, and selected PHP admin/security files.

Not independently verified: live HTTP status codes, redirects, live canonical tags, rendered layouts at viewport widths, real mobile interaction, Core Web Vitals, actual form/mail delivery, Search Console indexing, external backlinks, analytics/conversion rates, or PHP host configuration. These must not be described as tested or confirmed.

## C. Page Inventory

- Sitemap contains **89 URL entries** on the `deploy` branch.
- Sitemap URLs use `https://ioka.online/` and have `lastmod` set to `2026-10-07`.
- The path files corresponding to all 89 sitemap entries were found in the branch during static checks.
- `pricing.html` and `signup.html` were not found in this branch. Their absence from production is **not verified**.
- Live page inventory and HTTP outcomes remain pending.

## D. Critical / High Priority Findings

### OP-SEO-001 — Conflicting brand and canonical domain signals
- **Severity:** Critical
- **Evidence:** `index.html` title identifies “Ioka”; its canonical, Open Graph URL, Organization, WebSite and WebPage structured data use `ioka.online`. The sitemap lists 89 `ioka.online` URLs. The repository README describes the site as OrcaPulse.
- **Impact:** Search engines and sharing platforms may receive the wrong brand/domain; any migration or domain consolidation could be undermined.
- **Recommended action:** Confirm the intended live domain and authoritative brand assets, then update canonical URLs, Open Graph/Twitter metadata, JSON-LD, sitemap and internal absolute URLs consistently. Do not mass-replace domains until the target is explicitly confirmed.
- **Effort:** Medium–High, because changes may span many files.

### OP-SEO-002 — robots.txt sitemap URL conflicts with the sitemap host
- **Severity:** High
- **Evidence:** `robots.txt` declares `https://orcapulse.com/sitemap.xml`, while the checked-in sitemap lists `https://ioka.online/` URLs. The requested domain in project context is `orcapulse.io`.
- **Impact:** Crawlers may be directed to a sitemap on a different domain than the canonical URL set.
- **Recommended action:** After confirming the production domain, align the robots sitemap directive and XML sitemap host to the same canonical origin.
- **Effort:** Low.

### OP-SEC-001 — Hardcoded administrator password fallback
- **Severity:** High
- **Evidence:** `admin/config.php` contains a hardcoded initial-password fallback; README also instructs users to change a default password. Password value omitted from this report.
- **Impact:** If the fallback remains usable or the credential has been shared, it increases admin compromise risk.
- **Recommended action:** Remove the hardcoded fallback, verify login uses a secure stored password hash, rotate the credential, confirm recovery works, and ensure no secret is committed. Use a separate secure configuration mechanism for any bootstrap credential.
- **Effort:** Medium. Requires careful verification of the current authentication/reset flow.

### OP-CONV-001 — Brand/contact settings are internally inconsistent
- **Severity:** High
- **Evidence:** `data/settings.json` identifies the brand as Ioka, gives an `ioka.online` base URL and contact email, while its CTA URL points to an OrcaPulse Calendly path.
- **Impact:** Leads may see mismatched company identity and destination details, reducing trust and potentially misrouting conversions.
- **Recommended action:** Confirm the intended business identity, email, domain, phone, social profiles and booking URL. Update the central settings and check that page-build/admin propagation produces consistent visible details.
- **Effort:** Low–Medium.

## E. Additional SEO Findings

### OP-SEO-003 — Some article pages lack Open Graph URL and JSON-LD
- **Severity:** Medium
- **Evidence:** Static inspection found these article files without an `og:url` and JSON-LD block: `insight-aeo-geo-in-practice.html`, `insight-death-of-the-generalist-gcc.html`, `insight-dubai-vs-riyadh-budgets.html`, `insight-first-ninety-days.html`, `insight-flat-fees-defense.html`, and `insight-one-metric-retention.html`.
- **Impact:** Social share identity and structured context may be less consistent than on other pages.
- **Recommended action:** Verify all insight templates and page sources; add correct per-page canonical URL and suitable Article/BlogPosting JSON-LD where content supports it. Validate the resulting graph.
- **Effort:** Medium.

### OP-SEO-004 — LocalBusiness structured data on service-area pages needs validation
- **Severity:** Medium
- **Evidence:** Static audit found 17 city pages and 9 country pages with LocalBusiness schema that did not include address or telephone, using service-area fields instead.
- **Impact:** The schema may imply a local business presence that is not supported by actual location/contact data.
- **Recommended action:** Confirm whether real staffed locations exist. If not, consider organization/professional-service markup with truthful `areaServed` and no fictitious local NAP; if locations do exist, use verified real data.
- **Effort:** Medium.

## F. UX, Mobile and Accessibility

The source includes responsive stylesheet breakpoints, focus-visible styles, a skip link, and a mobile menu implemented with JavaScript. This is evidence that responsive/accessibility mechanisms exist in code, not proof that all experiences pass.

**Pending hands-on validation:** 375×812, 390×844, 768×1024, 1280×800, and 1440×900. Test mobile-menu open/close, keyboard navigation, focus order, contrast, touch targets, headings, alt text, form validation, reduced motion and WCAG 2.2 AA criteria. No visual/UI issue should be reported as confirmed without rendered-page evidence.

## G. Conversion

- The settings file’s brand/domain/contact mismatch is the confirmed conversion/trust issue (OP-CONV-001).
- The settings file references a Calendly booking destination; successful booking flow has not been tested.
- Lead capture, thank-you routing, analytics attribution and email delivery remain unverified in the live environment.

## H. Frontend / Admin Security Notes

- `.htaccess` includes security headers, blocking of dotfiles and selected data/config files, and upload/admin-related rules; this has not been validated on the actual hosting server.
- Login source includes CSRF handling, honeypot and rate limiting. The actual rate-limit and session behavior has not been runtime-tested.
- Password-reset email delivery via PHP mail has not been tested.
- Admin configuration should be treated as needing credential hardening before a release (OP-SEC-001).

## I. Prioritized Issue Register

| ID | Priority | Finding | Target |
|---|---|---|---|
| OP-SEO-001 | P0 | Brand/canonical domain mismatch | All pages, settings, sitemap, structured data |
| OP-SEC-001 | P0 | Hardcoded admin password fallback | `admin/config.php`, auth flow |
| OP-SEO-002 | P1 | robots sitemap host mismatch | `robots.txt`, `sitemap.xml` |
| OP-CONV-001 | P1 | Brand/contact/CTA inconsistency | `data/settings.json`, generated pages |
| OP-SEO-003 | P2 | Missing OG URL/JSON-LD on selected articles | Six listed insight files, template |
| OP-SEO-004 | P2 | LocalBusiness schema needs truthfulness review | 26 country/city pages |

P0 means resolve before production release; P1 means next; P2 means important quality improvement after domain/source correctness.

## J. Developer Notes

1. Confirm the exact production source repository/branch and canonical domain before edits.
2. Work on a dedicated fix branch; never directly change production branch as the first step.
3. Keep existing design, page structure and useful content intact. Do not remove pages without a URL migration/redirect plan.
4. Update common generators/settings before manually editing many pages, where the architecture supports it.
5. Keep PHP secrets out of Git; test admin login and recovery in staging.
6. Generate a file-level diff and run HTML/link/metadata/security checks before opening a pull request.
7. Update the sitemap only after the canonical URL strategy is confirmed.

## K. One-by-one Execution Roadmap

**Stage 0 — Source/domain confirmation (blocker):** Confirm `orcapulse.io` vs `orcapulse.com` and identify the live repository/branch.  
**Stage 1 — Identity/canonical repair:** Align brand, title/meta, canonical, OG/Twitter, JSON-LD, internal absolute URLs, base settings, sitemap and robots directive.  
**Stage 2 — Admin security:** Remove hardcoded password fallback; rotate password; validate hashed password storage, reset/recovery, rate limiting, file permissions and upload restrictions.  
**Stage 3 — Metadata consistency:** Fix article OG/schema coverage and validate page metadata systematically.  
**Stage 4 — Schema review:** Correct service-area/local business schema based on genuine company locations.  
**Stage 5 — Rendered QA:** Test mobile/desktop layouts, keyboard accessibility, links, forms, booking flow, redirects, headers and HTTP statuses.  
**Stage 6 — Release review:** Present diff/test evidence, open a pull request, and deploy only after release approval and confirmed hosting workflow.

## L. Audit Status

- Source-level evidence captured: **Partial audit complete**
- Live URL crawl / HTTP validation: **Pending**
- Visual/mobile/accessibility runtime test: **Pending**
- Admin security runtime test: **Pending**
- Code changes made: **None**
- Commits pushed: **None**
- Deployment performed: **None**

The first implementation step is blocked until the canonical domain and live source are confirmed. Once confirmed, implement one stage at a time with a reviewable GitHub diff and test evidence.

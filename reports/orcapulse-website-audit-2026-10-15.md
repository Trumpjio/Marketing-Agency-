# OrcaPulse Website Audit — Source-Level Review

**Audit date:** 15 October 2026  
**Repository reviewed:** `Trumpjio/Marketing-Agency-`  
**Audited ref:** `deploy`  
**Production domain:** `https://orcapulse.io`  
**Test domain:** `https://ioka.online` (historical testing domain; not the production canonical domain)  
**Scope:** Static repository/source review. No website code was changed as part of the audit. No production deployment, live-browser verification, Lighthouse run, Search Console inspection, analytics inspection, or form/CTA transaction test was completed.

## A. Executive summary

The highest-risk finding is a **production identity and canonical-domain mismatch in the audited source**. The reviewed `deploy` branch contains Ioka branding and `ioka.online` canonicals, while the sitemap is also built around `ioka.online`; `robots.txt` points to `orcapulse.com`, not the confirmed production domain `orcapulse.io`. This is not proof that the live production website is broken, but it is a release blocker if this branch is intended to publish OrcaPulse.

Other confirmed source-level findings:
1. `robots.txt` declares the wrong sitemap host (`orcapulse.com`).
2. `sitemap.xml` contains 89 URLs on the historical test host `ioka.online`.
3. Core templates/pages inspected (home, services, about, contact, insights) use Ioka titles/canonicals/identity in the audited branch.
4. Six sampled insight/article pages lack both `og:url` and JSON-LD schema.
5. Seventeen city pages and nine country pages use `LocalBusiness` schema without an address or telephone. If no genuine local establishment exists in each target location, use a more suitable organization/service schema and accurate `areaServed` instead of implying local premises.
6. `admin/config.php` contains a hardcoded initial-password fallback. Treat it as exposed; remove the fallback and rotate credentials as part of an approved, tested security change.
7. Admin recovery flow has a misleading reset-page error message referring to write access for `admin/config.php`, although the inspected password-update path stores the password hash in `data/.auth.json`. Correct the message and verify recovery on a staging copy.
8. The mobile navigation uses clickable-looking `div` headings rather than native buttons in the source. Confirm keyboard operation and screen-reader state in a browser; if the JS does not provide equivalent behavior, replace them with buttons with `aria-expanded` and controlled-panel relationships.

**Release recommendation:** Do not deploy the audited branch to `orcapulse.io` until the production domain and OrcaPulse brand are applied consistently, the sitemap/robots references are corrected, and the security credential fallback is removed. The review branch contains this report only; no site files have been altered.

## B. Scope and method

Reviewed repository metadata and the `deploy` branch, including `index.html`, `services.html`, `about.html`, `contact.html`, `insights.html`, `robots.txt`, `sitemap.xml`, `data/settings.json`, `.htaccess`, and selected admin authentication/recovery files. The sitemap contains 89 entries; the corresponding paths were checked against files in the audited branch. Static inspection is not equivalent to confirming HTTP status, rendered output, crawlability, or production behavior.

The production URL could not be independently loaded with the available browser inspection route during this audit. Accordingly, all findings below are explicitly labeled as source-level unless otherwise stated. No unsupported traffic, ranking, conversion, speed, or accessibility scores are asserted.

## C. Page inventory and audit status

- **Sitemap inventory:** 89 URL entries, all on `https://ioka.online/`, with `lastmod` set to `2026-10-07`.
- **Source path check:** All 89 sitemap paths were found in the audited branch. This does not prove those URLs return HTTP 200 in production.
- **Core pages sampled:** Home, Services, About, Contact, Insights.
- **Article pages with a confirmed metadata/schema gap in the sample:** `insight-aeo-geo-in-practice.html`, `insight-death-of-the-generalist-gcc.html`, `insight-dubai-vs-riyadh-budgets.html`, `insight-first-ninety-days.html`, `insight-flat-fees-defense.html`, `insight-one-metric-retention.html`.
- **Not found in this branch:** `pricing.html` and `signup.html`. This is not evidence that they are missing from the live website; check production and analytics before deciding whether to create, redirect, or omit them.
- **Not verified:** Live HTTP statuses, redirects, canonical response headers, rendered mobile layout, Core Web Vitals, Search Console coverage, indexation, analytics/conversion data, working email delivery, and real CTA/booking completion.

## D. Technical SEO

### OP-SEO-001 — Critical: canonical domain and brand mismatch
- **Location:** `index.html`, `services.html`, `about.html`, `contact.html`, `insights.html`, `data/settings.json`, and sitemap URLs.
- **Evidence:** The audited branch identifies the site as Ioka and references `https://ioka.online`; production domain confirmed by the owner is `https://orcapulse.io`. The branch appears to represent the test version rather than a production-ready OrcaPulse build.
- **Correction:** Replace test-domain canonicals, Open Graph URLs, JSON-LD URLs, settings base URL, sitemap locations, and any internal absolute URLs with the final HTTPS `orcapulse.io` host. Restore OrcaPulse naming and approved brand metadata consistently. Search the full repository for stale Ioka/test-domain references before release.
- **Acceptance test:** No unintended `ioka.online` or `orcapulse.com` production references remain; canonical, OG URL, schema URL, sitemap and internal absolute URLs agree on `https://orcapulse.io`.

### OP-SEO-002 — High: robots sitemap host is incorrect
- **Location:** `robots.txt`
- **Evidence:** Sitemap directive currently points to `https://orcapulse.com/sitemap.xml`; confirmed production domain is `orcapulse.io`.
- **Correction:** Change to `Sitemap: https://orcapulse.io/sitemap.xml` after the production sitemap is validated.
- **Acceptance test:** Fetch `https://orcapulse.io/robots.txt`; verify it returns 200 and names the production sitemap.

### OP-SEO-003 — High: sitemap uses test-domain URLs
- **Location:** `sitemap.xml`
- **Evidence:** 89 entries use `https://ioka.online/`; last-modified dates are set to 7 October 2026.
- **Correction:** Regenerate sitemap using only intended, canonical, indexable `orcapulse.io` URLs returning 200. Remove redirected, duplicate, noindex, or non-canonical URLs. Update lastmod only when page content materially changes.
- **Acceptance test:** Every sitemap URL is on the production host, canonical to itself (unless intentionally consolidated), indexable, and returns 200.

### OP-SEO-004 — Medium: article Open Graph URL and structured data are missing on sampled pages
- **Location:** The six article paths listed in Section C.
- **Evidence:** Source inspection found no `og:url` and no JSON-LD schema in these six sampled files.
- **Correction:** Add a canonical `og:url` and appropriate article schema (for example, `Article` or `BlogPosting`) with accurate headline, description, author/publisher, image, datePublished/dateModified where supported, and production URL. Do not invent author, image, or dates.
- **Acceptance test:** Validate each template and representative URL with Schema.org/Google rich-result validators; ensure schema matches visible content.

### OP-SEO-005 — Medium: location pages may overstate local-business presence
- **Location:** 17 city pages and 9 country pages in the audited branch.
- **Evidence:** These pages use `LocalBusiness` structured data without an address or telephone.
- **Correction:** If OrcaPulse has no genuine physical establishment in each location, use an appropriate `Organization` or service-oriented type with truthful `areaServed`; use `LocalBusiness` only where the represented local business and required details are real and accurate.
- **Acceptance test:** Schema describes the real business model, is consistent with visible page content, and contains no invented local NAP data.

## E. UX/UI and conversion

**Status: partially audited at source level; rendered design not verified.** No claim is made that a particular element is visually broken without a live render or screenshot.

- The source contains shared navigation and CTA settings, including a Calendly URL in `data/settings.json`. Confirm the intended OrcaPulse destination and that the live CTA opens the correct booking flow.
- Test all primary and secondary CTAs on desktop and mobile, including success/cancel states and analytics events. No booking transaction was submitted during this audit.
- Review each page template for one clear primary action, consistent OrcaPulse positioning, readable hierarchy, and relevant proof. Make only evidence-backed changes; preserve the existing approved visual system rather than redesigning it without a specific defect.
- Validate all visible links, footer links, mobile menu controls, and external destinations after domain migration.

## F. Mobile, accessibility, and frontend quality

### OP-A11Y-001 — Medium pending browser verification: mobile menu headings lack native control semantics
- **Location:** Mobile navigation in the shared HTML/JS/CSS.
- **Evidence:** Expandable-looking group headings are represented as `div.nav__mobile-heading` elements and styled as clickable. A `div` does not inherently provide button keyboard semantics.
- **Correction:** Inspect event handlers in-browser. If these headings expand/collapse content, implement native `button` elements, `aria-expanded`, `aria-controls`, visible focus, Enter/Space activation, and correct focus order.
- **Acceptance test:** Keyboard-only operation succeeds; screen readers announce each control and its expanded state; no focus is trapped or lost.

### Required responsive and accessibility checks
Run on 375px, 390px, 768px, 1280px and 1440px viewports. Check horizontal overflow, clipped content, sticky header/menu overlap, readable line lengths, tap targets, keyboard focus, contrast, reduced-motion behavior, semantic heading order, form labels/errors, and accessible names. Target WCAG 2.2 AA. These tests have not been executed in a rendered browser during this source review.

### Existing positive source observations
- `.htaccess` includes security headers, hidden-file restrictions, compression/caching rules, and conditional HSTS for HTTPS.
- Login source includes CSRF protection, a honeypot, rate limiting, and an authentication check.
- Styles include visible-focus rules, a skip link, and reduced-motion handling for reveal effects.
These are code observations only; effective behavior depends on server configuration and browser testing.

## G. Security and administration

### OP-SEC-001 — Critical: hardcoded initial admin password fallback
- **Location:** `admin/config.php`
- **Evidence:** The source includes a hardcoded initial-password fallback. The credential is intentionally not reproduced in this report.
- **Risk:** If deployed or shared, a static fallback can expose administrative access, especially if reused or if initialization logic permits it.
- **Correction:** Remove the hardcoded fallback and require a secure one-time initialization or password-reset path. Rotate the admin password immediately; invalidate any credentials that may have been exposed. Verify that no secret remains in Git history or deployment artifacts. Keep secrets out of source control.
- **Acceptance test:** Authentication works with the rotated credential; the old/default credential does not work; unauthenticated users cannot access admin routes or data; secrets are supplied through a secure configuration mechanism.

### OP-SEC-002 — Medium: password reset error message does not match storage behavior
- **Location:** `admin/reset.php`
- **Evidence:** The error message refers to `admin/config.php` needing write access, while the inspected update path stores the password hash in `data/.auth.json`.
- **Correction:** Align the user-facing error with the actual failure condition. Verify permissions and reset success on staging before changing production.
- **Acceptance test:** Reset succeeds once with a valid token; expired and reused tokens fail; the password hash is stored in the intended file; no token or sensitive data is disclosed.

### OP-SEC-003 — Medium: verify admin recovery file exposure and mail delivery
- **Location:** `admin/forgot.php`, `admin/recover.php`, `.htaccess`, and data directory permissions.
- **Evidence:** Reset URL is assembled from the incoming host and current directory; reset email uses PHP `mail()`; recovery flow uses a file under `data/`. Mail delivery and server-level access controls were not tested.
- **Correction:** Build reset URLs from a trusted configured production base URL rather than untrusted request host data; ensure sensitive recovery files are not web-accessible and have restrictive permissions; test mail delivery and rate limits; keep responses neutral to avoid account enumeration.
- **Acceptance test:** Reset links always use HTTPS `orcapulse.io`, work from the intended deployment path, expire and are single-use, and recovery artifacts cannot be fetched over HTTP.

## H. Prioritized issue register

| ID | Severity | Effort | Issue | Recommended order |
|---|---|---:|---|---:|
| OP-SEO-001 | Critical / release blocker | Medium | Test identity/domain in source | 1 |
| OP-SEC-001 | Critical / security | Low–Medium | Hardcoded admin credential fallback | 1 |
| OP-SEO-002 | High | Low | Incorrect robots sitemap host | 2 |
| OP-SEO-003 | High | Medium | Sitemap built on test domain | 2 |
| OP-SEC-003 | High–Medium | Medium | Verify reset URL and recovery-file safety | 2 |
| OP-SEO-004 | Medium | Low–Medium | Missing article OG URL and schema on sampled pages | 3 |
| OP-SEO-005 | Medium | Medium | Potentially inaccurate LocalBusiness markup | 3 |
| OP-SEC-002 | Medium | Low | Misleading reset error message | 3 |
| OP-A11Y-001 | Medium, pending browser test | Low–Medium | Mobile menu semantics | 4 |

## I. Developer implementation specifications

1. Treat `https://orcapulse.io` as production and `https://ioka.online` as a test domain only.
2. Use one shared production base URL for canonicals, Open Graph, schema, sitemap generation, and reset links.
3. Run a repository-wide search for `ioka.online`, `orcapulse.com`, Ioka brand text, and stale test URLs before release. Do not blindly replace occurrences in historical notes or intentional test configuration.
4. Generate the sitemap from the canonical page inventory; verify every URL response and indexability before submission.
5. Apply Article/BlogPosting schema only where content supports the fields; do not fabricate author, date, image, physical address, or telephone.
6. Remove hardcoded credentials and rotate admin access before production release.
7. Keep changes minimal and scoped. Preserve the current approved design and site architecture; do not remove pages or redesign the site as part of these technical fixes.
8. Run PHP lint/static checks where possible, validate HTML and structured data, and test on staging before production deployment.

## J. Recommended implementation and release roadmap

**P0 — Before release**
- Confirm the exact branch and deployment mechanism that feed `orcapulse.io`.
- Normalize brand and production domain across templates, settings, canonicals, OG, schema, sitemap, robots, and absolute internal URLs.
- Remove the hardcoded admin credential fallback; rotate credentials; verify admin reset/recovery protections.
- Validate production sitemap and robots responses.

**P1 — SEO consistency**
- Add missing article OG URLs and accurate article schema.
- Review location schema and replace unsupported LocalBusiness claims.
- Check all 89 sitemap entries for canonicality, indexability, redirects and live status.

**P2 — Browser QA**
- Test all required viewports and key journeys.
- Verify keyboard navigation, menu semantics, contrast, focus, reduced motion, and form accessibility against WCAG 2.2 AA.
- Test all CTAs, Calendly, email delivery, analytics events, and admin recovery in staging.

**P3 — Post-release monitoring**
- Submit the validated sitemap in Google Search Console and relevant webmaster tools.
- Monitor index coverage, canonical selection, crawl errors, and conversion events after release.
- Establish a baseline for Core Web Vitals and real-user mobile performance; do not infer scores from source alone.

## K. Limitations and unresolved verification

- Production `orcapulse.io` pages, `robots.txt`, and sitemap could not be fetched through the available browser inspection route. The site may load normally for users; production behavior remains unverified.
- No browser screenshots or rendered visual comparison were available, so visual defects are not marked as confirmed.
- No Lighthouse/PageSpeed, Search Console, analytics, server logs, PHP runtime, SMTP, or staging environment access was used.
- `pricing.html` and `signup.html` are absent from the audited branch, but live presence/absence was not verified.
- The 89 sitemap URLs correspond to branch files, not verified live HTTP 200 responses.
- The report does not claim rankings, traffic losses, performance scores, conversion losses, or indexation outcomes.

## L. Change and deployment status

- Audit report only; no website code changes made during this review.
- No secrets or passwords included in the report.
- No production deployment performed.
- The report is intended to be committed to a separate review branch so it can be reviewed without modifying the existing `deploy` branch.
- Implementation changes should be made in a separate, testable change set after the findings and release target are confirmed.

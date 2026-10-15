# OrcaPulse Website Audit — Findings and Remediation Plan

**Audit date:** 15 October 2026  
**Scope:** Static source review of `Trumpjio/Marketing-Agency-`, branch `deploy`, plus repository metadata and the supplied audit requirements. This is not a live-browser, Lighthouse, crawler, analytics, or server-configuration test.

## A. Executive summary

The most important finding is a **source-of-truth mismatch**. The reviewed branch is branded **Ioka**, uses `ioka.online` in canonical URLs, metadata, schema, sitemap and settings, and includes an OrcaPulse Calendly link. The intended production site is **OrcaPulse / orcapulse.io**. Until the correct production source is identified, website code should not be merged to a production branch.

The reviewed branch contains an 89-URL sitemap, and every listed URL path has a matching file in the branch. This confirms repository file coverage only; it does not prove those URLs are live or return successful HTTP responses.

Confirmed priorities:
1. Resolve source/domain/brand mismatch before deployment.
2. Align production-domain references after the correct target is confirmed.
3. Review a hard-coded admin password fallback in `admin/config.php`; rotate credentials and remove any usable default under a controlled change.
4. Check article metadata/schema consistency and location-page structured-data accuracy.
5. Verify mobile-navigation semantics and keyboard/screen-reader behavior.
6. Run live URL, responsive, accessibility, form and security tests against the confirmed production environment.

No website files have been changed and no deployment was made as part of this audit.

## B. Scope, evidence and limitations

Evidence is from source files in `Trumpjio/Marketing-Agency-` branch `deploy`, unless stated otherwise. The production website could not be independently fetched with the available browser interface. Findings about deployed HTTP codes, redirects, Core Web Vitals, live appearance, uptime, analytics, form delivery and effective security headers are therefore **not verified**.

The 89 sitemap URL paths were checked against repository paths. This is not a live crawl. `pricing.html` and `signup.html` were not found on this branch; their production availability is unverified and they must not be called live 404s based on this evidence.

No Lighthouse/PageSpeed score, traffic, ranking, conversion-rate, WCAG conformance result or vulnerability exploitability is claimed.

## C. Source inventory and status

| Item | Evidence | Status |
|---|---|---|
| Repository | `Trumpjio/Marketing-Agency-` | Confirmed |
| Branch reviewed | `deploy` | Confirmed |
| Default branch | `main` | Confirmed |
| Homepage | `index.html` | Source inspected |
| Sitemap | `sitemap.xml` | 89 URLs; listed paths match repository files |
| Robots | `robots.txt` | Source inspected |
| Site settings | `data/settings.json` | Source inspected |
| Core pages | `services.html`, `about.html`, `contact.html`, `insights.html` | Metadata inspected |
| Admin auth | `admin/config.php`, `admin/login.php`, `admin/forgot.php`, `admin/reset.php`, `admin/recover.php` | Selected source inspected |
| Live site | `https://orcapulse.io` | Not independently verified |
| Live HTTP/redirect checks | — | Not run |
| Responsive screenshots | — | Not captured |
| Lighthouse/Core Web Vitals | — | Not measured |
| Form/email delivery | — | Not tested |
| Analytics/conversion tracking | — | Not verified |
| Server filesystem permissions | — | Not verified |

## D. Critical source-of-truth and brand/domain mismatch

**ID:** SRC-001  
**Severity:** Critical  
**Priority:** P0 — resolve before website-code deployment  
**Effort:** Small investigation; remediation scope depends on confirmed source.

**Evidence:** In branch `deploy`, `index.html` title is “Specialist Growth Marketing Agency | Ioka”; its canonical and Open Graph URL use `https://ioka.online/index.html`; Organization/WebSite schema names Ioka and references `ioka.online`; header and footer display IOKA. `data/settings.json` also identifies Ioka, uses `hello@ioka.online`, and sets the site URL to `https://ioka.online`. `sitemap.xml` lists Ioka-hosted URLs, while `robots.txt` points its sitemap directive to `https://orcapulse.com/sitemap.xml`. The homepage also links to `https://calendly.com/orcapulse/intro-call`.

**Risk:** Publishing this branch as OrcaPulse could expose the wrong brand, canonicalize content to another domain, point crawlers at a mismatched sitemap and confuse search engines/users.

**Action:** Confirm the exact production GitHub repo, branch and hosting linkage. Then inventory brand/domain references and update only values validated for the intended site. Check canonicals, Open Graph/Twitter metadata, schema IDs/URLs, sitemap, robots, settings, email addresses, navigation, footer, image URLs, redirects and analytics IDs. Do not perform blind global replacement: third-party links (including Calendly) may intentionally contain “orcapulse”.

**Acceptance criteria:** All indexable pages use the confirmed canonical host; sitemap and robots directive match it; site name/contact/schema match the approved brand; no unintended old-domain references remain; redirects are tested; approved pages remain available.

## E. SEO findings

### SEO-001 — Sitemap host and robots sitemap directive disagree
**Severity:** High | **Priority:** P1 after source confirmation | **Effort:** Small

Evidence: `sitemap.xml` contains 89 Ioka-hosted URLs; `robots.txt` declares `https://orcapulse.com/sitemap.xml`. The homepage and settings instead reference `ioka.online`. The intended domain supplied for this work is `orcapulse.io`, different from both.

Recommendation: after confirming the production host, align sitemap URLs, robots sitemap directive, canonicals and schema URLs to one preferred host. Test redirects and submit the final sitemap in the correct Search Console property.

Acceptance: robots points to the intended sitemap, sitemap URLs are canonical and return success in a live crawl, and alternate-host redirects are validated.

### SEO-002 — Inconsistent article metadata/schema coverage
**Severity:** Medium | **Priority:** P2 | **Effort:** Medium

Evidence from selected article sources: `insight-aeo-geo-in-practice.html`, `insight-death-of-the-generalist-gcc.html`, `insight-dubai-vs-riyadh-budgets.html`, `insight-first-ninety-days.html`, `insight-flat-fees-defense.html` and `insight-one-metric-retention.html` lack an `og:url` tag and JSON-LD schema in the inspected source. Coverage should be checked across all articles before estimating total impact.

Recommendation: apply a consistent article metadata pattern with page-specific title, accurate description, canonical, Open Graph/Twitter tags and appropriate Article/BlogPosting JSON-LD. Include author, dates, publisher, image and mainEntityOfPage only when accurate; do not invent them.

Acceptance: every intended indexable article has unique and accurate metadata, one self-referencing canonical on the preferred host, and valid structured data.

### SEO-003 — Location pages may overstate local-business identity
**Severity:** Medium | **Priority:** P2 | **Effort:** Medium

Evidence: source review found 17 city pages and 9 country pages using LocalBusiness schema without an address or telephone; fields include name, URL and areaServed.

Qualification: this alone does not prove invalid structured data or a penalty. It creates a risk of implying a physical local business where no genuine local NAP details are supplied.

Recommendation: use LocalBusiness only where the organization qualifies and truthful location data is available. For remote services without an office in every city/country, consider a more appropriate Organization/ProfessionalService representation and truthful `areaServed`. Never fabricate addresses or phone numbers.

Acceptance: schema describes the actual business, validates and matches visible page content.

### SEO-004 — Missing production crawl/indexation evidence
**Severity:** High | **Priority:** P1 | **Effort:** Medium

Evidence: live domain, `robots.txt` and `sitemap.xml` could not be independently retrieved through the available browser interface. Repository files alone cannot verify indexability.

Action: run a live crawl after confirming the deployment source. Check status codes, redirect chains, canonical targets, robots directives, noindex headers/meta, orphan pages, internal links, duplicate metadata, alt text, pagination if present, URL normalization and sitemap equality. Verify Search Console coverage and URL inspection if access is provided.

Acceptance: dated crawl export and test report with per-URL status. Do not infer indexing from repository presence.

## F. UX/UI, mobile navigation and accessibility

### A11Y-001 — Mobile menu category labels are non-semantic divs
**Severity:** Medium | **Priority:** P2 | **Effort:** Small–Medium

Evidence: homepage uses `.nav__mobile-heading` category labels implemented as `<div>` (“Services”, “Industries”, “Locations”, “Studio”); `styles/glass.css` styles headings with `cursor: pointer`. This indicates an interaction affordance without native button semantics. Runtime handlers and keyboard behavior still require browser testing.

Recommendation: if headings expand/collapse groups, use `button type="button"` with accessible names and `aria-expanded`/`aria-controls`; ensure keyboard activation, visible focus, accurate state and hidden links removed from the tab order. If headings are labels only, remove pointer affordance and style as headings.

Acceptance: keyboard-only operation, correct expanded/collapsed announcements, visible focus and screen-reader check.

### A11Y-002 — Validate responsive layout and WCAG 2.2 AA
**Severity:** High for confirmed blockers; currently unverified | **Priority:** P1 test | **Effort:** Medium

Source evidence: a skip link and `:focus-visible` styles exist; responsive styles include breakpoints near 1000/900, 760 and 420 px. These declarations do not establish rendered accessibility or absence of overflow.

Test at 375, 390, 768, 1280 and 1440 px. Check horizontal overflow; menu/form usability; heading/order; focus order; contrast and target size; image alt; label/error associations; focus handling for overlays; reduced motion; and zoom at 200–400%. Use manual tests and automated tooling. Automated scans alone are not certification.

### UX-001 — Live visual/design review not verified
**Severity:** Unrated pending browser review | **Priority:** P1 test | **Effort:** Medium

No live screenshots were available to substantiate specific layout defects. Preserve the existing design and page structure. Capture the required viewport screenshots and assess spacing, typography, line lengths, contrast, image crops, sticky elements, overlays and mobile footer. Only make issue-specific changes with reproducible evidence.

## G. Conversion and functional checks

### CONV-001 — Lead form endpoint and notification path untested
**Severity:** High until verified | **Priority:** P1 | **Effort:** Medium

Evidence: homepage form posts to `admin/lead.php`, includes a client-side honeypot and status element. Source inspection cannot establish that the endpoint is live, submissions are validated or notifications delivered.

Test with approved test data: required fields, malformed email, URL handling, server-side validation, spam/rate limiting, duplicate submission, accessible success/failure message, notification recipient, retention/privacy notice and mobile keyboard behavior. Never use real personal data in test submissions.

Acceptance: test lead handled safely end-to-end and status communicated accessibly. Confirm privacy/consent requirements before adding tracking.

### CONV-002 — CTA destination verification
**Severity:** Medium | **Priority:** P2 | **Effort:** Small

Evidence: prominent CTAs link to `https://calendly.com/orcapulse/intro-call`. Confirm it is the intentionally approved booking destination for the production brand; do not change it merely because surrounding source labels Ioka.

Acceptance: CTA labels match destinations, new tabs are safe where used, booking page works and conversion events are tested without duplicate firing.

### CONV-003 — Analytics and conversion attribution
**Severity:** Medium | **Priority:** P2 | **Effort:** Medium

Analytics installation and conversion events were not verified. Inventory tags and consent behavior, then confirm one source of truth for form submit, qualified lead and booked call events. Avoid double firing and validate in analytics/debug tools. Do not claim measurement is broken without test evidence.

## H. Security and admin review

### SEC-001 — Hard-coded admin password fallback
**Severity:** High | **Priority:** P1 | **Effort:** Small–Medium

Evidence: `admin/config.php` contains an initial-password fallback. The credential is intentionally not reproduced here.

Risk: default/fallback secrets in version control increase account-takeover risk if still usable in production or copied elsewhere. The source does not prove the fallback is active on the live host.

Action: verify whether the fallback is accepted, rotate the admin password, remove the fallback in favor of environment/config secret management or a secure password hash, and invalidate exposed secrets. Review Git history and access logs after remediation. Never publish credentials or reset tokens.

Acceptance: no hard-coded usable password remains; rotated authentication works; rate limits remain effective; regression tests pass.

### SEC-002 — Verify auth, recovery and storage protections on host
**Severity:** High priority to verify | **Priority:** P1 | **Effort:** Medium

Evidence: `admin/login.php` includes CSRF handling, a honeypot and rate limiting; password reset uses a time-limited single-use token, and authentication storage references `data/.auth.json`. `.htaccess` blocks some file types and hidden files; an `admin/.htaccess` was not observed in the reviewed inventory.

Action: test on the actual PHP host that sensitive data directories cannot be fetched, tokens expire and are single-use, hashes are secure, recovery is not guessable, reset email works, session cookies use suitable Secure/HttpOnly/SameSite flags, session IDs rotate after auth, and all admin endpoints enforce authorization. Verify permissions and backups. Do not claim exploitability without controlled testing.

Acceptance: documented cases pass; sensitive files are inaccessible directly; admin pages require authorization; recovery works without permitting unauthorized resets.

### SEC-003 — Password-reset error message may be misleading
**Severity:** Low–Medium | **Priority:** P3 | **Effort:** Small

Evidence: `admin/reset.php` suggests `admin/config.php` must be writable, while inspected code stores the password hash through `auth_set_password` in `data/.auth.json`.

Recommendation: confirm the actual error condition and explain it accurately to users without exposing internals. Keep technical details in logs.

## I. Performance and frontend code quality

**Status:** Not measured live; no performance score is available.

Source references Google Fonts, multiple CSS files and inline JavaScript. These are not automatically defects. Measure first on the confirmed production domain: LCP, INP, CLS, TTFB, page weight, requests, image bytes, font loading, render-blocking CSS/JS, caching and compression. Optimize evidenced bottlenecks, use responsive/compressed images, load fonts efficiently and defer non-critical work while preserving design.

Security headers, compression and cache rules exist in `.htaccess`, but effective behavior depends on host configuration; verify real HTTP responses. CSP accommodates inline scripts/styles and external resources. Do not tighten it without inventorying dependencies and testing the admin/editor.

## J. Prioritized issue register

| ID | Category | Issue | Severity | Priority | Effort | Evidence/status |
|---|---|---|---|---|---|---|
| SRC-001 | Source/deployment | Ioka/ioka.online branch conflicts with intended OrcaPulse/orcapulse.io | Critical | P0 | S–M investigation | Confirmed in source |
| SEO-001 | Technical SEO | Sitemap host and robots directive mismatch | High | P1 after source confirmation | S | Confirmed in source |
| SEC-001 | Security | Hard-coded admin password fallback | High | P1 | S–M | Source confirmed; live use unverified |
| SEO-004 | Technical SEO | No live crawl/indexability proof | High | P1 | M | Testing required |
| A11Y-002 | Accessibility/mobile | WCAG 2.2 AA/responsive behavior not tested | High if failures found | P1 test | M | Defects unverified |
| CONV-001 | Conversion | Form endpoint/delivery not tested end-to-end | High until verified | P1 | M | Testing required |
| SEC-002 | Security | Host-level admin/recovery/storage protections need verification | High priority to verify | P1 | M | Testing required |
| SEO-002 | On-page/schema | Selected articles missing `og:url` and schema | Medium | P2 | M | Confirmed on six selected articles |
| SEO-003 | Schema | LocalBusiness schema on 26 location pages lacks address/phone | Medium | P2 | M | Pattern confirmed; suitability depends on actual operations |
| A11Y-001 | Accessibility | Mobile menu headings use non-semantic divs with pointer affordance | Medium | P2 | S–M | Source pattern confirmed; runtime test needed |
| CONV-002 | Conversion | Confirm CTA destination remains approved | Medium | P2 | S | Confirmation needed |
| CONV-003 | Analytics | Verify tracking and form/booking events | Medium | P2 | M | Not verified |
| UX-001 | UX/UI | Visual review at required viewports | Unrated | P1 test | M | Screenshots unavailable |
| SEC-003 | Admin UX | Reset error message may misstate writable-file requirement | Low–Medium | P3 | S | Source inconsistency observed |
| PERF-001 | Performance | Measure CWV before optimization | Unrated | P1 test | M | No metrics available |

Effort: S = small, M = medium. Priority reflects risk and workflow sequence, not measured business impact.

## K. Developer implementation notes and release gates

1. **Source gate:** confirm exact repository, branch and hosting linkage for `orcapulse.io`. Do not deploy the Ioka source as OrcaPulse.
2. **Backup gate:** preserve production files and content; verify rollback path before any changes.
3. **P0:** resolve the source/domain mismatch.
4. **P1:** remediate confirmed security/config concerns in the correct source; align robots/sitemap/canonicals; conduct live crawl, form, admin and accessibility/responsive checks.
5. **P2:** standardize metadata/schema and mobile menu semantics; verify CTA destination and analytics.
6. **P3:** correct error copy and optimize only measured performance bottlenecks.
7. Test HTML/links and real browsers at 375, 390, 768, 1280 and 1440 px; perform keyboard/screen-reader checks, schema validation, PHP/admin regression tests and smoke tests for top pages/form.
8. Make changes on a dedicated branch and open a PR with per-file summary, tests and screenshots. Do not merge until checks pass and production source is confirmed. Release with rollback and post-release checks.
9. Preserve current page structure and design. Do not remove pages or broadly redesign as part of this audit.

## L. Final assessment

This review identified repository-level consistency and security concerns and specifies testable remediation. It has **not** established that the live OrcaPulse site uses these files, that live pages are broken, that search engines have deindexed content, or that Core Web Vitals/accessibility fail. Those claims require the real production source and live testing.

**Current status:** Source-level audit report complete; remediation/deployment is blocked until the production source and domain mapping are verified. No website code changes or production deployment were made.

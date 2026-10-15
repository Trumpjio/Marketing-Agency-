# OrcaPulse — Audit Findings, Remediation Notes, and Release Plan

**Updated:** 15 October 2026  
**Confirmed production domain:** https://orcapulse.io  
**Historical test domain:** https://ioka.online  
**Repository reviewed:** Trumpjio/Marketing-Agency-  
**Source branch reviewed:** deploy  
**Remediation branch:** fix/orcapulse-source-audit-remediation  
**Deployment status:** Not deployed. Production source mapping remains unverified.

## Executive decision

Do not merge/deploy the candidate website source to production until the exact Hostinger document root / deployed commit for `orcapulse.io` is compared with this repository. The `deploy` branch README describes an OrcaPulse site, but the actual homepage, shared styles, settings, canonicals, and sitemap identify Ioka and `ioka.online`. That inconsistency is confirmed in source; whether this branch currently serves `orcapulse.io` is not confirmed.

The historical `ioka.online` host was used for testing and must not be treated as production. The production domain is `orcapulse.io`. Do not globally replace names/domains across pages until the intended site content is confirmed; page copy is genuinely about a marketing agency, not the OrcaPulse AI sales-automation product.

## Confirmed source findings

| ID | Severity | Finding | Status / action |
|---|---|---|---|
| SRC-01 | P0 release blocker | Source-of-truth mismatch: `deploy` site is Ioka, not a verified OrcaPulse production site | Open; compare deployed files and commit first |
| SEO-01 | P1 | `robots.txt` points at `orcapulse.com/sitemap.xml` | Patched on remediation branch to `https://orcapulse.io/sitemap.xml` |
| SEO-02 | P1 | Sitemap has 89 entries, all on `ioka.online` | Open; regenerate only after the real OrcaPulse page inventory is confirmed |
| SEO-03 | P1 | Titles, canonicals, OG, JSON-LD, footer, settings and page content consistently identify Ioka/`ioka.online` | Open; do not bulk-rebrand until source parity is verified |
| SEC-01 | P1 critical | `admin/config.php` includes a committed plaintext initial password | Patched on remediation branch: removes hardcoded default; initial password can be read from server env var `ORCAPULSE_ADMIN_PASSWORD`; rotate any credential that may have been exposed |
| SEC-02 | P1 high | `admin/forgot.php` builds links using request `HTTP_HOST` | Patched on remediation branch: uses a trusted HTTPS `ORCAPULSE_BASE_URL` configuration, defaulting to `https://orcapulse.io`; request Host no longer used |
| SEC-03 | P2 | Password-reset / recovery errors incorrectly instruct making `admin/config.php` writable even though hashes are stored in `data/.auth.json` | Patched messages on remediation branch |
| SEC-04 | P2 | Password reset mail failure was suppressed without being logged | Patched: logs mail failure; neutral response retained to avoid account enumeration |
| A11Y-01 | P2 | Mobile accordion section headings were clickable `div`s without keyboard/button semantics | Patched in `scripts/main.js` on remediation branch to use buttons, `aria-expanded`, `aria-controls`, hidden state and Escape handling |
| SEO-04 | P2 | Six sampled article pages lack `og:url` and JSON-LD structured data | Open; add accurate Article/BlogPosting schema, only using known information |
| SEO-05 | P2 | 17 city and 9 country pages use `LocalBusiness` schema without address/telephone | Open; validate whether genuine local establishments exist; otherwise use accurate Organization/service schema and areaServed |
| CVR-01 | P1 | Contact page includes a zero-filled WhatsApp number and legacy Ioka contact details | Confirmed in source; do not publish fake contact details; replace only with verified business contact info |
| CVR-02 | P1/P2 | Publicly indexed `orcapulse.ai/pricing` copy appears to describe pay-as-you-go billing while also calling the product “totally free” | Needs copy correction on the actual product-site source: say no subscription/no fixed monthly fee, while clearly explaining billable usage |
| TRUST-01 | P2 | Indexed claims include “10x” activity / “15x revenue”, rankings and security badges (ISO 27001/GDPR) without proof found in audit | Link each claim to evidence or remove/qualify it; don't imply certifications unless valid and current |
| UX-01 | P2 | Global `overflow-x:hidden` could mask horizontal overflow rather than fix cause | Needs browser inspection at mobile breakpoints |
| PERF-01 | P3 | Cache setup: HTML around one hour, CSS/JS around one month with versioned query strings | Validate invalidation behavior at release; not enough evidence to call this a live performance defect |

## Remediation branch changes

Branch: `fix/orcapulse-source-audit-remediation`

1. `admin/config.php`: removed the committed default admin password; initial login requires server-side `ORCAPULSE_ADMIN_PASSWORD`. Auth hash writes now use a temporary file + atomic rename, restrictive file permissions, and explicit success/failure returns. Password and CSRF helpers reject malformed array inputs rather than throwing PHP type errors.
2. Reset-token storage in `admin/config.php`: writes are atomic and use restrictive permissions; token creation/consumption fails closed if persistence fails; tokens are validated as strings.
3. `admin/forgot.php`: reset URLs use trusted HTTPS `ORCAPULSE_BASE_URL`, never request `HTTP_HOST`; malformed email values are rejected; no token is created when the trusted base URL is invalid; mail failures are logged while the response remains neutral to avoid account enumeration.
4. `admin/reset.php`: validates token/password input types, consumes the token before attempting the password write so a successful write cannot leave a reusable token, and gives actionable failure guidance. If the write fails after consumption, the user must request a new link.
5. `admin/recover.php`: validates submitted values and corrects password-storage failure guidance; the existing recovery flow remains.
6. `scripts/main.js`: mobile accordion headings are native buttons with `aria-expanded`, `aria-controls`, hidden-panel state and Escape handling. The existing guide code was preserved.
7. `robots.txt`: removed the stale/misleading sitemap directive until the production sitemap and source identity are verified.
8. `README.md`: removed the hardcoded password setup instructions, removed unsafe advice to use permissions `777`, added an explicit source-mismatch release warning, and removed instructions to delete the existing Hostinger document root before upload.
9. `.github/workflows/quality-checks.yml`: added PHP syntax checks, JavaScript syntax validation, sitemap XML parsing, a warning for non-production sitemap hosts, and guards against reintroducing the known hardcoded password, Host-header reset URLs, or unverified robots sitemap directives.

Important caveats:
- This is a proposal branch and PR #3, not production. Nothing was merged or deployed.
- The live production document root / deployed commit for `orcapulse.io` has not been supplied or verified in this connector session. The `deploy` branch's homepage, settings and sitemap are consistently Ioka-branded / `ioka.online`; do not bulk-rebrand this 89-URL site without proving it is the intended OrcaPulse source.
- The sitemap itself remains a legacy Ioka sitemap and must be rebuilt from the verified production page inventory. Removing the robots directive prevents this branch from actively advertising the wrong sitemap; it does not fix existing indexed URLs or production canonicals.
- The new workflow has been committed, but it has not yet reported a run. PHP lint, browser QA, mail delivery, auth/recovery end-to-end tests, live host checks, and production SEO validation remain unverified.
- Before any deployment, configure server-side `ORCAPULSE_ADMIN_PASSWORD`, `ORCAPULSE_BASE_URL=https://orcapulse.io`, and `ORCAPULSE_ADMIN_EMAIL` if required; verify login, reset, recovery, and file permissions on staging. Treat the previously committed credential as exposed and rotate it.
- Existing design and guide code was preserved; test mobile menu behavior in a rendered browser before merging.

## Public product-site copy finding

Indexed product pricing copy on `https://www.orcapulse.ai/pricing` appears inconsistent: the page describes per-use rates but also says the service is “totally free to use.” Recommended framing: **“No subscription or fixed monthly fee. You pay only for the usage you consume; usage charges apply.”** Verify exact prices, billable units, and any free credits before publishing. This finding belongs to the actual product-site source, which is not proven to be this static-site repository.

## Not measured / not verified

- Whether `orcapulse.io` resolves, its live HTTP status and redirect chain, TLS, and actual HTML served
- Whether `orcapulse.io` is deployed from `Trumpjio/Marketing-Agency-`, which branch, and which commit / Hostinger directory is live
- Browser screenshots or rendering at widths 375, 390, 768, 1280, 1440 px
- Lighthouse/PageSpeed scores, Core Web Vitals, console/network failures, performance field data
- Sitemap URL HTTP 200/indexability/canonical outcomes on production
- Google Search Console/Bing Webmaster Tools, ranking/index coverage, traffic and conversion analytics
- Real CTA/Calendly completion, live email delivery, admin login and reset/recovery transactions
- Proof behind external trust claims, testimonials, case-study performance, certifications
- Actual mobile clipping and WCAG conformance

Browser-tool inability to access a domain is not proof that the domain is offline. No performance scores, traffic losses, ranking losses or conversion losses are invented here.

## Release checklist

### P0 — release/source ownership
- [ ] In Hostinger, identify the document root serving `orcapulse.io`.
- [ ] Save a production backup and record file hashes / exact Git SHA.
- [ ] Compare production home page, CSS/JS and assets with candidate branch.
- [ ] Confirm that the Ioka pages are not a separate project/legacy site.
- [ ] Keep changes in staging/review branch until this is settled.

### P1 — identity, SEO, security
- [ ] Confirm the actual intended page inventory and OrcaPulse positioning for .io.
- [ ] Align all production canonicals, OG URLs, JSON-LD URLs, absolute internal links, settings, sitemap, and robots to `https://orcapulse.io`.
- [ ] Remove/rotate all exposed admin credentials; ensure no default credential remains in code or history.
- [ ] Configure trusted `ORCAPULSE_BASE_URL=https://orcapulse.io`.
- [ ] Confirm `data/.auth.json`, reset tokens and recovery material cannot be fetched publicly and use restrictive permissions.
- [ ] Verify email delivery and that reset links are HTTPS, single-use and expire correctly.
- [ ] Check all sitemap URLs return 200, are indexable and canonical before submission.

### P2 — content, accessibility, conversion
- [ ] Add complete article metadata/schema to the sampled six articles using real values.
- [ ] Correct inappropriate LocalBusiness schema on city/country pages.
- [ ] Replace placeholder contact details with verified phone/email/WhatsApp or remove them.
- [ ] Reconcile product pricing language and substantiation of trust claims on the real `.ai` source.
- [ ] Test mobile accordion with keyboard and screen reader.
- [ ] Test all navigation, internal/external links, CTA and booking journeys.
- [ ] Validate layout, horizontal overflow, tap targets and focus at the required viewports.

### P3 — monitoring
- [ ] Run HTML/schema validation, PHP lint and dependency/static checks.
- [ ] Run rendered-browser functional regression and accessibility checks.
- [ ] Run Lighthouse/PageSpeed and establish field Core Web Vitals baseline.
- [ ] Verify robots and sitemap directly over HTTPS; submit sitemap in Search Console/Bing.
- [ ] Monitor crawl errors, canonical selection, forms/booking conversions and 404s after launch.

## Source links
- Repository: https://github.com/Trumpjio/Marketing-Agency-
- Audited branch: https://github.com/Trumpjio/Marketing-Agency-/tree/deploy
- Remediation branch: https://github.com/Trumpjio/Marketing-Agency-/tree/fix/orcapulse-source-audit-remediation
- Existing source audit PR: https://github.com/Trumpjio/Marketing-Agency-/pull/2
- Product pricing page referenced in prior research: https://www.orcapulse.ai/pricing

**Current status:** source-level fixes recorded in a non-production branch; not merged or deployed. Critical unresolved blocker is confirming the actual production source for `orcapulse.io`.

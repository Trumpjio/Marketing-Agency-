# OrcaPulse Website Audit — Source Review

**Audit date:** 15 October 2026  
**Audit type:** Repository/source inspection. Not a live production crawl, performance test, or hands-on browser/device test.  
**Scope reviewed:** `Trumpjio/Marketing-Agency-`, branch `deploy`. No production files or website code have been modified.

## A. Executive summary

The most important finding is a source-of-truth mismatch. The inspected `deploy` branch appears configured as an Ioka website, while the expected project/domain context is OrcaPulse. Before making SEO or security fixes, confirm that this branch is actually the source intended for the live OrcaPulse website. Applying domain-wide edits to the wrong source could damage the existing site.

The branch contains 89 URLs in `sitemap.xml`; all 89 corresponding paths were found as files in this branch. This confirms repository inventory only, not that all URLs work in production.

## B. Source and inventory status

- Repository: `Trumpjio/Marketing-Agency-`
- Reviewed branch: `deploy`
- Repository default branch: `main`
- Sitemap inventory: 89 listed URLs, using the `ioka.online` host.
- Branch file presence: all 89 sitemap paths were found in the branch.
- Live status: not independently verified. The browser inspection route could not fetch the production domain, robots file, or sitemap.
- `pricing.html` and `signup.html`: not found in the inspected `deploy` branch. Their live availability is unknown; do not report them as production 404s without testing production.

## C. Critical issue register

| ID | Priority | Area | Evidence | Recommended action |
|---|---|---|---|---|
| SRC-001 | P0 — blocker | Source / brand / canonical domain | `index.html` uses title “Specialist Growth Marketing Agency | Ioka”, canonical and OG URL `https://ioka.online/index.html`, and Ioka branding. `data/settings.json` also contains Ioka branding and `https://ioka.online`. | Confirm the correct repository, branch, production source, brand and canonical domain before code edits. Do not bulk-replace strings until the source of truth is established. |
| SEO-001 | P1 — high | Sitemap domain | `sitemap.xml` lists 89 URLs under `https://ioka.online/`. | After confirming canonical domain, generate the sitemap from the verified production URL inventory, with only canonical, indexable, intended URLs. |
| SEO-002 | P1 — high | Robots sitemap directive | `robots.txt` ends with `Sitemap: https://orcapulse.com/sitemap.xml`, while the sitemap contents use `ioka.online`; intended domain context is `orcapulse.io`. | Align robots sitemap URL, sitemap URLs, canonicals and internal absolute URLs with the confirmed production domain in one controlled change. |
| SEC-001 | P1 — high | Admin credential management | `admin/config.php` includes a hardcoded initial-password fallback. | Treat the fallback as exposed: remove the production fallback, rotate the admin password, verify password hashing/storage, and ensure no secret is committed. Do not place credentials in reports or commits. |
| SEO-003 | P2 — medium | Article metadata consistency | Sampled article pages including `insight-aeo-geo-in-practice.html`, `insight-death-of-the-generalist-gcc.html`, `insight-dubai-vs-riyadh-budgets.html`, `insight-first-ninety-days.html`, `insight-flat-fees-defense.html` and `insight-one-metric-retention.html` lack `og:url` and JSON-LD schema in the inspected source. | Add consistent, page-specific Open Graph URL metadata and suitable structured data only after the correct canonical domain/template is confirmed. Avoid duplicative or inaccurate schema. |
| SEO-004 | P2 — medium | Local schema accuracy | 17 city pages and 9 country pages include `LocalBusiness` structured data without address or telephone fields, using name/url/area served. | Validate whether a real staffed local location exists in each area. Use truthful business details where applicable; otherwise consider an appropriate Organization/ProfessionalService representation with `areaServed`, subject to schema review. Do not invent local addresses or phone numbers. |
| A11Y-001 | P2 — medium | Mobile navigation semantics | The mobile navigation uses clickable-looking `div.nav__mobile-heading` elements for section headings in the source and CSS styles them as interactive. A `div` does not provide native button keyboard semantics by default. | Inspect and test the actual event logic, then use semantic buttons for expandable groups, with accessible names, `aria-expanded`, controlled-panel relationships and visible focus. Confirm keyboard operation before marking resolved. |
| ADMIN-001 | P2 — medium | Password reset messaging | `admin/reset.php` error copy suggests `admin/config.php` must be writable, while inspected logic saves the password hash through `data/.auth.json`. | Correct the troubleshooting message to match actual storage and deployment requirements; test reset success/failure, token expiry and single-use handling. |
| ADMIN-002 | P2 — medium | Reset link host handling | `admin/forgot.php` builds its reset URL from the incoming `HTTP_HOST` and directory path. | Validate host against a configured canonical origin and test the deployed subdirectory/alias behavior. Avoid trusting arbitrary host headers when building recovery links. |

## D. Technical SEO

**Confirmed from source**
- Canonical, Open Graph, sitemap and robots host configuration are inconsistent across the inspected branch.
- The `deploy` sitemap contains 89 Ioka-host URLs with a static `lastmod` date of 7 October 2026.
- Sampled article pages are missing `og:url` and structured data.
- Sampled city/country pages use LocalBusiness schema without visible address/telephone properties.

**Not established**
- Live HTTP status codes, redirect chains, canonical indexing decisions, robots accessibility in production, Core Web Vitals, Google Search Console coverage, analytics attribution, backlink quality, actual duplicate-content indexing, or production sitemap submission status.

**Recommended checks after source confirmation**
1. Crawl the production host and record HTTP status, indexability, canonical, title, description, H1, internal links and schema for each discovered URL.
2. Compare sitemap entries against canonical, indexable 200-status pages. Remove redirected, broken, noindex or noncanonical URLs only after verifying replacement targets.
3. Check redirect rules and choose one canonical hostname/protocol.
4. Validate structured data and metadata using current validators and production HTML.
5. Confirm analytics, consent handling and conversion events before interpreting performance.

## E. UX/UI and responsive review

This was a source inspection, not a visual browser/device review. Visual alignment, clipping, overlap, page speed and real-device behavior have **not** been certified.

Source-level observations:
- CSS includes responsive breakpoints around 1000/900, 760 and 420 pixels in the inspected files.
- A skip link and focus-visible styles exist.
- The mobile hamburger is styled at approximately 44×44 pixels.
- The mobile navigation’s expandable headings appear to use generic div elements; keyboard semantics require correction/verification.

Recommended validation viewports: 375×812, 390×844, 768×1024, 1280×800 and 1440×900. Test every page type, menus, forms, accordions, sliders, sticky elements, footer and dialogs. Confirm no horizontal overflow, hidden controls or overlapping content.

## F. Conversion and analytics

The settings file’s CTA URL points to `https://calendly.com/orcapulse/intro-call` while the rest of the settings use Ioka branding and domain. This could be intentional, but must be confirmed after the brand/source mismatch is resolved.

No analytics access or event stream was available, so conversion rate, lead quality, form completion and campaign attribution cannot be reported.

Recommended: verify the primary conversion path end-to-end; validate CTA links on mobile; confirm success states and tracking for booked calls/forms; ensure privacy/consent requirements are met. Do not add new features as part of this audit.

## G. Accessibility validation plan

Target: WCAG 2.2 AA. A conformance claim cannot be made from static source alone.

Test:
- Keyboard-only traversal and visible focus
- Semantic heading and landmark order
- Menu/accordion button semantics and expanded state
- Contrast for text, links, focus indicators and controls
- Image alternative text and decorative image handling
- Form labels, required fields, errors and status announcements
- Zoom/reflow and 320 CSS-pixel layout
- Reduced motion preferences
- Touch target size and spacing
- Screen reader announcement for dynamic navigation and dialogs

Use automated checks as a supplement; manual keyboard and assistive-technology tests are required.

## H. Security and admin review

**Source-level risks to investigate**
- Hardcoded initial password fallback in `admin/config.php`.
- Recovery links are constructed using the request host.
- Reset page’s permission/error guidance appears inconsistent with the implementation.

**Positive controls observed in source**
- Login code includes CSRF handling, honeypot and rate-limiting logic.
- Password reset token logic has a one-hour expiry and single-use behavior.
- The root `.htaccess` includes security headers and blocks certain private file patterns.

These observations are not a penetration test. Validate admin directory access controls, file permissions, data directory exposure, cookie flags, HTTPS enforcement, mail delivery, logs, backups and rate limits in a safe staging environment. Rotate exposed credentials before release.

## I. Priority roadmap

### Phase 0 — must resolve before edits
1. Identify the actual live website source (repository and branch) and confirm that OrcaPulse is the target brand/domain.
2. Capture a production URL inventory and verify the current domain/redirect/canonical behavior.
3. Rotate the admin credential and remove hardcoded fallback secrets in a controlled patch.

### Phase 1 — domain and crawl correctness
1. Align canonical URLs, OG URLs, sitemap URLs and robots sitemap directive.
2. Validate all sitemap entries against production status, indexability and canonical.
3. Correct article metadata/schema omissions and review LocalBusiness structured data accuracy.

### Phase 2 — admin reliability and accessibility
1. Replace clickable generic mobile navigation headings with accessible controls where needed.
2. Fix reset troubleshooting copy and validate reset host/token behavior.
3. Run WCAG-focused manual checks and automated scans.

### Phase 3 — performance and conversion verification
1. Measure live Core Web Vitals and page weight using real URLs and device profiles.
2. Verify CTA/book-call flow, forms, event tracking and mobile behavior.
3. Re-test critical paths after any approved fixes.

## J. Developer specifications

- Do not redesign or restructure pages during the remediation pass.
- Preserve existing page content, navigation and URLs unless a documented migration/redirect plan is approved.
- Fix one category at a time; validate on staging before production.
- Do not invent business addresses, phone numbers, performance metrics or analytics results.
- Use a single configured canonical origin rather than scattered hard-coded hosts.
- Build sitemap output from verified canonical/indexable pages.
- Remove hardcoded admin credentials and use a secure, documented password provisioning process.
- Use semantic native controls for interactive navigation and ensure accessible expanded/focus state.
- Add page-specific structured data only where the underlying details are accurate.
- Provide change list, tests run, before/after evidence and rollback plan for each patch.

## K. Limitations

- The live domain could not be fetched through the available browser inspection path during this audit.
- This is primarily a repository-source review. It does not establish production rendering, 404s, redirects, uptime, ranking positions, real user experience, Core Web Vitals, conversion metrics or accessibility conformance.
- `pricing.html` and `signup.html` being absent from this branch is not evidence that they are absent from production.
- A previous quick-parser warning about short article descriptions was rechecked and withdrawn; those descriptions are not listed as issues.
- No source files have been changed, no commits have been pushed, and no deployment has been triggered.

## L. Release gate

**Status: Not ready for website-code deployment.** First confirm that `Trumpjio/Marketing-Agency-` branch `deploy` is the correct production source for OrcaPulse. It currently contains Ioka branding and a domain mismatch, so a blind edit or deployment could publish the wrong site. This report can be committed separately without changing website code.

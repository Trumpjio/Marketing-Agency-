# OrcaPulse design, content-slot and conversion remediation — 16 October 2026

## Scope

Reviewed the `deploy` source from a separate branch, `fix/orcapulse-professional-layout`. The audit covered 89 root-level HTML pages, the shared CSS cascade, the Contact Us submission path, CMS slot markup, generated editorial visuals, and responsive browser regressions. No client names, testimonials, awards or performance outcomes were invented.

## Findings fixed

### 1. Empty/placeholder content and vertical gaps — high priority

- Scanned all 89 HTML pages for `data-op-empty="true"`, empty `OP-COLLECTION` / `OP-BLOG` marker pairs, known empty source-driven sections and generic placeholder copy. The current source did not contain any remaining explicitly marked empty sections or empty comment-delimited CMS slots at the first scan; this finding appears to have described an earlier version of the site.
- Removed 27 visible map-admin instruction paragraphs such as “Switch this section on in the admin…”. Those pages already had actual OpenStreetMap embed markup, but the map section was marked hidden. Removed the hidden flag so the real map can render; did not replace it with fake office/location content.
- Removed 18 generic “Related reading.” paragraphs that were presented as copy but added no information.
- Added ongoing checks that fail if empty slot markers, map-admin placeholder copy, hidden map sections or generic “Related reading.” filler return.
- Preserved sections containing real editorial copy, links, diagrams or map embeds. The normalizer deliberately does not fabricate team bios, awards, testimonials or case studies to fill space.

### 2. Lead form and booking path — high priority

- Kept `contact.html` as the single public enquiry form page. All other public pages must remain form-free.
- Kept the home-page CTA and primary CTA buttons booking-led. The regression validator checks that primary CTAs point to Cal.com or Calendly and that the home page still links to the Contact Us form.
- Verified the Contact Us form uses asynchronous submission and accessible pending/success/error feedback; it keeps entered values on a failed submission and resets only after a positive server response.
- The backend validates fields, enforces its rate limiter, writes leads atomically with restrictive permissions, and exposes an authenticated admin Enquiries inbox with CSV export. Email notification is an additional path; the admin inbox remains the durable review path. Actual Hostinger mail delivery was not verified in this source-only pass.

### 3. Visual proof and repetitive content — high priority

- Changed the home-page “Case studies” label to “Illustrative workstreams”. The section now explicitly says the examples describe working methods, not published client case studies or measured client performance. This prevents illustrative diagrams from being mistaken for evidence.
- Replaced unsupported first-party metrics where the pages attributed numbers to internal audits, internal account books or unsourced performance uplifts. These tiles now explain a reproducible measurement method rather than displaying unverifiable results.
- Reworded the unsupported 20–40% paid-media waste claim as a structured account/tracking/attribution audit process.
- Corrected first-party claims in selected thought-leadership pages, including unsupported retention, LinkedIn reach, Arabic SEO and property-campaign outcome claims. Specific figures are not substituted with new figures.
- Added six reusable original SVG editorial illustrations for playbooks, field notes, essays, teardowns, benchmarks and cross-market topics. The normalizer inserted 27 image instances into editorial article/list visuals with meaningful alt text. These illustrations are intentionally abstract; they do not imply real clients, awards or results.
- The existing service/industry pages also use original process diagrams rather than fabricated case-study photography. A real verified case study should only be published when the agency provides the client-approved case data, attribution window, baseline and outcome evidence.

### 4. CSS conflicts and mobile layout — high priority

- Aligned the overlapping `glass.css` and `OrcaPulse.css` color/font tokens around the OrcaPulse navy/blue palette.
- Corrected the desktop hero hierarchy: the navigation previously ended around y=112 while the hero H1 began around y=52, causing visible overlap. Also reduced the oversized full-width hero capsule, oversized label pills, excessive hero space and card padding/radii.
- Fixed the footer grid: the markup has two direct children (brand and link navigation), but the previous CSS declared four top-level columns. The footer now uses a brand column plus a four-column link grid.
- Removed global `overflow-x: hidden/clip` masking from the base site rules. Horizontal overflow must be diagnosed at the offending component rather than silently clipped.
- Added CI checks for header/H1 collision, an oversized desktop hero CTA, an excessively tall text-only hero, a compressed footer and horizontal overflow. Responsive Chromium screenshots/diagnostics are captured for 89 HTML pages at five viewport widths; the design-review screenshot harness covers representative pages at 390, 768 and 1440 px.

## Source files changed

- `styles/OrcaPulse.css`, `styles/glass.css`: brand tokens, heading scale, spacing, button geometry, card treatments, hero, footer, editorial art and overflow behavior.
- `scripts/normalize_content_slots.py`: repeatable empty-slot, map placeholder, related-copy, editorial illustration and unsupported-stat cleanup.
- `scripts/validate_conversion_paths.py`: guards for the single-form rule, booking-only primary CTAs, empty placeholders, hidden maps, unsupported first-party statistics and global overflow masking.
- `scripts/smoke_site_responsive.cjs` and `.github/workflows/quality-checks.yml`: responsive screenshots, computed geometry diagnostics and layout regression checks.
- `assets/illustrations/editorial-*.svg`: six reusable editorial art assets.
- Affected root-level HTML pages include `index.html`, `contact.html`, `insights.html`, location templates, industry/service pages and the affected insight articles.

## Verification and release status

- Source/security validation has passed on the review branch after the new content guards were introduced.
- The normalizer first scanned all 89 HTML pages. Its initial run removed 27 map-admin placeholder paragraphs and 18 generic “Related reading.” paragraphs, added 27 editorial SVG instances and changed 42 HTML files. Later targeted passes removed additional unsupported stat/claim cards.
- Browser-responsive tests run 89 pages at five viewport widths and exercise mobile navigation, accordions, form success/validation/rate-limit, JS errors, local resource requests and horizontal overflow. Confirm the latest browser job is green before merging.
- Pull request: [#7 — design/content/conversion remediation](https://github.com/Trumpjio/Marketing-Agency-/pull/7).
- This branch has **not** been confirmed live on Hostinger. Production rendering, real lead delivery, mail configuration, public DNS and real-user performance remain post-deploy smoke checks.

## Remaining source-evidence rule

Do not publish a numerical client result, named testimonial, award, logo or case study unless the underlying evidence is supplied and disclosure is permitted. Editorial market estimates that are explicitly presented as external facts should retain a directly accessible source link and date; the current pass prioritized undocumented *first-party performance* numbers rather than inventing citations.

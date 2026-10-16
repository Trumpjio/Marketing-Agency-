# OrcaPulse Hostinger Release Plan

**Date:** 16 October 2026  
**Repository:** `Trumpjio/Marketing-Agency-`  
**Production domain:** `https://orcapulse.io`  
**Historical test domain:** `https://ioka.online`  
**Implementation branch:** `fix/orcapulse-hostinger-release`  
**Deployment target:** Hostinger Git deployment / website document root  
**Status:** Implementation branch created; no production changes or deployment performed.

## Release objective

Publish the approved public marketing website directly through Hostinger, without using Render. Preserve the existing agency page structure and content while correcting brand/domain consistency, admin security, SEO metadata, accessibility, and deployment safety.

## Confirmed source findings

- The `deploy` branch has 89 sitemap URLs, all using `ioka.online`.
- `index.html` metadata, JSON-LD, shared styles and settings identify Ioka / `ioka.online`.
- `robots.txt` points to `orcapulse.com/sitemap.xml`, which conflicts with the confirmed production domain `orcapulse.io`.
- `data/settings.json` uses Ioka brand and email settings; the CTA points to an OrcaPulse Calendly URL.
- The `main` branch contains only a README, so it is not a deployable website source.
- Security/accessibility remediations are prepared on `fix/orcapulse-source-audit-remediation`; GitHub Actions passed on the latest recorded remediation commit.
- The exact Hostinger document root, connected branch, and deployed commit have not yet been verified.

## Implementation checklist

### P0 — Source and release safety
- [ ] Verify Hostinger's configured repository, branch, deployment directory and currently deployed commit.
- [ ] Back up the current Hostinger document root before release.
- [ ] Compare the live site files with this source branch; do not replace a different site.
- [ ] Keep all work on this implementation branch until tests and review pass.

### P1 — Brand, domain, conversion and security
- [ ] Use `OrcaPulse` and `https://orcapulse.io` consistently in page titles, canonical tags, Open Graph/Twitter metadata, JSON-LD, absolute internal URLs, settings and sitemap.
- [ ] Replace historical `ioka.online` contact details only with verified OrcaPulse contact details.
- [ ] Remove placeholder phone/WhatsApp values; do not invent business contact data.
- [ ] Preserve the intended Calendly destination only after confirming it is the correct production booking link.
- [ ] Keep the security remediation for admin credentials, password recovery, reset-token persistence and least-privilege file permissions.
- [ ] Rotate any administrator credential previously committed in source.

### P2 — SEO, accessibility and quality
- [ ] Rebuild sitemap from the actual published page inventory and confirm all URLs use the production origin.
- [ ] Align `robots.txt` with the verified sitemap URL.
- [ ] Add accurate article social metadata and structured data where missing.
- [ ] Review city/country schema so it does not imply unverified physical offices.
- [ ] Test mobile navigation, keyboard operation, responsive widths, focus states and horizontal overflow.
- [ ] Run PHP syntax, JavaScript syntax, sitemap XML and security regression checks.

### P3 — Hostinger release
- [ ] Configure `ORCAPULSE_BASE_URL=https://orcapulse.io` and a new secure `ORCAPULSE_ADMIN_PASSWORD` in the hosting environment if supported by the PHP hosting setup.
- [ ] Test admin login, password reset/recovery, content editing, image uploads and booking/contact actions in staging.
- [ ] Deploy the reviewed branch through Hostinger Git integration, not Render.
- [ ] Verify HTTPS, redirects, canonical URLs, key pages, sitemap and browser cache behavior after deployment.

## Explicit exclusions

- Do not modify the separate `Trumpjio/Ai-` Business OS repository.
- Do not change DNS or production files during source review.
- Do not deploy the Ioka-branded `deploy` branch as-is.
- Do not fabricate contact details, certifications, performance claims or local office addresses.
- Do not claim live/browser/end-to-end tests passed unless they were actually run.

## Current blocker

The repository contains a complete marketing-site source, but its identity is inconsistent with the confirmed production domain. The Hostinger repository/branch/document-root mapping must be verified before production replacement. Work can proceed safely on this branch; production deployment remains blocked until source mapping and staging verification are complete.

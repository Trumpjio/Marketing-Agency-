# OrcaPulse Hostinger Release Plan

**Updated:** 16 October 2026  
**Repository:** `Trumpjio/Marketing-Agency-`  
**Production domain:** `https://orcapulse.io`  
**Historical test domain:** `https://ioka.online`  
**Release branch:** `fix/orcapulse-hostinger-release`  
**Hostinger deployment branch:** `deploy`  
**Hostinger install directory:** `public_html`  
**Status:** Source changes and static validation are prepared. Production deployment still requires a Hostinger backup, a check of the existing admin password hash/configuration, and the manual Hostinger redeploy.

## Implemented on the release branch

- Normalized the website's Ioka branding and `ioka.online` canonical/social/structured-data URLs to OrcaPulse and `https://orcapulse.io`.
- Updated `data/settings.json`, `robots.txt`, and the 89-entry XML sitemap. Sitemap URLs now use `orcapulse.io`; `lastmod` was refreshed to 16 October 2026 for the entries that were previously dated 7 October.
- Fixed the normalized stylesheet filename reference by supplying `styles/OrcaPulse.css`, so pages resolve the stylesheet on case-sensitive hosting.
- Removed the unverified legacy public email and zero-filled WhatsApp links; no replacement contact information was invented. The existing Calendly call-to-action remains.
- Removed the empty separator wrapper left on the contact page.
- Hardened admin password initialization, password hashing/token storage and password recovery; removed the committed default admin password; avoided trust in request `HTTP_HOST` when building reset URLs.
- Updated the mobile navigation accordion to use keyboard-operable buttons and ARIA state.
- Added missing `og:url` and `BlogPosting` metadata to insight articles where absent.
- Replaced service-area `LocalBusiness` structured-data nodes with `Service` nodes using `areaServed`, avoiding claims of unverified staffed offices.
- Added validation for JSON-LD, local HTML/assets, settings, sitemap, PHP/JavaScript syntax, and known security regressions.

## Validation evidence

Latest completed site-wide normalization and validation run: [GitHub Actions](https://github.com/Trumpjio/Marketing-Agency-/actions/runs/38860264917).

The run reported:
- 89 HTML pages.
- 89 sitemap URLs.
- 9,044 local HTML/asset references checked.
- 27 service-area `LocalBusiness` schema nodes converted.
- No legacy Ioka brand/domain references or known placeholder public contact details remaining in scanned site text.
- Validation passed.

The release validation workflow also passed PHP lint, JavaScript syntax, sitemap XML, settings, local links/assets, structured-data checks and security regression guards on the preceding source revision. After the final sitemap and documentation edits, rerun the workflow before merging.

## Hostinger deployment checklist

1. Before deployment, make a full backup of the current `public_html` directory. The Hostinger Git integration can overwrite existing files.
2. Hostinger is configured for repository `Trumpjio/Marketing-Agency-`, branch `deploy`, install directory `public_html`, with manual deployment. Merge this reviewed release to `deploy`, then manually click Hostinger's **Redeploy**.
3. Admin login: the old committed password has been removed. Preserve the existing ignored runtime file `data/.auth.json` if it contains a valid password hash. If no hash exists, configure `ORCAPULSE_ADMIN_PASSWORD` securely before the first admin login; otherwise initial login will be unavailable. Never restore the old hardcoded password.
4. Public email, phone, WhatsApp and address remain blank until the owner provides verified production details. The Calendly CTA is still present and needs a real browser click test after deployment.
5. After redeploy, verify homepage and inner pages, stylesheet and images, HTTPS and redirects, admin login/recovery, page editing/uploads, canonical URLs, contact CTA, and `https://orcapulse.io/sitemap.xml` in a browser.

## Remaining external verification

- No connected Hostinger action is available here, so the manual Hostinger deployment itself cannot be triggered or confirmed from GitHub.
- Live browser rendering, real PHP/admin interaction, email delivery, existing runtime-file persistence and post-deployment smoke tests must be checked in Hostinger after the backup/redeploy.
- Product-site pricing/claim findings for `orcapulse.ai` are outside this repository and must be fixed in that product-site source separately.

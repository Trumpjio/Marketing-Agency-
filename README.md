# OrcaPulse Marketing Website

Static HTML, CSS, JavaScript, and PHP admin website for the OrcaPulse growth marketing agency.

## Source of truth

- Repository: `Trumpjio/Marketing-Agency-`
- Website/release branch: `deploy`
- Intended public origin: `https://orcapulse.io/`
- Hostinger target recorded in the deployment setup: `public_html`
- Deployment mode: manual Git redeploy; merging or pushing to GitHub does not by itself make the live website update.

The populated marketing website lives on `deploy`. GitHub's default branch is `main`; do not change Hostinger to `main` unless the repository layout is deliberately changed and validated first.

This repository is the marketing-agency website, not the separate `Trumpjio/Ai-` Business OS repository or the OrcaPulse product application associated with `orcapulse.ai`.

## Project structure

- `index.html` — homepage
- Other root `*.html` pages — services, industries, cities, countries, insights, and policy pages
- `styles/` — global and OrcaPulse-specific styles
- `scripts/main.js` — navigation and frontend interactions
- `robots.txt`, `sitemap.xml`, `llms.txt` — crawler and page-discovery files
- `admin/` — PHP administration panel and recovery flow
- `data/settings.json` — public site settings and call-to-action URL
- `uploads/` — uploaded assets managed by the admin panel
- `scripts/validate_orcapulse_site.py` — static pre-release validator
- `.github/workflows/quality-checks.yml` — PHP/JavaScript and release validation workflow

## Local validation

Run these checks from the repository root in an environment with PHP, Node.js, and Python 3 installed:

```bash
find admin -type f -name '*.php' -print0 | xargs -0 -n1 php -l
node --check scripts/main.js
python3 scripts/validate_orcapulse_site.py
python3 - <<'PY'
import xml.etree.ElementTree as ET
ET.parse("sitemap.xml")
print("sitemap.xml: valid XML")
PY
```

The validator checks the page inventory against sitemap URLs, local HTML/asset references, structured data syntax, production domain references, shared settings, and selected security regressions. These checks are source-level tests; they do not replace a real browser test or a post-deployment check.

## Admin secrets and runtime data

Never commit passwords, tokens, reset data, or runtime authentication files. Configure these values securely in the hosting environment when required:

- `ORCAPULSE_ADMIN_PASSWORD` — first-time admin password initialization
- `ORCAPULSE_BASE_URL=https://orcapulse.io` — trusted password-reset origin
- `ORCAPULSE_ADMIN_EMAIL` — verified address used for admin recovery
- `ORCAPULSE_LEAD_NOTIFY_EMAIL` — optional verified recipient for new website-enquiry notifications
- `ORCAPULSE_LEAD_NOTIFY_FROM` — optional verified sender address on the `orcapulse.io` domain; notification mail is skipped unless both lead-notification settings are valid and PHP `mail()` is available

Lead enquiries are saved to the authenticated admin Enquiries inbox first. Email notification is best-effort and cannot replace the saved inbox record. Configure and test the notification recipient/sender in Hostinger before relying on email alerts; do not reuse a product-domain recovery email unless the business owner has explicitly verified that routing.

Preserve an existing valid `data/.auth.json` across deployments if it contains the active password hash. Back it up securely before any deployment that could replace or remove runtime files. Confirm environment-variable support and file persistence in Hostinger before relying on password recovery. Do not put secrets in the repository, web-accessible settings, or logs.

Public contact fields are intentionally blank where details have not been verified. The booking CTA is configured in `data/settings.json`; check its destination in a browser before launch.

## Hostinger release checklist

1. Confirm in Hostinger that the connected repository is `Trumpjio/Marketing-Agency-`, branch `deploy`, and install directory `public_html`.
2. Download a backup of the current production `public_html` and separately preserve required runtime data securely.
3. Review the Git diff and confirm release validation passed for the exact commit to be deployed.
4. Trigger Hostinger's manual **Redeploy** action. A GitHub merge is not proof of deployment.
5. Verify the homepage and representative service, industry, location, insight, contact, and policy pages; check HTTPS, canonical URLs, sitemap, robots.txt, CSS/JS assets, menu interactions, booking CTA, and admin authentication/recovery where access permits.
6. Record the deployed commit and test evidence. If the live site cannot be accessed, report the limitation rather than marking it passed.

A source-level validation pass does not verify DNS, the live Hostinger document root, email delivery, the persistence of runtime files, live visual rendering, field Core Web Vitals, or Search Console indexing.

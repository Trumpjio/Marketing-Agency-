# Recommended WordPress Plugins for OrcaPulse

A short, opinionated list. Install in this order — essential first, then quality-of-life, then optional.

## Essential (install day one)

### 1. Elementor (free) — Page Builder
- **Why:** You asked for Elementor compatibility. This is the base editor.
- **Where:** WP Admin → Plugins → Add New → search "Elementor" → Install → Activate
- **Setup:** no setup needed, works immediately

### 2. Elementor Pro (paid, ~$59/yr)
- **Why:** Unlocks Theme Builder (reusable header, footer, single-post templates), Form widget, Popup Builder, Nav Menu widget. Essential for a real agency site.
- **Where:** https://elementor.com/pro/
- **Setup:** Install via License Manager after purchase

### 3. Rank Math SEO (free) — SEO + Schema
- **Why:** Handles title tags, meta descriptions, canonical URLs, Open Graph tags, XML sitemap, schema markup, redirects. Replaces the hand-coded SEO in the static site.
- **Where:** Plugins → Add New → search "Rank Math"
- **Setup:** Run the Setup Wizard (10 min). Configure:
  - Local SEO (if you have a physical address)
  - Sitemap
  - Rich Snippets per post type

### 4. Caching — pick one:
   
   **Option A: WP Rocket (paid, ~$59/yr) — recommended**
   - All-in-one caching + lazy loading + minification + CDN integration
   - https://wp-rocket.me/
   
   **Option B: W3 Total Cache (free)**
   - More technical but free
   - Plugins → Add New → search "W3 Total Cache"

## Highly recommended

### 5. Imagify (free tier + paid) — Image optimisation
- **Why:** Auto-compresses uploaded images, converts to WebP, serves optimised sizes
- **Where:** Plugins → Add New → search "Imagify"
- **Setup:** Register free account for API key, enable WebP conversion

### 6. Wordfence Security (free) — baseline security
- **Why:** Firewall, malware scanner, 2FA, login hardening
- **Where:** Plugins → Add New → search "Wordfence"

### 7. UpdraftPlus (free) — Backups
- **Why:** Scheduled off-site backups (Google Drive, Dropbox, S3)
- **Where:** Plugins → Add New → search "UpdraftPlus"
- **Setup:** schedule daily DB backup + weekly full backup

## For a form or lead-gen page

### 8. WPForms (free tier + paid)
- **Why:** If you ever need a form that is NOT the Calendly booking (contact, enquiry, etc.)
- **Where:** Plugins → Add New → search "WPForms"
- Alternative: use Elementor Pro Form Widget (already included)

## For Arabic / multilingual

### 9. Loco Translate (free)
- **Why:** Translate theme strings into Arabic / French / any language
- **Where:** Plugins → Add New → search "Loco Translate"

### 10. WPML or Polylang
- **Why:** If you want full multilingual site (Arabic + English versions of every page)
- **WPML:** Paid, more features. **Polylang:** Free tier available.

## For CRM / lead handling

### 11. HubSpot WordPress Plugin (free)
- **Why:** Pipe form submissions / tracking into HubSpot CRM
- **Where:** Plugins → Add New → search "HubSpot"

## Analytics

### 12. Google Site Kit (free)
- **Why:** Google-made plugin that connects Analytics 4, Search Console, PageSpeed Insights, AdSense
- **Where:** Plugins → Add New → search "Site Kit by Google"

### 13. MonsterInsights (free tier)
- Alternative to Site Kit if you want a nicer dashboard
- Shows GA data inside WordPress

## For calendars / booking (if moving off Calendly)

### 14. Amelia (paid, ~$79/yr)
- Elementor-compatible appointment booking
- Multiple staff, Google Calendar sync, payments

## What NOT to install

- **Multiple SEO plugins** — pick one (Rank Math OR Yoast OR SEOPress). Running two breaks sitemaps.
- **Jetpack** — bloats page weight. Skip unless you specifically need its features.
- **Divi, Beaver Builder** — don't mix page builders. Elementor is enough.
- **Any "all-in-one" bundle plugin** that duplicates what you already have.

## Install sequence (first day)

1. Elementor + Elementor Pro
2. OrcaPulse theme (activate)
3. Rank Math SEO (run wizard)
4. WP Rocket OR W3 Total Cache
5. Imagify
6. Wordfence
7. UpdraftPlus (schedule backups immediately)
8. Google Site Kit (connect GA4 + Search Console)

Everything above is ~$130/yr in total for paid plugins (Elementor Pro + WP Rocket), or ~$0 if you use the free alternatives. The paid ones are worth it for an agency site.

# OrcaPulse — WordPress Theme Install Guide

This is the OrcaPulse WordPress theme + plugin recommendations, packaged so you can upload directly and edit visually in Elementor.

## 1. What's in the package

```
wordpress-theme/
├── orcapulse/              <-- the theme folder (zip this to upload)
│   ├── style.css           Theme header + design system
│   ├── functions.php       Theme setup, nav menus, Elementor support
│   ├── header.php          Shared header with hamburger nav
│   ├── footer.php          Shared footer with 4 columns
│   ├── index.php           Posts fallback
│   ├── page.php            Default page template
│   ├── single.php          Single blog post
│   ├── front-page.php      Homepage router
│   ├── 404.php             Error page
│   ├── readme.txt          WP standard readme
│   └── assets/
│       ├── glass.css       Full design system (2000+ lines)
│       └── main.js         Universal site script
├── orcapulse-theme.zip     <-- upload this via Appearance > Themes > Add New > Upload
├── starter-content.html    All static HTML pages (reference content to paste into Elementor)
└── INSTALL.md              This file
```

## 2. Required plugins

Install these from WordPress > Plugins > Add New:

### Essential (install first)
1. **Elementor** (free) — the page builder
2. **Elementor Pro** (paid) — unlocks theme builder, popups, forms, nav menu widget
3. **Rank Math SEO** (free) — manages SEO, schema, sitemap, redirects
4. **WP Rocket** (paid) OR **W3 Total Cache** (free) — page caching + minification for speed

### Highly recommended
5. **Imagify** (free tier) — image compression for Core Web Vitals
6. **Wordfence Security** (free) — baseline security
7. **UpdraftPlus** (free) — automatic backups

### Optional
8. **Loco Translate** (free) — translate theme strings (Arabic especially)
9. **Advanced Custom Fields** (free) — if you want to add structured fields to pages later

## 3. Install the theme

1. Zip the `orcapulse/` folder (or use the included `orcapulse-theme.zip`).
2. WordPress admin → Appearance → Themes → Add New → Upload Theme.
3. Choose the zip, upload, activate.
4. Appearance → Menus — create your menus (see Step 5 below).

## 4. Install Elementor

1. Plugins → Add New → search "Elementor" → Install → Activate.
2. Install Elementor Pro (if you have a licence) — Elementor Pro gives you the Nav Menu widget and the Theme Builder.
3. Elementor → Settings → verify "OrcaPulse" shows under "Theme Support" (it should auto-detect).

## 5. Create nav menus

The theme registers these menu locations. Each one is used in header or footer:

| Location | Purpose | Example items |
|---|---|---|
| `services` | Mobile panel — Services group | All services, Paid Media, SEO, etc. |
| `industries` | Mobile panel — Industries group | Real Estate, Hospitality, etc. |
| `locations-gcc` | Mobile panel — Locations GCC | UAE, Saudi, Qatar, Kuwait, Bahrain |
| `locations-global` | Mobile panel — Locations Global | USA, Canada, Australia, NZ |
| `studio` | Mobile panel — Studio group | Work, Insights, About |
| `footer-services` | Footer column 1 | 4 service links |
| `footer-industries` | Footer column 2 | 4 industry links |
| `footer-studio` | Footer column 3 | About, Insights, Locations, Book a Call |
| `footer-legal` | Footer column 4 | Privacy, Terms |

Appearance → Menus → Create a menu for each → tick its location → add page links.

## 6. Create the pages

The theme does **not** auto-create pages. You create them yourself in Pages → Add New, then edit with Elementor:

**Core pages:** Home, Services, Work, About, Insights, Locations, Privacy Policy, Terms

**Service pages (8):** Search & Discovery, Paid Media, Web & Commerce, Product & App, Social Presence, Content & Editorial, Film & Motion, Brand & Design

**Industry pages (4):** Real Estate, Hospitality, Dental Clinics, Fintech

**Country pages (9):** UAE, Saudi Arabia, Qatar, Kuwait, Bahrain, USA, Canada, Australia, New Zealand

**City pages (18):** Dubai, Abu Dhabi, Riyadh, Jeddah, Doha, Lusail, Kuwait City, Hawalli, Manama, Muharraq, New York, Miami, Toronto, Vancouver, Sydney, Melbourne, Auckland, Wellington

**Industry-city combo pages (12):** real-estate-marketing-dubai, dental-marketing-riyadh, etc.

The `starter-content.html` folder contains the HTML version of every page. You can copy any section's HTML directly into Elementor's **HTML widget** to get the exact same look. Or recreate section-by-section with Elementor's native widgets, using the CSS classes documented below.

## 7. Design system — CSS classes to use in Elementor

The theme ships the complete `glass.css` design system. Use these class names on any Elementor Section, Column, or Widget's **Advanced → CSS Classes** field:

### Layout
- `container` — centered content container (max-width 1200px)
- `section` — standard section padding
- `grid grid--2` / `grid grid--3` / `grid grid--4` — responsive grids
- `hero` — hero section (top of page, oversized H1)

### Components
- `glass` — glass-card component (frosted background, border)
- `feature-card` — simpler card with link styling
- `chips` — wrap bullet lists with this to render as pill chips
- `chip` — individual pill
- `btn btn--primary` — primary CTA button
- `btn btn--ghost` — secondary ghost button
- `faq-list` — wrap `<details class="faq">` elements
- `cta-block` — final CTA section
- `prose` — long-form reading column (max-width, nice line-height)
- `kicker` — small uppercase label above a section head
- `section-head` — section heading container
- `reveal` — fade-in-on-scroll animation (handled by main.js)

### Hero
- `hero__sub` — hero subheadline
- `hero__actions` — hero CTA row
- `hero__sub-links` — hero text-link row (below the primary CTA)

### Nav / Footer (don't customise — rendered by theme)
- `nav` / `nav__inner` / `nav__mobile-trigger` / `nav__mobile-panel`
- `footer` / `footer__inner` / `footer__cols` / `footer__col`

## 8. Elementor Pro — recommended workflow

If you have Elementor Pro:

1. **Templates → Theme Builder → Header** — build a single reusable header using the OrcaPulse CSS classes. Assign to "Entire Site".
2. **Templates → Theme Builder → Footer** — same for footer. Assign to "Entire Site".
3. **Templates → Theme Builder → Single Post** — one template for all `insight-*` blog posts.
4. **For each page**, use **Elementor Canvas** or **Elementor Full-Width** page template so the theme's header/footer render naturally.

If you don't have Elementor Pro, the theme's own `header.php` and `footer.php` render automatically.

## 9. Site settings to configure

### Site identity
Appearance → Customize → Site Identity:
- Site Title: OrcaPulse
- Tagline: A specialist growth partner for ambitious businesses. Rooted in the GCC, working globally.
- Logo: upload your logo (optional — the ORCA/PULSE text brand will render if no logo)

### Theme-specific CTA
Appearance → Customize → add these mods via the Customizer or wp-config:
- `orcapulse_cta_url` → https://calendly.com/orcapulse/intro-call (or your real Calendly URL)
- `orcapulse_cta_label` → Book a Call

### Reading
Settings → Reading:
- Front page displays: A static page → select your Home page
- Posts page: select your Insights page

### Permalinks
Settings → Permalinks: use "Post name" structure.

### SEO (Rank Math)
After installing Rank Math, run the Setup Wizard. Import OpenGraph defaults from the theme (already handled).

## 10. Speed and performance

Install **WP Rocket** (or W3 Total Cache):
- Enable page caching
- Enable minification for CSS and JS
- Enable lazy loading for images
- Enable WebP conversion (via Imagify)

For Core Web Vitals, aim for:
- LCP < 2.5s
- CLS < 0.1
- INP < 200ms

## 11. Known limitations / notes

- The theme does **not** auto-import the content of the static HTML pages. Elementor templates would need to be built manually per page, OR you can paste HTML sections into Elementor HTML widgets.
- The theme assumes you'll use **Elementor Pro Theme Builder** for header/footer long-term. If you don't have Pro, the theme's own PHP templates render instead.
- Images are **not** included. You'll need to upload your own via Media Library.

## 12. Support

Theme by OrcaPulse. For issues, contact your developer.

---

**Fast path to live site:**
1. Upload `orcapulse-theme.zip` → activate
2. Install Elementor + Elementor Pro + Rank Math + WP Rocket
3. Create menus (Appearance → Menus)
4. Create pages (Pages → Add New) and edit with Elementor
5. Set static front page (Settings → Reading)
6. Configure caching (WP Rocket defaults are good)
7. Submit sitemap to Google Search Console (Rank Math auto-generates)

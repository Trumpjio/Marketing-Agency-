# Maison — WordPress Theme

Premium, Cartier-inspired WordPress theme for a growth agency. Light palette, editorial serif typography, outcome-led content model. Built lean — no page builders, no bloat.

## What this is

A production-ready custom WordPress theme — not a page-builder shell. Everything is PHP templates + ACF-managed fields + ~900 lines of hand-written CSS. Designed to be fast, maintainable, and SEO/AEO/GEO ready out of the box.

## Requirements

- WordPress 6.4+
- PHP 8.1+
- **Advanced Custom Fields Pro** (required — the field groups are registered in `inc/acf-fields.php` using Repeater and Options Page, both ACF Pro features)

## Install

1. Zip the `maison/` folder → `maison.zip`
2. WordPress admin → **Appearance → Themes → Add New → Upload Theme**
3. Upload, activate
4. Install **Advanced Custom Fields Pro** (acf.com — paid plugin)
5. **Settings → Permalinks → Save** (flushes rewrite rules so `/work`, `/services`, `/llms.txt` work)

## Recommended plugin stack (5 plugins, no more)

| Plugin | Purpose |
|---|---|
| Advanced Custom Fields Pro | Dynamic fields for homepage, case studies, services (REQUIRED) |
| Rank Math Pro | SEO + schema (preferred over Yoast) |
| WP Rocket | Caching, lazy load, delay JS |
| ShortPixel / Imagify | Image compression + WebP/AVIF |
| Fluent Forms | Lightweight contact forms |
| WP Mail SMTP | Reliable transactional email |

**Do not install:** Elementor, Divi, WPBakery, Astra Pro, or any marketplace theme-builder. They will destroy performance and the design system.

## Content model

- **Pages:** Standard WP pages (About, Contact, Legal). The homepage is any page assigned to **Settings → Reading → Front page**.
- **Case Studies** (`case_study` CPT, slug `/work/*`): outcome-led portfolio entries with client, duration, KPIs, challenge/approach/outcome blocks.
- **Services** (`service` CPT, slug `/services/*`): capability deep-dives with promise, deliverables, process, FAQs (serialized to FAQPage schema).
- **Testimonials** (`testimonial` CPT, non-public): reusable quote content.
- **Posts** (`/blog/*`): long-form editorial / insights for SEO.
- **Taxonomies:** Industry (case studies), Capability (cross-reference).

## SEO / AEO / GEO built in

- **JSON-LD** on every page: Organization, WebSite, BreadcrumbList, Service, CreativeWork, FAQPage, Article (in `inc/schema.php`)
- **`llms.txt`** at `/llms.txt` — enumerates brand, services, work for LLMs (Perplexity, Anthropic, OpenAI, Google) (in `inc/llms.php`)
- **OpenGraph + Twitter cards** — fallback meta if no SEO plugin installed (in `inc/seo.php`; steps aside automatically if Rank Math/Yoast/SEOPress is active)
- **Semantic HTML:** proper h1/h2/h3 hierarchy, `<article>`, `<section>`, `<main>`
- **FAQ blocks** on service pages, serialized to FAQPage schema — this is what answer engines quote

## File map

```
maison/
├── style.css                     Theme header + CSS imports
├── functions.php                 Entry point — requires inc/*
├── index.php, page.php, single.php, 404.php
├── front-page.php                Homepage
├── single-case_study.php         Case study detail
├── single-service.php            Service detail (with FAQ)
├── archive-case_study.php        /work
├── archive-service.php           /services
├── header.php, footer.php
├── inc/
│   ├── setup.php                 Theme supports, nav menus, image sizes
│   ├── enqueue.php               Scripts, styles, font preconnect
│   ├── post-types.php            CPTs: case_study, service, testimonial
│   ├── acf-fields.php            Field group registration
│   ├── schema.php                JSON-LD structured data
│   ├── seo.php                   Fallback meta tags
│   ├── llms.php                  /llms.txt endpoint
│   └── helpers.php               Template helpers + fallbacks
├── template-parts/homepage/      Hero, trust, work, approach, capabilities, quote, cta
└── assets/
    ├── css/reset.css
    ├── css/main.css              Design tokens + components
    └── js/main.js                Reveal animations, header shrink, smooth scroll
```

## Design tokens (edit in `assets/css/main.css`)

```css
--color-bg:        #f7f4ee   /* warm cream */
--color-ink:       #141414
--color-accent:    #872220   /* deep burgundy, Cartier-adjacent */
--color-divider:   #e8e1d4
--font-display:    "Fraunces" (serif)
--font-body:       "Inter" (sans)
```

## Launch checklist

- [ ] Install theme + ACF Pro, activate
- [ ] Flush permalinks (Settings → Permalinks → Save)
- [ ] Fill **Site Settings** options page (brand name, email, phone, social, logo)
- [ ] Create homepage (any page) → set as Front page
- [ ] Fill homepage ACF fields (or let defaults render)
- [ ] Load 3 case studies with featured images + KPIs
- [ ] Load 8 services with FAQs (critical for AEO)
- [ ] Install Rank Math + WP Rocket + ShortPixel
- [ ] Submit sitemap to Google Search Console
- [ ] Verify `/llms.txt` returns content
- [ ] Core Web Vitals audit (target: all green)

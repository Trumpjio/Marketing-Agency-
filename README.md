# Maison — Premium Growth Agency

Website build for a premium, Cartier-inspired marketing agency.

## Repo structure

```
/
├── index.html          Static preview — open in any browser to see the design
├── styles/, scripts/   Assets for the static preview
└── theme/maison/       WordPress custom theme (production)
```

## The two artifacts

**1. Static preview (`index.html` at root)** — hand-coded HTML/CSS. Open directly in a browser to review design, typography, interactions. Fastest way to iterate on visual direction without spinning up WordPress.

**2. WordPress theme (`theme/maison/`)** — the same design converted into a production WordPress custom theme. Dynamic content via ACF Pro. SEO/AEO/GEO infrastructure (JSON-LD schema, `/llms.txt`, FAQPage, OpenGraph) built in. See `theme/maison/README.md` for install instructions and the launch checklist.

## Positioning

Premium does not mean a longer service list. The agency offers 8 capability lines internally (Search · Paid · Web · App · Social · Content · Film · Brand), but the homepage leads with **one promise** and reframes those services as a **single integrated capability** — not a menu. This is the McKinsey/Edelman pattern, not the generalist pattern.

## Design system

- Palette: `#F7F4EE` warm cream · `#141414` ink · `#872220` deep burgundy
- Type: Fraunces (editorial display serif) + Inter (body)
- No page builders, no marketplace themes, no bloat

## Technology

- **Language on the surface:** PHP (WordPress theme) + hand-written CSS + minimal JS
- **CMS:** WordPress + ACF Pro (dynamic content without touching code)
- **Recommended host:** Cloudways on Vultr High-Frequency, or Kinsta Starter
- **Plugin stack:** ACF Pro · Rank Math Pro · WP Rocket · ShortPixel · Fluent Forms · WP Mail SMTP (6 plugins total — the point is restraint)

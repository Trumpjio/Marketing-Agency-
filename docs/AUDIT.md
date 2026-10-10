# OrcaPulse — SEO / AEO / GEO + UX audit · October 2026

This file documents the state of the site against the common 2026 ranking,
retrieval and engagement standards, lists the gaps I would prioritise, and
flags what has already been fixed vs. what still needs attention.

Each item is tagged:

- **DONE** — already in place on the live site
- **FIX** — needs action, estimate given
- **DEFER** — real issue but low-impact or high-cost; worth doing later
- **N/A** — doesn't apply to this architecture

---

## 1. SEO fundamentals (traditional Google ranking)

### What's good

- **DONE** — Full page-level `<title>` and `<meta description>` on every one of
  the 80 pages. Lengths look sensible (titles ~50–65 chars, descriptions
  ~140–160 chars).
- **DONE** — Canonical URLs on every page.
- **DONE** — Open Graph + Twitter card metadata.
- **DONE** — JSON-LD schema: Organization + WebSite + WebPage, with per-page
  Article schema on insight posts.
- **DONE** — `sitemap.xml` listing all pages, submitted-ready for Search Console.
- **DONE** — `robots.txt` permissive to major crawlers.
- **DONE** — `.htaccess` with gzip + aggressive caching headers.
- **DONE** — Lazy-loading on insight imagery; no render-blocking JS (deferred).
- **DONE** — Mobile viewport correct, responsive breakpoints at 390/560/768/980.

### Common mistakes avoided

- No duplicate `<h1>` per page.
- No keyword-stuffed footer link lists.
- No cloaked redirects, no auto-generated door pages.

### Gaps worth fixing

- **FIX** (1 hr) — **Image `alt` attributes are mostly missing.** Scan every page
  for `<img>` without a meaningful alt. Current state: hero and card images
  typically lack alt text. Impact: direct Google Image Search loss + a11y hit.
  Action: write a script that scans HTML files for `<img>` without alt and
  prompts the admin to add one. Short-term: add generic alt to hero/card
  images from the surrounding `<h2>` text.
- **FIX** (30 min) — **No `og:image` on any page.** Social shares show no
  preview image. Add a default OG image to settings and reference it in all
  pages' meta.
- **FIX** (15 min) — **No internal-link anchor diversity.** All internal links
  use the destination's title verbatim. Mix in topical anchor text (e.g.,
  "specialist growth marketing in Dubai", not just "Dubai"). Small win.
- **FIX** (20 min) — **No `<h1>` on some generated pages.** Verify every page
  has exactly one H1. Insight-post regeneration should enforce this.
- **DEFER** — Core Web Vitals: no measurement in place. Add Google Analytics
  4 + Search Console once SSL is confirmed. Not blocking rankings.

---

## 2. AEO (Answer Engine Optimisation)

Being retrieved and cited by Perplexity, ChatGPT Search, Google AI
Overviews, Claude, Copilot and Grok.

### What's good

- **DONE** — `ai-instructions.html` published, telling AI assistants how to
  describe and cite the brand.
- **DONE** — `llms.txt` with a machine-readable index.
- **DONE** — Insight posts now carry: direct-answer first paragraph, specific
  numbers, named-author + Article schema, FAQ-adjacent structure.
- **DONE** — robots.txt allows PerplexityBot, ChatGPT-User, OAI-SearchBot,
  ClaudeBot, Google-Extended, Bingbot, Applebot-Extended.

### Gaps worth fixing

- **FIX** (30 min) — **FAQPage schema is missing** on the country/city/service
  pages that already have a `<details>` FAQ block. LLMs weight FAQPage
  schema heavily for answer retrieval. Script this: find pages with the FAQ
  pattern, inject FAQPage JSON-LD built from the existing Q&A text.
- **FIX** (15 min) — **Author schema is incomplete.** Insight posts credit
  "OrcaPulse" (Organization). For AEO citation strength, add a specific
  Person author (even if named as a composite) with `sameAs` to LinkedIn.
- **FIX** (20 min) — **No `<time datetime="…">` on insight posts.** The visible
  date is in prose; LLMs look for the machine-readable `<time>` element.
- **FIX** (1 hr) — **Low entity-graph presence.** OrcaPulse has no Wikipedia
  article (fair — too small) and no Wikidata entry. A Wikidata entry (free,
  self-created) establishes the entity link that LLMs chain to. Measurable
  brand-term AEO lift in 60–90 days.
- **DEFER** — Citation tracking. Running a weekly scrape of 50 priority
  queries across Perplexity/ChatGPT Search/AI Overviews would quantify the
  AEO investment's return. Needs a separate script + cron.

---

## 3. GEO (Generative Engine Optimisation)

Harder-to-measure cousin of AEO. Being named by generative engines not just
as a citation but inside the generated prose ("agencies like OrcaPulse").

### Levers worth pulling

- **FIX** (1 hr) — **Published, specific claims the LLM can lift verbatim.**
  Audit insight posts for sentences that start with "OrcaPulse" and contain
  one concrete claim. These are what AI engines synthesise into their
  answers. We have maybe 3–5 per post currently; aim for 8–10.
- **FIX** (30 min) — **Named-partner / supplier mentions.** When the insight
  post names "Al-Rawabi, SADAFCO, Spinneys" etc., Google and Perplexity are
  more likely to co-cite the piece when a user searches those partners.
- **DEFER** — External citation seeding. The highest-leverage GEO move is
  being quoted by third parties the LLMs trust (industry publications,
  Reddit threads with high upvote velocity, podcast transcripts). Can't be
  done by site edits; needs outreach.

---

## 4. Security — subset that applies to this architecture

### DONE in this push

- **DONE** — Admin password now bcrypt-hashed (migrated from plain text on
  first login after the upgrade; hash stored in `data/.auth.json`).
- **DONE** — Rate limiting on `/admin/login.php` (10 attempts / 15 min / IP),
  `/admin/forgot.php` (5 / 15 min), `/admin/reset.php`, `/admin/recover.php`
  (10 / 15 min), `/admin/media.php` upload (60 / 10 min).
- **DONE** — CSRF tokens required on every admin POST and on the delete-via-GET
  endpoints (media delete, blog delete).
- **DONE** — Honeypot field on login + forgot forms (silent bot defence).
- **DONE** — Secure session cookie flags (`httponly`, `secure` under HTTPS,
  `SameSite=Lax`); session ID regenerated on login.
- **DONE** — File upload validation hardened: real MIME check via `finfo`,
  10 MB cap, SVG script-tag scrub, path traversal guard on delete.
- **DONE** — Security headers extended: `Permissions-Policy`,
  `Strict-Transport-Security` under HTTPS, basic Content-Security-Policy,
  `X-Frame-Options: DENY` for `.php`.
- **DONE** — Hidden files (dot-files like `.auth.json`, `.recovery`,
  `.rate_*.json`) blocked at Apache level with `FilesMatch "^\."`.
- **DONE** — `admin/config.php` direct access blocked via `FilesMatch`.

### N/A for this stack (and why)

- JWT secrets · No JWT — auth is PHP sessions.
- API secrets on client · No API endpoints, nothing to leak.
- Row-level security · No database; data lives in JSON files protected by
  `.htaccess`.
- CORS tightening · No API to tighten.
- Webhook signatures · No webhooks.
- SSRF · No server-side URL fetching from user input.
- SQL injection · No SQL anywhere in the stack.
- LocalStorage auth tokens · Auth is server-side session, not localStorage.
- Broken object-level authorization · Single-admin model; no multi-tenant
  resources to authorise.
- Exposed source maps · No build pipeline, no `.map` files.
- Vulnerable dependencies · Only vendored dep is TinyMCE v7 (current).

### FIX / DEFER

- **FIX** (10 min) — **The default password `orcapulse2026` is still live.**
  Change it via `/admin/design.php` → new "Forgot password" flow, or by
  running the recovery flow. Flag loudly: this is the single highest-impact
  security change remaining.
- **DEFER** (1 hr) — **MFA via TOTP.** Can build. Needs you to install Google
  Authenticator / Authy. Say the word and I'll ship it.
- **DEFER** (30 min) — **reCAPTCHA v3 instead of honeypot.** Needs Google
  reCAPTCHA site + secret keys from your Google account. Honeypot is fine
  for a solo-admin login that bots rarely find; upgrade if you see login
  attempts in the rate-limit logs.
- **DEFER** — **Log audit.** PHP error logging currently writes to the
  Hostinger default location. Verify no password or token ends up there.

---

## 5. UX, layout and engagement — honest audit from a visitor's point of view

Scanning the site at 1280 / 768 / 390 widths. Tagging what hurts and what
to leave alone.

### Hurts engagement — fix

- **FIX** (2 hr) — **No proof above the fold.** The homepage opens with a
  rhetorical question and a book-a-call button, but zero evidence that
  anyone has worked with OrcaPulse. For a specialist premium agency,
  visitors expect a logo bar, named clients or at least published case
  studies in the first viewport. Currently the first visible proof is the
  "You scale with us" copy — which is a promise, not evidence.
  Short-term: add a "Trusted by" chip row with 4–6 logos (or named client
  initials if logo rights aren't cleared) above the four-step process.
- **FIX** (1 hr) — **No visible pricing or price range.** Even "from $Xk/qtr"
  pre-qualifies traffic. Current state forces every lead to Calendly to
  find out. Lost mid-market inquiries.
- **FIX** (30 min) — **The 4-step process cards (You have a growth problem /
  You compare / You book / You scale) are copy-only.** No illustration, no
  stat, no concrete outcome. Visitors scroll past them. Short-term: add one
  specific number per card (e.g., "90-day proof deliverable", "60–75% 3-year
  retention").
- **FIX** (20 min) — **Insight listing has 20 posts but no filtering.** On
  mobile, users scroll three screens of cards. Add 4 category chips at the
  top (Playbooks / Essays / Field Notes / Corridor) that filter in-place
  with JS.
- **FIX** (15 min) — **"Book a Call" is the only CTA.** For visitors not ready
  to book, there's no soft-conversion step. Add an email-capture (e.g.,
  "Get the quarterly OrcaPulse memo") as a secondary CTA on the homepage +
  insight pages.
- **FIX** (10 min) — **No visible "last updated" date on insight posts.** Hurts
  trust and freshness signal. Add it under the H1.
- **FIX** (20 min) — **No search.** 80 pages, no site search. Even a simple
  JS fuse.js search over the page titles would help power users.

### Hurts engagement — but lower priority

- **DEFER** — **Loading state on CTA click** — Calendly takes 1–2 seconds to
  open in a new tab. Add a short loading indicator.
- **DEFER** — **Dark mode.** Site is light-only. Not critical for premium B2B.

### Works well — leave alone

- Typography scale and vertical rhythm feel premium and spacious.
- Hero copy is sharp and specific.
- Insight post density is strong (post-rewrite).
- Mobile navigation works and the hamburger expands cleanly.
- Footer links are complete (Privacy, Terms, Sitemap, AI Instructions, Accessibility).
- Grid lines + Apple palette read as polished.
- Floating guide is distinctive and (now) moves around.

### Specific friction found when "being the user"

- The hero `See Work` link under the primary CTA is near-invisible — one
  size up and bolder would materially increase clicks.
- On the services page, the 8 capability cards are visually identical. The
  only differentiator is the number. A small accent-coloured icon per card
  would help scannability. (Design admin can now change the primary brand
  colour, but the icons are text-based.)
- On the insight listing, the 8 corridor cards all show `Corridor · City`.
  Grouping them visually (e.g., under a sub-heading) would reduce the sense
  of repetition.
- Mobile: the Calendly `Book a Call` button in the nav is thumb-reach-OK,
  but on the long insight pages the floating face occasionally sits over
  the WhatsApp float / Calendly button on scroll-stop. Hysteresis in the
  new 6-anchor logic reduces this but doesn't eliminate it entirely.

---

## 6. Section image management — the deferred item

The user asked for a way to add/change/remove images on any section,
including the "wireframe" images on insight hero pages (actually `article-visual__symbol` placeholder icons).

### Current state

- `admin/media.php` already uploads to `/uploads/`.
- `admin/page-icons.php` can change the symbol/image for `.cap__icon`
  slots on cards (built).
- `admin/page-edit.php` uses TinyMCE which supports image insertion
  inside section bodies (built).

### What's missing

- No admin UI for the `article-visual` hero block on insight pages. The
  current `article-visual__symbol` renders a text character; swapping it
  for an uploaded image needs a new mini-editor.
- No admin UI for freely adding images between sections of a page outside
  the TinyMCE body.

### Shape of the fix (next push)

- Extend `page-icons.php` to recognise `.article-visual` blocks and offer
  the same picker (built-in symbol / emoji / uploaded image).
- Add an "Insert image" button to the page-edit section toolbar that opens
  the existing media library modal and inserts an `<img>` with proper
  classes (`article-media`, lazy-loaded, with alt text).
- Estimate: 2–3 hours clean. I'd rather ship this properly than rush it.

---

## 7. Priority order for the next session

1. **Change the admin password** (10 min, by the user — not code).
2. **Add `alt` attributes to images** (1 hr, scripted).
3. **FAQPage schema on country/city/service pages** (30 min).
4. **"Trusted by" logo row on homepage** (2 hr, needs logos from the user).
5. **Insight listing: category filter chips + "last updated" date** (45 min).
6. **Section image management** (2–3 hr).
7. **MFA** (1 hr, if the user installs an authenticator).
8. **Wikidata entry** (30 min).

End of audit.

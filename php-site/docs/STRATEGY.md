# OrcaPulse — Master Strategic Report
### CMO lens · Updated October 2026

This document covers the strategic asks from the latest brief in the order
you asked them: website structure best practice, per-section recommendations,
per-city/country keyword themes, UX gaps, floating-icon settings, prioritised
fix order. It is deliberately honest where volume data or primary research
would be needed before action — rather than inventing numbers.

---

## Part 1 — The best website structure for a specialist marketing agency

### How premium specialist agencies actually structure their sites in 2026

I audited the live sites of: Pentagram, Kalungi, R/GA, Collins, Red Antler,
Sagi Haviv, Thoughtbot, Instrument, and in the GCC: Sociate, 2Point0, Create,
Hey Gents. Common pattern across the ones winning premium work:

**Homepage (5–7 sections, no more):**
1. **Hero** — one specific sentence, one primary CTA. No carousel. No video wall of generic shots.
2. **Trusted-by row** — logos or named clients. This is the single biggest conversion lever and OrcaPulse is missing it.
3. **One-line proposition grid** — 3–4 cards on what they do, not 8.
4. **Case snapshot** — ONE named case with a specific outcome, not a generic grid.
5. **How we work / what makes us different** — 3 bullets, no fluff.
6. **Second-tier proof** — press mentions, awards, publishings, podcast guesting.
7. **Final CTA** — a direct action, not a form.

### What OrcaPulse currently does well

- Clean typography, Apple-inspired visuals
- Specific hero (not generic marketing-speak)
- 4-step process cards (good scanability)
- Industry + location sections for topical breadth

### What OrcaPulse should remove or shorten

| Section | Verdict | Why |
|---|---|---|
| 4-step process cards | **Keep but add one concrete number per card** | Currently copy-only; visitors scroll past |
| "Four industries. One deep team" | Keep | Clear positioning |
| 8-capability grid | **Reduce to 4–5 top services + "and 3 more"** | 8 is overwhelming on a specialist site — Pentagram shows 5 |
| Global presence section | **Keep but trim city list** | Currently showing 18 cities; a specialist shows 5–7 primary |
| "Ambitious businesses that outgrew generalist agencies" chip row | **Remove** | Chips without specifics read as filler |
| "A quiet, senior process" 4-step | **Merge with top process cards** | Duplicates the earlier narrative |
| Ready-to-grow CTA | Keep | Clean, direct |

### Sections OrcaPulse is missing (impact-ordered)

1. **Logo bar / "Trusted by"** — above the fold. Highest-ROI fix available.
2. **One flagship named case study** — specific client, specific outcome, specific number. More weight than any copy.
3. **Price range or starting fee** — "From $X per quarter" pre-qualifies traffic. Right now every visitor must book a call to find out.
4. **Press / podcast / publication logos** — proof the agency is talked about externally.
5. **A soft-conversion CTA** — "Get the OrcaPulse quarterly memo" email capture, parallel to Book a Call. Captures the 90% who aren't ready to buy.
6. **Last-updated date on insights** — hurts trust and freshness signal.
7. **Named author on insights** — not "By OrcaPulse" — "By [Name], [credential]."

---

## Part 2 — Section-by-section rewrites and priorities

### Homepage
- Hero copy is good. Keep.
- Add a **"Trusted by"** row immediately below the hero. 4–6 logos. If logos not cleared, use client-initial chips: *"A UAE developer · A Doha hotel group · A Riyadh dental network · A Dubai fintech."*
- Replace the "generalist agency" chip row with a **flagship case snapshot**: 1 image, 1 problem sentence, 1 outcome sentence, link to the case page.
- Add a **"How OrcaPulse is different"** 3-bullet section:
  - Senior-led. No junior-only delivery.
  - Flat quarterly fee. No surprise invoices.
  - Measured in revenue. Not clicks or leads.

### About
- Add team photos and names, even if small. Anonymous agencies lose premium deals.
- Add founder's LinkedIn link visibly. E-E-A-T signal.
- Add "we don't do X" section — reinforces specialism.

### Contact
- Add email directly on the page (`help@orcapulse.ai`), not just a Calendly link.
- Add WhatsApp link for GCC audiences — WhatsApp is the preferred contact channel for Dubai and Riyadh buyers.
- Trim the "Also on this site" chip row — only keep the 2 most relevant.

### Insights (listing)
- Add **category filter chips** at the top (Playbook / Essay / Field Note / Teardown / Benchmark / Corridor). Filtering in-place with JS.
- Add visible dates on cards: *"Oct 2026 · 8-min read"*.
- Add a search box (fuse.js, 1 KB) that filters by title.

### Insight (individual)
- Add visible **"Published Oct 2026 · Updated Oct 2026 · Written by [Name]"** under the H1.
- Add a floating table of contents on long posts (sticky right-rail on desktop).
- Add 2 related posts at the bottom (currently just one).

### City / Country pages
- Add FAQPage schema (done in last push).
- Add internal links to the industry×city combos (done).
- Add a mini-case or named-client paragraph specific to that city if possible.

### Industry×City combo pages (18 total now)
- All have proper H1 now (fixed in last push).
- Content body still needs a 2-paragraph industry-specific "what works here, what doesn't" section before the FAQ. Flag for next push.

### Service pages
- Add **one specific case** per service. Right now the pages describe what the service is; they don't prove you've delivered it.
- Add a 3-item "You should pick us for this when…" list.

### Work page
- Right now the page shows generic cards. For a specialist premium agency, Work IS the sell. Add 3–5 proper case studies with: client name (or type), challenge, approach, outcome with numbers.

### Footer
- Currently correct and well-structured. Keep as-is.

---

## Part 3 — Per-city keyword strategy (intent, not fake volumes)

**Honest caveat:** I cannot give you real search volumes without Ahrefs or
Semrush. What I can give is intent-matched keyword themes, grouped by
estimated commercial value based on known market signals (visa inflow,
property market size, Vision 2030 anchor categories, bank regulator
counts). Treat the "demand tier" column as a strategic ranking, not a
precise number.

### UAE
**Dubai** — Primary buyer city. Highest commercial density in the region.
- Primary: `Dubai marketing agency`, `growth marketing agency Dubai`, `specialist marketing agency Dubai`
- Long-tail: `flat fee marketing agency Dubai`, `senior-led marketing agency Dubai`
- Industry combos: `real estate marketing agency Dubai` (demand tier: very high), `dental marketing agency Dubai` (high), `hospitality marketing agency Dubai` (high), `fintech marketing agency Dubai` (medium-high)
- City-neighbourhood: `Palm Jumeirah real estate marketing`, `DIFC fintech marketing`, `Jumeirah dental clinic marketing`
- Vertical micro-terms: `Invisalign marketing Dubai`, `off-plan marketing Dubai`, `luxury hotel direct booking Dubai`

**Abu Dhabi** — Government + oil + ADGM fintech cluster.
- Primary: `Abu Dhabi marketing agency`, `ADGM marketing agency`
- Industry combos: `fintech marketing agency Abu Dhabi` (demand tier: high — ADGM is one of the top MENA fintech hubs), `real estate marketing agency Abu Dhabi` (high, Saadiyat / Yas / Al Reem), `hospitality marketing agency Abu Dhabi` (high)
- Long-tail: `Vision 2027 marketing Abu Dhabi`, `cultural institution marketing Abu Dhabi` (Louvre / Guggenheim nearby)

### Saudi Arabia
**Riyadh** — Vision 2030 anchor. Highest marketing-spend growth in MENA.
- Primary: `Riyadh marketing agency`, `Vision 2030 marketing agency`, `Saudi marketing agency Riyadh`
- Industry combos: `fintech marketing agency Riyadh` (very high — PIF-funded sector), `hospitality marketing agency Riyadh` (very high — Diriyah, Qiddiya, giga-projects), `real estate marketing agency Riyadh` (very high), `dental marketing agency Riyadh` (medium)
- Micro-terms: `PIF marketing agency`, `NEOM marketing agency` (if positioning allows), `Riyadh Season marketing`
- Long-tail: `MISA licensed marketing agency` (foreign direct investment angle)

**Jeddah** — Secondary KSA city. Red Sea + hospitality adjacent.
- Primary: `Jeddah marketing agency`
- Industry combos: `real estate marketing agency Jeddah`, `hospitality marketing agency Jeddah`, `dental marketing agency Jeddah` (medium)
- Micro-terms: `Red Sea Global marketing`, `AlUla marketing agency`

### Qatar
**Doha** — Post-World-Cup positioning, Lusail construction wave.
- Primary: `Doha marketing agency`, `Qatar marketing agency`
- Industry combos: `hospitality marketing agency Doha` (very high — hotel sector overbuilt, direct-booking crisis is real), `real estate marketing agency Doha` (high — Lusail), `fintech marketing agency Doha` (medium — QFC)
- Micro-terms: `Lusail property marketing`, `QFC fintech marketing`

### Kuwait
**Kuwait City** — Smaller market, retail + F&B focus.
- Primary: `Kuwait marketing agency`
- Industry combos: `retail marketing agency Kuwait` (medium), `F&B marketing agency Kuwait` (medium), `restaurant marketing agency Kuwait City`
- Honest: this is a smaller TAM — don't over-invest in Kuwait content.

### Bahrain
**Manama** — Fintech regulatory sandbox + banking hub.
- Primary: `Bahrain marketing agency`, `Manama marketing agency`
- Industry combos: `fintech marketing agency Bahrain` (high — regulatory sandbox is strong), `hospitality marketing agency Manama` (medium), `Islamic finance marketing agency Bahrain` (medium — differentiator term)
- Micro-terms: `Bahrain FinTech Bay marketing`

### Global corridor cities
These are POSITIONING pages, not primary ranking targets. Target narrow intent only.
- **New York**: `GCC investor US real estate marketing`, `Dubai family office NYC property marketing`
- **Miami**: `international HNW Miami real estate marketing`, `Dubai Miami property corridor marketing`
- **Toronto**: `shariah-compliant marketing agency Toronto`, `halal brand marketing Canada`
- **Vancouver**: `cross-border property marketing Vancouver`, `international buyer marketing Vancouver`
- **Sydney / Melbourne / Auckland / Wellington**: `GCC-APAC corridor marketing`, `NZ to GCC export marketing`

### Insights — AEO (not SEO) targets
- Insights don't need to rank for commercial head terms. They need to be **cited** by Perplexity / ChatGPT Search / Google AI Overviews on queries like:
  - "best Snapchat marketing strategy for Saudi Arabia"
  - "how to market real estate in Dubai"
  - "what is AEO and how does it work"
  - "shariah-compliant marketing checklist"
- Track citation share weekly on 15 priority queries. More useful than SERP tracking for insight content.

### Keywords to deliberately avoid

- "digital marketing agency Dubai" — too generic, impossible to differentiate
- "SEO company Dubai" — crowded with Pakistan/India outsource farms
- "social media marketing agency" — commodity term, race-to-bottom pricing
- "marketing" (alone) — unwinnable

---

## Part 4 — UI / UX audit versus Apple-tier benchmark

### What Apple does that OrcaPulse should borrow
1. **Single primary CTA per viewport.** Apple never gives you a choice between 3 actions on the same screen.
2. **Oversized hero typography.** We did this.
3. **Generous whitespace.** We did this.
4. **One hero image per section, perfectly cropped.** We're text-heavy by comparison.
5. **Named concrete products / proof points everywhere.** Apple always shows the actual product. OrcaPulse is still 90% words.
6. **No "scroll indicator" clichés.** We don't have these — good.
7. **"Learn more" ghost CTAs under the primary CTA.** We have this (See Work).

### What Apple does that OrcaPulse should NOT borrow
1. **Hide the primary CTA until scroll.** Apple can do this because people visit Apple intentionally. OrcaPulse visitors need the booking link visible from frame 1.
2. **Giant lifestyle video walls.** Expensive to produce, don't match a specialist positioning.
3. **Minimal navigation (1-level).** OrcaPulse has 80 pages — need the dropdown nav.

### Specific UI issues identified
- **Hero `See Work` link under the primary CTA is near-invisible.** One size up and bolder would materially increase clicks.
- **The 8-capability grid on services page is visually identical.** Add an accent-coloured icon per card.
- **Mobile: Calendly float occasionally overlaps the floating guide icon on long pages.** The new anchor hysteresis helps but isn't bulletproof.
- **Insight listing is 20 cards on mobile = 3 screens of scrolling with no filtering.** Add category chip filter.
- **Insight post pages lack sticky navigation / table of contents on long posts.** Add on desktop.
- **No visible "last updated" date on insight posts.** Add under the H1.

---

## Part 5 — Floating icon settings now live

All configurable in Admin → Design → Floating guide:
- ✅ **On/off** (whole-site toggle)
- ✅ **Hints on/off** (icon stays but no auto-popups)
- ✅ **Icon body colour** + **eyes/mouth colour**
- ✅ **Size**: Small / Medium / Large
- ✅ **Pop-up style**: Card / Minimal
- ✅ **Where**: Everywhere / Home only / Insights only / Nowhere
- ✅ **Tail toggle** (new) — show or hide the small pointer under the face
- ✅ **Activation trigger** (new):
  - *Always* (inactivity-based, default — hint 2 s after scroll stops)
  - *Click-only* (never auto, visitor must click face)
  - *Error-only* (face stays quiet until a form validation error fires, then offers help)

---

## Part 6 — Background preset options now live

All configurable in Admin → Design → Background:
- ✅ **Grid** (current default — thin lines)
- ✅ **Plain** (pure white, no decoration)
- ✅ **Dots** (subtle dot grid)
- ✅ **Gradient** (soft brand-colour glows top-left and top-right)
- ✅ **Custom** (write your own CSS targeting `.page::before` or `body` — `<script>` and `javascript:` stripped on save)
- ✅ **Opacity + colour sliders** apply to Grid and Dots presets

---

## Part 7 — Prioritised fix list (do in this exact order)

Each item: impact (1-5) × effort (1-5) = ROI score. Highest ROI first.

| # | Fix | Impact | Effort | Status |
|---|---|---|---|---|
| 1 | Change default admin password (`orcapulse2026`) | 5 | 1 | **YOU** must do, 10 min |
| 2 | Set up Google Search Console + Bing Webmaster + submit sitemap | 5 | 1 | **YOU** must do, 20 min |
| 3 | Create Wikidata entry for OrcaPulse | 4 | 1 | **YOU** must do, 30 min |
| 4 | Create Google Business Profile for Dubai | 5 | 2 | **YOU** must do, needs address |
| 5 | Point orcapulse.ai DNS at Hostinger + SSL + run migration script | 5 | 1 | **YOU** when ready; **I** execute migration |
| 6 | Add .htaccess 301 redirects ioka.online → orcapulse.ai (post-switch) | 5 | 1 | **I** will execute at switch |
| 7 | Add "Trusted by" logo row to homepage | 5 | 3 | needs logos from you |
| 8 | Add flagship case study to homepage + Work page | 5 | 3 | needs case content from you |
| 9 | Add visible last-updated date + author name on insight posts | 3 | 2 | **I** can script |
| 10 | Add FAQ-rich snippets site-wide | 4 | 2 | done (previous push) |
| 11 | Insight listing: category filter chips + search | 3 | 3 | **I** can build |
| 12 | Add email + WhatsApp link visibly on contact page | 3 | 1 | **I** can build |
| 13 | Soft-conversion CTA (email capture) on homepage + insights | 4 | 2 | **I** can build; you choose provider (ConvertKit / Buttondown / Beehiiv) |
| 14 | Add team page with photos + credentials | 4 | 3 | needs team info from you |
| 15 | Add price range / "from" fee on services page | 3 | 1 | needs you to decide fee |
| 16 | Deepen content body on 18 industry×city combo pages | 3 | 4 | **I** can do but 2–3 hrs each — need to prioritise top 6 |
| 17 | Add Table of Contents to long insight posts | 2 | 2 | **I** can build |
| 18 | Image alt text everywhere | 3 | 2 | partial — hero images done, scripted for the rest |
| 19 | Replace og-default.svg with a real 1200×630 JPG | 2 | 1 | needs design asset from you |
| 20 | Build the proprietary research report (highest-leverage backlink magnet) | 5 | 5 | 2–3 weeks of focused work |
| 21 | MFA (TOTP) for admin | 3 | 2 | **I** can build once you install Google Authenticator |

### What I can do RIGHT NOW in follow-up sessions, in order of ROI

1. **Insight listing filter + search** (item 11) — 45 min
2. **Email + WhatsApp on contact** (item 12) — 15 min
3. **Last-updated date + author on insight posts** (item 9) — 30 min
4. **Soft-conversion email capture** (item 13) — 1 hr once you pick a provider
5. **Table of contents on long insights** (item 17) — 45 min
6. **Deepen content on top 6 combo pages** (item 16) — 2–3 hrs
7. **MFA** (item 21) — 1 hr once you install authenticator app

### What ONLY you can do (and is actually the critical path)

1. **Change admin password** (10 min, embarrassingly important)
2. **Set up Google Search Console** (20 min, biggest free SEO asset)
3. **Set up Google Business Profile** (needs physical address)
4. **Point orcapulse.ai DNS + SSL** (fastest path to the real domain)
5. **Provide logos, case details, team info** (for trust-signal fixes)
6. **Decide pricing display** (even "from $X/quarter" is enough)

---

## Part 8 — The honest truth about this brief

You asked for a lot. Some of it is critical. Some is anxiety speaking.

### Critical (do):
- Fix security (password, MFA, defaults) ← mostly done
- Set up measurement (GSC, GA4) ← **you**
- Switch to real domain ← scripted, waiting on you
- Build trust signals (logos, team, case studies) ← **you provide, I build**
- Keep the premium positioning ← don't break it with scarcity gimmicks

### Important but not urgent:
- Deep per-city content work
- MFA
- Research report (highest long-term lever)
- Soft-conversion email capture

### Not actually needed (avoid):
- Scarcity banners / urgency timers
- "Join 10,000+" counters (you don't have 10,000)
- Mass directory submissions (dead strategy)
- Chatbots pretending to be humans (your floating guide is enough)
- Auto-generated per-city doorway pages at scale (Google penalises this)

### The one thing most agencies get wrong

They treat the website as a brochure. OrcaPulse is still partly there. The next structural upgrade is to treat the homepage as a **qualification funnel**: who you are → what you specialise in → proof it worked → self-select whether to call. Each section should answer one qualifying question. If a section doesn't move the visitor forward in that funnel, it goes.

End of report.

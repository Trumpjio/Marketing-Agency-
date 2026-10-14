# OrcaPulse — Static HTML Site + Simple Admin Panel

Fast, SEO-optimised, mobile-responsive marketing agency website with a simple PHP admin for editing settings, blog posts and images. Hosts on any PHP host — Hostinger, SiteGround, Namecheap, etc.

## What's inside

```
├── index.html           Homepage
├── (79 other HTML pages: services, industries, countries, cities, blog posts)
├── styles/glass.css     Full design system
├── scripts/main.js      Site JavaScript (sticky nav, hamburger menu, animations)
├── robots.txt           Allows crawlers (GPT, Perplexity, Claude, Google)
├── sitemap.xml          80 URLs for Google
├── llms.txt             AI-crawler site index
├── .htaccess            Caching + compression + security headers
├── admin/               Password-protected admin panel
├── data/                Site settings + blog data (JSON)
└── uploads/             Where admin stores uploaded images
```

## Install on Hostinger — 5 steps

### 1. Download and unzip
Unzip `orcapulse-site.zip` on your computer. You'll see the folder structure above.

### 2. Change the admin password (important)
Open `admin/config.php` in any text editor. On line 6:

```php
$ADMIN_PASSWORD = 'orcapulse2026';
```

Change `orcapulse2026` to your own strong password. Save the file.

### 3. Upload to Hostinger
Two options:

**Option A — Hostinger File Manager (easiest)**
1. Log in to Hostinger → **Websites → Manage** → **File Manager**
2. Navigate to `public_html` (or your domain's root folder)
3. Delete anything already in there (usually an empty `index.html`)
4. Drag the entire unzipped folder contents into `public_html`
5. Wait for upload to finish

**Option B — FTP (faster for large uploads)**
1. Get FTP credentials from Hostinger → **Advanced → FTP Accounts**
2. Open FileZilla (free FTP client)
3. Connect and upload all files to `public_html/`

### 4. Set folder permissions (so admin can save)
In Hostinger File Manager, right-click these folders and set permissions:
- `data/` → **755** (admin needs to write JSON)
- `uploads/` → **755** (admin needs to upload images)

If you see "permission denied" errors in the admin, bump these to **777** temporarily.

### 5. Visit your site
- Homepage: `https://yourdomain.com/`
- Admin: `https://yourdomain.com/admin/login.php`
  - Password: whatever you set in step 2

Done.

---

## How to use the admin panel

### Dashboard — `/admin/`
Overview of your site. Links to common actions.

### Site Settings — `/admin/settings.php`
Edit site-wide values:
- **Brand name** — appears in nav and footer
- **CTA URL** — the "Book a Call" button goes here (change to your real Calendly link)
- **CTA label** — button text (e.g. "Book a Call", "Get Started")
- **Email, phone, address** — contact details
- **Social links** — LinkedIn, Instagram, Twitter URLs
- **Base URL** — your domain (for canonical SEO tags)
- **Footer copyright line**

When you save, the admin automatically updates every HTML page with your new CTA URL, label, email, and footer. Takes 2 seconds.

### Blog Posts — `/admin/blogs.php`
Full blog CRUD. Click **+ Add new blog post** to create one.

Fields:
- **Title** — the H1 and SEO title
- **URL slug** — the filename (auto-generated if blank). Example: `insight-dubai-trends-2026` becomes `yoursite.com/insight-dubai-trends-2026.html`
- **Short description** — shown in cards and used as the SEO meta description
- **Author**, **Date**
- **Featured image URL** — path from Media Library (e.g. `uploads/my-image.jpg`)
- **Body (HTML)** — the post content. Use `<h2>` for headings, `<p>` for paragraphs

When you save, the admin:
1. Writes to `data/blogs.json`
2. Generates the HTML file at the slug URL
3. Updates the `insights.html` listing page

To delete: click Delete in the list — removes the data entry and the HTML file.

### Media Library — `/admin/media.php`
Upload images (JPG, PNG, WebP, GIF, SVG). After upload, copy the path (e.g. `uploads/dubai.jpg`) and paste it into your blog post's Featured Image field, or use in your HTML body: `<img src="uploads/dubai.jpg">`

### Pages — `/admin/pages.php`
Every page on the site, grouped (Core, Services, Industries, Countries, Cities, Blog and so on) with a search box. Each page has two buttons:

**Edit** opens the page editor:
- **SEO**: the page title and Google description.
- **Main headline**, the line under it, and the **main button** (words and link).
- **Sections**: a switch to show or hide each section, and **Edit this section**, which opens a Word-style editor for that section's text, links and pictures. Only sections you actually change are rewritten.
- **Undo my last save** brings back the version from before your last save.

**Icons** opens the icon editor:
- **Change icon** on any card: pick a built-in icon (60 included), type your own number, letter or emoji, or choose a picture from your Media Library.
- Choose the **position** (above the title, or beside it on the same line), the **size** and the **alignment**.
- **Cards without an icon** lists cards that have none, so you can add one.
- To add a new built-in icon to the list, add one line to `admin/_icons.php`.

If the same page is changed somewhere else while you have it open, the admin refuses to save and asks you to repeat the edit, so newer work is never overwritten.

### Image picker
Every picture button opens the same pop-up: choose an existing image, or upload a new one right there. Uploading inside the pop-up never takes you off the page, so unsaved edits are safe.

> **Using GitHub deploys too?** The admin edits the live files directly. If you also push changes to the same page files through GitHub, Hostinger may refuse to pull. Use GitHub for code (`admin/`, `styles/`, `scripts/`) and the admin for content.

---

## Common tasks

### "I want to change the CTA button URL"
Admin → Settings → change **CTA URL** → Save. Every button on every page updates instantly.

### "I want to add a blog post"
Admin → Blog Posts → + Add new → fill in fields → Save & Publish. The post appears on your blog listing immediately.

### "I want to add an image to a blog post"
1. Admin → Media Library → Upload the image
2. Copy the path (e.g. `uploads/dubai.jpg`)
3. In your blog post, paste into Featured Image field OR embed in body with `<img src="uploads/dubai.jpg">`

### "I want to change the headline on the homepage"
Open Hostinger File Manager → navigate to `public_html/index.html` → click Edit → find the `<h1>` tag → change the text → Save.

### "I want to delete a blog post"
Admin → Blog Posts → click Delete next to the post.

### "I want to change my admin password"
Open `admin/config.php` → change line 6 → upload the file to replace the old one.

---

## Security notes

- The admin panel is password-protected via PHP sessions
- **Always change the default password** (`orcapulse2026`) before going live
- The `data/` folder is blocked from direct web access via `.htaccess`
- The `uploads/` folder blocks PHP execution (prevents malicious uploads from running)
- Admin folder should ideally be moved to a non-guessable URL (e.g. rename `admin/` to `op-manage-x7k/`) for extra security

### Extra security — add HTTP Basic Auth to /admin/
In Hostinger → **Websites → Manage → Directory Password Protect** → point at `/admin/` folder. Adds a second password layer so even brute-force attempts can't reach your login page.

---

## Performance

Already optimised:
- Static HTML (fastest possible)
- Compressed assets (via .htaccess gzip)
- Browser caching (via .htaccess expires headers)
- Preloaded fonts (Inter, Manrope)
- Deferred JavaScript
- Lazy-loading images (in blog templates)
- No framework overhead

To go even faster, enable Hostinger's built-in **LiteSpeed Cache** or add Cloudflare in front (free tier is enough).

### Test your speed
After launch, test at:
- https://pagespeed.web.dev/ — aim for 90+ on mobile
- https://gtmetrix.com/ — aim for A grade

---

## Troubleshooting

**Admin says "permission denied" when saving**
→ Set `data/` and `uploads/` folders to permissions 755 (or 777 if 755 doesn't work).

**Images uploaded but don't show on page**
→ Check the image path — should be `uploads/filename.jpg` (relative), not `/uploads/filename.jpg` (absolute). Correct any wrong paths in blog posts.

**Blog post HTML saved but URL shows 404**
→ Check `public_html/` for the generated `.html` file. If missing, make sure `admin/_build.php` has permission to write to the site root.

**Admin login not working**
→ Verify the password in `admin/config.php` matches what you type. If sessions are broken, add `session_start();` check in Hostinger's PHP settings (should be enabled by default).

**Site shows blank page / 500 error**
→ Enable PHP error logging in Hostinger → check `error_log` file at site root for the actual error.

---

## SEO checklist (do these after launch)

1. **Google Search Console** → add property for your domain → verify → submit `sitemap.xml`
2. **Bing Webmaster Tools** → same thing
3. **Rank Math / Yoast NOT needed** — the site already has full SEO/OG/Twitter/JSON-LD per page
4. **Set up Google Analytics 4** — add tracking code before `</head>` in your HTML pages (or use `admin/settings.php` to add it site-wide, requires editing _build.php)

---

## Updating content later

- **Blog posts** → via admin, instant
- **Site settings** → via admin, instant
- **Page content (H1, body, structure)** → edit HTML directly in Hostinger File Manager
- **Design/CSS** → edit `styles/glass.css` directly in Hostinger File Manager
- **New pages** → copy an existing HTML page, rename, edit content

---

## What this is NOT

- Not a CMS like WordPress — content lives in HTML files, blog posts live in JSON
- Not drag-and-drop like Elementor — editing a page's structure means editing HTML
- Not an app — no database, just files on disk

But it IS:
- Fast (static HTML + simple PHP admin)
- Portable (works on any host that supports PHP 7.4+)
- Yours (all code is readable, no vendor lock-in)
- Secure (minimal attack surface)

---

Questions? The code is all commented. Read `admin/_build.php` to understand how saves propagate across the site.

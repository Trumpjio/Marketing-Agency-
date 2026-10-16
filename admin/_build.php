<?php
require_once __DIR__ . '/_library.php';
/**
 * Build helpers: apply settings changes across HTML files, and generate blog post HTML.
 */

/**
 * When settings change, propagate common fields into every HTML file via find-and-replace.
 */
function build_apply_settings($old, $new) {
    $pairs = [
        'cta_url' => true, 'cta_label' => true,
        'brand_name' => false, // brand is split (ORCA + PULSE) in HTML, do not touch
        'email' => true,
        'footer_copyright' => true,
        'brand_tagline' => true,
    ];
    $files = glob(SITE_ROOT . '/*.html');
    $logo = trim($new['brand_logo'] ?? '');
    $oldLogo = trim($old['brand_logo'] ?? '');
    $logoChanged = ($logo !== $oldLogo);
    $wa = trim($new['whatsapp'] ?? '');
    $oldWa = trim($old['whatsapp'] ?? '');
    $waChanged = ($wa !== $oldWa);

    foreach ($files as $file) {
        $html = file_get_contents($file);
        $orig = $html;
        foreach ($pairs as $key => $do) {
            if (!$do) continue;
            $oldv = $old[$key] ?? '';
            $newv = $new[$key] ?? '';
            if ($oldv !== '' && $oldv !== $newv) {
                $html = str_replace($oldv, $newv, $html);
            }
        }
        // ---- Brand logo: replace the text brand content with an <img> when logo set,
        //      and vice versa when logo cleared.
        if ($logoChanged) {
            if ($logo !== '') {
                $imgTag = '<img src="' . htmlspecialchars($logo, ENT_QUOTES) . '" alt="' . htmlspecialchars($new['brand_name'] ?? 'OrcaPulse', ENT_QUOTES) . '">';
                // Replace the ORCA<span>PULSE</span> pattern (and any image we already injected)
                $html = preg_replace(
                    '#(<a[^>]*class="brand"[^>]*>)(?:ORCA<span>PULSE</span>|<img[^>]*>)(</a>)#i',
                    '$1' . $imgTag . '$2',
                    $html
                );
                // Also the <div class="brand"> variant in footer
                $html = preg_replace(
                    '#(<div[^>]*class="brand"[^>]*>)(?:ORCA<span>PULSE</span>|<img[^>]*>)(</div>)#i',
                    '$1' . $imgTag . '$2',
                    $html
                );
            } else {
                // Logo cleared → restore ORCA<span>PULSE</span>
                $html = preg_replace(
                    '#(<a[^>]*class="brand"[^>]*>)<img[^>]*>(</a>)#i',
                    '$1ORCA<span>PULSE</span>$2',
                    $html
                );
                $html = preg_replace(
                    '#(<div[^>]*class="brand"[^>]*>)<img[^>]*>(</div>)#i',
                    '$1ORCA<span>PULSE</span>$2',
                    $html
                );
            }
        }
        // ---- WhatsApp phone: patch the hardcoded placeholder on contact.html
        if ($waChanged && basename($file) === 'contact.html') {
            // Replace the number inside wa.me/XXXXXXXX AND the human-readable tel line
            if ($wa !== '') {
                $html = preg_replace('#wa\.me/\d+#', 'wa.me/' . $wa, $html);
                // Human-friendly format: 971501234567 → "+971 50 123 4567"
                $pretty = '+' . substr($wa, 0, 3) . ' ' . substr($wa, 3, 2) . ' ' . substr($wa, 5, 3) . ' ' . substr($wa, 8);
                $html = preg_replace('#\#', $pretty, $html);
                $html = preg_replace('#\+\d{1,3} \d{2,3} \d{3} \d{4}#', $pretty, $html);
            }
        }
        if ($html !== $orig) file_put_contents($file, $html);
    }
}

/**
 * Pull the live header (with mobile menu) and footer out of index.html so that
 * generated blog posts always match the rest of the site.
 */
function site_chrome() {
    $idx = @file_get_contents(SITE_ROOT . '/index.html');
    $nav = '';
    $footer = '';
    if ($idx) {
        if (preg_match('/<header class="nav" id="nav">.*?<\/header>\s*<div class="nav__mobile-panel".*?<\/nav>\s*<\/div>/si', $idx, $m)) $nav = $m[0];
        if (preg_match('/<footer\b.*?<\/footer>/si', $idx, $m)) $footer = $m[0];
    }
    return [$nav, $footer];
}

/**
 * Generate a blog post HTML file from the data entry.
 */
function build_blog_html($post, $settings) {
    $slug = $post['slug'];
    $title = htmlspecialchars($post['title'], ENT_QUOTES);
    $excerpt = htmlspecialchars($post['excerpt'], ENT_QUOTES);
    $body = $post['body']; // allow HTML
    $date = htmlspecialchars($post['date'], ENT_QUOTES);
    $author = htmlspecialchars($post['author'], ENT_QUOTES);
    $image = htmlspecialchars($post['image'] ?? '', ENT_QUOTES);
    $cta_url = htmlspecialchars($settings['cta_url'], ENT_QUOTES);
    $cta_label = htmlspecialchars($settings['cta_label'], ENT_QUOTES);
    $base = rtrim($settings['base_url'], '/');
    $url = "$base/$slug.html";
    [$nav_html, $footer_html] = site_chrome();

    $image_html = $image ? "<section class=\"section\"><div class=\"container\"><div class=\"article-visual reveal\" style=\"aspect-ratio:16/9;overflow:hidden;border-radius:var(--r-lg);\"><img src=\"$image\" alt=\"\" style=\"width:100%;height:100%;object-fit:cover;\" loading=\"lazy\"></div></div></section>" : '';

    $jsonld = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title'],
        'description' => $post['excerpt'],
        'author' => ['@type' => 'Person', 'name' => $post['author']],
        'datePublished' => $post['date'],
        'image' => $image,
        'url' => $url,
    ], JSON_UNESCAPED_SLASHES);

    $cache = '20261013b';
    $fmt = $post['date'] ? date('F Y', strtotime($post['date'])) : '';
    $ogimg = $image ? '<meta property="og:image" content="' . $base . '/' . ltrim($image, '/') . '">' : '';
    return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$title | OrcaPulse</title>
<meta name="description" content="$excerpt">
<link rel="canonical" href="$url">
<meta property="og:type" content="article">
<meta property="og:title" content="$title">
<meta property="og:description" content="$excerpt">
<meta property="og:url" content="$url">
$ogimg
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/glass.css?v=$cache">
<link rel="stylesheet" href="styles/custom-design.css?v=$cache">
<link rel="stylesheet" href="styles/template-design.css?v=$cache">
<script type="application/ld+json">$jsonld</script>
</head>
<body data-template="post">
<div class="page">
  <div class="orb orb-blue" style="top:220px;left:-280px;"></div>
  <div class="orb orb-cyan" style="top:1200px;right:-240px;"></div>
  $nav_html

  <main id="main">
  <section class="section hero" data-op-key="hero">
    <div class="container">
      <nav class="breadcrumb reveal" aria-label="Breadcrumb">
        <a href="index.html">Home</a> <span>›</span> <a href="insights.html">Insights</a>
      </nav>
      <h1 class="reveal">$title</h1>
      <p class="op-meta reveal" style="color:var(--muted);font-size:0.825rem;letter-spacing:0.03em;margin-top:0.5rem;max-width:60ch;margin-inline:auto;"><time datetime="$date">Published $fmt</time> &nbsp;·&nbsp; By $author</p>
      <p class="hero__sub reveal">$excerpt</p>
    </div>
  </section>

  $image_html

  <section class="section" data-op-key="article">
    <div class="container">
      <div class="prose reveal">
$body
      </div>
    </div>
  </section>

  <section class="section" data-op-key="final-cta">
    <div class="container">
      <div class="cta-block reveal">
        <h2>Thinking about this for your business?</h2>
        <p>A 30-minute call. No pitch deck. We'll tell you honestly whether we can help.</p>
        <a href="$cta_url" target="_blank" rel="noopener" class="btn btn--primary">$cta_label</a>
      </div>
    </div>
  </section>
  </main>

  $footer_html
</div>
<script src="scripts/custom-config.js?v=$cache"></script>
<script src="scripts/main.js?v=$cache" defer></script>
</body>
</html>
HTML;
}

/**
 * Posts changed: refresh the Blog page sections (featured, topics, newest, popular).
 * Kept under the old name so existing callers still work.
 */
function build_insights_listing($blogs = null, $settings = null) {
    return blog_regenerate();
}

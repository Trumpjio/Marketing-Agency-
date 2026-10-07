<?php
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
        if ($html !== $orig) file_put_contents($file, $html);
    }
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
    $brand_tagline = htmlspecialchars($settings['brand_tagline'], ENT_QUOTES);
    $copyright = htmlspecialchars($settings['footer_copyright'], ENT_QUOTES);
    $email = htmlspecialchars($settings['email'], ENT_QUOTES);

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

    return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$title</title>
<meta name="description" content="$excerpt">
<link rel="canonical" href="$url">
<meta property="og:type" content="article">
<meta property="og:title" content="$title">
<meta property="og:description" content="$excerpt">
<meta property="og:url" content="$url">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/glass.css">
<script type="application/ld+json">$jsonld</script>
</head>
<body>
<div class="page">
  <div class="orb orb-blue" style="top:220px;left:-280px;"></div>
  <div class="orb orb-cyan" style="top:1200px;right:-240px;"></div>
  <header class="nav" id="nav">
    <div class="container nav__inner">
      <a href="index.html" class="brand">ORCA<span>PULSE</span></a>
      <button class="nav__mobile-trigger" aria-expanded="false" aria-controls="mobile-menu" id="mobile-toggle">
        <span class="burger"><span></span><span></span><span></span></span> Menu
      </button>
    </div>
    <div class="nav__mobile-panel" id="mobile-menu">
      <div class="nav__mobile-group">
        <div class="nav__mobile-group-title">Explore</div>
        <a href="index.html">Home</a>
        <a href="services.html">Services</a>
        <a href="work.html">Work</a>
        <a href="insights.html">Insights</a>
        <a href="about.html">About</a>
        <a href="locations.html">Locations</a>
      </div>
      <a href="$cta_url" target="_blank" rel="noopener" class="btn btn--primary">$cta_label</a>
    </div>
  </header>

  <section class="section hero">
    <div class="container">
      <nav class="breadcrumb reveal" aria-label="Breadcrumb">
        <a href="index.html">Home</a> <span>›</span> <a href="insights.html">Insights</a>
      </nav>
      <h1 class="reveal">$title</h1>
      <p class="hero__sub reveal">$excerpt</p>
      <div style="color:var(--muted);font-size:0.825rem;margin-top:0.5rem;">By $author · $date</div>
    </div>
  </section>

  $image_html

  <section class="section">
    <div class="container">
      <div class="prose reveal">
$body
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-block reveal">
        <h2>Thinking about this for your business?</h2>
        <p>A 30-minute call. No pitch deck. We'll tell you honestly whether we can help.</p>
        <a href="$cta_url" target="_blank" rel="noopener" class="btn btn--primary">$cta_label</a>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container footer__inner">
      <div class="footer__brand">
        <a href="index.html" class="brand">ORCA<span>PULSE</span></a>
        <p class="footer__tag">$brand_tagline</p>
      </div>
    </div>
    <div class="container footer__bottom">
      <small>$copyright · <a href="mailto:$email">$email</a></small>
    </div>
  </footer>
</div>
<script src="scripts/main.js" defer></script>
</body>
</html>
HTML;
}

/**
 * Regenerate the insights.html listing page with current blog posts.
 */
function build_insights_listing($blogs, $settings) {
    $cards = '';
    foreach ($blogs as $post) {
        $slug = $post['slug'];
        $title = htmlspecialchars($post['title'], ENT_QUOTES);
        $excerpt = htmlspecialchars($post['excerpt'], ENT_QUOTES);
        $image = htmlspecialchars($post['image'] ?? '', ENT_QUOTES);
        $date = htmlspecialchars($post['date'], ENT_QUOTES);
        $imgTag = $image
            ? "<div style=\"aspect-ratio:4/3;overflow:hidden;border-radius:var(--r-md);margin-bottom:var(--s-3);\"><img src=\"$image\" alt=\"\" style=\"width:100%;height:100%;object-fit:cover;\" loading=\"lazy\"></div>"
            : "<div class=\"article-visual\" style=\"margin-bottom:var(--s-3);aspect-ratio:4/3;\"><div class=\"article-visual__symbol\" style=\"font-size:clamp(3rem,10vw,6rem);\">¶</div></div>";
        $cards .= "<a class=\"glass reveal\" href=\"$slug.html\">$imgTag<div style=\"color:var(--muted);font-size:0.75rem;margin-bottom:0.3rem;\">$date</div><h3 class=\"case__title\" style=\"font-size:1.125rem;margin-bottom:var(--s-1);\">$title</h3><p class=\"cap__desc\">$excerpt</p></a>\n";
    }

    $file = SITE_ROOT . '/insights.html';
    if (!file_exists($file)) return;
    $html = file_get_contents($file);
    // Replace the first <section> block that contains the grid
    $pattern = '/<section>\s*<div class="section-head reveal">.*?<\/section>\s*<section>.*?<\/section>\s*<section>.*?<\/section>/s';
    $replacement = "<section><div class=\"section-head reveal\"><div class=\"kicker\">Writing</div><h2>Insights, playbooks, essays.</h2></div><div class=\"grid grid--3\">$cards</div></section>";
    $new = preg_replace($pattern, $replacement, $html, 1);
    if ($new) file_put_contents($file, $new);
}

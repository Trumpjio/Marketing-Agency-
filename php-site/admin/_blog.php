<?php
/**
 * Blog data and the dynamic sections of the Blog page (insights.html).
 *
 * Posts come from two places:
 *   - the original articles (data/legacy-posts.json, static pages insight-*.html)
 *   - posts written in Admin → Blog Posts (data/blogs.json)
 * Category, featured, popular and hidden are stored per post in data/post-meta.json,
 * so they work the same for both kinds.
 *
 * insights.html holds markers where the posts go:
 *   <!--OP-BLOG:featured-->...<!--/OP-BLOG-->     one big card
 *   <!--OP-BLOG:categories-->...                  topic buttons + search
 *   <!--OP-BLOG:latest limit=24-->...             newest posts
 *   <!--OP-BLOG:popular limit=4-->...             posts marked "popular"
 * Anything that changes a post rewrites those blocks (blog_regenerate).
 */

const BLOG_DEFAULT_CATEGORIES = [
    ['slug' => 'playbook',  'label' => 'Playbooks',    'desc' => 'Step-by-step guides for a market or an industry.'],
    ['slug' => 'essay',     'label' => 'Essays',       'desc' => 'Opinions on the craft and business of marketing.'],
    ['slug' => 'field',     'label' => 'Field notes',  'desc' => 'Short observations from live campaigns.'],
    ['slug' => 'teardown',  'label' => 'Teardowns',    'desc' => 'Taking a tactic apart to see what works.'],
    ['slug' => 'benchmark', 'label' => 'Benchmarks',   'desc' => 'Numbers to measure yourself against.'],
    ['slug' => 'corridor',  'label' => 'Corridor',     'desc' => 'Marketing between the GCC and other regions.'],
];

function blog_categories() {
    $f = DATA_DIR . '/blog-categories.json';
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    return is_array($d) && $d ? array_values($d) : BLOG_DEFAULT_CATEGORIES;
}
function blog_categories_save(array $cats) {
    return file_put_contents(DATA_DIR . '/blog-categories.json', json_encode(array_values($cats), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}
function blog_category_label($slug) {
    foreach (blog_categories() as $c) if ($c['slug'] === $slug) return $c['label'];
    return $slug === '' ? 'Uncategorised' : ucfirst($slug);
}

function blog_meta_load() {
    $f = DATA_DIR . '/post-meta.json';
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    return is_array($d) ? $d : [];
}
function blog_meta_save(array $m) {
    return file_put_contents(DATA_DIR . '/post-meta.json', json_encode($m, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}
function blog_meta_set($slug, array $vals) {
    $m = blog_meta_load();
    $m[$slug] = array_merge($m[$slug] ?? [], $vals);
    return blog_meta_save($m);
}

/** All posts, newest first. Hidden posts only when asked for. */
function blog_posts($withHidden = false) {
    $legacy = is_file(DATA_DIR . '/legacy-posts.json') ? json_decode((string) file_get_contents(DATA_DIR . '/legacy-posts.json'), true) : [];
    $meta = blog_meta_load();
    $posts = [];
    $o = 0;
    foreach (is_array($legacy) ? $legacy : [] as $p) {
        if (!is_file(SITE_ROOT . '/' . $p['slug'] . '.html')) continue;
        $p['source'] = 'legacy'; $p['_o'] = $o++;
        $posts[] = $p;
    }
    foreach (blogs_load() as $p) {
        $p['source'] = 'admin'; $p['_o'] = -1; $p['category'] = $p['category'] ?? ''; $p['symbol'] = '¶';
        $posts[] = $p;
    }
    foreach ($posts as &$p) {
        $m = $meta[$p['slug']] ?? [];
        if (isset($m['category'])) $p['category'] = $m['category'];
        $p['featured'] = !empty($m['featured']);
        $p['popular'] = !empty($m['popular']);
        $p['hidden'] = !empty($m['hidden']);
        $p['label'] = blog_category_label($p['category'] ?? '');
        $p['symbol'] = $p['symbol'] ?? '¶';
        $p['image'] = $p['image'] ?? '';
        $p['excerpt'] = $p['excerpt'] ?? '';
    }
    unset($p);
    if (!$withHidden) $posts = array_values(array_filter($posts, fn($p) => !$p['hidden']));
    usort($posts, fn($a, $b) => [$b['date'], $a['_o']] <=> [$a['date'], $b['_o']]);
    return $posts;
}

/* ---------- Rendering ---------- */

function blog_date($d) { $t = strtotime($d); return $t ? date('j M Y', $t) : ''; }

function blog_visual($p, $style = 'margin-bottom:var(--s-3);aspect-ratio:4/3;') {
    if ($p['image'] !== '') return '<div class="article-visual op-visual" style="' . $style . '"><img src="' . h($p['image']) . '" alt="" loading="lazy" decoding="async"></div>';
    return '<div class="article-visual" style="' . $style . '"><div class="article-visual__label">' . h($p['label']) . '</div><div class="article-visual__symbol" style="font-size:clamp(3rem,10vw,6rem);">' . h($p['symbol']) . '</div></div>';
}
function blog_card($p) {
    return '<a class="glass reveal op-card" data-category="' . h($p['category']) . '" data-title="' . h(mb_strtolower($p['title'])) . '" href="' . h($p['slug']) . '.html">'
        . blog_visual($p)
        . '<h3 class="case__title" style="font-size:1.125rem;margin-bottom:var(--s-1);">' . h($p['title']) . '</h3>'
        . '<p class="cap__desc">' . h($p['excerpt']) . '</p>'
        . '<div class="op-card__meta">' . h($p['label']) . ' · ' . h(blog_date($p['date'])) . '</div></a>';
}

function blog_render($type, $opts) {
    $limit = preg_match('/\blimit=(\d+)/', $opts, $m) ? (int) $m[1] : 24;
    $posts = blog_posts();
    switch ($type) {
        case 'featured':
            $f = null;
            foreach ($posts as $p) if ($p['featured']) { $f = $p; break; }
            $f = $f ?: ($posts[0] ?? null);
            if (!$f) return '';
            return '<a class="glass op-featured reveal" href="' . h($f['slug']) . '.html">'
                . '<div class="op-featured__visual">' . blog_visual($f, 'aspect-ratio:16/10;margin:0;height:100%;') . '</div>'
                . '<div class="op-featured__body"><span class="op-featured__tag">' . h($f['label']) . ' · ' . h(blog_date($f['date'])) . '</span>'
                . '<h3>' . h($f['title']) . '</h3><p>' . h($f['excerpt']) . '</p><span class="op-featured__cta">Read the article →</span></div></a>';
        case 'categories':
            if (!$posts) return '';
            $counts = [];
            foreach ($posts as $p) $counts[$p['category']] = ($counts[$p['category']] ?? 0) + 1;
            $chips = '<button type="button" class="op-chip is-active" data-filter="all">All <span class="op-chip__n">' . count($posts) . '</span></button>';
            foreach (blog_categories() as $c) {
                if (empty($counts[$c['slug']])) continue;
                $chips .= '<button type="button" class="op-chip" data-filter="' . h($c['slug']) . '">' . h($c['label']) . ' <span class="op-chip__n">' . $counts[$c['slug']] . '</span></button>';
            }
            return '<div class="op-filter reveal"><div class="op-filter__chips" role="group" aria-label="Filter articles by topic">' . $chips . '</div>'
                . '<input id="op-search" type="search" placeholder="Search articles…" aria-label="Search articles"></div>'
                . '<p id="op-count" class="op-filter__count"></p>';
        case 'latest':
            $cards = '';
            foreach (array_slice($posts, 0, $limit) as $p) $cards .= "\n" . blog_card($p);
            return $cards === '' ? '' : '<div class="grid grid--3 op-latest">' . $cards . "\n</div>";
        case 'popular':
            $pop = array_slice(array_values(array_filter($posts, fn($p) => $p['popular'])), 0, $limit ?: 4);
            if (!$pop) return '';
            $li = '';
            foreach ($pop as $i => $p) {
                $li .= '<li><a class="op-popular__item reveal" href="' . h($p['slug']) . '.html"><span class="op-popular__n">' . sprintf('%02d', $i + 1) . '</span><span><strong>' . h($p['title']) . '</strong><small>' . h($p['label']) . ' · ' . h(blog_date($p['date'])) . '</small></span></a></li>';
            }
            return '<ol class="op-popular">' . $li . '</ol>';
    }
    return '';
}

const BLOG_MARKER_RE = '/<!--OP-BLOG:([a-z]+)([^>]*?)-->.*?<!--\/OP-BLOG-->/s';

/** Rewrite every blog marker on a page; hide a section whose markers produced nothing. */
function blog_render_page($html) {
    if (!str_contains($html, 'OP-BLOG:')) return $html;
    return preg_replace_callback(SECTION_RE, function ($m) {
        if (!str_contains($m[2], 'OP-BLOG:')) return $m[0];
        $empty = true;
        $inner = preg_replace_callback(BLOG_MARKER_RE, function ($mm) use (&$empty) {
            $out = blog_render($mm[1], $mm[2]);
            if ($out !== '') $empty = false;
            return '<!--OP-BLOG:' . $mm[1] . $mm[2] . '-->' . $out . '<!--/OP-BLOG-->';
        }, $m[2]);
        return '<section' . sec_attr_set($m[1], 'data-op-empty', $empty ? 'true' : '') . '>' . $inner . '</section>';
    }, $html);
}

/** Posts changed: refresh the Blog page (live and any unpublished draft of it). */
function blog_regenerate() {
    $n = 0;
    foreach ([SITE_ROOT . '/insights.html', DATA_DIR . '/pending/insights.html'] as $f) {
        if (!is_file($f)) continue;
        $html = file_get_contents($f);
        $new = blog_render_page($html);
        if ($new !== $html && file_put_contents($f, $new) !== false) $n++;
    }
    return $n;
}

<?php
/**
 * Content library: reusable entries (testimonials, case studies, team, awards)
 * that are written once here and shown in matching sections on many pages.
 *
 * Pages hold a marker where the entries go:
 *   <!--OP-COLLECTION:testimonials limit=3-->  ...generated cards...  <!--/OP-COLLECTION-->
 * Saving anything in the library rewrites the generated part of every page that
 * has a marker. A section with nothing to show is hidden (data-op-empty).
 */
require_once __DIR__ . '/_pagelib.php';
require_once __DIR__ . '/_sections.php';

const LIB_COLLECTIONS = [
    'testimonials' => [
        'label' => 'Testimonials', 'one' => 'testimonial',
        'intro' => 'Words from real clients. Only add quotes you have written permission to publish.',
        'title' => 'name', 'sub' => 'quote',
        'fields' => [
            'quote'   => ['Quote', 'textarea', 'What the client said, in their own words.', true],
            'name'    => ['Name', 'text', '', true],
            'role'    => ['Job title', 'text', '', false],
            'company' => ['Company', 'text', 'Leave empty if the client prefers not to be named.', false],
            'image'   => ['Photo or logo', 'image', 'Optional.', false],
            'link'    => ['Link to the original review', 'url', 'Optional. For example the Google, LinkedIn or Clutch page where it was posted.', false],
        ],
    ],
    'case_studies' => [
        'label' => 'Case studies', 'one' => 'case study',
        'intro' => 'Real results. Use measured numbers you can prove. If the client must stay anonymous, describe them (for example “Dubai property developer”).',
        'title' => 'title', 'sub' => 'result',
        'fields' => [
            'title'   => ['Title', 'text', 'For example “Cutting cost per lead for a Dubai developer”.', true],
            'client'  => ['Client or description', 'text', 'A name, or an anonymous description.', false],
            'result'  => ['Headline result', 'text', 'One measured line. For example “Cost per qualified lead down 38% in 6 months”.', false],
            'summary' => ['What we did and what happened', 'textarea', 'Two to four plain sentences.', true],
            'image'   => ['Picture', 'image', 'Optional.', false],
            'link'    => ['Link to the full story', 'url', 'Optional.', false],
        ],
    ],
    'services' => [
        'label' => 'Services', 'one' => 'service',
        'intro' => 'Your list of services. It appears on the home page and on every city and country page. Change a name or description here and every page follows.',
        'title' => 'title', 'sub' => 'summary',
        'fields' => [
            'title'   => ['Name', 'text', '', true],
            'summary' => ['One-line description', 'textarea', 'Keep it to one or two short sentences.', true],
            'link'    => ['Page it links to', 'url', 'For example service-paid-media.html', true],
        ],
    ],
    'team' => [
        'label' => 'Team', 'one' => 'team member',
        'intro' => 'The people clients work with.',
        'title' => 'name', 'sub' => 'role',
        'fields' => [
            'name'  => ['Name', 'text', '', true],
            'role'  => ['Role', 'text', '', true],
            'bio'   => ['Short bio', 'textarea', 'One to three sentences.', false],
            'image' => ['Photo', 'image', 'A square or portrait photo works best.', false],
            'link'  => ['Profile link', 'url', 'Optional. For example LinkedIn.', false],
        ],
    ],
    'awards' => [
        'label' => 'Awards and certifications', 'one' => 'award',
        'intro' => 'Only list awards and certifications you actually hold.',
        'title' => 'title', 'sub' => 'issuer',
        'fields' => [
            'title'  => ['Name of the award or certification', 'text', '', true],
            'issuer' => ['Given by', 'text', '', false],
            'year'   => ['Year', 'text', '', false],
            'image'  => ['Badge or logo', 'image', 'Optional.', false],
            'link'   => ['Link to verify it', 'url', 'Optional.', false],
        ],
    ],
];

/** Entries a collection starts with before anything is saved. */
function lib_defaults($c) {
    if ($c !== 'services') return [];
    $out = [];
    foreach ([
        ['search', 'Search & Discovery', 'SEO, AEO and GEO ranking in Google, Perplexity, ChatGPT, and AI Overviews.', 'service-search-discovery.html'],
        ['paid', 'Paid Media', 'Google, Meta, LinkedIn, TikTok, programmatic managed as one revenue portfolio.', 'service-paid-media.html'],
        ['web', 'Web & Commerce', 'Marketing sites, e-commerce, SaaS platforms. Built to convert, built to last.', 'service-web-commerce.html'],
        ['product', 'Product & App', 'iOS, Android, cross-platform scoping to shipped, with the growth loop baked in.', 'service-product-app.html'],
        ['social', 'Social Presence', 'Strategy and daily operations across every platform your audience actually lives on.', 'service-social-presence.html'],
        ['content', 'Content & Editorial', 'The long-form, the briefings, the owned media the asset that pays off for years.', 'service-content-editorial.html'],
        ['film', 'Film & Motion', 'Production, editing and motion design premium at thumb-scroll speed.', 'service-film-motion.html'],
        ['brand', 'Brand & Design', 'Identity, art direction, systems every surface made by the same hand.', 'service-brand-design.html'],
    ] as [$id, $t, $d, $l]) $out[] = ['id' => 'svc-' . $id, 'title' => $t, 'summary' => $d, 'link' => $l, 'visible' => 1, 'everywhere' => 1, 'tags' => ''];
    return $out;
}

function lib_dir() {
    $d = DATA_DIR . '/library';
    if (!is_dir($d)) @mkdir($d, 0755, true);
    return $d;
}
function lib_path($c) { return lib_dir() . '/' . $c . '.json'; }
function lib_valid($c) { return isset(LIB_COLLECTIONS[$c]); }
function lib_all($c) {
    if (!lib_valid($c)) return [];
    if (!is_file(lib_path($c))) return lib_defaults($c);
    $d = json_decode((string) file_get_contents(lib_path($c)), true);
    return is_array($d) ? array_values($d) : [];
}
function lib_save_all($c, array $items) {
    return lib_valid($c) && file_put_contents(lib_path($c), json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
}
function lib_find($c, $id) {
    foreach (lib_all($c) as $i => $it) if (($it['id'] ?? '') === $id) return [$i, $it];
    return [null, null];
}

/** Clean a posted entry. Returns [entry, error|null]. */
function lib_clean_entry($c, array $post, $id = null) {
    $e = ['id' => $id ?: bin2hex(random_bytes(4))];
    foreach (LIB_COLLECTIONS[$c]['fields'] as $k => [$label, $type, $help, $req]) {
        $v = trim((string) ($post[$k] ?? ''));
        if ($type === 'textarea') $v = trim(preg_replace("/\r\n?/", "\n", strip_tags($v)));
        else $v = trim(strip_tags($v));
        if ($type === 'url' && $v !== '' && !preg_match('#^(https?://|/|[A-Za-z0-9._-]+\.html)#i', $v)) return [null, $label . ' must start with https:// or be a page on this site.'];
        if ($type === 'image' && $v !== '' && !preg_match('#^(uploads/[A-Za-z0-9._/-]+|https://[^\s"<>]+)$#', $v)) return [null, $label . ' must be a picture from the Media Library.'];
        if ($req && $v === '') return [null, $label . ' is required.'];
        $e[$k] = $v;
    }
    $e['visible'] = !empty($post['visible']) ? 1 : 0;
    $e['everywhere'] = !empty($post['everywhere']) ? 1 : 0;
    $e['tags'] = trim(strtolower(preg_replace('/[^a-z0-9,\-\s]/i', '', (string) ($post['tags'] ?? ''))));
    return [$e, null];
}

/* ---------- Page types ---------- */

/** What kind of page this is: from the body's data-template, else from the file name. */
function page_type_of($file, $html = '') {
    if ($html !== '' && preg_match('/<body[^>]*\sdata-template="([^"]+)"/i', $html, $m)) return $m[1];
    $s = basename($file, '.html');
    $map = ['index' => 'home', 'about' => 'about', 'contact' => 'contact', 'work' => 'work', 'services' => 'services',
            'locations' => 'locations', 'insights' => 'blog'];
    if (isset($map[$s])) return $map[$s];
    if (in_array($s, ['privacy-policy', 'terms', 'accessibility-statement', 'ai-instructions', 'sitemap'], true)) return 'legal';
    foreach (['insight-' => 'post', 'service-' => 'service', 'industry-' => 'industry', 'country-' => 'country', 'city-' => 'city'] as $p => $t) {
        if (str_starts_with($s, $p)) return $t;
    }
    return str_contains($s, '-marketing-') ? 'combo' : 'other';
}

/* ---------- Rendering ---------- */

function lib_img($src, $alt, $cls) {
    if ($src === '') return '';
    return '<img class="' . $cls . '" src="' . h($src) . '" alt="' . h($alt) . '" loading="lazy" decoding="async">';
}
function lib_link_attrs($url) {
    return 'href="' . h($url) . '"' . (preg_match('#^https?://#i', $url) ? ' target="_blank" rel="noopener"' : '');
}
function lib_render_entry($c, $e, $i = 0) {
    switch ($c) {
        case 'services':
            return '<a class="glass reveal" href="' . h($e['link']) . '"><div class="cap__icon">' . sprintf('%02d', $i + 1) . '</div><h3 class="cap__title">' . h($e['title']) . '</h3><p class="cap__desc">' . h($e['summary']) . '</p></a>';
        case 'testimonials':
            $who = '<strong>' . h($e['name']) . '</strong>';
            $meta = trim($e['role'] . ($e['role'] !== '' && $e['company'] !== '' ? ', ' : '') . $e['company']);
            if ($meta !== '') $who .= '<span>' . h($meta) . '</span>';
            $cap = lib_img($e['image'] ?? '', $e['name'], 'op-quote__img') . '<div>' . $who . '</div>';
            $src = ($e['link'] ?? '') !== '' ? '<a class="op-quote__src" ' . lib_link_attrs($e['link']) . '>Read the original</a>' : '';
            return '<figure class="glass op-quote reveal"><blockquote><p>' . nl2br(h($e['quote'])) . '</p></blockquote><figcaption>' . $cap . '</figcaption>' . $src . '</figure>';
        case 'case_studies':
            $o = '<article class="glass op-case reveal">' . lib_img($e['image'] ?? '', $e['title'], 'op-case__img') . '<div class="op-case__body">';
            if (($e['client'] ?? '') !== '') $o .= '<p class="op-case__client">' . h($e['client']) . '</p>';
            $o .= '<h3>' . h($e['title']) . '</h3>';
            if (($e['result'] ?? '') !== '') $o .= '<p class="op-case__result">' . h($e['result']) . '</p>';
            $o .= '<p>' . nl2br(h($e['summary'])) . '</p>';
            if (($e['link'] ?? '') !== '') $o .= '<a class="op-case__link" ' . lib_link_attrs($e['link']) . '>Read the full story →</a>';
            return $o . '</div></article>';
        case 'team':
            $photo = ($e['image'] ?? '') !== ''
                ? lib_img($e['image'], $e['name'], 'op-person__img')
                : '<div class="op-person__img op-person__img--initial" aria-hidden="true">' . h(mb_strtoupper(mb_substr($e['name'], 0, 1))) . '</div>';
            $o = '<div class="glass op-person reveal">' . $photo . '<h3>' . h($e['name']) . '</h3><p class="op-person__role">' . h($e['role']) . '</p>';
            if (($e['bio'] ?? '') !== '') $o .= '<p>' . nl2br(h($e['bio'])) . '</p>';
            if (($e['link'] ?? '') !== '') $o .= '<a class="op-person__link" ' . lib_link_attrs($e['link']) . '>Profile</a>';
            return $o . '</div>';
        case 'awards':
            $sub = trim(($e['issuer'] ?? '') . (($e['issuer'] ?? '') !== '' && ($e['year'] ?? '') !== '' ? ' · ' : '') . ($e['year'] ?? ''));
            $o = '<div class="glass op-award reveal">' . lib_img($e['image'] ?? '', $e['title'], 'op-award__img') . '<h3>' . h($e['title']) . '</h3>';
            if ($sub !== '') $o .= '<p>' . h($sub) . '</p>';
            if (($e['link'] ?? '') !== '') $o .= '<a ' . lib_link_attrs($e['link']) . '>Verify</a>';
            return $o . '</div>';
    }
    return '';
}

/** Entries that should appear on a page, honouring visibility, "show everywhere" and tags. */
function lib_for_page($c, $slug, $type, $limit = 0) {
    $out = [];
    foreach (lib_all($c) as $e) {
        if (empty($e['visible'])) continue;
        if (empty($e['everywhere'])) {
            $tags = array_filter(array_map('trim', explode(',', strtolower((string) ($e['tags'] ?? '')))));
            if (!in_array($slug, $tags, true) && !in_array($type, $tags, true)) continue;
        }
        $out[] = $e;
    }
    return $limit > 0 ? array_slice($out, 0, $limit) : $out;
}

const LIB_MARKER_RE = '/<!--OP-COLLECTION:([a-z_]+)([^>]*?)-->.*?<!--\/OP-COLLECTION-->/s';

/** Rewrite the generated cards inside every collection marker, and hide sections that end up empty. */
function lib_render_page($html, $file) {
    $html = blog_render_page($html);
    if (!str_contains($html, 'OP-COLLECTION:')) return $html;
    $slug = basename($file, '.html');
    $type = page_type_of($file, $html);
    return preg_replace_callback(SECTION_RE, function ($m) use ($slug, $type) {
        if (!str_contains($m[2], 'OP-COLLECTION:')) return $m[0];
        $empty = true;
        $inner = preg_replace_callback(LIB_MARKER_RE, function ($mm) use ($slug, $type, &$empty) {
            $c = $mm[1];
            if (!lib_valid($c)) return $mm[0];
            $limit = preg_match('/\blimit=(\d+)/', $mm[2], $lm) ? (int) $lm[1] : 6;
            $items = lib_for_page($c, $slug, $type, $limit);
            if ($items) $empty = false;
            $cards = '';
            foreach ($items as $i => $e) $cards .= "\n" . lib_render_entry($c, $e, $i);
            $cls = $c === 'services' ? 'grid grid--4' . (str_contains($mm[2], 'layout=compact') ? ' grid--compact' : '') : 'op-collection op-collection--' . $c;
            $wrap = $items ? '<div class="' . $cls . '">' . $cards . "\n</div>" : '';
            return '<!--OP-COLLECTION:' . $c . $mm[2] . '-->' . $wrap . '<!--/OP-COLLECTION-->';
        }, $m[2]);
        $attrs = sec_attr_set($m[1], 'data-op-empty', $empty ? 'true' : '');
        return '<section' . $attrs . '>' . $inner . '</section>';
    }, $html);
}

/** After a library change: refresh every live page and every unpublished draft that has a marker. */
function lib_regenerate_all() {
    $n = 0;
    foreach (array_merge(glob(SITE_ROOT . '/*.html') ?: [], glob(DATA_DIR . '/pending/*.html') ?: []) as $f) {
        $html = file_get_contents($f);
        if ($html === false || !(str_contains($html, 'OP-COLLECTION:') || str_contains($html, 'OP-BLOG:'))) continue;
        $new = lib_render_page($html, $f);
        if ($new !== $html && file_put_contents($f, $new) !== false) $n++;
    }
    return $n;
}

/** How many live pages carry a marker for this collection. */
function lib_pages_using($c) {
    $n = 0;
    foreach (glob(SITE_ROOT . '/*.html') ?: [] as $f) if (str_contains((string) file_get_contents($f), 'OP-COLLECTION:' . $c)) $n++;
    return $n;
}

require_once __DIR__ . '/_blog.php';

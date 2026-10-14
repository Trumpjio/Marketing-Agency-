<?php
/**
 * Shared helpers for editing static pages from the admin.
 * Every write goes through page_save() so the previous version is always kept.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/_icons.php';

/* ---------- File access, backups ---------- */

/** Resolve a posted/queried file name to a real page path, or null. */
function page_path($name) {
    $name = basename((string) $name);
    if ($name === '' || !preg_match('/^[A-Za-z0-9._-]+\.html$/', $name)) return null;
    $path = SITE_ROOT . '/' . $name;
    return is_file($path) ? $path : null;
}

function page_backup_path($filepath) {
    return DATA_DIR . '/backups/' . basename($filepath) . '.bak';
}

/** Save a page, keeping exactly one previous version for "Undo". */
function page_save($filepath, $html) {
    $dir = DATA_DIR . '/backups';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $current = file_get_contents($filepath);
    if ($current !== false && $current !== $html) {
        file_put_contents(page_backup_path($filepath), $current);
    }
    return file_put_contents($filepath, $html) !== false;
}

/** Swap the page with its saved previous version. Returns false if none exists. */
function page_undo($filepath) {
    $bak = page_backup_path($filepath);
    if (!is_file($bak)) return false;
    $current = file_get_contents($filepath);
    $previous = file_get_contents($bak);
    file_put_contents($filepath, $previous);
    file_put_contents($bak, $current);
    return true;
}

/* ---------- Working copy (unpublished changes) ---------- */

/**
 * Edits made in the admin go to a working copy in data/pending/. The live
 * page only changes when the working copy is published, so every change can
 * be previewed first.
 */
function pending_path($file) {
    $d = DATA_DIR . '/pending';
    if (!is_dir($d)) @mkdir($d, 0755, true);
    return $d . '/' . basename($file);
}
function page_has_pending($file) { return is_file(pending_path($file)); }
function page_working_html($file) {
    $pp = pending_path($file);
    return is_file($pp) ? file_get_contents($pp) : file_get_contents(SITE_ROOT . '/' . basename($file));
}
function page_pending_save($file, $html) { return file_put_contents(pending_path($file), $html) !== false; }
function page_discard($file) { $pp = pending_path($file); if (is_file($pp)) unlink($pp); }
/** Copy the working copy over the live page (keeping one backup for Undo). */
function page_publish($file) {
    $pp = pending_path($file);
    $live = SITE_ROOT . '/' . basename($file);
    if (!is_file($pp) || !is_file($live)) return false;
    $ok = page_save($live, file_get_contents($pp));
    if ($ok) unlink($pp);
    return $ok;
}
function pending_files() {
    $out = [];
    foreach (glob(DATA_DIR . '/pending/*.html') ?: [] as $f) $out[] = basename($f);
    return $out;
}

/* ---------- Draft registry ---------- */

/**
 * Pages created via duplication start as drafts so they are excluded from
 * sitemap.xml and marked noindex,nofollow until the admin promotes them to
 * published. Stored in data/drafts.json so the state survives page edits.
 */
function drafts_load() {
    $f = DATA_DIR . '/drafts.json';
    if (!file_exists($f)) return [];
    $d = json_decode(file_get_contents($f), true);
    return is_array($d) ? $d : [];
}
function drafts_save($arr) {
    file_put_contents(DATA_DIR . '/drafts.json', json_encode($arr, JSON_PRETTY_PRINT));
}
function draft_mark($slug) {
    $d = drafts_load();
    if (!in_array($slug, $d, true)) { $d[] = $slug; drafts_save($d); }
}
function draft_unmark($slug) {
    $d = drafts_load();
    $d = array_values(array_filter($d, fn($x) => $x !== $slug));
    drafts_save($d);
}
function is_draft($slug) {
    return in_array($slug, drafts_load(), true);
}

/* ---------- Page duplication ---------- */

/**
 * Duplicate an existing page to a new slug and start it as a draft.
 *
 * Rewrites:
 *   - <title>, <meta name="description">, canonical, OG + Twitter URLs
 *   - JSON-LD @id values
 * Adds <meta name="robots" content="noindex,nofollow"> and <meta
 * name="x-op-draft" content="true"> so the clone is excluded from
 * search engines until promoted.
 *
 * Returns ['ok' => bool, 'error' => str, 'path' => new file path].
 */
function page_duplicate($sourceName, $newSlug, $newTitle = null) {
    $src = page_path($sourceName);
    if (!$src) return ['ok' => false, 'error' => 'Source page not found or not editable.'];

    // Validate slug — lower-case kebab, no path separators, no .html suffix allowed
    $slug = strtolower(trim($newSlug));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');
    if ($slug === '' || strlen($slug) > 80) {
        return ['ok' => false, 'error' => 'New URL slug is empty or too long (max 80 chars, lower-case letters, numbers, dashes).'];
    }
    $newFile = $slug . '.html';
    $dest = SITE_ROOT . '/' . $newFile;
    if (file_exists($dest)) {
        return ['ok' => false, 'error' => 'A page already exists at ' . $newFile . '. Choose a different slug.'];
    }

    $html = file_get_contents($src);
    if ($html === false) return ['ok' => false, 'error' => 'Could not read the source page.'];

    $srcBase = basename($src, '.html');
    $srcTitle = parse_title($html);
    $title = $newTitle ? trim($newTitle) : $srcTitle . ' (copy)';

    // 1. Rewrite <title>, meta description if the user supplied a title
    if ($newTitle) {
        $html = preg_replace('/<title>[^<]*<\/title>/i', '<title>' . h($title) . '</title>', $html, 1);
    }
    // 2. Canonical + OG + Twitter URLs (preserve host prefix)
    $html = preg_replace(
        '#(https?://[a-z0-9.-]+/)' . preg_quote($srcBase, '#') . '\.html#i',
        '$1' . $newFile,
        $html
    );
    // 3. Also update og:title/twitter:title to match the new title (if we got one)
    if ($newTitle) {
        $html = preg_replace('/<meta property="og:title" content="[^"]*"[^>]*>/i', '<meta property="og:title" content="' . h($title) . '" />', $html);
        $html = preg_replace('/<meta name="twitter:title" content="[^"]*"[^>]*>/i', '<meta name="twitter:title" content="' . h($title) . '" />', $html);
    }
    // 4. Inject noindex,nofollow + draft marker inside <head> (before </head>)
    if (stripos($html, 'name="robots"') === false) {
        $html = preg_replace(
            '#(</head>)#i',
            '    <meta name="robots" content="noindex,nofollow" />' . "\n" .
            '    <meta name="x-op-draft" content="true" />' . "\n" .
            '  $1',
            $html, 1
        );
    } else {
        $html = preg_replace('#<meta name="robots" content="[^"]*"[^>]*>#i', '<meta name="robots" content="noindex,nofollow" />', $html, 1);
    }

    if (file_put_contents($dest, $html) === false) {
        return ['ok' => false, 'error' => 'Could not write new page. Check Hostinger folder permissions (0755).'];
    }
    draft_mark($slug);
    return ['ok' => true, 'path' => $dest, 'file' => $newFile];
}

/** Promote a draft to published: strip the noindex marker + remove from draft registry + add to sitemap.xml. */
function page_promote_draft($slug) {
    $file = SITE_ROOT . '/' . basename($slug) . '.html';
    if (!is_file($file)) return false;
    $html = file_get_contents($file);
    // Flip robots to index,follow (or remove outright)
    $html = preg_replace('#<meta name="robots" content="noindex,nofollow"[^>]*>\s*#i', '', $html, 1);
    $html = preg_replace('#<meta name="x-op-draft" content="true"[^>]*>\s*#i', '', $html, 1);
    file_put_contents($file, $html);
    draft_unmark($slug);

    sitemap_add($file);
    return true;
}

/* ---------- sitemap.xml ---------- */

function sitemap_url($file) {
    $base = rtrim(settings_load()['base_url'] ?? 'https://orcapulse.com', '/');
    return $base . '/' . basename($file);
}
/** Add a page to sitemap.xml (once). */
function sitemap_add($file, $priority = '0.7') {
    $sitemap = SITE_ROOT . '/sitemap.xml';
    if (!is_file($sitemap)) return false;
    $xml = file_get_contents($sitemap);
    $url = sitemap_url($file);
    if (str_contains($xml, '<loc>' . $url . '</loc>')) return true;
    $entry = "  <url>\n    <loc>$url</loc>\n    <lastmod>" . date('Y-m-d') . "</lastmod>\n    <priority>$priority</priority>\n  </url>\n</urlset>";
    return file_put_contents($sitemap, str_replace('</urlset>', $entry, $xml)) !== false;
}
function sitemap_remove($file) {
    $sitemap = SITE_ROOT . '/sitemap.xml';
    if (!is_file($sitemap)) return false;
    $xml = file_get_contents($sitemap);
    $new = preg_replace('#\s*<url>\s*<loc>' . preg_quote(sitemap_url($file), '#') . '</loc>.*?</url>#s', '', $xml);
    return $new === $xml || file_put_contents($sitemap, $new) !== false;
}

/* ---------- Small parsers used by the editors ---------- */

function pl_first($html, $pattern, $group = 1) {
    return preg_match($pattern, $html, $m) ? trim($m[$group]) : '';
}
function parse_title($html) { return pl_first($html, '/<title>([^<]*)<\/title>/i'); }
function parse_desc($html)  { return pl_first($html, '/<meta\s+name="description"\s+content="([^"]*)"/i'); }
function parse_h1($html) {
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $html, $m)) {
        $v = preg_replace('/<br\s*\/?>/i', "\n", $m[1]);
        $v = preg_replace('/<span[^>]*>(.*?)<\/span>/si', '$1', $v);
        return trim(html_entity_decode(strip_tags($v), ENT_QUOTES, 'UTF-8'));
    }
    return '';
}
function parse_hero_sub($html) {
    return html_entity_decode(pl_first($html, '/<p class="hero__sub[^"]*"[^>]*>(.*?)<\/p>/si'), ENT_QUOTES, 'UTF-8');
}
/** Offset where the page content starts (just after the top menu and mobile menu). */
function body_offset($html) {
    if (preg_match('/<div class="nav__mobile-panel"[^>]*>.*?<\/nav>\s*<\/div>/si', $html, $m, PREG_OFFSET_CAPTURE)) {
        return $m[0][1] + strlen($m[0][0]);
    }
    if (preg_match('/<\/header>/i', $html, $m, PREG_OFFSET_CAPTURE)) return $m[0][1] + strlen($m[0][0]);
    return 0;
}
/** Run a transformation on the page content only, leaving the head and top menu alone. */
function in_body($html, callable $fn) {
    $o = body_offset($html);
    return substr($html, 0, $o) . $fn(substr($html, $o));
}
function parse_cta_url($html) {
    $b = substr($html, body_offset($html));
    return pl_first($b, '/<a[^>]*class="btn btn--primary[^"]*"[^>]*href="([^"]+)"/i')
        ?: pl_first($b, '/<a[^>]*href="([^"]+)"[^>]*class="btn btn--primary[^"]*"/i');
}
function parse_cta_label($html) {
    $b = substr($html, body_offset($html));
    return preg_match('/<a[^>]*class="btn btn--primary[^"]*"[^>]*>(.*?)<\/a>/si', $b, $m)
        ? trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8')) : '';
}

/* ---------- Replacers ---------- */

function replace_title($html, $new) {
    $esc = htmlspecialchars($new, ENT_QUOTES);
    $html = preg_replace_callback('/<title>[^<]*<\/title>/i', fn() => "<title>$esc</title>", $html, 1);
    foreach (['property="og:title"', 'name="twitter:title"'] as $attr) {
        $html = preg_replace_callback('/<meta\s+' . preg_quote($attr, '/') . '\s+content="[^"]*"\s*\/?>/i', fn() => "<meta $attr content=\"$esc\" />", $html, 1);
    }
    return $html;
}
function replace_desc($html, $new) {
    $esc = htmlspecialchars($new, ENT_QUOTES);
    foreach (['name="description"', 'property="og:description"', 'name="twitter:description"'] as $attr) {
        $html = preg_replace_callback('/<meta\s+' . preg_quote($attr, '/') . '\s+content="[^"]*"\s*\/?>/i', fn() => "<meta $attr content=\"$esc\" />", $html, 1);
    }
    return $html;
}
function replace_h1($html, $new) {
    return preg_replace_callback('/<h1([^>]*)>.*?<\/h1>/si', function ($m) use ($new) {
        $lines = preg_split('/\r?\n/', $new);
        $inner = implode('<br>', array_map(fn($l) => htmlspecialchars(trim($l), ENT_QUOTES), $lines));
        return '<h1' . $m[1] . '>' . $inner . '</h1>';
    }, $html, 1);
}
function replace_hero_sub($html, $new) {
    return preg_replace_callback('/<p class="hero__sub([^"]*)"([^>]*)>.*?<\/p>/si', function ($m) use ($new) {
        return '<p class="hero__sub' . $m[1] . '"' . $m[2] . '>' . htmlspecialchars($new, ENT_QUOTES) . '</p>';
    }, $html, 1);
}
function replace_cta_url($html, $old, $new) {
    if (!$old || !$new) return $html;
    return in_body($html, fn($b) => str_replace('href="' . $old . '"', 'href="' . htmlspecialchars($new, ENT_QUOTES) . '"', $b));
}
/** Change the words on every main button in the page content that currently says $old. */
function replace_cta_label($html, $old, $new) {
    if (!$old || !$new || $old === $new) return $html;
    $esc = htmlspecialchars($new, ENT_QUOTES);
    return in_body($html, function ($b) use ($old, $esc) {
        return preg_replace_callback('/<a([^>]*class="btn btn--primary[^"]*"[^>]*)>(.*?)<\/a>/si', function ($m) use ($old, $esc) {
            $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES, 'UTF-8'));
            return $text === $old ? '<a' . $m[1] . '>' . str_ireplace($old, $esc, $m[2]) . '</a>' : $m[0];
        }, $b);
    });
}

/* ---------- Sections ---------- */

const SECTION_RE = '/<section\b([^>]*)>(.*?)<\/section>/si';

/** All sections on the page, in document order. */
function parse_sections($html) {
    preg_match_all(SECTION_RE, $html, $matches, PREG_SET_ORDER);
    $out = [];
    foreach ($matches as $i => $sec) {
        $attrs = $sec[1];
        $inner = $sec[2];
        $title = '';
        if (preg_match('/<div class="kicker"[^>]*>(.*?)<\/div>/si', $inner, $km)) {
            $title = strip_tags($km[1]);
        } elseif (preg_match('/<h[123][^>]*>(.*?)<\/h[123]>/si', $inner, $hm)) {
            $title = preg_replace('/\s+/', ' ', strip_tags($hm[1]));
        } elseif (preg_match('/class="([^"]+)"/', $attrs, $cm)) {
            $title = 'Section: ' . $cm[1];
        } else {
            $title = 'Section ' . ($i + 1);
        }
        $out[] = [
            'index'  => $i,
            'title'  => mb_substr(trim(html_entity_decode($title, ENT_QUOTES, 'UTF-8')), 0, 70),
            'hidden' => str_contains($attrs, 'data-hidden="true"'),
            'inner'  => trim($inner),
        ];
    }
    return $out;
}

/** Apply visibility + inner-HTML edits to sections in one pass. */
function update_sections($html, array $visible, array $newInner) {
    $i = -1;
    return preg_replace_callback(SECTION_RE, function ($m) use (&$i, $visible, $newInner) {
        $i++;
        $attrs = preg_replace('/\s*data-hidden="[^"]*"/i', '', $m[1]);
        if (!in_array($i, $visible, true)) $attrs .= ' data-hidden="true"';
        $inner = array_key_exists($i, $newInner) ? "\n" . $newInner[$i] . "\n" : $m[2];
        return '<section' . $attrs . '>' . $inner . '</section>';
    }, $html);
}

/* ---------- Icons ---------- */

const ICON_SLOT_RE = '/<div class="(cap__icon|article-visual__symbol)"([^>]*)>(.*?)<\/div>/s';
const ICON_MISSING_RE = '/(<(?:div|a)\s+class="(?:glass|feature-card)[^"]*"[^>]*>)(\s*)(<h3[^>]*>)(.*?)(<\/h3>)/s';

function heading_after($html, $offset) {
    if (preg_match('/<h[1-4][^>]*>(.*?)<\/h[1-4]>/si', substr($html, $offset, 900), $hm)) {
        $t = preg_replace('/\s+/', ' ', strip_tags($hm[1]));
        return mb_substr(trim(html_entity_decode($t, ENT_QUOTES, 'UTF-8')), 0, 60);
    }
    return '';
}

function icon_attr($attrs, $name, $default) {
    return preg_match('/data-' . $name . '="([^"]*)"/', $attrs, $m) ? $m[1] : $default;
}

/** Icons already on the page, plus cards that have no icon yet. */
function icon_slots($html) {
    $existing = [];
    preg_match_all(ICON_SLOT_RE, $html, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach ($m as $i => $mm) {
        $attrs = $mm[2][0];
        $existing[] = [
            'index' => $i,
            'kind'  => $mm[1][0],
            'inner' => trim($mm[3][0]),
            'title' => heading_after($html, $mm[0][1] + strlen($mm[0][0])),
            'pos'   => icon_attr($attrs, 'pos', 'above'),
            'size'  => icon_attr($attrs, 'size', 'md'),
            'align' => icon_attr($attrs, 'align', 'left'),
        ];
    }
    $missing = [];
    preg_match_all(ICON_MISSING_RE, $html, $mm2, PREG_SET_ORDER);
    foreach ($mm2 as $j => $x) {
        $missing[] = ['index' => $j, 'title' => trim(preg_replace('/\s+/', ' ', strip_tags($x[4])))];
    }
    return ['existing' => $existing, 'missing' => $missing];
}

function icon_attrs_string($attrs, $pos, $size, $align, $empty) {
    $attrs = preg_replace('/\s+data-(pos|size|align|empty)="[^"]*"/', '', $attrs);
    if ($pos === 'inline') $attrs .= ' data-pos="inline"';
    if ($size === 'sm' || $size === 'lg') $attrs .= ' data-size="' . $size . '"';
    if ($align === 'center') $attrs .= ' data-align="center"';
    if ($empty) $attrs .= ' data-empty="true"';
    return $attrs;
}

/** Apply the icon form ($p = $_POST) to the page HTML. */
function apply_icon_changes($html, array $p) {
    $i = -1;
    $html = preg_replace_callback(ICON_SLOT_RE, function ($m) use (&$i, $p) {
        $i++;
        $inner = $m[3];
        $new = icon_value_to_html((string) ($p['ex_value'][$i] ?? 'keep'));
        if ($new !== null) $inner = $new;
        $attrs = $m[2];
        if ($m[1] === 'cap__icon') {
            $attrs = icon_attrs_string($attrs, $p['ex_pos'][$i] ?? 'above', $p['ex_size'][$i] ?? 'md', $p['ex_align'][$i] ?? 'left', trim($inner) === '');
        }
        return '<div class="' . $m[1] . '"' . $attrs . '>' . $inner . '</div>';
    }, $html);

    $j = -1;
    return preg_replace_callback(ICON_MISSING_RE, function ($m) use (&$j, $p) {
        $j++;
        $inner = icon_value_to_html((string) ($p['nw_value'][$j] ?? 'keep'));
        if ($inner === null || $inner === '') return $m[0];
        $attrs = icon_attrs_string('', $p['nw_pos'][$j] ?? 'above', $p['nw_size'][$j] ?? 'md', $p['nw_align'][$j] ?? 'left', false);
        return $m[1] . '<div class="cap__icon"' . $attrs . '>' . $inner . '</div>' . $m[2] . $m[3] . $m[4] . $m[5];
    }, $html);
}

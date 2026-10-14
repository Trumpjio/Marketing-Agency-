<?php
/**
 * Section manager helpers. Works on the existing static pages: every section is
 * a <section> element, and its settings live as data-* attributes on that tag
 * (plus CSS custom properties in its style attribute), so nothing sits outside
 * the page file and the old editor keeps working.
 *
 *   data-op-key      what the section is (hero, services, faq ...)
 *   data-op-id       unique id
 *   data-op-bg       background colour (hex)        -> --op-bg
 *   data-op-ink      text colour (hex)              -> --op-ink
 *   data-op-pad      sm | md | lg   (no attribute = the template's spacing)
 *   data-op-align    left | center
 *   data-op-image-pos above | below | left | right  (only when an image is set)
 *   data-op-source   testimonials | case_studies | team | awards | map (dynamic)
 *   data-hidden      "true" when hidden
 */
require_once __DIR__ . '/_pagelib.php';

const OP_KEY_LABELS = [
    'hero' => 'Hero', 'trust-bar' => 'Trust bar', 'services' => 'Services', 'why-us' => 'Why us',
    'results' => 'Results / case studies', 'process' => 'Process', 'testimonials' => 'Testimonials',
    'faq' => 'FAQ', 'final-cta' => 'Final call to action', 'story' => 'Our story',
    'mission-vision' => 'Mission and vision', 'team' => 'Team', 'awards' => 'Awards and certifications',
    'featured-post' => 'Featured post', 'categories' => 'Categories', 'latest-posts' => 'Latest posts',
    'popular-posts' => 'Popular posts', 'newsletter' => 'Newsletter', 'included' => "What's included",
    'benefits' => 'Benefits', 'pricing' => 'Pricing and packages', 'overview' => 'Country overview',
    'industries' => 'Industries we serve', 'local-results' => 'Local results', 'map' => 'Map',
    'market' => 'Local market guide', 'article' => 'Article',
    'custom' => 'Custom section', 'cards' => 'Cards', 'steps' => 'Steps',
];

/* ---------- Attribute helpers ---------- */

function sec_attr_get($attrs, $name) {
    return preg_match('/\s' . preg_quote($name, '/') . '="([^"]*)"/i', $attrs, $m) ? html_entity_decode($m[1], ENT_QUOTES) : null;
}
function sec_attr_set($attrs, $name, $value) {
    $attrs = preg_replace('/\s' . preg_quote($name, '/') . '="[^"]*"/i', '', $attrs);
    if ($value !== null && $value !== '') $attrs .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
    return $attrs;
}
function sec_style_set($attrs, $prop, $value) {
    $style = sec_attr_get($attrs, 'style') ?? '';
    $style = preg_replace('/(?:^|;)\s*' . preg_quote($prop, '/') . '\s*:\s*[^;]*/i', '', $style);
    $style = trim($style, " ;");
    if ($value !== null && $value !== '') $style = ($style === '' ? '' : $style . ';') . $prop . ':' . $value;
    return sec_attr_set($attrs, 'style', $style);
}
function sec_valid_hex($v) { return is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', trim($v)) ? strtolower(trim($v)) : ''; }

function sec_settings($attrs) {
    return [
        'key'    => sec_attr_get($attrs, 'data-op-key') ?? '',
        'id'     => sec_attr_get($attrs, 'data-op-id') ?? '',
        'source' => sec_attr_get($attrs, 'data-op-source') ?? '',
        'bg'     => sec_attr_get($attrs, 'data-op-bg') ?? '',
        'ink'    => sec_attr_get($attrs, 'data-op-ink') ?? '',
        'pad'    => sec_attr_get($attrs, 'data-op-pad') ?? '',
        'align'  => sec_attr_get($attrs, 'data-op-align') ?? '',
        'imgpos' => sec_attr_get($attrs, 'data-op-image-pos') ?? 'above',
        'hidden' => sec_attr_get($attrs, 'data-hidden') === 'true',
    ];
}

/** Apply posted design settings to a section's attribute string. */
function sec_apply_settings($attrs, array $p, $hasImage) {
    $bg  = sec_valid_hex($p['bg'] ?? '');
    $ink = sec_valid_hex($p['ink'] ?? '');
    $pad = in_array($p['pad'] ?? '', ['sm', 'md', 'lg'], true) ? $p['pad'] : '';
    $align = in_array($p['align'] ?? '', ['left', 'center'], true) ? $p['align'] : '';
    $pos = in_array($p['imgpos'] ?? 'above', ['above', 'below', 'left', 'right'], true) ? $p['imgpos'] : 'above';

    $attrs = sec_attr_set($attrs, 'data-op-bg', $bg);
    $attrs = sec_style_set($attrs, '--op-bg', $bg);
    $attrs = sec_attr_set($attrs, 'data-op-ink', $ink);
    $attrs = sec_style_set($attrs, '--op-ink', $ink);
    $attrs = sec_attr_set($attrs, 'data-op-pad', $pad);
    $attrs = sec_attr_set($attrs, 'data-op-align', $align);
    $attrs = sec_attr_set($attrs, 'data-op-image-pos', $hasImage ? $pos : '');
    return $attrs;
}

/* ---------- Splitting a page into sections and joining it back ---------- */

function sec_split($html) {
    preg_match_all(SECTION_RE, $html, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $out = ['prefix' => $html, 'blocks' => [], 'gaps' => [], 'suffix' => ''];
    if (!$m) return $out;
    $out['prefix'] = substr($html, 0, $m[0][0][1]);
    $end = 0;
    foreach ($m as $i => $mm) {
        $start = $mm[0][1];
        if ($i > 0) $out['gaps'][] = substr($html, $end, $start - $end);
        $out['blocks'][] = ['attrs' => $mm[1][0], 'inner' => $mm[2][0]];
        $end = $start + strlen($mm[0][0]);
    }
    $out['suffix'] = substr($html, $end);
    return $out;
}
function sec_block_html($b) { return '<section' . $b['attrs'] . '>' . $b['inner'] . '</section>'; }
function sec_join(array $split, array $blocks) {
    $out = $split['prefix'];
    foreach ($blocks as $i => $b) {
        if ($i > 0) $out .= $split['gaps'][$i - 1] ?? "\n\n      ";
        $out .= sec_block_html($b);
    }
    return $out . $split['suffix'];
}

/* ---------- Finding a balanced <div> block ---------- */

function sec_div_extent($html, $startPos) {
    if (!preg_match_all('/<(\/?)div\b[^>]*>/i', $html, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE, $startPos)) return null;
    $depth = 0;
    foreach ($m as $t) {
        $depth += $t[1][0] === '/' ? -1 : 1;
        if ($depth === 0) return [$startPos, $t[0][1] + strlen($t[0][0])];
    }
    return null;
}
function sec_find_div($html, $classNeedle) {
    if (!preg_match('/<div\b[^>]*class="[^"]*' . preg_quote($classNeedle, '/') . '[^"]*"[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE)) return null;
    return sec_div_extent($html, $m[0][1]);
}

/* ---------- Managed blocks: image, FAQ list, library marker, map ---------- */

const OP_TOKENS = ['faq' => '<!--OP-SLOT-FAQ-->', 'lib' => '<!--OP-SLOT-LIB-->', 'map' => '<!--OP-SLOT-MAP-->'];

/** Pull the managed blocks out of a section's inner HTML. Returns [editableHtml, slots, media]. */
function sec_extract($inner) {
    $slots = ['faq' => '', 'lib' => '', 'map' => ''];
    $media = ['src' => '', 'alt' => ''];
    if (preg_match('#<figure class="op-media"[^>]*>.*?</figure>\s*#si', $inner, $m)) {
        if (preg_match('/<img[^>]*\ssrc="([^"]*)"/i', $m[0], $s)) $media['src'] = html_entity_decode($s[1], ENT_QUOTES);
        if (preg_match('/<img[^>]*\salt="([^"]*)"/i', $m[0], $a)) $media['alt'] = html_entity_decode($a[1], ENT_QUOTES);
        $inner = str_replace($m[0], '', $inner);
    }
    if (($ext = sec_find_div($inner, 'faq-list'))) {
        $slots['faq'] = substr($inner, $ext[0], $ext[1] - $ext[0]);
        $inner = substr($inner, 0, $ext[0]) . OP_TOKENS['faq'] . substr($inner, $ext[1]);
    }
    if (($ext = sec_find_div($inner, 'op-map'))) {
        $slots['map'] = substr($inner, $ext[0], $ext[1] - $ext[0]);
        $inner = substr($inner, 0, $ext[0]) . OP_TOKENS['map'] . substr($inner, $ext[1]);
    }
    if (preg_match('/<!--OP-(COLLECTION|BLOG):[^>]*?-->.*?<!--\/OP-\1-->/s', $inner, $m)) {
        $slots['lib'] = $m[0];
        $inner = str_replace($m[0], OP_TOKENS['lib'], $inner);
    }
    return [trim($inner), $slots, $media];
}

/** Put the managed blocks back. A block whose token was deleted is appended inside the container. */
function sec_restore($editable, array $slots) {
    foreach (['faq', 'lib', 'map'] as $k) {
        $tok = OP_TOKENS[$k];
        if (str_contains($editable, $tok)) {
            $editable = str_replace($tok, $slots[$k], $editable);
        } elseif ($slots[$k] !== '') {
            $editable = sec_append_in_container($editable, $slots[$k]);
        }
    }
    return "\n" . trim($editable) . "\n";
}
/** Add HTML at the end of the section's content: inside its container if it has one, otherwise at the end. */
function sec_append_in_container($inner, $html) {
    if (preg_match('/<div class="container"[^>]*>/i', $inner, $m, PREG_OFFSET_CAPTURE) && ($ext = sec_div_extent($inner, $m[0][1]))) {
        $close = $ext[1] - strlen('</div>');
        return substr($inner, 0, $close) . "\n" . $html . "\n" . substr($inner, $close);
    }
    return rtrim($inner) . "\n" . $html . "\n";
}
/** True when most of the page's sections do not carry their own .container (the page wraps them in one). */
function sec_page_is_flat(array $blocks) {
    if (!$blocks) return false;
    $with = 0;
    foreach ($blocks as $b) if (preg_match('/<div class="container/i', $b['inner'])) $with++;
    return $with * 2 < count($blocks);
}
/** Remove the outer .container from a section's content, keeping what is inside it. */
function sec_unwrap_container($inner) {
    $t = trim($inner);
    if (preg_match('/^<div class="container"[^>]*>/i', $t, $m) && ($ext = sec_div_extent($t, 0)) && $ext[1] === strlen($t)) {
        return "\n" . trim(substr($t, strlen($m[0]), -strlen('</div>'))) . "\n";
    }
    return $inner;
}
function sec_media_html($src, $alt) {
    return '<figure class="op-media" data-op-media><img src="' . h($src) . '" alt="' . h($alt) . '" loading="lazy" decoding="async"></figure>';
}
/** Insert or remove the managed image. Position is handled by CSS from data-op-image-pos. */
function sec_media_apply($inner, $src, $alt, $pos) {
    $inner = preg_replace('#<figure class="op-media"[^>]*>.*?</figure>\s*#si', '', $inner);
    $src = trim($src);
    if ($src === '' || !preg_match('#^(uploads/[A-Za-z0-9._/-]+|https://[^\s"<>]+)$#', $src)) return $inner;
    $fig = sec_media_html($src, trim($alt) !== '' ? trim($alt) : 'Image');
    if ($pos === 'below') return sec_append_in_container($inner, $fig);
    if (preg_match('/<div class="container"[^>]*>/i', $inner, $m, PREG_OFFSET_CAPTURE)) {
        $at = $m[0][1] + strlen($m[0][0]);
        return substr($inner, 0, $at) . "\n" . $fig . substr($inner, $at);
    }
    return "\n" . $fig . $inner;
}

/* ---------- FAQ ---------- */

function faq_parse($html) {
    $items = [];
    if (preg_match_all('#<details\b[^>]*\bclass="[^"]*\bfaq\b[^"]*"[^>]*>\s*<summary>\s*<span>(.*?)</span>.*?<div class="faq__body">(.*?)</div>\s*</details>#si', $html, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) $items[] = ['q' => trim(html_entity_decode(strip_tags($x[1]), ENT_QUOTES, 'UTF-8')), 'a' => trim($x[2])];
    }
    return $items;
}
function faq_clean_answer($a) {
    $a = trim(str_replace("\r", '', (string) $a));
    $a = strip_tags($a, '<p><a><strong><em><ul><ol><li><br>');
    $a = preg_replace_callback('/<a\b[^>]*>/i', function ($m) {
        if (!preg_match('/href\s*=\s*"([^"]*)"/i', $m[0], $h)) return '<a>';
        $u = trim(html_entity_decode($h[1]));
        if (!preg_match('#^(https?://|mailto:|tel:|/|[a-z0-9._-]+\.html|\#)#i', $u)) return '<a>';
        return '<a href="' . h($u) . '"' . (preg_match('#^https?://#i', $u) ? ' target="_blank" rel="noopener"' : '') . '>';
    }, $a);
    if (!preg_match('/<(p|ul|ol)\b/i', $a)) {
        $paras = preg_split('/\n{2,}/', $a);
        $a = implode('', array_map(fn($p) => '<p>' . nl2br(trim($p)) . '</p>', array_filter($paras, 'strlen')));
    }
    return $a;
}
function faq_list_html(array $items) {
    $out = '<div class="faq-list reveal">';
    foreach ($items as $it) {
        $q = trim((string) ($it['q'] ?? ''));
        $a = faq_clean_answer($it['a'] ?? '');
        if ($q === '' || trim(strip_tags($a)) === '') continue;
        $out .= "\n            <details class=\"faq\">\n              <summary><span>" . h($q) . "</span><span class=\"faq__plus\">+</span></summary>\n              <div class=\"faq__body\">" . $a . "</div>\n            </details>";
    }
    return $out . "\n          </div>";
}
/** Keep the FAQPage structured data in step with the FAQs actually on the page. */
function faq_sync_schema($html) {
    $faqs = faq_parse($html);
    $entities = [];
    foreach ($faqs as $f) {
        $a = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($f['a']), ENT_QUOTES, 'UTF-8')));
        if ($f['q'] !== '' && $a !== '') $entities[] = ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
    }
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    $done = false;
    $html = preg_replace_callback('#<script type="application/ld\+json">(.*?)</script>#s', function ($m) use ($entities, &$done, $flags) {
        if (!str_contains($m[1], '"FAQPage"')) return $m[0];
        $d = json_decode($m[1], true);
        if (!is_array($d)) {
            // A FAQPage block that is not valid JSON (for example an unescaped quote): rebuild it from the page.
            if (str_contains($m[1], '"@graph"')) return $m[0];
            $d = ['@context' => 'https://schema.org', '@type' => 'FAQPage'];
        }
        $done = true;
        if (($d['@type'] ?? '') === 'FAQPage') {
            if (!$entities) return '';
            $d['mainEntity'] = $entities;
        } elseif (isset($d['@graph'])) {
            foreach ($d['@graph'] as $i => $node) {
                if (($node['@type'] ?? '') === 'FAQPage') {
                    if ($entities) $d['@graph'][$i]['mainEntity'] = $entities; else unset($d['@graph'][$i]);
                }
            }
            $d['@graph'] = array_values($d['@graph']);
        }
        return '<script type="application/ld+json">' . json_encode($d, $flags) . '</script>';
    }, $html);
    if (!$done && $entities) {
        $url = '';
        if (preg_match('/<link rel="canonical" href="([^"]+)"/i', $html, $c)) $url = $c[1];
        $ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage'] + ($url ? ['@id' => $url . '#faq'] : []) + ['mainEntity' => $entities];
        $tag = '    <script type="application/ld+json">' . json_encode($ld, $flags) . "</script>\n  </head>";
        $html = preg_replace_callback('#</head>#i', fn() => $tag, $html, 1);
    }
    return $html;
}

/* ---------- Map ---------- */

function map_osm_embed($lat, $lon, $zoom) {
    $zoom = max(3, min(17, (int) $zoom));
    $lonSpan = 360 / pow(2, $zoom) * 1.6;
    $latSpan = $lonSpan * 0.55;
    $bbox = implode(',', [round($lon - $lonSpan / 2, 5), round($lat - $latSpan / 2, 5), round($lon + $lonSpan / 2, 5), round($lat + $latSpan / 2, 5)]);
    return 'https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox . '&layer=mapnik&marker=' . round($lat, 5) . '%2C' . round($lon, 5);
}
function map_valid_embed($url) {
    return (bool) preg_match('#^https://(www\.openstreetmap\.org/export/embed\.html\?|www\.google\.com/maps/embed\?)[^\s"<>]+$#', $url);
}
/** Build the map block from admin fields, or '' when nothing valid was supplied. */
function map_block_html($title, $lat, $lon, $zoom, $custom) {
    $title = trim($title) !== '' ? trim($title) : 'Map';
    $custom = trim($custom);
    if ($custom !== '') {
        if (!map_valid_embed($custom)) return '';
        $src = $custom; $data = 'custom'; $link = '';
    } elseif (is_numeric($lat) && is_numeric($lon) && abs($lat) <= 90 && abs($lon) <= 180) {
        $src = map_osm_embed((float) $lat, (float) $lon, $zoom);
        $data = round((float) $lat, 5) . ',' . round((float) $lon, 5) . ',' . max(3, min(17, (int) $zoom));
        $link = '<a class="op-map__link" href="https://www.openstreetmap.org/?mlat=' . round((float) $lat, 5) . '&amp;mlon=' . round((float) $lon, 5) . '#map=' . max(3, min(17, (int) $zoom)) . '/' . round((float) $lat, 5) . '/' . round((float) $lon, 5) . '" target="_blank" rel="noopener">Open larger map</a>';
    } else return '';
    return '<div class="op-map reveal" data-op-map="' . h($data) . '" data-op-map-title="' . h($title) . '"><iframe src="' . h($src) . '" title="' . h($title) . '" loading="lazy" referrerpolicy="no-referrer"></iframe>' . $link . '</div>';
}
function map_parse($slotHtml) {
    $r = ['title' => '', 'lat' => '', 'lon' => '', 'zoom' => 11, 'custom' => ''];
    if ($slotHtml === '') return $r;
    if (preg_match('/data-op-map-title="([^"]*)"/', $slotHtml, $m)) $r['title'] = html_entity_decode($m[1], ENT_QUOTES);
    if (preg_match('/data-op-map="([^"]*)"/', $slotHtml, $m)) {
        if ($m[1] === 'custom') { if (preg_match('/<iframe[^>]*\ssrc="([^"]*)"/', $slotHtml, $s)) $r['custom'] = html_entity_decode($s[1], ENT_QUOTES); }
        else { $p = explode(',', $m[1]); $r['lat'] = $p[0] ?? ''; $r['lon'] = $p[1] ?? ''; $r['zoom'] = $p[2] ?? 11; }
    }
    return $r;
}

/* ---------- Section templates for "Add section" ---------- */

function sec_templates() {
    $cta = h(settings_load()['cta_url'] ?? 'https://calendly.com/orcapulse/intro-call');
    $cards = '';
    foreach ([['01', 'First point', 'Say what this is and why it matters to the customer.'], ['02', 'Second point', 'Keep it short, concrete and in plain words.'], ['03', 'Third point', 'One idea per card.']] as $c) {
        $cards .= "\n            <div class=\"glass reveal\"><div class=\"cap__icon\">{$c[0]}</div><h3 class=\"cap__title\">{$c[1]}</h3><p class=\"cap__desc\">{$c[2]}</p></div>";
    }
    $steps = '';
    foreach ([['01', 'Step one', 'What happens first.'], ['02', 'Step two', 'What happens next.'], ['03', 'Step three', 'How we build it.'], ['04', 'Step four', 'How we keep improving it.']] as $c) {
        $steps .= "\n            <div class=\"glass reveal\"><div class=\"cap__icon\">{$c[0]}</div><h3 class=\"cap__title\">{$c[1]}</h3><p class=\"cap__desc\">{$c[2]}</p></div>";
    }
    $head = fn($k, $t, $p) => "<div class=\"section-head reveal\"><div class=\"kicker\">$k</div><h2>$t</h2><p>$p</p></div>";
    return [
        'text' => ['label' => 'Heading and text', 'desc' => 'A heading with a short intro and a paragraph.',
            'html' => "<section class=\"section\" data-op-key=\"custom\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Small label','Section heading','A short line that introduces this section.')}<div class=\"prose reveal\"><p>Write your text here. Keep it short and clear.</p></div></div></section>"],
        'text-image' => ['label' => 'Text with image', 'desc' => 'Text on one side and a picture on the other. Replace the placeholder picture before publishing.',
            'html' => "<section class=\"section\" data-op-key=\"custom\" data-op-id=\"{{id}}\" data-op-image-pos=\"right\"><div class=\"container\"><figure class=\"op-media\" data-op-media><img src=\"uploads/placeholder.svg\" alt=\"Placeholder image\" loading=\"lazy\"></figure>{$head('Small label','Section heading','A short line that introduces this section.')}<div class=\"prose reveal\"><p>Write your text here. Keep it short and clear.</p></div><p class=\"reveal\" style=\"margin-top:var(--s-3)\"><a class=\"btn btn--primary\" href=\"$cta\" target=\"_blank\" rel=\"noopener\">Button text</a></p></div></section>"],
        'cards' => ['label' => 'Three cards', 'desc' => 'A row of three short cards.',
            'html' => "<section class=\"section\" data-op-key=\"cards\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Small label','Section heading','')}<div class=\"grid grid--3\">$cards\n          </div></div></section>"],
        'steps' => ['label' => 'Four steps', 'desc' => 'A numbered process.',
            'html' => "<section class=\"section\" data-op-key=\"steps\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('How it works','Our process','')}<div class=\"grid grid--4\">$steps\n          </div></div></section>"],
        'faq' => ['label' => 'FAQ', 'desc' => 'Questions and answers. Search engines read these too.',
            'html' => "<section class=\"section\" data-op-key=\"faq\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Questions','Common questions','')}" . faq_list_html([['q' => 'First question?', 'a' => 'Write the answer here.'], ['q' => 'Second question?', 'a' => 'Write the answer here.']]) . "</div></section>"],
        'cta' => ['label' => 'Call to action', 'desc' => 'A closing block with one button.',
            'html' => "<section class=\"section\" data-op-key=\"final-cta\" data-op-id=\"{{id}}\"><div class=\"container\"><div class=\"cta-block reveal\"><h2>Ready to talk?</h2><p>A 30-minute call. No pitch deck.</p><a href=\"$cta\" target=\"_blank\" rel=\"noopener\" class=\"btn btn--primary\">Book a strategy call</a></div></div></section>"],
        'trust' => ['label' => 'Trust bar', 'desc' => 'A short line with chips. Add logos from the editor if you have them.',
            'html' => "<section class=\"section\" data-op-key=\"trust-bar\" data-op-id=\"{{id}}\" data-op-pad=\"sm\"><div class=\"container\"><p class=\"trust__label\" style=\"text-align:center\">Trusted by</p><ul class=\"chips chips--center\" style=\"justify-content:center\"><li class=\"chip\">First name</li><li class=\"chip\">Second name</li><li class=\"chip\">Third name</li></ul></div></section>"],
        'map' => ['label' => 'Map', 'desc' => 'An OpenStreetMap map. Set the place in the Map box after saving.',
            'html' => "<section class=\"section\" data-op-key=\"map\" data-op-source=\"map\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Where we work','On the map','')}" . map_block_html('Map', 25.2048, 55.2708, 10, '') . "</div></section>"],
        'services' => ['label' => 'Services (from library)', 'desc' => 'Shows your list of services from Content library, as cards that link to each service page.',
            'html' => "<section class=\"section\" data-op-key=\"services\" data-op-source=\"services\" data-op-empty=\"true\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Services','What we do','Eight services that work together.')}<!--OP-COLLECTION:services limit=8--><!--/OP-COLLECTION--></div></section>"],
        'testimonials' => ['label' => 'Testimonials (from library)', 'desc' => 'Shows the testimonials you add in Content library. Hidden until there is at least one.',
            'html' => "<section class=\"section\" data-op-key=\"testimonials\" data-op-source=\"testimonials\" data-op-empty=\"true\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('What clients say','In their words','')}<!--OP-COLLECTION:testimonials--><!--/OP-COLLECTION--></div></section>"],
        'case_studies' => ['label' => 'Case studies (from library)', 'desc' => 'Shows the case studies you add in Content library. Hidden until there is at least one.',
            'html' => "<section class=\"section\" data-op-key=\"results\" data-op-source=\"case_studies\" data-op-empty=\"true\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Results','Work we are proud of','')}<!--OP-COLLECTION:case_studies--><!--/OP-COLLECTION--></div></section>"],
        'team' => ['label' => 'Team (from library)', 'desc' => 'Shows the people you add in Content library.',
            'html' => "<section class=\"section\" data-op-key=\"team\" data-op-source=\"team\" data-op-empty=\"true\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Team','The people you will work with','')}<!--OP-COLLECTION:team--><!--/OP-COLLECTION--></div></section>"],
        'awards' => ['label' => 'Awards (from library)', 'desc' => 'Shows the awards and certifications you add in Content library.',
            'html' => "<section class=\"section\" data-op-key=\"awards\" data-op-source=\"awards\" data-op-empty=\"true\" data-op-id=\"{{id}}\"><div class=\"container\">{$head('Recognition','Awards and certifications','')}<!--OP-COLLECTION:awards--><!--/OP-COLLECTION--></div></section>"],
    ];
}
function sec_new_block($tplKey) {
    $t = sec_templates()[$tplKey] ?? null;
    if (!$t) return null;
    $html = str_replace('{{id}}', 'sec_' . bin2hex(random_bytes(4)), $t['html']);
    if (!preg_match(SECTION_RE, $html, $m)) return null;
    return ['attrs' => $m[1], 'inner' => "\n" . $m[2] . "\n"];
}

/** A short, human name for a section row. */
function sec_title($attrs, $inner) {
    $key = sec_attr_get($attrs, 'data-op-key');
    if ($key && $key !== 'custom' && isset(OP_KEY_LABELS[$key])) return OP_KEY_LABELS[$key];
    if (preg_match('/<h[123][^>]*>(.*?)<\/h[123]>/si', $inner, $m)) return mb_substr(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(preg_replace('#<br\s*/?>#i', ' ', $m[1])), ENT_QUOTES, 'UTF-8'))), 0, 60);
    if (preg_match('/<div class="kicker"[^>]*>(.*?)<\/div>/si', $inner, $m)) return mb_substr(trim(strip_tags($m[1])), 0, 60);
    return 'Section';
}

/* ---------- Applying a posted form to the page ---------- */

/**
 * Rebuild the page from the editor form: order, deletions, new sections,
 * visibility, content, design settings, image, FAQ and map.
 * Returns [html, error|null]. Never drops every section by accident.
 */
function sec_process_post($html, array $post) {
    $split = sec_split($html);
    $orig = $split['blocks'];
    $order = array_values(array_filter((array) ($post['order'] ?? []), 'is_string'));
    $visible = array_flip((array) ($post['section_visible'] ?? []));
    $secs = (array) ($post['sec'] ?? []);

    if ($orig && !$order) return [$html, 'The section list was empty, so nothing was changed.'];
    $flat = sec_page_is_flat($orig);

    $blocks = [];
    $seen = [];
    foreach ($order as $tok) {
        if (isset($seen[$tok])) continue;
        $seen[$tok] = true;
        $p = (array) ($secs[$tok] ?? []);
        if (preg_match('/^s(\d+)$/', $tok, $m) && isset($orig[(int) $m[1]])) {
            $b = $orig[(int) $m[1]];
        } elseif (preg_match('/^n\d+$/', $tok)) {
            $b = sec_new_block($p['tpl'] ?? '');
            if (!$b) continue;
            if ($flat) $b['inner'] = sec_unwrap_container($b['inner']);
        } else continue;

        // content + managed blocks
        [$editable, $slots, $media] = sec_extract($b['inner']);
        if (($p['dirty'] ?? '0') === '1' && isset($p['html'])) $editable = trim((string) $p['html']);
        if (isset($p['faq_q']) && is_array($p['faq_q'])) {
            $items = [];
            foreach ($p['faq_q'] as $i => $q) $items[] = ['q' => $q, 'a' => $p['faq_a'][$i] ?? ''];
            $slots['faq'] = faq_list_html($items);
            if (!str_contains($editable, OP_TOKENS['faq'])) $editable = sec_append_in_container($editable, OP_TOKENS['faq']);
        }
        if (array_key_exists('map_lat', $p)) {
            $slots['map'] = map_block_html($p['map_title'] ?? '', $p['map_lat'] ?? '', $p['map_lon'] ?? '', $p['map_zoom'] ?? 11, $p['map_custom'] ?? '');
            if ($slots['map'] !== '' && !str_contains($editable, OP_TOKENS['map'])) $editable = sec_append_in_container($editable, OP_TOKENS['map']);
        }
        $inner = sec_restore($editable, $slots);

        // image
        $img = array_key_exists('img', $p) ? trim((string) $p['img']) : $media['src'];
        $alt = array_key_exists('imgalt', $p) ? (string) $p['imgalt'] : $media['alt'];
        $inner = sec_media_apply($inner, $img, $alt, $p['imgpos'] ?? 'above');
        $hasImage = str_contains($inner, 'class="op-media"');

        // attributes: design + visibility
        $attrs = $b['attrs'];
        if (array_key_exists('bg', $p) || array_key_exists('pad', $p)) $attrs = sec_apply_settings($attrs, $p, $hasImage);
        else $attrs = sec_attr_set($attrs, 'data-op-image-pos', $hasImage ? (sec_attr_get($attrs, 'data-op-image-pos') ?? 'above') : '');
        $attrs = sec_attr_set($attrs, 'data-hidden', isset($visible[$tok]) ? '' : 'true');
        $blocks[] = ['attrs' => $attrs, 'inner' => $inner];
    }
    if ($orig && !$blocks) return [$html, 'That would remove every section, so nothing was changed.'];

    $out = sec_join($split, $blocks);
    return [faq_sync_schema($out), null];
}

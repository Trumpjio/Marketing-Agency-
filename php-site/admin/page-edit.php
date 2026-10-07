<?php require_once __DIR__ . '/_layout.php';
require __DIR__ . '/_build.php';

$file = basename($_GET['file'] ?? '');
if (!$file || !preg_match('/\.html$/i', $file)) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Invalid file.'];
    header('Location: pages.php'); exit;
}
$filepath = SITE_ROOT . '/' . $file;
if (!file_exists($filepath)) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => "File not found: $file"];
    header('Location: pages.php'); exit;
}

$html = file_get_contents($filepath);

/* ---------- Parse helpers ---------- */
function parse_field_str($html, $pattern, $group = 1) {
    if (preg_match($pattern, $html, $m)) return trim($m[$group]);
    return '';
}
function parse_title($html) { return parse_field_str($html, '/<title>([^<]*)<\/title>/i'); }
function parse_desc($html)  { return parse_field_str($html, '/<meta\s+name="description"\s+content="([^"]*)"/i'); }
function parse_h1($html) {
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $html, $m)) {
        $v = preg_replace('/<br\s*\/?>/i', "\n", $m[1]);
        $v = preg_replace('/<span[^>]*>(.*?)<\/span>/si', '$1', $v);
        return trim(strip_tags($v));
    }
    return '';
}
function parse_hero_sub($html) {
    return parse_field_str($html, '/<p class="hero__sub[^"]*"[^>]*>(.*?)<\/p>/si');
}
function parse_cta_url($html) {
    return parse_field_str($html, '/<a[^>]*class="btn btn--primary[^"]*"[^>]*href="([^"]+)"/i')
        ?: parse_field_str($html, '/<a[^>]*href="([^"]+)"[^>]*class="btn btn--primary[^"]*"/i');
}
function parse_cta_label($html) {
    if (preg_match('/<a[^>]*class="btn btn--primary[^"]*"[^>]*>(.*?)<\/a>/si', $html, $m)) {
        return trim(strip_tags($m[1]));
    }
    return '';
}
function parse_sections($html) {
    // Find all <section ...>...</section> blocks AFTER </header>
    if (preg_match('/<\/header>(.*?)<footer/si', $html, $m)) {
        $body = $m[1];
        preg_match_all('/<section([^>]*)>(.*?)<\/section>/si', $body, $matches, PREG_SET_ORDER);
        $out = [];
        foreach ($matches as $i => $sec) {
            $attrs = $sec[1];
            $inner = $sec[2];
            // Determine section title: first h1/h2/h3 or kicker text
            $title = '';
            if (preg_match('/<div class="kicker"[^>]*>(.*?)<\/div>/si', $inner, $km)) {
                $title = strip_tags($km[1]);
            } elseif (preg_match('/<h[123][^>]*>(.*?)<\/h[123]>/si', $inner, $hm)) {
                $title = strip_tags($hm[1]);
                $title = preg_replace('/<[^>]+>/', '', $title);
                $title = preg_replace('/\s+/', ' ', $title);
                $title = mb_substr(trim($title), 0, 70);
            } else {
                // Fallback — classname or snippet
                if (preg_match('/class="([^"]+)"/', $attrs, $cm)) {
                    $title = 'Section: ' . $cm[1];
                } else {
                    $title = 'Section ' . ($i + 1);
                }
            }
            $hidden = str_contains($attrs, 'data-hidden="true"');
            $out[] = ['index' => $i, 'title' => $title, 'hidden' => $hidden];
        }
        return $out;
    }
    return [];
}

/* ---------- Replace helpers ---------- */
function replace_title($html, $new) {
    return preg_replace('/<title>[^<]*<\/title>/i', '<title>' . h($new) . '</title>', $html, 1);
}
function replace_desc($html, $new) {
    $esc = htmlspecialchars($new, ENT_QUOTES);
    $html = preg_replace('/<meta\s+name="description"\s+content="[^"]*"\s*\/?>/i', '<meta name="description" content="'.$esc.'" />', $html, 1);
    $html = preg_replace('/<meta\s+property="og:description"\s+content="[^"]*"\s*\/?>/i', '<meta property="og:description" content="'.$esc.'" />', $html, 1);
    $html = preg_replace('/<meta\s+name="twitter:description"\s+content="[^"]*"\s*\/?>/i', '<meta name="twitter:description" content="'.$esc.'" />', $html, 1);
    return $html;
}
function replace_og_title($html, $new) {
    $esc = htmlspecialchars($new, ENT_QUOTES);
    $html = preg_replace('/<meta\s+property="og:title"\s+content="[^"]*"\s*\/?>/i', '<meta property="og:title" content="'.$esc.'" />', $html, 1);
    $html = preg_replace('/<meta\s+name="twitter:title"\s+content="[^"]*"\s*\/?>/i', '<meta name="twitter:title" content="'.$esc.'" />', $html, 1);
    return $html;
}
function replace_h1($html, $new) {
    return preg_replace_callback('/<h1([^>]*)>.*?<\/h1>/si', function($m) use ($new) {
        $lines = preg_split('/\r?\n/', $new);
        $inner = implode('<br>', array_map(fn($l) => h(trim($l)), $lines));
        return '<h1' . $m[1] . '>' . $inner . '</h1>';
    }, $html, 1);
}
function replace_hero_sub($html, $new) {
    return preg_replace_callback('/<p class="hero__sub([^"]*)"([^>]*)>.*?<\/p>/si', function($m) use ($new) {
        return '<p class="hero__sub'.$m[1].'"'.$m[2].'>' . h($new) . '</p>';
    }, $html, 1);
}
function replace_cta_url($html, $old, $new) {
    if (!$old || !$new) return $html;
    return str_replace('href="'.$old.'"', 'href="'.$new.'"', $html);
}
function replace_cta_label($html, $old, $new) {
    if (!$old || !$new || $old === $new) return $html;
    return preg_replace_callback('/<a([^>]*class="btn btn--primary[^"]*"[^>]*)>(.*?)<\/a>/si', function($m) use ($old, $new) {
        $inner = $m[2];
        if (stripos(strip_tags($inner), $old) !== false) {
            $inner = str_replace($old, $new, $inner);
        }
        return '<a' . $m[1] . '>' . $inner . '</a>';
    }, $html, 1);
}
function toggle_section_visibility($html, $sectionIndex, $hidden) {
    // Rebuild each section with/without data-hidden="true"
    $count = -1;
    return preg_replace_callback('/<section([^>]*)>/si', function($m) use (&$count, $sectionIndex, $hidden) {
        $count++;
        if ($count !== $sectionIndex) return $m[0];
        // Only operate on sections AFTER header (which this body is)
        $attrs = preg_replace('/\s*data-hidden="[^"]*"/i', '', $m[1]);
        if ($hidden) {
            $attrs .= ' data-hidden="true"';
        }
        return '<section' . $attrs . '>';
    }, $html);
}

/* ---------- Handle POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title'] ?? '');
    $new_desc  = trim($_POST['desc'] ?? '');
    $new_h1    = trim($_POST['h1'] ?? '');
    $new_sub   = trim($_POST['hero_sub'] ?? '');
    $new_cta_url = trim($_POST['cta_url'] ?? '');
    $new_cta_label = trim($_POST['cta_label'] ?? '');
    $new_body = $_POST['body_html'] ?? '';
    $enable_body = !empty($_POST['enable_body_override']);

    $old_cta_url = parse_cta_url($html);
    $old_cta_label = parse_cta_label($html);

    if ($new_title !== '')  { $html = replace_title($html, $new_title); $html = replace_og_title($html, $new_title); }
    if ($new_desc !== '')   { $html = replace_desc($html, $new_desc); }
    if ($new_h1 !== '')     { $html = replace_h1($html, $new_h1); }
    if ($new_sub !== '')    { $html = replace_hero_sub($html, $new_sub); }
    if ($new_cta_url !== '' && $old_cta_url) { $html = replace_cta_url($html, $old_cta_url, $new_cta_url); }
    if ($new_cta_label !== '' && $old_cta_label) { $html = replace_cta_label($html, $old_cta_label, $new_cta_label); }

    // Section visibility toggles
    if (!empty($_POST['section_visible'])) {
        // $_POST['section_visible'] is an array of section indexes that should be VISIBLE
        $visible = array_map('intval', $_POST['section_visible']);
        $sections = parse_sections($html);
        foreach ($sections as $s) {
            $should_hide = !in_array($s['index'], $visible, true);
            $html = toggle_section_visibility($html, $s['index'], $should_hide);
        }
    }

    // Rich-text body override — only when toggle is on
    if ($enable_body && $new_body !== '') {
        $html = preg_replace('/(<\/header>)(.*?)(<footer)/si', '$1' . "\n" . $new_body . "\n      " . '$3', $html, 1);
    }

    file_put_contents($filepath, $html);
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Saved. <a href=\"../$file\" target=\"_blank\">View page →</a>"];
    header('Location: page-edit.php?file=' . urlencode($file));
    exit;
}

/* ---------- Extract current values ---------- */
$cur_title = parse_title($html);
$cur_desc  = parse_desc($html);
$cur_h1    = parse_h1($html);
$cur_sub   = parse_hero_sub($html);
$cur_cta_url = parse_cta_url($html);
$cur_cta_label = parse_cta_label($html);
$cur_sections = parse_sections($html);
$cur_body = '';
if (preg_match('/<\/header>(.*?)<footer/si', $html, $m)) {
    $cur_body = trim($m[1]);
}
?>

<h2 style="margin-bottom:0.25rem;">Edit page: <?= h(preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', $cur_title) ?: $file) ?></h2>
<p style="color:#6b7280;margin-top:0;margin-bottom:1.5rem;"><a href="pages.php">← back to pages</a> &nbsp;·&nbsp; <a href="../<?= h($file) ?>" target="_blank">view live →</a> &nbsp;·&nbsp; <code style="background:#f3f4f6;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.75rem;"><?= h($file) ?></code></p>

<form method="post">
  <div class="card">
    <h3 style="margin-top:0;">🔍 SEO</h3>
    <div class="field">
      <label>Page title (shows in browser tab + Google)</label>
      <input type="text" name="title" value="<?= h($cur_title) ?>" required>
      <div class="help">Keep under 60 characters. Current: <?= strlen($cur_title) ?></div>
    </div>
    <div class="field">
      <label>Short description (shows in Google search snippets)</label>
      <textarea name="desc" rows="2" required style="font-family:inherit;font-size:0.9rem;"><?= h($cur_desc) ?></textarea>
      <div class="help">Keep 140-160 characters. Current: <?= strlen($cur_desc) ?></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">✨ Main headline</h3>
    <div class="field">
      <label>Big headline at top of page</label>
      <textarea name="h1" rows="2" required style="font-family:inherit;font-size:1rem;font-weight:600;"><?= h($cur_h1) ?></textarea>
      <div class="help">Use Enter for a line break.</div>
    </div>
    <div class="field">
      <label>Sub-headline (one-line description under the big headline)</label>
      <textarea name="hero_sub" rows="2"><?= h($cur_sub) ?></textarea>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">🎯 "Book a Call" button</h3>
    <div class="row">
      <div class="field">
        <label>Button URL (where it goes when clicked)</label>
        <input type="text" name="cta_url" value="<?= h($cur_cta_url) ?>">
      </div>
      <div class="field">
        <label>Button text</label>
        <input type="text" name="cta_label" value="<?= h($cur_cta_label) ?>">
      </div>
    </div>
  </div>

  <?php if (!empty($cur_sections)): ?>
  <div class="card">
    <h3 style="margin-top:0;">👁 Sections — show / hide</h3>
    <p style="color:#6b7280;font-size:0.875rem;margin-bottom:1rem;">Toggle any section off to hide it from the live site. Toggle it on to show it again. Nothing is deleted.</p>
    <?php foreach ($cur_sections as $s): ?>
      <div class="section-row <?= $s['hidden'] ? 'is-hidden' : '' ?>">
        <label class="switch">
          <input type="checkbox" name="section_visible[]" value="<?= $s['index'] ?>" <?= $s['hidden'] ? '' : 'checked' ?>>
          <span class="slider"></span>
        </label>
        <div class="section-row__info">
          <div class="section-row__title"><?= h($s['title']) ?></div>
          <div class="section-row__meta">Section <?= $s['index'] + 1 ?> of <?= count($cur_sections) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <h3 style="margin-top:0;">✏️ Full-page editor (advanced)</h3>
    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0.75rem;">
      <input type="checkbox" name="enable_body_override" id="enable_body_override">
      <span>Enable visual editor for the entire page body</span>
    </label>
    <div class="help" style="margin-bottom:0.75rem;">Use this to add new sections, rewrite content, insert images, or change layout. Click "Insert → Image" to pick from your media library.</div>
    <textarea id="body_html" name="body_html" rows="20"><?= h($cur_body) ?></textarea>
  </div>

  <div style="display:flex;gap:0.5rem;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);">
    <button type="submit" class="btn">💾 Save changes</button>
    <a href="pages.php" class="btn btn--ghost">Cancel</a>
    <a href="../<?= h($file) ?>" target="_blank" class="btn btn--ghost">Preview live →</a>
  </div>
</form>

<script>
  // Initialize TinyMCE WYSIWYG — only when user opts in
  var tinyInit = false;
  var toggle = document.getElementById('enable_body_override');
  function initTiny() {
    if (tinyInit) return;
    tinymce.init({
      selector: '#body_html',
      height: 500,
      menubar: 'edit insert format table',
      plugins: 'autolink lists link image media table code help wordcount',
      toolbar: 'undo redo | blocks | bold italic underline | forecolor | alignleft aligncenter alignright | bullist numlist | link image media | removeformat | code',
      content_style: 'body { font-family: Inter, -apple-system, sans-serif; font-size: 14px; } img { max-width: 100%; }',
      branding: false,
      promotion: false,
      // Custom image picker — opens our media library modal
      file_picker_types: 'image',
      file_picker_callback: function(callback) {
        window._tinyPickerCb = callback;
        openMediaForTiny();
      }
    });
    tinyInit = true;
  }
  toggle.addEventListener('change', function() {
    if (this.checked) initTiny();
    else if (tinyInit) { tinymce.remove('#body_html'); tinyInit = false; }
  });

  // Make media picker work for TinyMCE too
  function openMediaForTiny() {
    document.getElementById('media-modal').classList.add('is-open');
    fetch('media.php?list=1').then(r => r.json()).then(files => {
      var grid = document.getElementById('mp-grid');
      if (!files.length) {
        grid.innerHTML = '<p style="color:#6b7280;grid-column:1/-1;">No images yet. Upload one above.</p>';
        return;
      }
      grid.innerHTML = files.map(function(f) {
        return '<div class="mp-item" onclick="pickMediaForTiny(\'' + f + '\')">' +
               '<img src="../' + f + '" alt=""><div class="mp-name">' + f.replace('uploads/','') + '</div></div>';
      }).join('');
    });
  }
  function pickMediaForTiny(path) {
    if (window._tinyPickerCb) {
      window._tinyPickerCb('/' + path, { alt: '' });
      window._tinyPickerCb = null;
    }
    closeMedia();
  }
</script>

<?php require __DIR__ . '/_footer.php'; ?>

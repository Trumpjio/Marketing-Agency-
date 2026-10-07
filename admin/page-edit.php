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

// ---------- PARSE helpers ----------
function parse_field($html, $pattern, $group = 1) {
    if (preg_match($pattern, $html, $m)) return trim($m[$group]);
    return '';
}
function parse_title($html) { return parse_field($html, '/<title>([^<]*)<\/title>/i'); }
function parse_desc($html)  { return parse_field($html, '/<meta\s+name="description"\s+content="([^"]*)"/i'); }
function parse_h1($html)    {
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $html, $m)) {
        // Replace <br> with newline for editing, strip other tags
        $v = preg_replace('/<br\s*\/?>/i', "\n", $m[1]);
        $v = preg_replace('/<span[^>]*>(.*?)<\/span>/si', '$1', $v);
        return trim(strip_tags($v));
    }
    return '';
}
function parse_hero_sub($html) {
    return parse_field($html, '/<p class="hero__sub[^"]*"[^>]*>(.*?)<\/p>/si');
}
function parse_cta_url($html) {
    return parse_field($html, '/<a[^>]*class="btn btn--primary[^"]*"[^>]*href="([^"]+)"/i')
        ?: parse_field($html, '/<a[^>]*href="([^"]+)"[^>]*class="btn btn--primary[^"]*"/i');
}
function parse_cta_label($html) {
    if (preg_match('/<a[^>]*class="btn btn--primary[^"]*"[^>]*>(.*?)<\/a>/si', $html, $m)) {
        return trim(strip_tags($m[1]));
    }
    return '';
}
function parse_images($html) {
    preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $m);
    return array_values(array_unique($m[1] ?? []));
}

// ---------- REPLACE helpers ----------
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
    // Preserve class attributes on the <h1>
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
    // Replace the inner text of the first .btn--primary <a>
    return preg_replace_callback('/<a([^>]*class="btn btn--primary[^"]*"[^>]*)>(.*?)<\/a>/si', function($m) use ($old, $new) {
        $inner = $m[2];
        if (stripos(strip_tags($inner), $old) !== false) {
            $inner = str_replace($old, $new, $inner);
        }
        return '<a' . $m[1] . '>' . $inner . '</a>';
    }, $html, 1);
}
function replace_image($html, $old, $new) {
    if (!$old || !$new || $old === $new) return $html;
    return str_replace('src="'.$old.'"', 'src="'.$new.'"', $html);
}

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title'] ?? '');
    $new_desc  = trim($_POST['desc'] ?? '');
    $new_h1    = trim($_POST['h1'] ?? '');
    $new_sub   = trim($_POST['hero_sub'] ?? '');
    $new_cta_url = trim($_POST['cta_url'] ?? '');
    $new_cta_label = trim($_POST['cta_label'] ?? '');

    $old_cta_url = parse_cta_url($html);
    $old_cta_label = parse_cta_label($html);

    if ($new_title !== '')  { $html = replace_title($html, $new_title); $html = replace_og_title($html, $new_title); }
    if ($new_desc !== '')   { $html = replace_desc($html, $new_desc); }
    if ($new_h1 !== '')     { $html = replace_h1($html, $new_h1); }
    if ($new_sub !== '')    { $html = replace_hero_sub($html, $new_sub); }
    if ($new_cta_url !== '' && $old_cta_url) { $html = replace_cta_url($html, $old_cta_url, $new_cta_url); }
    if ($new_cta_label !== '' && $old_cta_label) { $html = replace_cta_label($html, $old_cta_label, $new_cta_label); }

    // Image replacements
    if (!empty($_POST['img_old'])) {
        foreach ($_POST['img_old'] as $i => $old_src) {
            $new_src = trim($_POST['img_new'][$i] ?? '');
            if ($new_src && $new_src !== $old_src) {
                $html = replace_image($html, $old_src, $new_src);
            }
        }
    }

    // Raw body override — only if user explicitly set it
    if (!empty($_POST['raw_body_enable']) && isset($_POST['raw_body'])) {
        $raw = $_POST['raw_body'];
        // Replace between </header> and <footer
        $html = preg_replace('/(<\/header>)(.*?)(<footer)/si', '$1' . "\n" . $raw . "\n      " . '$3', $html, 1);
    }

    file_put_contents($filepath, $html);
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Saved. <a href=\"../$file\" target=\"_blank\">View page →</a>"];
    header('Location: page-edit.php?file=' . urlencode($file));
    exit;
}

// ---------- Extract current values for form ----------
$cur_title = parse_title($html);
$cur_desc  = parse_desc($html);
$cur_h1    = parse_h1($html);
$cur_sub   = parse_hero_sub($html);
$cur_cta_url = parse_cta_url($html);
$cur_cta_label = parse_cta_label($html);
$cur_images = parse_images($html);
$cur_body = '';
if (preg_match('/<\/header>(.*?)<footer/si', $html, $m)) {
    $cur_body = trim($m[1]);
}
?>

<h2 style="margin-bottom:0.25rem;">Edit: <code style="font-size:0.9rem;background:#f3f4f6;padding:0.15rem 0.4rem;border-radius:4px;"><?= h($file) ?></code></h2>
<p style="color:#6b7280;margin-top:0;margin-bottom:1.5rem;"><a href="pages.php">← back to pages</a> &nbsp;·&nbsp; <a href="../<?= h($file) ?>" target="_blank">view live →</a></p>

<form method="post">
  <div class="card">
    <h3 style="margin-top:0;">SEO</h3>
    <div class="field">
      <label>SEO Title (browser tab + Google search result)</label>
      <input type="text" name="title" value="<?= h($cur_title) ?>" required>
      <div class="help">Keep under 60 characters for best Google display. Current: <?= strlen($cur_title) ?> chars.</div>
    </div>
    <div class="field">
      <label>Meta Description (shown in Google snippets)</label>
      <textarea name="desc" rows="2" required style="font-family:inherit;font-size:0.9rem;"><?= h($cur_desc) ?></textarea>
      <div class="help">Keep 140-160 characters. Current: <?= strlen($cur_desc) ?> chars.</div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Hero (top of page)</h3>
    <div class="field">
      <label>Main headline (H1)</label>
      <textarea name="h1" rows="2" required style="font-family:inherit;font-size:1rem;font-weight:600;"><?= h($cur_h1) ?></textarea>
      <div class="help">Use line breaks for multiple lines. One H1 per page.</div>
    </div>
    <div class="field">
      <label>Sub-headline (short description below H1)</label>
      <textarea name="hero_sub" rows="2"><?= h($cur_sub) ?></textarea>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Primary CTA</h3>
    <div class="row">
      <div class="field">
        <label>Button URL</label>
        <input type="text" name="cta_url" value="<?= h($cur_cta_url) ?>">
      </div>
      <div class="field">
        <label>Button text</label>
        <input type="text" name="cta_label" value="<?= h($cur_cta_label) ?>">
      </div>
    </div>
    <div class="help">Updates the main "Book a Call" button on this page.</div>
  </div>

  <?php if (!empty($cur_images)): ?>
  <div class="card">
    <h3 style="margin-top:0;">Images on this page</h3>
    <?php foreach ($cur_images as $i => $src): ?>
      <div class="field">
        <label>Image <?= $i + 1 ?></label>
        <input type="hidden" name="img_old[]" value="<?= h($src) ?>">
        <div style="display:flex;gap:0.5rem;align-items:center;">
          <?php if (!str_starts_with($src, 'data:')): ?>
            <img src="../<?= h(ltrim($src, '/')) ?>" alt="" style="width:60px;height:45px;object-fit:cover;border:1px solid #e5e7eb;border-radius:4px;">
          <?php endif; ?>
          <input type="text" name="img_new[]" value="<?= h($src) ?>" style="flex:1;">
        </div>
        <div class="help">Replace with another image path (e.g. <code>uploads/your-image.jpg</code>). Upload new images in <a href="media.php">Media Library</a>.</div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <h3 style="margin-top:0;">Advanced: full page body HTML</h3>
    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0.75rem;">
      <input type="checkbox" name="raw_body_enable" id="raw_body_enable">
      <span>Enable raw HTML editing (for custom section changes)</span>
    </label>
    <textarea name="raw_body" rows="18" style="font-family:'SF Mono',Menlo,monospace;font-size:0.8rem;" readonly><?= h($cur_body) ?></textarea>
    <div class="help">Only HTML between the header and footer. Classes like <code>.hero</code>, <code>.chips</code>, <code>.glass</code>, <code>.grid grid--3</code>, <code>.faq</code> are available.</div>
  </div>

  <div style="display:flex;gap:0.5rem;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);">
    <button type="submit" class="btn">Save changes</button>
    <a href="pages.php" class="btn btn--ghost">Cancel</a>
    <a href="../<?= h($file) ?>" target="_blank" class="btn btn--ghost">Preview live page</a>
  </div>
</form>

<script>
  // Enable raw body editing when checkbox toggled
  document.getElementById('raw_body_enable').addEventListener('change', function() {
    document.querySelector('textarea[name=raw_body]').readOnly = !this.checked;
  });
</script>

<?php require __DIR__ . '/_footer.php'; ?>

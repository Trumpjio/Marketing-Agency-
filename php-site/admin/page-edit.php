<?php require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_pagelib.php';

$file = basename($_GET['file'] ?? '');
$filepath = page_path($file);
if (!$filepath) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Page not found.'];
    header('Location: pages.php'); exit;
}
$html = file_get_contents($filepath);
$self = 'page-edit.php?file=' . urlencode($file);

/* ---------- Handle POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'undo') {
        $_SESSION['flash'] = page_undo($filepath)
            ? ['type' => 'ok', 'msg' => 'Went back to the previous version of this page.']
            : ['type' => 'err', 'msg' => 'There is no earlier version to go back to.'];
        header('Location: ' . $self); exit;
    }

    if (($_POST['page_hash'] ?? '') !== md5($html)) {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => 'This page was changed somewhere else after you opened it, so nothing was saved. Your screen has been refreshed. Please make your edit again.'];
        header('Location: ' . $self); exit;
    }

    $cur = [
        'title' => parse_title($html), 'desc' => parse_desc($html), 'h1' => parse_h1($html),
        'sub' => parse_hero_sub($html), 'cta_url' => parse_cta_url($html), 'cta_label' => parse_cta_label($html),
    ];
    $new = [
        'title' => trim($_POST['title'] ?? ''), 'desc' => trim($_POST['desc'] ?? ''),
        'h1' => trim(str_replace("\r", '', $_POST['h1'] ?? '')), 'sub' => trim($_POST['hero_sub'] ?? ''),
        'cta_url' => trim($_POST['cta_url'] ?? ''), 'cta_label' => trim($_POST['cta_label'] ?? ''),
    ];

    // 1. Sections first (their inner HTML may contain the headline/CTA), then simple fields.
    if (!empty($_POST['sections_submitted'])) {
        $visible = array_map('intval', $_POST['section_visible'] ?? []);
        $edits = [];
        foreach (($_POST['section_dirty'] ?? []) as $idx => $flag) {
            if ($flag === '1' && isset($_POST['section_html'][$idx])) $edits[(int) $idx] = trim($_POST['section_html'][$idx]);
        }
        $html = update_sections($html, $visible, $edits);
    }
    // Re-read the CTA after section edits, then apply only fields the user actually changed.
    $curAfter = ['cta_url' => parse_cta_url($html), 'cta_label' => parse_cta_label($html)];
    if ($new['title'] !== '' && $new['title'] !== $cur['title']) $html = replace_title($html, $new['title']);
    if ($new['desc'] !== '' && $new['desc'] !== $cur['desc'])    $html = replace_desc($html, $new['desc']);
    if ($new['h1'] !== '' && $new['h1'] !== $cur['h1'])          $html = replace_h1($html, $new['h1']);
    if ($new['sub'] !== '' && $new['sub'] !== $cur['sub'])       $html = replace_hero_sub($html, $new['sub']);
    if ($new['cta_url'] !== '' && $new['cta_url'] !== $cur['cta_url'])       $html = replace_cta_url($html, $curAfter['cta_url'], $new['cta_url']);
    if ($new['cta_label'] !== '' && $new['cta_label'] !== $cur['cta_label']) $html = replace_cta_label($html, $curAfter['cta_label'], $new['cta_label']);

    page_save($filepath, $html);
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Saved. <a href="../' . h($file) . '" target="_blank">View the page →</a>'];
    header('Location: ' . $self); exit;
}

/* ---------- Current values for the form ---------- */
$cur_title = parse_title($html);
$cur_desc  = parse_desc($html);
$cur_h1    = parse_h1($html);
$cur_sub   = parse_hero_sub($html);
$cur_cta_url = parse_cta_url($html);
$cur_cta_label = parse_cta_label($html);
$sections = parse_sections($html);
$has_undo = is_file(page_backup_path($filepath));
$page_name = preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', $cur_title) ?: $file;
?>

<h2 style="margin-bottom:0.25rem;">Edit page: <?= h($page_name) ?></h2>
<p style="color:#6b7280;margin-top:0;margin-bottom:1.5rem;">
  <a href="pages.php">← back to pages</a> &nbsp;·&nbsp;
  <a href="../<?= h($file) ?>" target="_blank">view live →</a> &nbsp;·&nbsp;
  <a href="page-icons.php?file=<?= urlencode($file) ?>"><strong>Edit icons on this page →</strong></a>
</p>

<form method="post" id="page-form">
  <input type="hidden" name="page_hash" value="<?= md5($html) ?>">
  <input type="hidden" name="sections_submitted" value="1">

  <div class="card">
    <h3 style="margin-top:0;">SEO (Google)</h3>
    <div class="field">
      <label>Page title (browser tab and Google)</label>
      <input type="text" name="title" value="<?= h($cur_title) ?>" required>
      <div class="help">Aim for under 60 characters. Now: <?= mb_strlen($cur_title) ?></div>
    </div>
    <div class="field">
      <label>Short description (the grey text under the title in Google)</label>
      <textarea name="desc" rows="2" required style="font-family:inherit;font-size:0.9rem;"><?= h($cur_desc) ?></textarea>
      <div class="help">Aim for 140 to 160 characters. Now: <?= mb_strlen($cur_desc) ?></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Main headline</h3>
    <div class="field">
      <label>Big headline at the top of the page</label>
      <textarea name="h1" rows="2" required style="font-family:inherit;font-size:1rem;font-weight:600;"><?= h($cur_h1) ?></textarea>
      <div class="help">Press Enter for a new line. If you want to restyle this text, use "Edit this section" below instead.</div>
    </div>
    <div class="field">
      <label>Line of text under the headline</label>
      <textarea name="hero_sub" rows="2"><?= h($cur_sub) ?></textarea>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Main button</h3>
    <p style="color:#6b7280;font-size:0.8rem;margin:-0.25rem 0 1rem;">Changes the buttons inside this page. The "Book a Call" button in the top menu is changed in Site Settings.</p>
    <div class="row">
      <div class="field">
        <label>Where the button goes (link)</label>
        <input type="text" name="cta_url" value="<?= h($cur_cta_url) ?>">
      </div>
      <div class="field">
        <label>Words on the button</label>
        <input type="text" name="cta_label" value="<?= h($cur_cta_label) ?>">
      </div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Sections</h3>
    <p style="color:#6b7280;font-size:0.875rem;margin-bottom:1rem;">Use the switch to show or hide a section on the live site (nothing is deleted). Click <strong>Edit this section</strong> to change its text, links and pictures in a visual editor, like Word.</p>
    <?php foreach ($sections as $s): $n = $s['index']; ?>
      <div class="section-row <?= $s['hidden'] ? 'is-hidden' : '' ?>" style="flex-wrap:wrap;">
        <label class="switch" title="Show or hide this section">
          <input type="checkbox" name="section_visible[]" value="<?= $n ?>" <?= $s['hidden'] ? '' : 'checked' ?>>
          <span class="slider"></span>
        </label>
        <div class="section-row__info">
          <div class="section-row__title"><?= h($s['title']) ?></div>
          <div class="section-row__meta">Section <?= $n + 1 ?> of <?= count($sections) ?><?= $s['hidden'] ? ' · currently hidden' : '' ?></div>
        </div>
        <button type="button" class="btn btn--ghost" style="padding:0.4rem 0.8rem;font-size:0.8rem;" data-edit-section="<?= $n ?>">Edit this section</button>
        <div id="wrap_<?= $n ?>" style="display:none;flex-basis:100%;margin-top:0.75rem;">
          <textarea id="sec_<?= $n ?>" name="section_html[<?= $n ?>]"><?= h($s['inner']) ?></textarea>
          <input type="hidden" id="dirty_<?= $n ?>" name="section_dirty[<?= $n ?>]" value="0">
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="display:flex;gap:0.5rem;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);flex-wrap:wrap;">
    <button type="submit" class="btn">Save changes</button>
    <a href="pages.php" class="btn btn--ghost">Cancel</a>
    <a href="../<?= h($file) ?>" target="_blank" class="btn btn--ghost">Preview live →</a>
  </div>
</form>

<?php if ($has_undo): ?>
<form method="post" style="margin-top:0.5rem;" onsubmit="return confirm('Go back to the version of this page from before your last save?');">
  <input type="hidden" name="action" value="undo">
  <button type="submit" class="btn btn--ghost">↩ Undo my last save</button>
</form>
<?php endif; ?>

<script>
  document.querySelectorAll('[data-edit-section]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var n = btn.dataset.editSection;
      var wrap = document.getElementById('wrap_' + n);
      var opening = wrap.style.display === 'none';
      wrap.style.display = opening ? 'block' : 'none';
      btn.textContent = opening ? 'Close editor' : 'Edit this section';
      if (opening && !tinymce.get('sec_' + n)) initSectionEditor(n);
    });
  });

  function initSectionEditor(n) {
    tinymce.init({
      selector: '#sec_' + n,
      license_key: 'gpl',
      height: 420,
      menubar: false,
      plugins: 'autolink lists link image media table code',
      toolbar: 'undo redo | blocks | bold italic underline | forecolor | alignleft aligncenter alignright | bullist numlist | link image | removeformat | code',
      // Keep the page's own markup (cards, buttons, classes) exactly as it is.
      verify_html: false,
      convert_urls: false,
      entity_encoding: 'raw',
      content_css: '../styles/glass.css',
      content_style: 'body{padding:16px;background:#f7f9fc;} .reveal{opacity:1!important;transform:none!important;} section{padding-block:0!important;}',
      branding: false,
      promotion: false,
      file_picker_types: 'image',
      file_picker_callback: function (callback) { window._tinyPickerCb = callback; openMediaForTiny(); },
      setup: function (ed) {
        ed.on('input change undo redo', function () { document.getElementById('dirty_' + n).value = '1'; });
      }
    });
  }

  document.getElementById('page-form').addEventListener('submit', function () { tinymce.triggerSave(); });
</script>

<?php require __DIR__ . '/_footer.php'; ?>

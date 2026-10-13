<?php
require_once __DIR__ . '/config.php';
require_login();
// Hold the page output until the script ends, so pages can still redirect
// (header Location) after the layout has been included, e.g. after saving a form.
ob_start();
$current = basename($_SERVER['PHP_SELF'], '.php');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — OrcaPulse</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- TinyMCE WYSIWYG editor, self-hosted (GPL) so it works without any external service -->
<script src="vendor/tinymce/tinymce.min.js"></script>
<style>
  :root { --blue: #635bff; --cyan: #18bfff; --bg: #f7f9fc; --ink: #0a0e27; --muted: #6b7280; --line: #e5e7eb; --surface: #fff; --danger: #dc2626; --ok: #059669; }
  * { box-sizing: border-box; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg); color: var(--ink); margin: 0; }
  .layout { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
  @media (max-width: 820px) { .layout { grid-template-columns: minmax(0, 1fr); } .sidebar, .main { min-width: 0; } }
  .sidebar { background: var(--surface); border-right: 1px solid var(--line); padding: 1.5rem 1rem; }
  .sidebar h1 { font-size: 1rem; margin: 0 0 1.5rem; font-weight: 800; letter-spacing: 0.02em; }
  .sidebar h1 span { color: var(--blue); }
  .sidebar nav { display: flex; flex-direction: column; gap: 0.25rem; }
  .sidebar a { padding: 0.6rem 0.75rem; border-radius: 6px; color: var(--ink); text-decoration: none; font-size: 0.875rem; font-weight: 500; }
  .sidebar a:hover { background: var(--bg); }
  .sidebar a.active { background: var(--blue); color: white; }
  .sidebar .logout { margin-top: auto; border-top: 1px solid var(--line); padding-top: 1rem; }
  @media (max-width: 820px) {
    .sidebar { position: sticky; top: 0; z-index: 10; border-bottom: 1px solid var(--line); padding: 0.6rem 1rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.35rem 1rem; }
    .sidebar h1 { margin: 0; }
    .sidebar nav { flex-direction: row; order: 3; width: 100%; overflow-x: auto; padding-bottom: 0.25rem; }
    .sidebar nav a { white-space: nowrap; flex-shrink: 0; }
    .sidebar .logout { margin: 0; padding: 0; border: 0; }
  }
  .main { padding: 2rem; max-width: 1100px; }
  @media (max-width: 560px) { .main { padding: 1rem; } }
  h2 { font-size: 1.5rem; font-weight: 700; margin: 0 0 1rem; }
  .card { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 1.5rem; margin-bottom: 1rem; }
  .field { margin-bottom: 1rem; }
  label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--ink); }
  .help { font-size: 0.75rem; color: var(--muted); margin-top: 0.3rem; }
  input[type=text], input[type=url], input[type=email], input[type=password], input[type=date], textarea, select { width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--line); border-radius: 6px; font: inherit; background: #fff; }
  input:focus, textarea:focus, select:focus { outline: 2px solid var(--blue); outline-offset: -1px; }
  textarea { min-height: 140px; font-family: 'SF Mono', Menlo, monospace; font-size: 0.825rem; line-height: 1.5; resize: vertical; }
  .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1rem; background: var(--blue); color: white; border: 0; border-radius: 6px; font: inherit; font-weight: 600; font-size: 0.875rem; cursor: pointer; text-decoration: none; }
  .btn:hover { background: #4e46d9; }
  .btn--ghost { background: transparent; color: var(--ink); border: 1px solid var(--line); }
  .btn--ghost:hover { background: var(--bg); }
  .btn--danger { background: var(--danger); }
  .btn--danger:hover { background: #b91c1c; }
  .notice { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
  .notice--ok { background: #d1fae5; color: #065f46; }
  .notice--err { background: #fee2e2; color: #991b1b; }
  .list { width: 100%; border-collapse: collapse; }
  .list th { text-align: left; padding: 0.6rem; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--line); }
  .list td { padding: 0.75rem 0.6rem; border-bottom: 1px solid var(--line); font-size: 0.875rem; vertical-align: top; }
  .list tr:hover { background: var(--bg); }
  .pill { display: inline-block; padding: 0.15rem 0.5rem; background: var(--bg); border-radius: 999px; font-size: 0.7rem; font-weight: 600; color: var(--muted); }
  .pill--ok { background: #d1fae5; color: #065f46; }
  .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  @media (max-width: 560px) { .row { grid-template-columns: 1fr; } }
  .toolbar { display: flex; gap: 0.5rem; margin-bottom: 1rem; align-items: center; flex-wrap: wrap; }
  .toolbar input { max-width: 320px; }
  .thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }

  /* Toggle switch */
  .switch { position: relative; display: inline-block; width: 42px; height: 24px; flex-shrink: 0; }
  .switch input { opacity: 0; width: 0; height: 0; }
  .switch .slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e0; border-radius: 999px; transition: background 0.2s; }
  .switch .slider::before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: transform 0.2s; }
  .switch input:checked + .slider { background: var(--blue); }
  .switch input:checked + .slider::before { transform: translateX(18px); }

  /* Section-toggle row */
  .section-row { display: flex; align-items: center; gap: 1rem; padding: 0.9rem 1rem; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 0.5rem; background: #fff; }
  .section-row.is-hidden { background: #f9fafb; opacity: 0.65; }
  .section-row__info { flex: 1; min-width: 0; }
  .section-row__title { font-weight: 600; font-size: 0.95rem; color: var(--ink); margin: 0 0 0.1rem; }
  .section-row__meta { color: var(--muted); font-size: 0.75rem; }

  /* Icon editor rows */
  .icon-row { display: grid; grid-template-columns: 56px 1fr; gap: 1rem; align-items: start; padding: 1rem; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 0.6rem; background: #fff; }
  .icon-row__preview { width: 56px; height: 56px; border-radius: 12px; background: linear-gradient(135deg, rgba(99,91,255,.14), rgba(24,191,255,.1)); color: var(--blue); display: grid; place-items: center; font-weight: 800; font-size: 1.1rem; overflow: hidden; }
  .icon-row__preview svg { width: 26px; height: 26px; }
  .icon-row__preview img { width: 100%; height: 100%; object-fit: cover; }
  .icon-row__top { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.6rem; }
  .icon-row__title { font-weight: 600; font-size: 0.925rem; }
  .icon-row__meta { color: var(--muted); font-size: 0.75rem; }
  .icon-row__opts { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem; }
  .icon-row__opts label { font-size: 0.72rem; font-weight: 600; color: var(--muted); margin-bottom: 0.2rem; }
  .icon-row__opts select { padding: 0.45rem 0.6rem; font-size: 0.85rem; }
  @media (max-width: 560px) { .icon-row { grid-template-columns: 1fr; } .icon-row__opts { grid-template-columns: 1fr; } }
  .icon-row.is-new { border-style: dashed; background: #fafbff; }

  /* Icon picker modal */
  #icon-modal { display: none; position: fixed; inset: 0; background: rgba(10,14,39,0.6); z-index: 1100; align-items: center; justify-content: center; padding: 1rem; }
  #icon-modal.is-open { display: flex; }
  #icon-modal .modal-box { background: #fff; border-radius: 12px; width: 100%; max-width: 720px; max-height: 88vh; overflow: hidden; display: flex; flex-direction: column; }
  #icon-modal .modal-head { padding: 1rem 1.5rem; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
  #icon-modal .modal-head h3 { margin: 0; font-size: 1.1rem; }
  #icon-modal .modal-close { background: transparent; border: 0; font-size: 1.5rem; cursor: pointer; color: var(--muted); line-height: 1; padding: 0.25rem 0.5rem; }
  #icon-modal .modal-body { padding: 1.25rem 1.5rem; overflow-y: auto; }
  .ip-tabs { display: flex; gap: 0.4rem; margin-bottom: 1rem; flex-wrap: wrap; }
  .ip-tab { padding: 0.45rem 0.9rem; border: 1px solid var(--line); border-radius: 999px; background: #fff; font: inherit; font-size: 0.825rem; font-weight: 600; cursor: pointer; color: var(--ink); }
  .ip-tab.is-active { background: var(--blue); border-color: var(--blue); color: #fff; }
  .ip-panel { display: none; }
  .ip-panel.is-active { display: block; }
  .ip-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(78px, 1fr)); gap: 0.5rem; }
  .ip-item { border: 1px solid var(--line); border-radius: 10px; background: #fff; padding: 0.7rem 0.3rem 0.45rem; cursor: pointer; text-align: center; color: var(--ink); transition: all .12s; font: inherit; }
  .ip-item:hover { border-color: var(--blue); color: var(--blue); transform: translateY(-1px); }
  .ip-item svg { width: 26px; height: 26px; display: block; margin: 0 auto 0.35rem; }
  .ip-item span { display: block; font-size: 0.65rem; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .ip-quick { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-top: 0.75rem; }
  .ip-quick button { font-size: 1.25rem; padding: 0.3rem 0.6rem; border: 1px solid var(--line); border-radius: 8px; background: #fff; cursor: pointer; }
  .ip-quick button:hover { border-color: var(--blue); }

  /* Image picker modal */
  .image-picker-field { display: flex; gap: 0.5rem; align-items: center; }
  .image-picker-preview { width: 60px; height: 45px; object-fit: cover; border: 1px solid var(--line); border-radius: 4px; background: #f3f4f6; flex-shrink: 0; }
  .image-picker-btn { padding: 0.5rem 0.75rem; font-size: 0.8rem; background: var(--bg); border: 1px solid var(--line); color: var(--ink); border-radius: 6px; cursor: pointer; white-space: nowrap; }
  .image-picker-btn:hover { border-color: var(--blue); color: var(--blue); }

  #media-modal { display: none; position: fixed; inset: 0; background: rgba(10,14,39,0.6); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
  #media-modal.is-open { display: flex; }
  #media-modal .modal-box { background: #fff; border-radius: 12px; width: 100%; max-width: 760px; max-height: 85vh; overflow: hidden; display: flex; flex-direction: column; }
  #media-modal .modal-head { padding: 1rem 1.5rem; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
  #media-modal .modal-head h3 { margin: 0; font-size: 1.1rem; }
  #media-modal .modal-close { background: transparent; border: 0; font-size: 1.5rem; cursor: pointer; color: var(--muted); line-height: 1; padding: 0.25rem 0.5rem; }
  #media-modal .modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
  #media-modal .mp-upload { padding: 1rem; background: var(--bg); border: 2px dashed var(--line); border-radius: 8px; margin-bottom: 1rem; text-align: center; }
  #media-modal .mp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem; }
  #media-modal .mp-item { border: 2px solid transparent; border-radius: 6px; overflow: hidden; cursor: pointer; background: #fff; transition: all 0.1s; }
  #media-modal .mp-item:hover { border-color: var(--blue); transform: translateY(-2px); }
  #media-modal .mp-item img { width: 100%; height: 90px; object-fit: cover; display: block; }
  #media-modal .mp-item .mp-name { padding: 0.3rem 0.4rem; font-size: 0.7rem; color: var(--muted); text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <h1>ORCA<span>PULSE</span> Admin</h1>
    <nav>
      <a href="index.php" class="<?= $current==='index'?'active':'' ?>">Dashboard</a>
      <a href="settings.php" class="<?= $current==='settings'?'active':'' ?>">Site Settings</a>
      <a href="design.php" class="<?= $current==='design'?'active':'' ?>">Design</a>
      <a href="pages.php" class="<?= in_array($current,['pages','page-edit'])?'active':'' ?>">Pages</a>
      <a href="library.php" class="<?= $current==='library'?'active':'' ?>">Content Library</a>
      <a href="templates.php" class="<?= $current==='templates'?'active':'' ?>">Page Templates</a>
      <a href="blogs.php" class="<?= in_array($current,['blogs','blog-edit'])?'active':'' ?>">Blog Posts</a>
      <a href="media.php" class="<?= $current==='media'?'active':'' ?>">Media Library</a>
      <a href="subscribers.php" class="<?= $current==='subscribers'?'active':'' ?>">Subscribers</a>
      <a href="../index.html" target="_blank">View Site →</a>
    </nav>
    <div class="logout">
      <a href="logout.php">Log out</a>
    </div>
  </aside>
  <main class="main">
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="notice notice--<?= h($_SESSION['flash']['type']) ?>"><?= $_SESSION['flash']['msg'] ?></div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

<!-- Shared media picker modal (used from any page) -->
<div id="media-modal" role="dialog" aria-modal="true" aria-label="Media Library">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Pick an image</h3>
      <button type="button" class="modal-close" onclick="closeMedia()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="mp-upload">
        <input type="file" id="mp-file" accept="image/*">
        <div class="help" style="margin-top:0.5rem;" id="mp-upload-msg">Upload a new picture here, or click one below to use it.</div>
      </div>
      <div class="mp-grid" id="mp-grid">Loading…</div>
    </div>
  </div>
</div>
<script>
  // One picker for everything: openMediaWith(callback) calls callback('uploads/file.png').
  var mpPick = null;
  function openMediaWith(cb) {
    mpPick = cb;
    document.getElementById('media-modal').classList.add('is-open');
    loadMediaGrid();
  }
  function closeMedia() {
    document.getElementById('media-modal').classList.remove('is-open');
    mpPick = null;
  }
  function loadMediaGrid() {
    var grid = document.getElementById('mp-grid');
    return fetch('media.php?list=1').then(function (r) { return r.json(); }).then(function (files) {
      grid.innerHTML = files.length ? files.map(function (f) {
        return '<div class="mp-item" data-path="' + f + '"><img src="../' + f + '" alt=""><div class="mp-name">' + f.replace('uploads/', '') + '</div></div>';
      }).join('') : '<p style="color:#6b7280;grid-column:1/-1;">No images yet. Upload one above.</p>';
    }).catch(function () { grid.innerHTML = '<p style="color:#dc2626;">Could not load your images.</p>'; });
  }
  // Fill a normal text field (and its little preview) with the chosen image path.
  function openMedia(inputId) {
    openMediaWith(function (path) {
      var el = document.getElementById(inputId);
      if (!el) return;
      el.value = path;
      el.dispatchEvent(new Event('change'));
      var preview = document.querySelector('[data-preview-for="' + inputId + '"]');
      if (preview) { preview.src = '../' + path; preview.style.visibility = 'visible'; }
    });
  }
  // Used by the rich-text editors' image button.
  function openMediaForTiny() {
    openMediaWith(function (path) {
      if (window._tinyPickerCb) { window._tinyPickerCb('/' + path, { alt: '' }); window._tinyPickerCb = null; }
    });
  }
  document.getElementById('mp-grid').addEventListener('click', function (e) {
    var it = e.target.closest('.mp-item');
    if (!it) return;
    var cb = mpPick;
    closeMedia();
    if (cb) cb(it.dataset.path);
  });
  // Upload without leaving the page, so unsaved edits are never lost.
  document.getElementById('mp-file').addEventListener('change', function () {
    var msg = document.getElementById('mp-upload-msg');
    if (!this.files.length) return;
    var fd = new FormData();
    fd.append('file', this.files[0]);
    msg.textContent = 'Uploading…';
    var input = this;
    fetch('media.php?ajax=1', { method: 'POST', body: fd }).then(function (r) { return r.json(); }).then(function (res) {
      input.value = '';
      if (res.error) { msg.textContent = res.error; return; }
      msg.textContent = 'Uploaded. Click it below to use it.';
      loadMediaGrid();
    }).catch(function () { msg.textContent = 'Upload failed. Please try again.'; });
  });
</script>

<?php require_once __DIR__ . '/_icons.php'; ?>
<!-- Icon picker modal (built-in icons, text/emoji, or an uploaded image) -->
<div id="icon-modal" role="dialog" aria-modal="true" aria-label="Choose an icon">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Choose an icon</h3>
      <button type="button" class="modal-close" onclick="closeIconPicker()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="ip-tabs">
        <button type="button" class="ip-tab is-active" data-tab="lib">Icons</button>
        <button type="button" class="ip-tab" data-tab="text">Number, letter or emoji</button>
        <button type="button" class="ip-tab" data-tab="img">My own image</button>
        <button type="button" class="ip-tab" data-tab="none">No icon</button>
      </div>
      <div class="ip-panel is-active" data-panel="lib"><div class="ip-grid" id="ip-grid"></div></div>
      <div class="ip-panel" data-panel="text">
        <label for="ip-text">Type up to 8 characters</label>
        <div style="display:flex;gap:0.5rem;">
          <input type="text" id="ip-text" maxlength="8" placeholder="e.g. 01, A, ★ or 🚀">
          <button type="button" class="btn" id="ip-text-use">Use this</button>
        </div>
        <div class="ip-quick" id="ip-quick"></div>
      </div>
      <div class="ip-panel" data-panel="img">
        <p style="margin-top:0;color:#6b7280;font-size:0.875rem;">Pick a picture from your Media Library, or upload a new one there. Square images look best.</p>
        <button type="button" class="btn" id="ip-img-open">Open Media Library</button>
        <input type="hidden" id="icon-img-proxy">
      </div>
      <div class="ip-panel" data-panel="none">
        <p style="margin-top:0;color:#6b7280;font-size:0.875rem;">The icon box is hidden on the page. You can add an icon back here at any time.</p>
        <button type="button" class="btn btn--ghost" id="ip-none-use">Remove icon</button>
      </div>
    </div>
  </div>
</div>
<script>
  var ORCA_ICONS = <?= json_encode($ICON_LIBRARY) ?>;
  var ipRow = null;

  function ipSvg(name) {
    return '<svg class="oi" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + ORCA_ICONS[name] + '</svg>';
  }
  function ipTab(name) {
    document.querySelectorAll('#icon-modal .ip-tab').forEach(function (t) { t.classList.toggle('is-active', t.dataset.tab === name); });
    document.querySelectorAll('#icon-modal .ip-panel').forEach(function (p) { p.classList.toggle('is-active', p.dataset.panel === name); });
  }
  function openIconPicker(rowId) {
    ipRow = document.getElementById(rowId);
    var grid = document.getElementById('ip-grid');
    if (!grid.children.length) {
      grid.innerHTML = Object.keys(ORCA_ICONS).map(function (n) {
        return '<button type="button" class="ip-item" data-name="' + n + '">' + ipSvg(n) + '<span>' + n + '</span></button>';
      }).join('');
      document.getElementById('ip-quick').innerHTML = ['01','02','03','04','05','★','✓','→','◉','●','◆','✦','🚀','📈','🎯','💡','🏠','🏨','🌍','💼','✅','⚡']
        .map(function (c) { return '<button type="button" data-char="' + c + '">' + c + '</button>'; }).join('');
    }
    ipTab('lib');
    document.getElementById('icon-modal').classList.add('is-open');
  }
  function closeIconPicker() { document.getElementById('icon-modal').classList.remove('is-open'); }
  function ipApply(value, previewNode) {
    if (!ipRow) return;
    ipRow.querySelector('.icon-value').value = value;
    var box = ipRow.querySelector('.icon-row__preview');
    box.innerHTML = '';
    if (typeof previewNode === 'string') box.innerHTML = previewNode; else if (previewNode) box.appendChild(previewNode);
    ipRow.classList.add('is-changed');
    closeIconPicker();
  }
  document.addEventListener('click', function (e) {
    var t = e.target.closest('#icon-modal .ip-tab');
    if (t) { ipTab(t.dataset.tab); return; }
    var it = e.target.closest('#icon-modal .ip-item');
    if (it) { ipApply('lib:' + it.dataset.name, ipSvg(it.dataset.name)); return; }
    var q = e.target.closest('#ip-quick button');
    if (q) { document.getElementById('ip-text').value = q.dataset.char; document.getElementById('ip-text-use').click(); return; }
    if (e.target.id === 'icon-modal') closeIconPicker();
  });
  document.getElementById('ip-text-use').addEventListener('click', function () {
    var v = document.getElementById('ip-text').value.trim();
    if (!v) return;
    ipApply('text:' + v, document.createTextNode(v));
  });
  document.getElementById('ip-none-use').addEventListener('click', function () { ipApply('none', ''); });
  document.getElementById('ip-img-open').addEventListener('click', function () {
    var row = ipRow;
    closeIconPicker();
    ipRow = row;
    openMedia('icon-img-proxy');
  });
  document.getElementById('icon-img-proxy').addEventListener('change', function () {
    var p = this.value;
    if (!p) return;
    var img = document.createElement('img');
    img.src = '../' + p;
    ipApply('img:' + p, img);
    this.value = '';
  });
</script>

<?php
require_once __DIR__ . '/config.php';
require_login();
$current = basename($_SERVER['PHP_SELF'], '.php');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — OrcaPulse</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- TinyMCE WYSIWYG editor (free, no key needed in no-API mode) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<style>
  :root { --blue: #635bff; --cyan: #18bfff; --bg: #f7f9fc; --ink: #0a0e27; --muted: #6b7280; --line: #e5e7eb; --surface: #fff; --danger: #dc2626; --ok: #059669; }
  * { box-sizing: border-box; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg); color: var(--ink); margin: 0; }
  .layout { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
  @media (max-width: 820px) { .layout { grid-template-columns: 1fr; } .sidebar { position: sticky; top: 0; z-index: 10; border-bottom: 1px solid var(--line); } }
  .sidebar { background: var(--surface); border-right: 1px solid var(--line); padding: 1.5rem 1rem; }
  .sidebar h1 { font-size: 1rem; margin: 0 0 1.5rem; font-weight: 800; letter-spacing: 0.02em; }
  .sidebar h1 span { color: var(--blue); }
  .sidebar nav { display: flex; flex-direction: column; gap: 0.25rem; }
  .sidebar a { padding: 0.6rem 0.75rem; border-radius: 6px; color: var(--ink); text-decoration: none; font-size: 0.875rem; font-weight: 500; }
  .sidebar a:hover { background: var(--bg); }
  .sidebar a.active { background: var(--blue); color: white; }
  .sidebar .logout { margin-top: auto; border-top: 1px solid var(--line); padding-top: 1rem; }
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
      <a href="pages.php" class="<?= in_array($current,['pages','page-edit'])?'active':'' ?>">Pages</a>
      <a href="blogs.php" class="<?= in_array($current,['blogs','blog-edit'])?'active':'' ?>">Blog Posts</a>
      <a href="media.php" class="<?= $current==='media'?'active':'' ?>">Media Library</a>
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
      <form class="mp-upload" method="post" action="media.php?return=modal" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*" onchange="this.form.submit()">
        <div class="help" style="margin-top:0.5rem;">or click an image below to use it</div>
      </form>
      <div class="mp-grid" id="mp-grid">Loading…</div>
    </div>
  </div>
</div>
<script>
  var mpTarget = null;
  function openMedia(inputId) {
    mpTarget = inputId;
    document.getElementById('media-modal').classList.add('is-open');
    fetch('media.php?list=1').then(r => r.json()).then(files => {
      var grid = document.getElementById('mp-grid');
      if (!files.length) {
        grid.innerHTML = '<p style="color:#6b7280;grid-column:1/-1;">No images yet. Upload one above.</p>';
        return;
      }
      grid.innerHTML = files.map(function(f) {
        return '<div class="mp-item" onclick="pickMedia(\'' + f + '\')">' +
               '<img src="../' + f + '" alt=""><div class="mp-name">' + f.replace('uploads/','') + '</div></div>';
      }).join('');
    }).catch(function() {
      document.getElementById('mp-grid').innerHTML = '<p style="color:#dc2626;">Failed to load images.</p>';
    });
  }
  function closeMedia() {
    document.getElementById('media-modal').classList.remove('is-open');
    mpTarget = null;
  }
  function pickMedia(path) {
    if (mpTarget) {
      var el = document.getElementById(mpTarget);
      if (el) {
        el.value = path;
        el.dispatchEvent(new Event('change'));
        var preview = document.querySelector('[data-preview-for="' + mpTarget + '"]');
        if (preview) preview.src = '../' + path;
      }
    }
    closeMedia();
  }
</script>

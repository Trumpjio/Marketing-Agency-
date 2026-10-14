<?php
require_once __DIR__ . '/config.php';
require_login();
require_once __DIR__ . '/_library.php';

$c = $_GET['c'] ?? 'testimonials';
if (!lib_valid($c)) $c = 'testimonials';
$def = LIB_COLLECTIONS[$c];
$self = 'library.php?c=' . $c;
$edit_id = $_GET['id'] ?? '';
$is_new = isset($_GET['new']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $items = lib_all($c);
    $msg = null;

    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        [$idx] = $id !== '' ? lib_find($c, $id) : [null];
        [$entry, $err] = lib_clean_entry($c, $_POST, $idx !== null ? $id : null);
        if ($err) {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => h($err)];
            header('Location: ' . $self . ($idx !== null ? '&id=' . urlencode($id) : '&new=1')); exit;
        }
        if ($idx !== null) $items[$idx] = $entry; else $items[] = $entry;
        $msg = ucfirst($def['one']) . ' saved.';
    } elseif ($action === 'delete') {
        $items = array_values(array_filter($items, fn($e) => ($e['id'] ?? '') !== ($_POST['id'] ?? '')));
        $msg = ucfirst($def['one']) . ' deleted.';
    } elseif ($action === 'toggle') {
        foreach ($items as &$e) if (($e['id'] ?? '') === ($_POST['id'] ?? '')) $e['visible'] = empty($e['visible']) ? 1 : 0;
        unset($e);
        $msg = 'Updated.';
    } elseif ($action === 'move') {
        [$i] = lib_find($c, $_POST['id'] ?? '');
        $j = $i === null ? null : $i + (($_POST['dir'] ?? '') === 'up' ? -1 : 1);
        if ($i !== null && isset($items[$j])) { $t = $items[$i]; $items[$i] = $items[$j]; $items[$j] = $t; }
        $msg = 'Order updated.';
    }
    if ($msg !== null) {
        lib_save_all($c, $items);
        $n = lib_regenerate_all();
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => $msg . ($n ? ' ' . $n . ' page' . ($n === 1 ? '' : 's') . ' refreshed on the site.' : '')];
    }
    header('Location: ' . $self); exit;
}

require_once __DIR__ . '/_layout.php';
$items = lib_all($c);
$entry = null;
if ($edit_id !== '') { [, $entry] = lib_find($c, $edit_id); }
$used = lib_pages_using($c);
?>
<style>
  .tabs { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .tabs a { padding: .5rem .9rem; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--ink); text-decoration: none; font-size: .85rem; font-weight: 600; }
  .tabs a.is-on { background: var(--blue); border-color: var(--blue); color: #fff; }
  .lib-row { display: flex; align-items: center; gap: .9rem; padding: .8rem 1rem; border: 1px solid var(--line); border-radius: 10px; background: #fff; margin-bottom: .5rem; flex-wrap: wrap; }
  .lib-row.is-off { opacity: .6; }
  .lib-row img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; background: #f3f4f6; }
  .lib-row__info { flex: 1; min-width: 180px; }
  .lib-row__title { font-weight: 600; font-size: .95rem; }
  .lib-row__meta { color: var(--muted); font-size: .78rem; }
  .lib-row__actions { display: flex; gap: .3rem; align-items: center; flex-wrap: wrap; }
  .lib-row__actions form { margin: 0; }
  .mini { padding: .32rem .6rem; font: inherit; font-size: .78rem; font-weight: 600; background: #fff; border: 1px solid var(--line); border-radius: 6px; cursor: pointer; color: var(--ink); text-decoration: none; display: inline-block; }
  .mini:hover { border-color: var(--blue); color: var(--blue); }
  .mini--danger:hover { border-color: var(--danger); color: var(--danger); }
  .img-pick { display: flex; gap: .5rem; align-items: center; }
  .img-pick img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); background: #f3f4f6; }
  .checks { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .checks label { display: flex; align-items: center; gap: .5rem; font-weight: 500; margin: 0; }
  .checks input { width: auto; }
</style>

<h2>Content library</h2>
<p style="color:#6b7280;margin-top:0;">Write testimonials, case studies, team members and awards once. Every matching section on the site updates by itself. Sections with nothing to show stay hidden, so no empty boxes ever appear.</p>

<div class="tabs">
  <?php foreach (LIB_COLLECTIONS as $k => $d): ?>
    <a href="library.php?c=<?= h($k) ?>" class="<?= $k === $c ? 'is-on' : '' ?>"><?= h($d['label']) ?> (<?= count(lib_all($k)) ?>)</a>
  <?php endforeach; ?>
</div>

<?php if ($is_new || $entry): ?>
  <?php $v = $entry ?: ['visible' => 1, 'everywhere' => 1, 'tags' => '']; ?>
  <div class="card">
    <h3 style="margin-top:0;"><?= $entry ? 'Edit' : 'Add' ?> <?= h($def['one']) ?></h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= h($entry['id'] ?? '') ?>">
      <?php foreach ($def['fields'] as $k => [$label, $type, $help, $req]): $val = $v[$k] ?? ''; ?>
        <div class="field">
          <label for="f_<?= h($k) ?>"><?= h($label) ?><?= $req ? ' *' : '' ?></label>
          <?php if ($type === 'textarea'): ?>
            <textarea id="f_<?= h($k) ?>" name="<?= h($k) ?>" rows="4" style="font-family:inherit;font-size:.9rem;min-height:100px;" <?= $req ? 'required' : '' ?>><?= h($val) ?></textarea>
          <?php elseif ($type === 'image'): ?>
            <div class="img-pick">
              <img data-preview-for="f_<?= h($k) ?>" src="<?= $val ? '../' . h(ltrim($val, '/')) : '' ?>" alt="" onerror="this.style.visibility='hidden'">
              <input type="text" id="f_<?= h($k) ?>" name="<?= h($k) ?>" value="<?= h($val) ?>" placeholder="No picture">
              <button type="button" class="btn btn--ghost" onclick="openMedia('f_<?= h($k) ?>')">Pick / upload</button>
              <button type="button" class="btn btn--ghost" onclick="document.getElementById('f_<?= h($k) ?>').value='';">Remove</button>
            </div>
          <?php else: ?>
            <input type="<?= $type === 'url' ? 'text' : 'text' ?>" id="f_<?= h($k) ?>" name="<?= h($k) ?>" value="<?= h($val) ?>" <?= $req ? 'required' : '' ?>>
          <?php endif; ?>
          <?php if ($help): ?><div class="help"><?= h($help) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>

      <div class="checks">
        <label><input type="checkbox" name="visible" value="1" <?= !empty($v['visible']) ? 'checked' : '' ?>> Show on the site</label>
        <label><input type="checkbox" name="everywhere" value="1" id="everywhere" <?= !empty($v['everywhere']) ? 'checked' : '' ?>> Show on every page that has a matching section</label>
      </div>
      <div class="field" id="tags-field">
        <label for="f_tags">Or show it only on these pages</label>
        <input type="text" id="f_tags" name="tags" value="<?= h($v['tags'] ?? '') ?>" placeholder="city-dubai, country-uae, service-seo, city">
        <div class="help">Separate with commas. Use a page name (the file name without .html, like <code>city-dubai</code>) or a page type: <code>home</code>, <code>about</code>, <code>service</code>, <code>city</code>, <code>country</code>, <code>industry</code>. Only used when the box above is unticked.</div>
      </div>
      <div style="display:flex;gap:.5rem;">
        <button class="btn" type="submit">Save</button>
        <a class="btn btn--ghost" href="<?= h($self) ?>">Cancel</a>
      </div>
    </form>
  </div>
<?php else: ?>
  <p style="color:#6b7280;margin:0 0 1rem;"><?= h($def['intro']) ?>
    <?= $used ? 'Used in sections on <strong>' . $used . ' page' . ($used === 1 ? '' : 's') . '</strong>.' : 'No page has a section for this yet. Add one from <a href="pages.php">Edit page → Add a section</a>.' ?></p>
  <p><a class="btn" href="<?= h($self) ?>&amp;new=1">+ Add <?= h($def['one']) ?></a></p>

  <?php if (!$items): ?>
    <div class="card" style="text-align:center;color:#6b7280;">Nothing here yet. Sections that use this list stay hidden until you add the first entry.</div>
  <?php endif; ?>
  <?php foreach ($items as $i => $e):
      $title = $e[$def['title']] ?? '';
      $sub = mb_substr((string) ($e[$def['sub']] ?? ''), 0, 110);
      $where = !empty($e['everywhere']) ? 'every page' : ($e['tags'] !== '' ? 'only: ' . $e['tags'] : 'no pages (add tags)');
  ?>
    <div class="lib-row <?= empty($e['visible']) ? 'is-off' : '' ?>">
      <?php if (!empty($e['image'])): ?><img src="../<?= h(ltrim($e['image'], '/')) ?>" alt=""><?php endif; ?>
      <div class="lib-row__info">
        <div class="lib-row__title"><?= h($title) ?></div>
        <div class="lib-row__meta"><?= h($sub) ?> · shown on <?= h($where) ?><?= empty($e['visible']) ? ' · hidden' : '' ?></div>
      </div>
      <div class="lib-row__actions">
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= h($e['id']) ?>"><input type="hidden" name="dir" value="up"><button class="mini" <?= $i === 0 ? 'disabled' : '' ?> title="Move up">↑</button></form>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= h($e['id']) ?>"><input type="hidden" name="dir" value="down"><button class="mini" <?= $i === count($items) - 1 ? 'disabled' : '' ?> title="Move down">↓</button></form>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= h($e['id']) ?>"><button class="mini"><?= empty($e['visible']) ? 'Show' : 'Hide' ?></button></form>
        <a class="mini" href="<?= h($self) ?>&amp;id=<?= urlencode($e['id']) ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Delete this <?= h($def['one']) ?>? It will disappear from every page.');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($e['id']) ?>"><button class="mini mini--danger">Delete</button></form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
  (function () {
    var cb = document.getElementById('everywhere'), tf = document.getElementById('tags-field');
    if (cb && tf) { var sync = function () { tf.style.opacity = cb.checked ? '.5' : '1'; }; cb.addEventListener('change', sync); sync(); }
    document.querySelectorAll('[data-preview-for]').forEach(function (img) {
      var inp = document.getElementById(img.dataset.previewFor);
      if (inp) inp.addEventListener('change', function () { img.src = inp.value ? '../' + inp.value.replace(/^\//, '') : ''; img.style.visibility = inp.value ? 'visible' : 'hidden'; });
    });
  })();
</script>

<?php require __DIR__ . '/_footer.php'; ?>

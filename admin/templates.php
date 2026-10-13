<?php
require_once __DIR__ . '/config.php';
require_login();
require_once __DIR__ . '/_templates.php';

$t = $_GET['t'] ?? 'city';
if (!isset(TPL_TYPES[$t])) $t = 'city';
$self = 'templates.php?t=' . $t;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $all = tpl_load();
    if ($action === 'save') {
        $clean = tpl_clean($_POST);
        if ($clean) $all[$t] = $clean; else unset($all[$t]);
        $_SESSION['flash'] = tpl_save($all)
            ? ['type' => 'ok', 'msg' => 'Saved. The design now applies to all ' . count(tpl_pages($t)) . ' ' . h(strtolower(TPL_TYPES[$t][0])) . '. Sections with their own page-level design keep it.']
            : ['type' => 'err', 'msg' => 'Could not save. Check that the site folder is writable.'];
    } elseif ($action === 'reset') {
        unset($all[$t]);
        tpl_save($all);
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Back to the default design for ' . h(strtolower(TPL_TYPES[$t][0])) . '.'];
    } elseif ($action === 'restore') {
        $_SESSION['flash'] = tpl_restore_previous()
            ? ['type' => 'ok', 'msg' => 'Restored the template designs from before your last save.']
            : ['type' => 'err', 'msg' => 'There is no earlier version to restore.'];
    } elseif ($action === 'clear_overrides') {
        $n = tpl_clear_overrides($t);
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Removed page-level colours, spacing and alignment on ' . $n . ' page' . ($n === 1 ? '' : 's') . '. They now follow the template.'];
    }
    header('Location: ' . $self); exit;
}

require_once __DIR__ . '/_layout.php';
$all = tpl_load();
$s = $all[$t] ?? [];
$counts = tpl_counts();
$pages = tpl_pages($t);
$keys = tpl_keys($t);
$over = tpl_override_pages($t);
$label = TPL_TYPES[$t][0];

function tpl_color($name, $val, $id) { ?>
  <div class="dpair">
    <input type="color" value="<?= h($val ?: '#ffffff') ?>" data-color-for="<?= h($id) ?>">
    <input type="text" id="<?= h($id) ?>" name="<?= h($name) ?>" value="<?= h($val) ?>" placeholder="default" maxlength="7">
    <button type="button" class="mini" data-clear="<?= h($id) ?>">Reset</button>
  </div>
<?php }
function tpl_select($name, $val, array $opts, $first = 'Default') { ?>
  <select name="<?= h($name) ?>">
    <option value=""><?= h($first) ?></option>
    <?php foreach ($opts as $k => $l): ?><option value="<?= h($k) ?>" <?= $val === $k ? 'selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?>
  </select>
<?php }
?>
<style>
  .scope { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
  @media (max-width: 760px) { .scope { grid-template-columns: 1fr; } }
  .scope > div { border-radius: 10px; padding: 1rem 1.1rem; font-size: .85rem; line-height: 1.5; }
  .scope__many { background: #eef2ff; border: 1px solid #c7d2fe; color: #312e81; }
  .scope__one { background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d; }
  .scope h4 { margin: 0 0 .3rem; font-size: .9rem; }
  .tpl-tabs { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1.25rem; }
  .tpl-tabs a { padding: .45rem .85rem; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--ink); text-decoration: none; font-size: .82rem; font-weight: 600; }
  .tpl-tabs a.is-on { background: var(--blue); border-color: var(--blue); color: #fff; }
  .tpl-tabs small { opacity: .7; font-weight: 500; margin-left: .25rem; }
  .grid-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; }
  .dpair { display: flex; gap: .4rem; align-items: center; }
  .dpair input[type=color] { width: 38px; height: 36px; padding: 0; border: 1px solid var(--line); border-radius: 6px; background: #fff; cursor: pointer; flex-shrink: 0; }
  .mini { padding: .32rem .6rem; font: inherit; font-size: .78rem; font-weight: 600; background: #fff; border: 1px solid var(--line); border-radius: 6px; cursor: pointer; color: var(--ink); }
  .mini:hover { border-color: var(--blue); color: var(--blue); }
  .sec-table { width: 100%; border-collapse: collapse; }
  .sec-table th { text-align: left; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); padding: .4rem .5rem; border-bottom: 1px solid var(--line); }
  .sec-table td { padding: .55rem .5rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
  .sec-table select, .sec-table input[type=text] { padding: .45rem .55rem; font-size: .82rem; }
  @media (max-width: 900px) { .sec-table, .sec-table tbody, .sec-table tr, .sec-table td { display: block; width: 100%; } .sec-table thead { display: none; } .sec-table td { border: 0; padding: .3rem 0; } .sec-table tr { border-bottom: 1px solid var(--line); padding: .6rem 0; } }
  details.pagelist summary { cursor: pointer; font-weight: 600; font-size: .85rem; }
  details.pagelist ul { columns: 2; margin: .6rem 0 0; padding-left: 1.1rem; font-size: .82rem; color: var(--muted); }
  @media (max-width: 560px) { details.pagelist ul { columns: 1; } }
</style>

<h2>Page templates</h2>
<p style="color:#6b7280;margin-top:0;">Pick a type of page and change how every page of that type looks, in one place.</p>

<div class="scope">
  <div class="scope__many"><h4>This screen: many pages at once</h4>Colours, spacing, corners and heading style for <strong>all pages of one type</strong>, for example every city page. Changes go live on all of them straight away.</div>
  <div class="scope__one"><h4>Pages → Edit: one page only</h4>The words, pictures, sections and buttons of a <strong>single page</strong>. Editing the Dubai page never touches Doha. A section's own design there beats the template.</div>
</div>

<div class="tpl-tabs">
  <?php foreach (TPL_TYPES as $k => $d): ?>
    <a href="templates.php?t=<?= h($k) ?>" class="<?= $k === $t ? 'is-on' : '' ?>"><?= h($d[0]) ?><small><?= (int) ($counts[$k] ?? 0) ?></small></a>
  <?php endforeach; ?>
</div>

<form method="post" id="tpl-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save">

  <div class="card">
    <h3 style="margin-top:0;"><?= h($label) ?> <span class="pill"><?= count($pages) ?> page<?= count($pages) === 1 ? '' : 's' ?></span></h3>
    <?php if (TPL_TYPES[$t][1]): ?><p style="color:#6b7280;margin-top:-.4rem;"><?= h(TPL_TYPES[$t][1]) ?></p><?php endif; ?>
    <details class="pagelist">
      <summary>Show the <?= count($pages) ?> page<?= count($pages) === 1 ? '' : 's' ?> this changes</summary>
      <ul><?php foreach ($pages as $file => $title): ?><li><a href="page-edit.php?file=<?= urlencode($file) ?>"><?= h($title) ?></a></li><?php endforeach; ?></ul>
    </details>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Look and feel</h3>
    <div class="grid-fields">
      <div class="field"><label>Page background colour</label><?php tpl_color('bg', $s['bg'] ?? '', 'tpl_bg'); ?></div>
      <div class="field"><label>Accent colour (links, small labels)</label><?php tpl_color('accent', $s['accent'] ?? '', 'tpl_accent'); ?></div>
      <div class="field"><label>Space between sections</label><?php tpl_select('pad', $s['pad'] ?? '', TPL_PAD, 'Site default'); ?></div>
      <div class="field"><label>Corners of cards and boxes</label><?php tpl_select('radius', $s['radius'] ?? '', TPL_RADIUS, 'Site default'); ?></div>
      <div class="field"><label>Section headings</label><?php tpl_select('head_align', $s['head_align'] ?? '', ['left' => 'Always left', 'center' => 'Always centred'], 'Site default'); ?></div>
      <div class="field"><label>Heading size</label><?php tpl_select('h_size', $s['h_size'] ?? '', TPL_HEAD, 'Site default'); ?></div>
    </div>
    <div class="help">Fonts and the site's main colours are set once for the whole site under <a href="design.php">Design</a>. Anything left on “default” follows that.</div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Section designs</h3>
    <?php if (!$keys): ?>
      <p style="color:#6b7280;margin:0;">The pages of this type do not have named sections yet. Once they do, each section (hero, services, FAQ ...) can get its own background, text colour, spacing and alignment here.</p>
    <?php else: ?>
      <p style="color:#6b7280;margin-top:0;">Set how a section looks on <strong>every</strong> <?= h(strtolower($label)) ?>. Leave a box on default to keep the site look.</p>
      <table class="sec-table">
        <thead><tr><th>Section</th><th>Background</th><th>Text colour</th><th>Spacing</th><th>Alignment</th></tr></thead>
        <tbody>
        <?php foreach ($keys as $k): $r = $s['sections'][$k] ?? []; $id = preg_replace('/\W/', '', $k); ?>
          <tr>
            <td><strong><?= h(OP_KEY_LABELS[$k] ?? ucfirst(str_replace('-', ' ', $k))) ?></strong></td>
            <td><?php tpl_color("sections[$k][bg]", $r['bg'] ?? '', "sb_$id"); ?></td>
            <td><?php tpl_color("sections[$k][ink]", $r['ink'] ?? '', "si_$id"); ?></td>
            <td><?php tpl_select("sections[$k][pad]", $r['pad'] ?? '', TPL_PAD, 'Default'); ?></td>
            <td><?php tpl_select("sections[$k][align]", $r['align'] ?? '', ['left' => 'Left', 'center' => 'Centred'], 'Default'); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div style="display:flex;gap:.5rem;flex-wrap:wrap;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);">
    <button class="btn" type="submit" onclick="return confirm('Apply this design to all <?= count($pages) ?> <?= h(strtolower($label)) ?> now?');">Save for all <?= count($pages) ?> page<?= count($pages) === 1 ? '' : 's' ?></button>
    <a class="btn btn--ghost" href="<?= h($self) ?>">Cancel</a>
  </div>
</form>

<div class="card" style="margin-top:1rem;">
  <h3 style="margin-top:0;">Other options</h3>
  <?php if ($over): ?>
    <p style="margin-top:0;"><strong><?= count($over) ?> page<?= count($over) === 1 ? '' : 's' ?></strong> of this type have their own colours, spacing or alignment on some sections, so those sections ignore the template:
      <span style="color:#6b7280;"><?= h(implode(', ', array_slice(array_values($over), 0, 6))) ?><?= count($over) > 6 ? ' and ' . (count($over) - 6) . ' more' : '' ?></span></p>
    <form method="post" style="display:inline;" onsubmit="return confirm('Remove the page-level colours, spacing and alignment from all <?= count($over) ?> pages so they follow the template? Words and pictures are not touched.');"><?= csrf_field() ?><input type="hidden" name="action" value="clear_overrides"><button class="btn btn--ghost" type="submit">Make those pages follow the template</button></form>
  <?php else: ?>
    <p style="margin-top:0;color:#6b7280;">No page of this type has its own section design. Every page follows the template.</p>
  <?php endif; ?>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem;">
    <form method="post" onsubmit="return confirm('Remove the template design for <?= h(strtolower($label)) ?> and go back to the site default?');"><?= csrf_field() ?><input type="hidden" name="action" value="reset"><button class="btn btn--ghost" type="submit">Reset <?= h(strtolower($label)) ?> to the site default</button></form>
    <?php if (is_file(DATA_DIR . '/templates.prev.json')): ?>
      <form method="post" onsubmit="return confirm('Go back to the template designs from before your last save? This covers every page type.');"><?= csrf_field() ?><input type="hidden" name="action" value="restore"><button class="btn btn--ghost" type="submit">↩ Undo my last save</button></form>
    <?php endif; ?>
  </div>
</div>

<script>
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-clear]'); if (!b) return;
    var el = document.getElementById(b.dataset.clear); el.value = '';
    var cp = document.querySelector('[data-color-for="' + b.dataset.clear + '"]'); if (cp) cp.value = '#ffffff';
  });
  document.addEventListener('input', function (e) {
    var cp = e.target.closest('[data-color-for]');
    if (cp) { document.getElementById(cp.dataset.colorFor).value = cp.value; return; }
    if (e.target.matches('.dpair input[type=text]') && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
      var c2 = document.querySelector('[data-color-for="' + e.target.id + '"]'); if (c2) c2.value = e.target.value;
    }
  });
</script>

<?php require __DIR__ . '/_footer.php'; ?>

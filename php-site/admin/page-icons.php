<?php require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_pagelib.php';

$file = basename($_GET['file'] ?? '');
$filepath = page_path($file);
if (!$filepath) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Page not found.'];
    header('Location: pages.php'); exit;
}
$html = file_get_contents($filepath);
$self = 'page-icons.php?file=' . urlencode($file);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['page_hash'] ?? '') !== md5($html)) {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => 'This page was changed somewhere else after you opened it, so nothing was saved. Your screen has been refreshed. Please make your edit again.'];
        header('Location: ' . $self); exit;
    }
    page_save($filepath, apply_icon_changes($html, $_POST));
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Icons saved. <a href="../' . h($file) . '" target="_blank">View the page →</a>'];
    header('Location: ' . $self); exit;
}

$slots = icon_slots($html);
$title = preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', parse_title($html)) ?: $file;

/** Render the small preview of what is currently inside an icon box. */
function icon_preview_html($inner) {
    if (trim($inner) === '') return '<span style="font-size:0.7rem;color:#9ca3af;font-weight:500;">none</span>';
    return str_replace('src="uploads/', 'src="../uploads/', $inner);
}
/** The three layout dropdowns for one icon. */
function icon_layout_fields($prefix, $i, $pos, $size, $align) { ?>
  <div class="icon-row__opts">
    <div>
      <label>Position</label>
      <select name="<?= $prefix ?>_pos[<?= $i ?>]">
        <option value="above" <?= $pos === 'above' ? 'selected' : '' ?>>Above the title</option>
        <option value="inline" <?= $pos === 'inline' ? 'selected' : '' ?>>Beside the title (same line)</option>
      </select>
    </div>
    <div>
      <label>Size</label>
      <select name="<?= $prefix ?>_size[<?= $i ?>]">
        <option value="sm" <?= $size === 'sm' ? 'selected' : '' ?>>Small</option>
        <option value="md" <?= $size === 'md' ? 'selected' : '' ?>>Medium</option>
        <option value="lg" <?= $size === 'lg' ? 'selected' : '' ?>>Large</option>
      </select>
    </div>
    <div>
      <label>Alignment</label>
      <select name="<?= $prefix ?>_align[<?= $i ?>]">
        <option value="left" <?= $align === 'left' ? 'selected' : '' ?>>Left</option>
        <option value="center" <?= $align === 'center' ? 'selected' : '' ?>>Centre</option>
      </select>
    </div>
  </div>
<?php }
?>

<h2 style="margin-bottom:0.25rem;">Icons: <?= h($title) ?></h2>
<p style="color:#6b7280;margin-top:0;margin-bottom:1.5rem;">
  <a href="pages.php">← back to pages</a> &nbsp;·&nbsp;
  <a href="../<?= h($file) ?>" target="_blank">view live →</a> &nbsp;·&nbsp;
  <a href="page-edit.php?file=<?= urlencode($file) ?>">Edit text and sections →</a>
</p>

<form method="post">
  <input type="hidden" name="page_hash" value="<?= md5($html) ?>">

  <div class="card">
    <h3 style="margin-top:0;">Icons on this page</h3>
    <p style="color:#6b7280;font-size:0.875rem;margin-bottom:1rem;">Click <strong>Change icon</strong> to pick a different one: a built-in icon, your own number, letter or emoji, or a picture from your Media Library. Then choose where it sits and how big it is.</p>

    <?php if (empty($slots['existing'])): ?>
      <p style="color:#6b7280;">This page has no icons yet. Open "Cards without an icon" below to add some.</p>
    <?php endif; ?>

    <?php foreach ($slots['existing'] as $s): $i = $s['index']; $isCard = $s['kind'] === 'cap__icon'; ?>
      <div class="icon-row" id="exrow_<?= $i ?>">
        <div class="icon-row__preview"><?= icon_preview_html($s['inner']) ?></div>
        <div>
          <div class="icon-row__top">
            <div>
              <div class="icon-row__title"><?= h($s['title'] ?: 'Icon ' . ($i + 1)) ?></div>
              <div class="icon-row__meta"><?= $isCard ? 'Card icon' : 'Large article picture symbol' ?></div>
            </div>
            <button type="button" class="btn btn--ghost" style="padding:0.4rem 0.8rem;font-size:0.8rem;" onclick="openIconPicker('exrow_<?= $i ?>')">Change icon</button>
          </div>
          <input type="hidden" class="icon-value" name="ex_value[<?= $i ?>]" value="keep">
          <?php if ($isCard) icon_layout_fields('ex', $i, $s['pos'], $s['size'], $s['align']); ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($slots['missing'])): ?>
  <div class="card">
    <details>
      <summary style="cursor:pointer;font-weight:700;font-size:1.05rem;">Cards without an icon (<?= count($slots['missing']) ?>)</summary>
      <p style="color:#6b7280;font-size:0.875rem;margin:0.75rem 0 1rem;">These cards have no icon at the moment. Click <strong>Add icon</strong> on any card to give it one. Cards you leave alone stay as they are.</p>
      <?php foreach ($slots['missing'] as $s): $j = $s['index']; ?>
        <div class="icon-row is-new" id="nwrow_<?= $j ?>">
          <div class="icon-row__preview"><span style="font-size:0.7rem;color:#9ca3af;font-weight:500;">none</span></div>
          <div>
            <div class="icon-row__top">
              <div>
                <div class="icon-row__title"><?= h($s['title'] ?: 'Card ' . ($j + 1)) ?></div>
                <div class="icon-row__meta">No icon yet</div>
              </div>
              <button type="button" class="btn btn--ghost" style="padding:0.4rem 0.8rem;font-size:0.8rem;" onclick="openIconPicker('nwrow_<?= $j ?>')">Add icon</button>
            </div>
            <input type="hidden" class="icon-value" name="nw_value[<?= $j ?>]" value="keep">
            <?php icon_layout_fields('nw', $j, 'above', 'md', 'left'); ?>
          </div>
        </div>
      <?php endforeach; ?>
    </details>
  </div>
  <?php endif; ?>

  <div style="display:flex;gap:0.5rem;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);flex-wrap:wrap;">
    <button type="submit" class="btn">Save icons</button>
    <a href="pages.php" class="btn btn--ghost">Cancel</a>
    <a href="../<?= h($file) ?>" target="_blank" class="btn btn--ghost">Preview live →</a>
  </div>
</form>

<?php if (is_file(page_backup_path($filepath))): ?>
<form method="post" action="page-edit.php?file=<?= urlencode($file) ?>" style="margin-top:0.5rem;" onsubmit="return confirm('Go back to the version of this page from before your last save?');">
  <input type="hidden" name="action" value="undo">
  <button type="submit" class="btn btn--ghost">↩ Undo my last save</button>
</form>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

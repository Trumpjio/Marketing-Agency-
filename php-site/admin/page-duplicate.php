<?php require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_pagelib.php';

$file = basename($_GET['file'] ?? '');
$filepath = page_path($file);
if (!$filepath) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Page not found.'];
    header('Location: pages.php'); exit;
}

$sourceTitle = parse_title(file_get_contents($filepath));
$suggestedSlug = basename($file, '.html') . '-copy';
$flash = '';
$flashType = '';
$newPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $newSlug = trim($_POST['new_slug'] ?? '');
        $newTitle = trim($_POST['new_title'] ?? '') ?: null;
        $res = page_duplicate($file, $newSlug, $newTitle);
        if ($res['ok']) {
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Draft created at <code>' . h($res['file']) . '</code>. It is set to noindex,nofollow until you promote it. <a href="page-edit.php?file=' . urlencode($res['file']) . '" style="color:#635bff;">Edit it →</a>'];
            header('Location: pages.php');
            exit;
        } else {
            $flash = $res['error'];
            $flashType = 'err';
        }
    } elseif ($action === 'promote') {
        $slug = trim($_POST['slug'] ?? '');
        if (page_promote_draft($slug)) {
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Draft promoted to published. It is now indexable and in the sitemap.'];
        } else {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Could not promote draft.'];
        }
        header('Location: pages.php');
        exit;
    }
}
?>

<h2>Duplicate page</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">Make a copy of <strong><?= h($sourceTitle ?: $file) ?></strong> (<code><?= h($file) ?></code>). The duplicate starts as a draft — indexing is blocked and it is excluded from the sitemap until you promote it.</p>

<?php if ($flash): ?>
  <div class="notice notice--<?= h($flashType) ?>" style="margin-bottom:1rem;"><?= h($flash) ?></div>
<?php endif; ?>

<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="create">

  <div class="card">
    <div class="field">
      <label>New URL slug</label>
      <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
        <span style="color:#9ca3af;font-size:0.9rem;">https://orcapulse.ai/</span>
        <input type="text" name="new_slug" value="<?= h($suggestedSlug) ?>" required pattern="[a-z0-9-]+" maxlength="80" style="flex:1;min-width:200px;" />
        <span style="color:#9ca3af;font-size:0.9rem;">.html</span>
      </div>
      <div class="help">Lower-case letters, numbers and dashes only. Max 80 characters.</div>
    </div>

    <div class="field">
      <label>New page title (optional — leave blank to keep the source title with "(copy)" suffix)</label>
      <input type="text" name="new_title" placeholder="<?= h($sourceTitle) ?> (copy)" />
      <div class="help">If you set a new title it also updates OG + Twitter card titles.</div>
    </div>

    <div style="margin-top:1rem;padding:0.9rem 1rem;background:#eff6ff;border-left:3px solid #635bff;border-radius:6px;font-size:0.875rem;color:#1e40af;">
      <strong>Safety guarantees:</strong>
      <ul style="margin:0.4rem 0 0 1.2rem;line-height:1.6;">
        <li>The duplicate's canonical URL points to the new slug (no duplicate-content signal to Google).</li>
        <li>The duplicate carries <code>&lt;meta name="robots" content="noindex,nofollow"&gt;</code> until you promote it.</li>
        <li>The duplicate is NOT added to <code>sitemap.xml</code> until promoted.</li>
        <li>The source page and its settings are untouched.</li>
      </ul>
    </div>
  </div>

  <div style="display:flex;gap:0.5rem;">
    <button type="submit" class="btn">Create draft duplicate</button>
    <a href="pages.php" class="btn btn--ghost">Cancel</a>
  </div>
</form>

<?php
$drafts = drafts_load();
if (!empty($drafts)):
?>
  <h3 style="margin-top:2.5rem;margin-bottom:0.75rem;">Existing drafts</h3>
  <div class="card" style="padding:0;overflow:hidden;">
    <table class="list" style="margin:0;">
      <tbody>
      <?php foreach ($drafts as $slug): ?>
        <tr>
          <td style="padding:0.6rem 1rem;"><strong><?= h($slug) ?>.html</strong></td>
          <td style="text-align:right;padding:0.6rem 1rem;">
            <a href="page-edit.php?file=<?= urlencode($slug . '.html') ?>" class="btn" style="padding:0.4rem 0.9rem;font-size:0.8rem;">Edit draft</a>
            <a href="../<?= h($slug) ?>.html" target="_blank" class="btn btn--ghost" style="padding:0.4rem 0.9rem;font-size:0.8rem;">Preview</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Promote this draft to published? It will become indexable and be added to the sitemap.');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="promote">
              <input type="hidden" name="slug" value="<?= h($slug) ?>">
              <button type="submit" class="btn btn--primary" style="padding:0.4rem 0.9rem;font-size:0.8rem;">Promote to published</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

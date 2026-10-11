<?php require_once __DIR__ . '/_layout.php';
$files = glob(SITE_ROOT . '/*.html');
sort($files);

// Group by type for easier navigation
$groups = [
    'Core' => [],
    'Services' => [],
    'Industries' => [],
    'Countries' => [],
    'Cities' => [],
    'Industry × City' => [],
    'Blog Posts' => [],
    'Legal' => [],
];

foreach ($files as $file) {
    $name = basename($file);
    if (preg_match('/^service-/', $name)) $groups['Services'][] = $file;
    elseif (preg_match('/^industry-/', $name)) $groups['Industries'][] = $file;
    elseif (preg_match('/^country-/', $name)) $groups['Countries'][] = $file;
    elseif (preg_match('/^city-/', $name)) $groups['Cities'][] = $file;
    elseif (preg_match('/^insight-/', $name)) $groups['Blog Posts'][] = $file;
    elseif (in_array($name, ['privacy-policy.html', 'terms.html'])) $groups['Legal'][] = $file;
    elseif (preg_match('/-marketing-/', $name)) $groups['Industry × City'][] = $file;
    else $groups['Core'][] = $file;
}

function page_title($file) {
    $html = file_get_contents($file);
    if (preg_match('/<title>([^<]+)<\/title>/', $html, $m)) {
        return preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', $m[1]);
    }
    return basename($file);
}

$search = trim($_GET['q'] ?? '');
?>

<h2>Pages</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">Edit any page's SEO, headlines, CTA button and images. Blog posts have their own full editor in <a href="blogs.php">Blog Posts</a>.</p>

<div class="toolbar">
  <form method="get" style="display:flex;gap:0.5rem;flex:1;max-width:400px;">
    <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search pages by filename or title...">
    <button type="submit" class="btn btn--ghost">Search</button>
    <?php if ($search): ?><a href="pages.php" class="btn btn--ghost">Clear</a><?php endif; ?>
  </form>
  <span style="color:#6b7280;font-size:0.875rem;"><?= count($files) ?> pages total</span>
</div>

<?php foreach ($groups as $group_name => $group_files):
    if (empty($group_files)) continue;
    if ($search) {
        $group_files = array_filter($group_files, function($f) use ($search) {
            return stripos(basename($f), $search) !== false || stripos(page_title($f), $search) !== false;
        });
        if (empty($group_files)) continue;
    }
?>
  <div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:0.75rem 1rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:0.825rem;color:#374151;letter-spacing:0.03em;text-transform:uppercase;">
      <?= h($group_name) ?> <span style="color:#9ca3af;font-weight:400;text-transform:none;letter-spacing:0;">(<?= count($group_files) ?>)</span>
    </div>
    <table class="list">
      <tbody>
      <?php foreach ($group_files as $file):
        $name = basename($file);
        $title = page_title($file);
      ?>
        <?php
        $slug = basename($name, '.html');
        require_once __DIR__ . '/_pagelib.php';
        $draft = is_draft($slug);
        ?>
        <tr>
          <td style="padding-left:1rem;width:60%;"><strong style="font-size:0.925rem;"><?= h($title) ?></strong>
            <?php if ($draft): ?><span style="display:inline-block;padding:0.1rem 0.5rem;background:#fef3c7;color:#92400e;font-size:0.65rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;border-radius:999px;margin-left:0.5rem;">Draft</span><?php endif; ?>
            <br><code style="font-size:0.7rem;color:#9ca3af;"><?= h($name) ?></code></td>
          <td style="text-align:right;padding-right:1rem;white-space:nowrap;">
            <a href="page-edit.php?file=<?= urlencode($name) ?>" class="btn" style="padding:0.4rem 0.9rem;font-size:0.8rem;">Edit</a>
            <a href="page-icons.php?file=<?= urlencode($name) ?>" class="btn btn--ghost" style="padding:0.4rem 0.9rem;font-size:0.8rem;">Icons</a>
            <a href="page-duplicate.php?file=<?= urlencode($name) ?>" class="btn btn--ghost" style="padding:0.4rem 0.9rem;font-size:0.8rem;" title="Make a copy of this page as a draft">Duplicate</a>
            <a href="../<?= h($name) ?>" target="_blank" class="btn btn--ghost" style="padding:0.4rem 0.9rem;font-size:0.8rem;">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/_footer.php'; ?>

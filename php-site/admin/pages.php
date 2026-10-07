<?php require_once __DIR__ . '/_layout.php';
$files = glob(SITE_ROOT . '/*.html');
sort($files);
?>

<h2>Pages</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">All <?= count($files) ?> pages on your site. To edit page body content directly, use <strong>File Manager</strong> in your Hostinger panel to open the HTML file. For blog posts, use the <a href="blogs.php">Blog Posts</a> section — those get a full editor.</p>

<table class="list card" style="padding:0;">
  <thead>
    <tr>
      <th style="padding-left:1rem;">File</th>
      <th>Title</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($files as $file):
      $name = basename($file);
      $html = file_get_contents($file);
      preg_match('/<title>([^<]+)<\/title>/', $html, $m);
      $title = $m[1] ?? $name;
    ?>
      <tr>
        <td style="padding-left:1rem;"><code style="font-size:0.75rem;"><?= h($name) ?></code></td>
        <td><?= h(preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', $title)) ?></td>
        <td>
          <a href="../<?= h($name) ?>" target="_blank" class="btn btn--ghost" style="padding:0.4rem 0.7rem;font-size:0.75rem;">View</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/_footer.php'; ?>

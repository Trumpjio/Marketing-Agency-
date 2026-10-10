<?php require_once __DIR__ . '/_layout.php';
require __DIR__ . '/_build.php';

if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['slug'])) {
    if (($_GET['_csrf'] ?? '') !== csrf_token()) { http_response_code(403); exit('Forbidden.'); }
    $blogs = blogs_load();
    $known = array_column($blogs, 'slug');
    if (in_array($_GET['slug'], $known, true)) {
        $blogs = array_values(array_filter($blogs, fn($b) => $b['slug'] !== $_GET['slug']));
        blogs_save($blogs);
        $file = SITE_ROOT . '/' . basename($_GET['slug']) . '.html';
        if (file_exists($file)) unlink($file);
    }
    build_insights_listing($blogs, settings_load());
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Blog post deleted.'];
    header('Location: blogs.php');
    exit;
}

$blogs = blogs_load();
usort($blogs, fn($a, $b) => strcmp($b['date'], $a['date']));
?>

<h2>Blog Posts</h2>
<div class="toolbar">
  <a href="blog-edit.php" class="btn">+ Add new blog post</a>
  <span style="color:#6b7280;font-size:0.875rem;"><?= count($blogs) ?> posts</span>
</div>

<?php if (empty($blogs)): ?>
  <div class="card">
    <p style="margin:0;color:#6b7280;">No blog posts yet. <a href="blog-edit.php">Create your first one</a>.</p>
  </div>
<?php else: ?>
  <table class="list card" style="padding:0;">
    <thead>
      <tr>
        <th style="padding-left:1rem;">Title</th>
        <th>Slug</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($blogs as $post): ?>
        <tr>
          <td style="padding-left:1rem;"><strong><?= h($post['title']) ?></strong><br><span style="color:#6b7280;font-size:0.8rem;"><?= h($post['excerpt']) ?></span></td>
          <td><code style="font-size:0.75rem;"><?= h($post['slug']) ?></code></td>
          <td><?= h($post['date']) ?></td>
          <td>
            <a href="blog-edit.php?slug=<?= urlencode($post['slug']) ?>" class="btn btn--ghost" style="padding:0.4rem 0.7rem;font-size:0.75rem;">Edit</a>
            <a href="../<?= h($post['slug']) ?>.html" target="_blank" class="btn btn--ghost" style="padding:0.4rem 0.7rem;font-size:0.75rem;">View</a>
            <a href="?action=delete&slug=<?= urlencode($post['slug']) ?>&_csrf=<?= urlencode(csrf_token()) ?>" onclick="return confirm('Delete this blog post?')" class="btn btn--danger" style="padding:0.4rem 0.7rem;font-size:0.75rem;">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

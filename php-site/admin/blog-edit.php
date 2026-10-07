<?php require_once __DIR__ . '/_layout.php';
require __DIR__ . '/_build.php';

$blogs = blogs_load();
$editing_slug = $_GET['slug'] ?? '';
$post = null;
foreach ($blogs as $b) {
    if ($b['slug'] === $editing_slug) { $post = $b; break; }
}
$is_new = !$post;
if ($is_new) {
    $post = ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'image' => '', 'date' => date('Y-m-d'), 'author' => 'OrcaPulse'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post['title']   = trim($_POST['title']);
    $post['slug']    = trim($_POST['slug']) ?: 'insight-' . slugify($post['title']);
    $post['excerpt'] = trim($_POST['excerpt']);
    $post['body']    = $_POST['body'];
    $post['image']   = trim($_POST['image']);
    $post['date']    = trim($_POST['date']);
    $post['author']  = trim($_POST['author']);

    // Save to data
    $found = false;
    foreach ($blogs as $i => $b) {
        if ($b['slug'] === $editing_slug) { $blogs[$i] = $post; $found = true; break; }
    }
    if (!$found) $blogs[] = $post;
    blogs_save($blogs);

    // Generate the HTML file
    $settings = settings_load();
    $html = build_blog_html($post, $settings);
    file_put_contents(SITE_ROOT . '/' . $post['slug'] . '.html', $html);

    // Delete old HTML if slug changed
    if ($editing_slug && $editing_slug !== $post['slug']) {
        $oldFile = SITE_ROOT . '/' . $editing_slug . '.html';
        if (file_exists($oldFile)) unlink($oldFile);
    }

    // Refresh insights listing
    build_insights_listing($blogs, $settings);

    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Blog post saved and published.'];
    header('Location: blogs.php');
    exit;
}
?>

<h2><?= $is_new ? 'New blog post' : 'Edit blog post' ?></h2>

<form method="post">
  <div class="card">
    <div class="field">
      <label>Title</label>
      <input type="text" name="title" value="<?= h($post['title']) ?>" required placeholder="e.g. How dental clinics in Dubai win in 2026">
    </div>
    <div class="field">
      <label>URL slug (lower-case, hyphens, no spaces)</label>
      <input type="text" name="slug" value="<?= h($post['slug']) ?>" placeholder="auto-generated from title if blank">
      <div class="help">Final URL: <?= h(settings_load()['base_url']) ?>/<strong>SLUG</strong>.html</div>
    </div>
    <div class="field">
      <label>Short description (shown in cards, SEO meta)</label>
      <textarea name="excerpt" rows="2" required style="font-family:inherit;font-size:0.95rem;"><?= h($post['excerpt']) ?></textarea>
    </div>
    <div class="row">
      <div class="field">
        <label>Author</label>
        <input type="text" name="author" value="<?= h($post['author']) ?>">
      </div>
      <div class="field">
        <label>Date</label>
        <input type="date" name="date" value="<?= h($post['date']) ?>" required>
      </div>
    </div>
    <div class="field">
      <label>Featured image URL (optional)</label>
      <input type="text" name="image" value="<?= h($post['image']) ?>" placeholder="uploads/your-image.jpg or full URL">
      <div class="help">Tip: upload an image in <a href="media.php">Media</a> first, copy the path.</div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Body (HTML)</h3>
    <div class="help" style="margin-bottom:0.75rem;">Write in HTML. Use &lt;h2&gt; for section headings, &lt;p&gt; for paragraphs, &lt;strong&gt; for bold.</div>
    <textarea name="body" rows="22" style="font-family:'SF Mono',monospace;font-size:0.825rem;"><?= h($post['body']) ?></textarea>
  </div>

  <div style="display:flex;gap:0.5rem;">
    <button type="submit" class="btn">Save & Publish</button>
    <a href="blogs.php" class="btn btn--ghost">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>

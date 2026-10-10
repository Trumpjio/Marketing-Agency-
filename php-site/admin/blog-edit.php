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
    csrf_check();
    $post['title']   = trim($_POST['title']);
    $post['slug']    = slugify(trim($_POST['slug']) ?: 'insight-' . slugify($post['title']));
    $post['excerpt'] = trim($_POST['excerpt']);
    $post['body']    = $_POST['body'];
    $post['image']   = trim($_POST['image']);
    $post['date']    = trim($_POST['date']);
    $post['author']  = trim($_POST['author']);

    // Never overwrite a page that isn't one of the admin's own blog posts.
    $own = array_column($blogs, 'slug');
    if ($post['slug'] === '' || (file_exists(SITE_ROOT . '/' . $post['slug'] . '.html') && !in_array($post['slug'], $own, true))) {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => 'That URL is already used by another page. Please choose a different URL slug.'];
        header('Location: blog-edit.php' . ($editing_slug ? '?slug=' . urlencode($editing_slug) : ''));
        exit;
    }

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
  <?= csrf_field() ?>
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
      <label>Featured image (shown at top of blog + in cards)</label>
      <div class="image-picker-field">
        <img class="image-picker-preview" data-preview-for="blog-image" src="<?= $post['image'] ? '../' . h(ltrim($post['image'], '/')) : '' ?>" alt="" onerror="this.style.visibility='hidden'">
        <input type="text" id="blog-image" name="image" value="<?= h($post['image']) ?>" placeholder="click Pick image →">
        <button type="button" class="image-picker-btn" onclick="openMedia('blog-image')">📷 Pick image</button>
      </div>
      <div class="help">Click Pick image to browse the Media Library. Or type a path like <code>uploads/dubai.jpg</code>.</div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">✏️ Body — write your article</h3>
    <div class="help" style="margin-bottom:0.75rem;">Use the toolbar to format text, add headings, lists, links, and images — like Word. No HTML knowledge needed.</div>
    <textarea id="blog-body" name="body" rows="22"><?= h($post['body']) ?></textarea>
  </div>

  <script>
    tinymce.init({
      selector: '#blog-body',
      license_key: 'gpl',
      height: 480,
      menubar: 'edit insert format table',
      plugins: 'autolink lists link image media table code help wordcount',
      toolbar: 'undo redo | blocks | bold italic underline | forecolor | alignleft aligncenter alignright | bullist numlist | link image media | removeformat | code',
      content_style: 'body { font-family: Inter, -apple-system, sans-serif; font-size: 15px; line-height: 1.6; } img { max-width: 100%; }',
      branding: false,
      promotion: false,
      file_picker_types: 'image',
      file_picker_callback: function (callback) { window._tinyPickerCb = callback; openMediaForTiny(); },
      convert_urls: false,
      entity_encoding: 'raw'
    });
  </script>

  <div style="display:flex;gap:0.5rem;">
    <button type="submit" class="btn">Save & Publish</button>
    <a href="blogs.php" class="btn btn--ghost">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>

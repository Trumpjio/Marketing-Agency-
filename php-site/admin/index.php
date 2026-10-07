<?php require_once __DIR__ . '/_layout.php';
$settings = settings_load();
$blogs = blogs_load();
$page_count = count(glob(SITE_ROOT . '/*.html'));
?>
<h2>Dashboard</h2>
<p style="color:#6b7280;margin-bottom:2rem;">Edit your site content below. All changes apply instantly to your live site.</p>

<div class="card">
  <h3 style="margin-top:0;">Site at a glance</h3>
  <div class="row">
    <div>
      <div style="color:#6b7280;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Pages</div>
      <div style="font-size:1.5rem;font-weight:700;"><?= $page_count ?></div>
    </div>
    <div>
      <div style="color:#6b7280;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Blog posts</div>
      <div style="font-size:1.5rem;font-weight:700;"><?= count($blogs) ?></div>
    </div>
  </div>
</div>

<div class="card">
  <h3 style="margin-top:0;">Quick actions</h3>
  <div class="toolbar">
    <a href="settings.php" class="btn">Edit site settings</a>
    <a href="blog-edit.php" class="btn">Add blog post</a>
    <a href="media.php" class="btn btn--ghost">Upload image</a>
  </div>
</div>

<div class="card">
  <h3 style="margin-top:0;">Current settings</h3>
  <table class="list">
    <tr><td><strong>Brand name</strong></td><td><?= h($settings['brand_name']) ?></td></tr>
    <tr><td><strong>Call-to-action URL</strong></td><td><?= h($settings['cta_url']) ?></td></tr>
    <tr><td><strong>Call-to-action label</strong></td><td><?= h($settings['cta_label']) ?></td></tr>
    <tr><td><strong>Email</strong></td><td><?= h($settings['email']) ?></td></tr>
    <tr><td><strong>Phone</strong></td><td><?= h($settings['phone']) ?: '<em style="color:#9ca3af;">not set</em>' ?></td></tr>
  </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>

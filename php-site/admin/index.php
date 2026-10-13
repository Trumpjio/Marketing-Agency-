<?php require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_templates.php';
$settings = settings_load();
$posts_all = blog_posts(true);
$page_count = count(glob(SITE_ROOT . '/*.html'));
$unpublished = pending_files();
$lib_total = 0;
foreach (array_keys(LIB_COLLECTIONS) as $c) $lib_total += count(lib_all($c));
?>
<h2>Dashboard</h2>
<p style="color:#6b7280;margin-bottom:2rem;">Edit your site below. Page edits are saved as a draft first, so you can preview them and publish when you are happy.</p>

<?php if ($unpublished): ?>
<div class="card" style="border-color:#fde68a;background:#fffbeb;">
  <h3 style="margin-top:0;color:#92400e;">Unpublished changes (<?= count($unpublished) ?>)</h3>
  <p style="margin:0 0 .5rem;color:#92400e;font-size:.875rem;">Visitors do not see these yet.</p>
  <?php foreach ($unpublished as $f): ?>
    <a class="btn btn--ghost" style="padding:.35rem .75rem;font-size:.8rem;margin:0 .35rem .35rem 0;background:#fff;" href="page-edit.php?file=<?= urlencode($f) ?>"><?= h($f) ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;">Site at a glance</h3>
  <div class="row">
    <div>
      <div style="color:#6b7280;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Pages</div>
      <div style="font-size:1.5rem;font-weight:700;"><?= $page_count ?></div>
    </div>
    <div>
      <div style="color:#6b7280;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Blog posts</div>
      <div style="font-size:1.5rem;font-weight:700;"><?= count($posts_all) ?></div>
    </div>
    <div>
      <div style="color:#6b7280;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Library entries</div>
      <div style="font-size:1.5rem;font-weight:700;"><?= $lib_total ?></div>
    </div>
  </div>
</div>

<div class="card">
  <h3 style="margin-top:0;">Quick actions</h3>
  <div class="toolbar">
    <a href="pages.php" class="btn">Edit a page</a>
    <a href="library.php" class="btn btn--ghost">Content library</a>
    <a href="templates.php" class="btn btn--ghost">Page templates</a>
    <a href="blog-edit.php" class="btn btn--ghost">Add blog post</a>
    <a href="settings.php" class="btn btn--ghost">Site settings</a>
    <a href="media.php" class="btn btn--ghost">Upload image</a>
  </div>
</div>

<div class="card">
  <h3 style="margin-top:0;">Where do I change what?</h3>
  <table class="list">
    <tr><td><strong>One page</strong></td><td>Pages → Edit. Words, pictures, buttons, sections (add, remove, reorder, hide), a section's colours. Only that page changes.</td></tr>
    <tr><td><strong>Testimonials, case studies, team, awards</strong></td><td>Content Library. Write once, shown in every matching section.</td></tr>
    <tr><td><strong>Blog</strong></td><td>Blog Posts. Featured, topics and popular posts update the Blog page by themselves.</td></tr>
    <tr><td><strong>Many pages at once</strong></td><td>Page Templates. Colours, spacing, corners and heading style for every city page, country page, service page and so on.</td></tr>
    <tr><td><strong>Whole site</strong></td><td>Design (colours, fonts, buttons) and Site Settings (logo, contact details, main button).</td></tr>
  </table>
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

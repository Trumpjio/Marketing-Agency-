<?php
require_once __DIR__ . '/config.php';
require_login();
require_once __DIR__ . '/_build.php';

$tab = ($_GET['tab'] ?? 'posts') === 'topics' ? 'topics' : 'posts';

if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['slug'])) {
    if (($_GET['_csrf'] ?? '') !== csrf_token()) { http_response_code(403); exit('Forbidden.'); }
    $blogs = blogs_load();
    $known = array_column($blogs, 'slug');
    if (in_array($_GET['slug'], $known, true)) {
        $blogs = array_values(array_filter($blogs, fn($b) => $b['slug'] !== $_GET['slug']));
        blogs_save($blogs);
        $file = SITE_ROOT . '/' . basename($_GET['slug']) . '.html';
        if (file_exists($file)) unlink($file);
        sitemap_remove($file);
        $m = blog_meta_load(); unset($m[$_GET['slug']]); blog_meta_save($m);
    }
    blog_regenerate();
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Blog post deleted. The Blog page has been updated.'];
    header('Location: blogs.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    $slug = (string) ($_POST['slug'] ?? '');
    $valid = array_column(blog_posts(true), 'slug');
    $msg = null;
    if ($a === 'flag' && in_array($slug, $valid, true)) {
        $flag = $_POST['flag'] ?? '';
        if (in_array($flag, ['featured', 'popular', 'hidden'], true)) {
            $meta = blog_meta_load();
            $on = empty($meta[$slug][$flag]);
            if ($flag === 'featured' && $on) foreach ($meta as $k => $v) unset($meta[$k]['featured']);
            $meta[$slug][$flag] = $on;
            blog_meta_save($meta);
            $msg = ['featured' => $on ? 'Now the featured post.' : 'No longer featured. The newest post is shown instead.',
                    'popular' => $on ? 'Added to popular posts.' : 'Removed from popular posts.',
                    'hidden' => $on ? 'Hidden from the Blog page. The article itself is still online.' : 'Shown on the Blog page again.'][$flag];
        }
    } elseif ($a === 'category' && in_array($slug, $valid, true)) {
        $slugs = array_column(blog_categories(), 'slug');
        $c = (string) ($_POST['category'] ?? '');
        if ($c === '' || in_array($c, $slugs, true)) { blog_meta_set($slug, ['category' => $c]); $msg = 'Topic changed.'; }
    } elseif ($a === 'cat_add') {
        $label = trim(strip_tags((string) ($_POST['label'] ?? '')));
        $cs = blog_categories();
        $cslug = slugify($label);
        if ($label === '' || $cslug === '' || in_array($cslug, array_column($cs, 'slug'), true)) {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Give the topic a new name.'];
        } else {
            $cs[] = ['slug' => $cslug, 'label' => $label, 'desc' => trim(strip_tags((string) ($_POST['desc'] ?? '')))];
            blog_categories_save($cs); $msg = 'Topic added.';
        }
    } elseif ($a === 'cat_save') {
        $cs = blog_categories();
        foreach ($cs as &$c) if ($c['slug'] === ($_POST['cat'] ?? '')) {
            $label = trim(strip_tags((string) ($_POST['label'] ?? '')));
            if ($label !== '') $c['label'] = $label;
            $c['desc'] = trim(strip_tags((string) ($_POST['desc'] ?? '')));
        }
        unset($c);
        blog_categories_save($cs); $msg = 'Topic saved.';
    } elseif ($a === 'cat_delete') {
        $cs = blog_categories();
        $inUse = array_filter(blog_posts(true), fn($p) => $p['category'] === ($_POST['cat'] ?? ''));
        if ($inUse) {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => 'That topic still has ' . count($inUse) . ' post(s). Move them to another topic first.'];
        } else {
            blog_categories_save(array_values(array_filter($cs, fn($c) => $c['slug'] !== ($_POST['cat'] ?? ''))));
            $msg = 'Topic deleted.';
        }
    }
    if ($msg !== null) { blog_regenerate(); $_SESSION['flash'] = ['type' => 'ok', 'msg' => $msg . ' The Blog page has been updated.']; }
    header('Location: blogs.php?tab=' . ($a && str_starts_with($a, 'cat_') ? 'topics' : 'posts'));
    exit;
}

require_once __DIR__ . '/_layout.php';
$posts = blog_posts(true);
$cats = blog_categories();
$counts = [];
foreach ($posts as $p) $counts[$p['category']] = ($counts[$p['category']] ?? 0) + 1;
?>
<style>
  .tabs { display: flex; gap: .4rem; margin-bottom: 1.25rem; }
  .tabs a { padding: .5rem .9rem; border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--ink); text-decoration: none; font-size: .85rem; font-weight: 600; }
  .tabs a.is-on { background: var(--blue); border-color: var(--blue); color: #fff; }
  .mini { padding: .32rem .6rem; font: inherit; font-size: .75rem; font-weight: 600; background: #fff; border: 1px solid var(--line); border-radius: 6px; cursor: pointer; color: var(--ink); text-decoration: none; display: inline-block; }
  .mini:hover { border-color: var(--blue); color: var(--blue); }
  .mini.is-on { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
  .mini--danger:hover { border-color: var(--danger); color: var(--danger); }
  td form { display: inline; margin: 0; }
  td select { padding: .35rem .5rem; font-size: .8rem; width: auto; }
  tr.is-hidden td { opacity: .55; }
  .tcell { white-space: nowrap; }
</style>

<h2>Blog Posts</h2>
<div class="tabs">
  <a href="blogs.php" class="<?= $tab === 'posts' ? 'is-on' : '' ?>">Posts (<?= count($posts) ?>)</a>
  <a href="blogs.php?tab=topics" class="<?= $tab === 'topics' ? 'is-on' : '' ?>">Topics (<?= count($cats) ?>)</a>
</div>

<?php if ($tab === 'posts'): ?>
<p style="color:#6b7280;margin-top:0;">These posts fill the <a href="page-edit.php?file=insights.html">Blog page</a> automatically: the <strong>featured</strong> post at the top, the topic buttons, the newest posts and the <strong>popular</strong> list. Change a post here and the Blog page updates by itself. “Popular” is a list you choose: the site does not track page views.</p>
<div class="toolbar">
  <a href="blog-edit.php" class="btn">+ Add new blog post</a>
  <a href="page-edit.php?file=insights.html" class="btn btn--ghost">Edit the Blog page</a>
</div>

<div style="overflow-x:auto;"><table class="list card" style="padding:0;min-width:760px;">
  <thead><tr><th style="padding-left:1rem;">Title</th><th>Topic</th><th>Date</th><th>Featured</th><th>Popular</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($posts as $p): $own = $p['source'] === 'admin'; ?>
    <tr class="<?= $p['hidden'] ? 'is-hidden' : '' ?>">
      <td style="padding-left:1rem;"><strong><?= h($p['title']) ?></strong><?= $p['hidden'] ? ' <span class="pill">hidden</span>' : '' ?><br><span style="color:#6b7280;font-size:.78rem;"><?= h(mb_substr($p['excerpt'], 0, 90)) ?></span></td>
      <td class="tcell">
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="category"><input type="hidden" name="slug" value="<?= h($p['slug']) ?>">
          <select name="category" onchange="this.form.submit()">
            <option value="">Uncategorised</option>
            <?php foreach ($cats as $c): ?><option value="<?= h($c['slug']) ?>" <?= $p['category'] === $c['slug'] ? 'selected' : '' ?>><?= h($c['label']) ?></option><?php endforeach; ?>
          </select></form>
      </td>
      <td class="tcell"><?= h(blog_date($p['date'])) ?></td>
      <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="flag"><input type="hidden" name="flag" value="featured"><input type="hidden" name="slug" value="<?= h($p['slug']) ?>"><button class="mini <?= $p['featured'] ? 'is-on' : '' ?>" title="Show this post big at the top of the Blog page"><?= $p['featured'] ? '★ Featured' : '☆ Feature' ?></button></form></td>
      <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="flag"><input type="hidden" name="flag" value="popular"><input type="hidden" name="slug" value="<?= h($p['slug']) ?>"><button class="mini <?= $p['popular'] ? 'is-on' : '' ?>"><?= $p['popular'] ? '✓ Popular' : '+ Popular' ?></button></form></td>
      <td class="tcell">
        <?php if ($own): ?><a class="mini" href="blog-edit.php?slug=<?= urlencode($p['slug']) ?>">Edit</a>
        <?php else: ?><a class="mini" href="page-edit.php?file=<?= urlencode($p['slug']) ?>.html">Edit page</a><?php endif; ?>
        <a class="mini" href="../<?= h($p['slug']) ?>.html" target="_blank">View</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="flag"><input type="hidden" name="flag" value="hidden"><input type="hidden" name="slug" value="<?= h($p['slug']) ?>"><button class="mini"><?= $p['hidden'] ? 'Show' : 'Hide' ?></button></form>
        <?php if ($own): ?><a class="mini mini--danger" href="?action=delete&slug=<?= urlencode($p['slug']) ?>&_csrf=<?= urlencode(csrf_token()) ?>" onclick="return confirm('Delete this blog post? The page is removed from the site.')">Delete</a><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>

<?php else: ?>
<p style="color:#6b7280;margin-top:0;">Topics are the filter buttons on the Blog page. A topic shows up there once at least one post uses it.</p>
<?php foreach ($cats as $c): ?>
  <div class="card" style="padding:1rem 1.25rem;">
    <form method="post" style="display:grid;grid-template-columns:1fr 2fr auto auto;gap:.6rem;align-items:center;">
      <?= csrf_field() ?><input type="hidden" name="cat" value="<?= h($c['slug']) ?>">
      <input type="text" name="label" value="<?= h($c['label']) ?>" aria-label="Topic name">
      <input type="text" name="desc" value="<?= h($c['desc'] ?? '') ?>" placeholder="Short description (optional)" aria-label="Description">
      <button class="btn" name="action" value="cat_save"><?= (int) ($counts[$c['slug']] ?? 0) ?> post<?= ($counts[$c['slug']] ?? 0) === 1 ? '' : 's' ?> · Save</button>
      <button class="btn btn--ghost" name="action" value="cat_delete" onclick="return confirm('Delete this topic?')">Delete</button>
    </form>
  </div>
<?php endforeach; ?>
<div class="card" style="padding:1rem 1.25rem;">
  <form method="post" style="display:grid;grid-template-columns:1fr 2fr auto;gap:.6rem;align-items:center;">
    <?= csrf_field() ?><input type="hidden" name="action" value="cat_add">
    <input type="text" name="label" placeholder="New topic name" required aria-label="New topic name">
    <input type="text" name="desc" placeholder="Short description (optional)" aria-label="Description">
    <button class="btn">+ Add topic</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

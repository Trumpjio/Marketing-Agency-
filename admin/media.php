<?php
require_once __DIR__ . '/config.php';
if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);

// JSON API — list all uploaded images
if (isset($_GET['list'])) {
    require_login();
    header('Content-Type: application/json');
    $files = glob(UPLOADS_DIR . '/*');
    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
    $out = [];
    foreach ($files as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp','svg'])) {
            $out[] = 'uploads/' . basename($f);
        }
    }
    echo json_encode($out);
    exit;
}

// In-place upload used by the picker pop-up. Returns JSON, never redirects.
if (isset($_GET['ajax']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    header('Content-Type: application/json');
    if (!rate_limit('upload', 60, 600)) {
        echo json_encode(['error' => 'Too many uploads, please wait a few minutes.']); exit;
    }
    $f = $_FILES['file'] ?? null;
    $v = validate_upload($f);
    if (!$v['ok']) { echo json_encode(['error' => $v['error']]); exit; }
    $base = slugify(pathinfo($f['name'], PATHINFO_FILENAME)) ?: 'image';
    $safe = "$base." . $v['ext']; $i = 1;
    while (file_exists(UPLOADS_DIR . '/' . $safe)) { $safe = "$base-$i." . $v['ext']; $i++; }
    move_uploaded_file($f['tmp_name'], UPLOADS_DIR . '/' . $safe);
    @chmod(UPLOADS_DIR . '/' . $safe, 0644);
    echo json_encode(['path' => 'uploads/' . $safe]);
    exit;
}

require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file'])) {
    csrf_check();
    if (!rate_limit('upload', 60, 600)) {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Too many uploads, please wait a few minutes.'];
        header('Location: media.php'); exit;
    }
    $f = $_FILES['file'];
    $v = validate_upload($f);
    if ($v['ok']) {
        $base = slugify(pathinfo($f['name'], PATHINFO_FILENAME)) ?: 'image';
        $safe = "$base." . $v['ext'];
        $i = 1;
        while (file_exists(UPLOADS_DIR . '/' . $safe)) {
            $safe = "$base-$i." . $v['ext']; $i++;
        }
        move_uploaded_file($f['tmp_name'], UPLOADS_DIR . '/' . $safe);
        @chmod(UPLOADS_DIR . '/' . $safe, 0644);
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Uploaded: $safe"];
    } else {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => $v['error']];
    }
    header('Location: media.php');
    exit;
}

if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['file'])) {
    // Delete via GET is kept for the existing admin UI; wrap in POST would be
    // better long-term. For now, require a valid CSRF token in the URL.
    if (($_GET['_csrf'] ?? '') !== csrf_token()) { http_response_code(403); exit('Forbidden.'); }
    $file = UPLOADS_DIR . '/' . basename($_GET['file']);
    // Only delete real files inside uploads/ — never follow symlinks or ..
    $real = realpath($file);
    if ($real && str_starts_with($real, realpath(UPLOADS_DIR) . '/') && is_file($real)) {
        unlink($real);
    }
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Image deleted.'];
    header('Location: media.php');
    exit;
}

$files = glob(UPLOADS_DIR . '/*');
usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
?>

<h2>Media Library</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">Upload images here, then use the <strong>Pick image</strong> button on any page to insert them. No need to type paths.</p>

<div class="card">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="field">
      <label>Upload a new image (JPG, PNG, WebP, GIF, SVG — max 10 MB)</label>
      <input type="file" name="file" accept="image/*" required>
    </div>
    <button type="submit" class="btn">Upload</button>
  </form>
</div>

<?php if (empty($files)): ?>
  <p style="color:#6b7280;">No images uploaded yet.</p>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;">
    <?php foreach ($files as $file):
      $name = basename($file);
      $path = 'uploads/' . $name;
    ?>
      <div style="border:1px solid var(--line);border-radius:8px;overflow:hidden;background:#fff;">
        <img src="../<?= h($path) ?>" alt="" style="width:100%;height:110px;object-fit:cover;display:block;">
        <div style="padding:0.5rem;">
          <input type="text" value="<?= h($path) ?>" onclick="this.select()" readonly style="width:100%;font-size:0.7rem;border:0;padding:0.3rem;background:#f9fafb;border-radius:4px;">
          <div style="display:flex;justify-content:space-between;margin-top:0.4rem;">
            <a href="../<?= h($path) ?>" target="_blank" style="color:#635bff;font-size:0.7rem;text-decoration:none;">View</a>
            <a href="?action=delete&file=<?= urlencode($name) ?>&_csrf=<?= urlencode(csrf_token()) ?>" onclick="return confirm('Delete this image?')" style="color:#dc2626;font-size:0.7rem;text-decoration:none;">Delete</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

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

require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file'])) {
    $f = $_FILES['file'];
    if ($f['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','svg'];
        if (in_array($ext, $allowed)) {
            $safe = slugify(pathinfo($f['name'], PATHINFO_FILENAME)) . '.' . $ext;
            $target = UPLOADS_DIR . '/' . $safe;
            $i = 1;
            while (file_exists($target)) {
                $safe = slugify(pathinfo($f['name'], PATHINFO_FILENAME)) . "-$i.$ext";
                $target = UPLOADS_DIR . '/' . $safe;
                $i++;
            }
            move_uploaded_file($f['tmp_name'], $target);
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Uploaded: $safe"];
        } else {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => "File type not allowed."];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => "Upload failed."];
    }
    header('Location: media.php');
    exit;
}

if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['file'])) {
    $file = UPLOADS_DIR . '/' . basename($_GET['file']);
    if (file_exists($file)) unlink($file);
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
    <div class="field">
      <label>Upload a new image (JPG, PNG, WebP, GIF, SVG)</label>
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
            <a href="?action=delete&file=<?= urlencode($name) ?>" onclick="return confirm('Delete this image?')" style="color:#dc2626;font-size:0.7rem;text-decoration:none;">Delete</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

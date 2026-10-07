<?php require_once __DIR__ . '/_layout.php';

if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);

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
  <div class="media-grid">
    <?php foreach ($files as $file):
      $name = basename($file);
      $path = 'uploads/' . $name;
    ?>
      <div class="media-item">
        <img src="../<?= h($path) ?>" alt="">
        <div class="meta">
          <input type="text" value="<?= h($path) ?>" onclick="this.select()" readonly style="width:100%;border:0;background:transparent;">
          <div style="display:flex;justify-content:space-between;margin-top:0.3rem;">
            <a href="../<?= h($path) ?>" target="_blank" style="color:#635bff;font-size:0.7rem;text-decoration:none;">View</a>
            <a href="?action=delete&file=<?= urlencode($name) ?>" onclick="return confirm('Delete this image?')" style="color:#dc2626;font-size:0.7rem;text-decoration:none;">Delete</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

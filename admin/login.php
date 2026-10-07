<?php
require_once __DIR__ . '/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['password'] ?? '') === $ADMIN_PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Wrong password.';
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login — OrcaPulse</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', -apple-system, sans-serif; background: #f7f9fc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
  .box { background: #fff; padding: 2.5rem; border-radius: 10px; width: 100%; max-width: 360px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
  h1 { font-size: 1.1rem; font-weight: 800; margin: 0 0 1.5rem; }
  h1 span { color: #635bff; }
  label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; }
  input { width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 6px; font: inherit; box-sizing: border-box; }
  button { width: 100%; padding: 0.75rem; background: #635bff; color: white; border: 0; border-radius: 6px; font: inherit; font-weight: 600; margin-top: 1rem; cursor: pointer; }
  button:hover { background: #4e46d9; }
  .err { color: #991b1b; background: #fee2e2; padding: 0.6rem; border-radius: 6px; margin-top: 1rem; font-size: 0.875rem; }
</style>
</head>
<body>
<form class="box" method="post">
  <h1>ORCA<span>PULSE</span> Admin</h1>
  <label>Password</label>
  <input type="password" name="password" autofocus required>
  <button type="submit">Log in</button>
  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
</form>
</body>
</html>

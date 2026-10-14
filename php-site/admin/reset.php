<?php
require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$valid = $token !== '' && reset_token_valid($token);
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    csrf_check();
    if (!rate_limit('reset', 10, 900)) {
        $error = 'Too many requests. Please wait 15 minutes.';
        $valid = false;
    }
    $pw1 = $_POST['password1'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (!$error && strlen($pw1) < 10) {
        $error = 'Password must be at least 10 characters.';
    } elseif ($pw1 !== $pw2) {
        $error = 'Passwords do not match.';
    } else {
        if (admin_update_password($pw1) && reset_token_consume($token)) {
            $success = true;
        } else {
            $error = 'Could not update password — the admin/config.php file may not be writable by the web server. Set it to 0644 in Hostinger File Manager and try again.';
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password — OrcaPulse Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', -apple-system, sans-serif; background: #f7f9fc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1rem; }
  .box { background: #fff; padding: 2.25rem; border-radius: 12px; width: 100%; max-width: 420px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
  h1 { font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem; }
  h1 span { color: #635bff; }
  h2 { font-size: 1.3rem; font-weight: 700; margin: 0 0 0.75rem; }
  p { color: #6b7280; font-size: 0.9rem; line-height: 1.5; margin: 0 0 1rem; }
  label { display: block; font-size: 0.875rem; font-weight: 600; margin: 1rem 0 0.4rem; }
  input { width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; font: inherit; box-sizing: border-box; }
  button { width: 100%; padding: 0.75rem; background: #1d1d1f; color: white; border: 0; border-radius: 980px; font: inherit; font-weight: 600; margin-top: 1rem; cursor: pointer; }
  button:hover { background: #635bff; }
  .err { color: #991b1b; background: #fee2e2; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
  .ok { color: #065f46; background: #d1fae5; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
  .back { text-align: center; margin-top: 1.25rem; font-size: 0.8125rem; }
  .back a { color: #635bff; text-decoration: none; }
  .req { color: #6b7280; font-size: 0.75rem; margin-top: 0.4rem; }
</style>
</head>
<body>
<div class="box">
  <h1>ORCA<span>PULSE</span> Admin</h1>
  <?php if ($success): ?>
    <h2>Password changed</h2>
    <p>Your new password is active. You can sign in now.</p>
    <div class="back"><a href="login.php">→ Go to sign in</a></div>
  <?php elseif (!$valid): ?>
    <h2>This link is no longer valid</h2>
    <p>Reset links expire after 1 hour and can only be used once. Please request a new link.</p>
    <div class="back"><a href="forgot.php">→ Request a new reset link</a></div>
  <?php else: ?>
    <h2>Set a new password</h2>
    <p>Choose a strong password. At least 10 characters. Mix letters, numbers and symbols where you can.</p>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <label>New password</label>
      <input type="password" name="password1" required minlength="10" autofocus>
      <div class="req">Minimum 10 characters.</div>
      <label>Confirm new password</label>
      <input type="password" name="password2" required minlength="10">
      <button type="submit">Change password</button>
    </form>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="back"><a href="login.php">← back to sign in</a></div>
  <?php endif; ?>
</div>
</body>
</html>

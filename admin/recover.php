<?php
require_once __DIR__ . '/config.php';

$recoveryFile = DATA_DIR . '/.recovery';
$fileExists = file_exists($recoveryFile);
$error = '';
$step = 'verify';   // verify -> change -> done
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!rate_limit('recover', 10, 900)) {
        $error = 'Too many requests. Please wait 15 minutes.';
    } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'verify') {
        $provided = trim($_POST['code'] ?? '');
        if (!$fileExists) {
            $error = 'The recovery file does not exist on the server. Create data/.recovery first via Hostinger File Manager (any text you choose).';
        } else {
            $stored = trim(file_get_contents($recoveryFile));
            if ($stored === '') {
                $error = 'The recovery file exists but is empty. Open it in File Manager and add any text, then try again.';
            } elseif (hash_equals($stored, $provided)) {
                $_SESSION['recovery_verified_at'] = time();
                $step = 'change';
            } else {
                $error = 'That text does not match the contents of data/.recovery. Open the file in File Manager and copy the text exactly.';
            }
        }
    } elseif ($action === 'change') {
        // Must have verified within the last 10 minutes
        if (empty($_SESSION['recovery_verified_at']) || (time() - $_SESSION['recovery_verified_at']) > 600) {
            $error = 'Verification expired. Please start again.';
            $step = 'verify';
            unset($_SESSION['recovery_verified_at']);
        } else {
            $pw1 = $_POST['password1'] ?? '';
            $pw2 = $_POST['password2'] ?? '';
            if (strlen($pw1) < 10) { $error = 'Password must be at least 10 characters.'; $step = 'change'; }
            elseif ($pw1 !== $pw2) { $error = 'Passwords do not match.'; $step = 'change'; }
            elseif (!admin_update_password($pw1)) {
                $error = 'Could not update admin/config.php. Make sure it is writable by the web server (0644 permissions in Hostinger).';
                $step = 'change';
            } else {
                // Delete the recovery file so the same code cannot be reused
                @unlink($recoveryFile);
                unset($_SESSION['recovery_verified_at']);
                $success = true;
                $step = 'done';
            }
        }
    }
    } // end rate-limit else
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>File-based Recovery — OrcaPulse Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', -apple-system, sans-serif; background: #f7f9fc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1rem; }
  .box { background: #fff; padding: 2.25rem; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
  h1 { font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem; }
  h1 span { color: #635bff; }
  h2 { font-size: 1.3rem; font-weight: 700; margin: 0 0 0.75rem; }
  p { color: #6b7280; font-size: 0.9rem; line-height: 1.5; margin: 0 0 1rem; }
  label { display: block; font-size: 0.875rem; font-weight: 600; margin: 1rem 0 0.4rem; }
  input, textarea { width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; font: inherit; box-sizing: border-box; font-family: inherit; }
  textarea { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 0.825rem; }
  button { width: 100%; padding: 0.75rem; background: #1d1d1f; color: white; border: 0; border-radius: 980px; font: inherit; font-weight: 600; margin-top: 1rem; cursor: pointer; }
  button:hover { background: #635bff; }
  .err { color: #991b1b; background: #fee2e2; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
  .ok { color: #065f46; background: #d1fae5; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
  .back { text-align: center; margin-top: 1.25rem; font-size: 0.8125rem; }
  .back a { color: #635bff; text-decoration: none; }
  code { background: #f3f4f6; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.825rem; }
  .steps { background: #f9fafb; padding: 0.9rem 1rem 0.9rem 2rem; border-radius: 8px; font-size: 0.825rem; line-height: 1.65; color: #4b5563; margin: 0 0 0.5rem; }
  .req { color: #6b7280; font-size: 0.75rem; margin-top: 0.4rem; }
</style>
</head>
<body>
<div class="box">
  <h1>ORCA<span>PULSE</span> Admin</h1>

  <?php if ($step === 'done' && $success): ?>
    <h2>Password changed</h2>
    <p>Your new password is active. The recovery file has been deleted.</p>
    <div class="back"><a href="login.php">→ Go to sign in</a></div>

  <?php elseif ($step === 'change'): ?>
    <h2>Set a new password</h2>
    <p>Verified. Choose a strong password — at least 10 characters.</p>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="change">
      <label>New password</label>
      <input type="password" name="password1" required minlength="10" autofocus>
      <div class="req">Minimum 10 characters.</div>
      <label>Confirm new password</label>
      <input type="password" name="password2" required minlength="10">
      <button type="submit">Change password</button>
    </form>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php else: /* verify step */ ?>
    <h2>File-based recovery</h2>
    <p>This proves you have server access, without needing email.</p>
    <ol class="steps">
      <li>Open Hostinger File Manager (or FTP).</li>
      <li>Create a file at <code>data/.recovery</code> containing any text — a random phrase, your favourite word, anything.</li>
      <li>Come back here, paste <strong>exactly the same text</strong>, and click Verify.</li>
    </ol>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="verify">
      <label>Contents of <code>data/.recovery</code></label>
      <textarea name="code" rows="3" required autofocus placeholder="Paste the exact text you put in the file"></textarea>
      <button type="submit">Verify</button>
    </form>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="back"><a href="login.php">← back to sign in</a></div>
  <?php endif; ?>
</div>
</body>
</html>

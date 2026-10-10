<?php
require_once __DIR__ . '/config.php';

$flash = '';
$flashType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    // Neutral response regardless of whether the email matches, so the form
    // does not reveal which address is the admin's.
    $generic = 'If that email matches the admin account, a reset link has been sent. Check your inbox (and spam folder) within the next few minutes.';

    if (!rate_limit('forgot', 5, 900)) {
        $flash = 'Too many requests. Please wait 15 minutes.';
        $flashType = 'err';
    } elseif (honeypot_tripped()) {
        $flash = $generic;
        $flashType = 'ok';
    } elseif ($email !== '' && strtolower($email) === strtolower($ADMIN_EMAIL)) {
        $token = reset_token_create();
        // Build the reset URL from the current request.
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'ioka.online';
        $dir = rtrim(dirname($_SERVER['REQUEST_URI']), '/');
        $resetUrl = "$scheme://$host$dir/reset.php?token=$token";

        $subject = 'OrcaPulse admin — password reset';
        $body    = "A password reset was requested for your OrcaPulse admin account.\n\n"
                 . "Click this link to set a new password (expires in 1 hour, single use):\n\n"
                 . $resetUrl . "\n\n"
                 . "If you did not request this, you can ignore this email.\n\n"
                 . "— OrcaPulse Admin";
        $headers = "From: $ADMIN_EMAIL\r\nReply-To: $ADMIN_EMAIL\r\nContent-Type: text/plain; charset=UTF-8";
        // Suppress any mail() warnings; we show the neutral message either way.
        @mail($ADMIN_EMAIL, $subject, $body, $headers);
        $flash = $generic;
        $flashType = 'ok';
    } else {
        $flash = $generic;
        $flashType = 'ok';
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password — OrcaPulse Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', -apple-system, sans-serif; background: #f7f9fc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1rem; }
  .box { background: #fff; padding: 2.25rem; border-radius: 12px; width: 100%; max-width: 440px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
  h1 { font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem; }
  h1 span { color: #635bff; }
  h2 { font-size: 1.3rem; font-weight: 700; margin: 0 0 0.75rem; }
  p { color: #6b7280; font-size: 0.9rem; line-height: 1.5; margin: 0 0 1rem; }
  label { display: block; font-size: 0.875rem; font-weight: 600; margin: 1rem 0 0.4rem; }
  input { width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; font: inherit; box-sizing: border-box; }
  button { width: 100%; padding: 0.75rem; background: #1d1d1f; color: white; border: 0; border-radius: 980px; font: inherit; font-weight: 600; margin-top: 1rem; cursor: pointer; }
  button:hover { background: #635bff; }
  .ok { color: #065f46; background: #d1fae5; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
  .alt { margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e5e7eb; }
  .alt h3 { font-size: 0.95rem; margin: 0 0 0.5rem; }
  .alt code { background: #f3f4f6; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.8rem; }
  .back { text-align: center; margin-top: 1.25rem; font-size: 0.8125rem; }
  .back a { color: #635bff; text-decoration: none; }
  .back a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="box">
  <h1>ORCA<span>PULSE</span> Admin</h1>
  <h2>Forgot password?</h2>
  <p>Enter the admin email address. If it matches, we'll send a password-reset link that works once and expires in 1 hour.</p>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <?= honeypot_field() ?>
    <label>Email address</label>
    <input type="email" name="email" required autofocus placeholder="hello@orcapulse.com">
    <button type="submit">Send reset link</button>
  </form>
  <?php if ($flash): ?><div class="ok"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

  <div class="alt">
    <h3>No email access? Use file-based recovery</h3>
    <p>If you can't receive email (for example, if your mailbox isn't set up on Hostinger yet), you can prove you own the server instead:</p>
    <ol style="color:#6b7280;font-size:0.875rem;line-height:1.65;padding-left:1.2rem;margin:0;">
      <li>Open Hostinger File Manager (or FTP).</li>
      <li>Create a new file at <code>data/.recovery</code> containing any text you choose &mdash; a random phrase.</li>
      <li>Go to <a href="recover.php" style="color:#635bff;">file-based recovery</a>, paste the exact text, and set your new password.</li>
    </ol>
  </div>

  <div class="back"><a href="login.php">← back to sign in</a></div>
</div>
</body>
</html>

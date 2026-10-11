<?php require_once __DIR__ . '/_layout.php';

$file = DATA_DIR . '/subscribers.json';
$subs = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

if (($_GET['export'] ?? '') === 'csv') {
    require_login();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orcapulse-subscribers.csv"');
    echo "email,created,source\n";
    foreach ($subs as $s) {
        echo '"' . str_replace('"', '""', $s['email']) . '",';
        echo '"' . ($s['created'] ?? '') . '",';
        echo '"' . str_replace('"', '""', $s['source'] ?? '') . "\"\n";
    }
    exit;
}

if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['email'])) {
    if (($_GET['_csrf'] ?? '') !== csrf_token()) { http_response_code(403); exit('Forbidden.'); }
    $email = strtolower($_GET['email']);
    $subs = array_values(array_filter($subs, fn($s) => strtolower($s['email']) !== $email));
    file_put_contents($file, json_encode($subs, JSON_PRETTY_PRINT));
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Subscriber removed.'];
    header('Location: subscribers.php'); exit;
}

// Sort newest first
usort($subs, fn($a, $b) => strcmp($b['created'] ?? '', $a['created'] ?? ''));
?>
<h2>Email Subscribers</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">People who opted into the OrcaPulse quarterly memo via the homepage. Export to CSV and import into your email provider (Mailchimp / Buttondown / Beehiiv) when ready.</p>

<div class="toolbar">
  <strong><?= count($subs) ?> subscribers</strong>
  <a href="?export=csv" class="btn btn--ghost">Export CSV</a>
</div>

<?php if (empty($subs)): ?>
  <p style="color:#9ca3af;">No subscribers yet. Once visitors sign up on the homepage they'll appear here.</p>
<?php else: ?>
  <div class="card" style="padding:0;overflow:hidden;">
    <table class="list" style="margin:0;">
      <thead><tr><th style="text-align:left;padding:0.75rem 1rem;">Email</th><th style="padding:0.75rem 1rem;">Signed up</th><th style="padding:0.75rem 1rem;">Source</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($subs as $s): ?>
        <tr>
          <td style="padding:0.5rem 1rem;"><?= h($s['email']) ?></td>
          <td style="padding:0.5rem 1rem;color:#6b7280;font-size:0.85rem;"><?= h(substr($s['created'] ?? '', 0, 10)) ?></td>
          <td style="padding:0.5rem 1rem;color:#6b7280;font-size:0.85rem;"><?= h($s['source'] ?? '') ?></td>
          <td style="padding:0.5rem 1rem;text-align:right;"><a href="?action=delete&email=<?= urlencode($s['email']) ?>&_csrf=<?= urlencode(csrf_token()) ?>" onclick="return confirm('Remove this subscriber?')" style="color:#dc2626;font-size:0.8rem;">Remove</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>

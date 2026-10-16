<?php
require_once __DIR__ . '/config.php';
require_login();

$file = DATA_DIR . '/leads/leads.json';
$leads = [];
if (is_file($file) && is_readable($file)) {
    $raw = file_get_contents($file);
    $decoded = $raw === false ? null : json_decode($raw, true);
    if (is_array($decoded)) {
        $leads = array_values(array_filter($decoded, 'is_array'));
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="orcapulse-leads-' . date('Y-m-d') . '.csv"');
    header('X-Content-Type-Options: nosniff');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Name', 'Email', 'Company', 'Website', 'Goals', 'Source']);
    foreach ($leads as $lead) {
        $row = [];
        foreach (['date', 'name', 'email', 'company', 'website', 'goals', 'source'] as $key) {
            $value = trim((string) ($lead[$key] ?? ''));
            // Guard spreadsheet users against CSV formula injection.
            if (preg_match('/^[\s]*[=+@-]/', $value)) $value = "'" . $value;
            $row[] = $value;
        }
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

require_once __DIR__ . '/_layout.php';
$total = count($leads);
$visible = array_slice(array_reverse($leads), 0, 100);
?>
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
  <div>
    <h2 style="margin-bottom:.35rem;">Enquiries</h2>
    <p style="margin:0;color:#6b7280;font-size:.9rem;">Private website enquiries captured from the public growth form.</p>
  </div>
  <?php if ($total > 0): ?>
    <a class="btn" href="leads.php?export=csv">Export CSV</a>
  <?php endif; ?>
</div>

<div class="card" style="display:flex;gap:2rem;align-items:center;flex-wrap:wrap;">
  <div>
    <div style="font-size:.75rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Total enquiries</div>
    <div style="font-size:1.8rem;font-weight:800;"><?= number_format($total) ?></div>
  </div>
  <div>
    <div style="font-size:.75rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Showing</div>
    <div style="font-size:1.8rem;font-weight:800;"><?= number_format(count($visible)) ?></div>
  </div>
</div>

<?php if (!$visible): ?>
  <div class="card">
    <h3 style="margin-top:0;">No enquiries yet</h3>
    <p style="margin-bottom:0;color:#6b7280;">New enquiries submitted through the website form will appear here after they have been saved successfully.</p>
  </div>
<?php else: ?>
  <div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
      <table class="list" style="min-width:760px;">
        <thead><tr><th>Date</th><th>Contact</th><th>Company</th><th>Request</th><th>Source</th></tr></thead>
        <tbody>
        <?php foreach ($visible as $lead): ?>
          <tr>
            <td><?= h($lead['date'] ?? '') ?></td>
            <td>
              <strong><?= h($lead['name'] ?? '') ?></strong><br>
              <a href="mailto:<?= h($lead['email'] ?? '') ?>"><?= h($lead['email'] ?? '') ?></a>
              <?php if (!empty($lead['website'])): ?><br><a href="<?= h($lead['website']) ?>" target="_blank" rel="noopener noreferrer"><?= h($lead['website']) ?></a><?php endif; ?>
            </td>
            <td><?= h($lead['company'] ?? '') ?></td>
            <td style="white-space:normal;min-width:240px;max-width:440px;">
              <?= nl2br(h($lead['goals'] ?? '')) ?>
            </td>
            <td><?= h($lead['source'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <p style="font-size:.8rem;color:#6b7280;">For privacy, this page is restricted to authenticated admins. Only the 100 newest enquiries are displayed; export includes all saved enquiries.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>

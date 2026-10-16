<?php
require_once __DIR__ . '/config.php';

function lead_redirect($state) {
    header('Location: ../index.html?sent=' . rawurlencode($state) . '#work-with-us', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed');
}

// Bots receive a neutral success response but their submission is never stored.
if (!empty($_POST['website_url'] ?? '')) {
    lead_redirect('1');
}

// Enforce the rate limiter instead of discarding its result.
if (!rate_limit('lead', 10, 600)) {
    lead_redirect('rate');
}

function lead_field($key, $maxBytes) {
    $raw = $_POST[$key] ?? '';
    if (!is_string($raw)) return null;
    $value = str_replace("\0", '', trim($raw));
    if (strlen($value) > $maxBytes) return null;
    return $value;
}

$name = lead_field('name', 240);
$email = lead_field('email', 254);
$company = lead_field('company', 320);
$website = lead_field('website', 2048);
$goals = lead_field('goals', 8000);
$source = lead_field('source', 100);

if (
    $name === null || $name === '' ||
    $email === null || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $company === null || $company === '' ||
    $goals === null || $goals === '' ||
    $website === null || $source === null
) {
    lead_redirect('invalid');
}

if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
    lead_redirect('invalid');
}

// Keep the stored lead list private under data/.htaccess and restrict file permissions.
$dir = DATA_DIR . '/leads';
if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
    error_log('OrcaPulse lead capture: could not create private lead directory.');
    lead_redirect('error');
}

$file = $dir . '/leads.json';
$handle = @fopen($file, 'c+');
if ($handle === false || !flock($handle, LOCK_EX)) {
    if (is_resource($handle)) fclose($handle);
    error_log('OrcaPulse lead capture: could not lock the lead store.');
    lead_redirect('error');
}

$raw = stream_get_contents($handle);
$all = ($raw === false || trim($raw) === '') ? [] : json_decode($raw, true);
if (!is_array($all)) {
    flock($handle, LOCK_UN);
    fclose($handle);
    error_log('OrcaPulse lead capture: existing lead store is not valid JSON; no data overwritten.');
    lead_redirect('error');
}

$all[] = [
    'date' => date('c'),
    'name' => strip_tags($name),
    'email' => $email,
    'company' => strip_tags($company),
    'website' => $website,
    'goals' => strip_tags($goals),
    'source' => preg_replace('/[^a-zA-Z0-9._-]/', '', $source),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
];

$json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$written = false;
if ($json !== false) {
    rewind($handle);
    $written = ftruncate($handle, 0) && fwrite($handle, $json) === strlen($json) && fflush($handle);
}
flock($handle, LOCK_UN);
fclose($handle);
@chmod($file, 0600);

if (!$written) {
    error_log('OrcaPulse lead capture: write to lead store failed.');
    lead_redirect('error');
}

lead_redirect('1');

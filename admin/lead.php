<?php
require_once __DIR__ . '/config.php';

function lead_response($state) {
    $messages = [
        '1' => 'Thank you. Your enquiry has been received.',
        'invalid' => 'Please review the required fields and check the website URL.',
        'rate' => 'Too many submissions were received from this connection. Please wait a few minutes or book a call directly.',
        'error' => 'We could not save your enquiry. Please try again or book a call directly.',
    ];
    $message = $messages[$state] ?? $messages['error'];
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    if (strpos($accept, 'application/json') !== false) {
        $codes = ['1' => 200, 'invalid' => 422, 'rate' => 429, 'error' => 500];
        http_response_code($codes[$state] ?? 500);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => $state === '1', 'state' => $state, 'message' => $message]);
        exit;
    }
    header('Location: ../contact.html?sent=' . rawurlencode($state) . '#contact-form-section', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed');
}

// Bots receive a neutral success response but their submission is never stored.
if (!empty($_POST['website_url'] ?? '')) {
    lead_response('1');
}

// Enforce the rate limiter instead of discarding its result.
if (!rate_limit('lead', 10, 600)) {
    lead_response('rate');
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
    lead_response('invalid');
}

if ($website !== '') {
    $websiteParts = parse_url($website);
    if (
        !filter_var($website, FILTER_VALIDATE_URL) ||
        !is_array($websiteParts) ||
        !in_array(strtolower($websiteParts['scheme'] ?? ''), ['http', 'https'], true)
    ) {
        lead_response('invalid');
    }
}

// Keep the stored lead list private under data/.htaccess and restrict file permissions.
$dir = DATA_DIR . '/leads';
if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
    error_log('OrcaPulse lead capture: could not create private lead directory.');
    lead_response('error');
}

@chmod($dir, 0700);
$file = $dir . '/leads.json';
$lockPath = $dir . '/.leads.lock';
$lockHandle = @fopen($lockPath, 'c');
if ($lockHandle === false || !flock($lockHandle, LOCK_EX)) {
    if (is_resource($lockHandle)) fclose($lockHandle);
    error_log('OrcaPulse lead capture: could not lock the lead store.');
    lead_response('error');
}
@chmod($lockPath, 0600);

$raw = is_file($file) ? file_get_contents($file) : '';
$all = ($raw === false || trim((string) $raw) === '') ? [] : json_decode($raw, true);
if (!is_array($all)) {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    error_log('OrcaPulse lead capture: existing lead store is not valid JSON; no data overwritten.');
    lead_response('error');
}

$all[] = [
    'date' => date('c'),
    'name' => strip_tags($name),
    'email' => $email,
    'company' => strip_tags($company),
    'website' => $website,
    'goals' => strip_tags($goals),
    'source' => preg_replace('/[^a-zA-Z0-9._-]/', '', $source),
];

$json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$written = false;
$tmp = false;
if ($json !== false) {
    $tmp = tempnam($dir, '.lead-store-');
    if ($tmp !== false) {
        $bytes = file_put_contents($tmp, $json, LOCK_EX);
        if ($bytes === strlen($json)) {
            @chmod($tmp, 0600);
            $written = @rename($tmp, $file);
        }
        if (!$written && is_file($tmp)) @unlink($tmp);
    }
}
flock($lockHandle, LOCK_UN);
fclose($lockHandle);

if (!$written) {
    error_log('OrcaPulse lead capture: atomic write to lead store failed.');
    lead_response('error');
}
@chmod($file, 0600);

// Optional email notification: only send when the owner has configured a
// verified recipient and a sender from the marketing site's own domain.
// The saved admin inbox is the system of record; notification failure never
// discards a valid enquiry.
$notifyTo = getenv('ORCAPULSE_LEAD_NOTIFY_EMAIL') ?: '';
$notifyFrom = getenv('ORCAPULSE_LEAD_NOTIFY_FROM') ?: '';
$fromDomain = strtolower(substr(strrchr($notifyFrom, '@') ?: '', 1));
if (
    $notifyTo !== '' &&
    $notifyFrom !== '' &&
    filter_var($notifyTo, FILTER_VALIDATE_EMAIL) &&
    filter_var($notifyFrom, FILTER_VALIDATE_EMAIL) &&
    $fromDomain === 'orcapulse.io' &&
    function_exists('mail')
) {
    $subject = 'New OrcaPulse website enquiry';
    $body = implode("\\n", [
        'A new enquiry was submitted through the Contact Us form.',
        '',
        'Name: ' . $name,
        'Email: ' . $email,
        'Company: ' . $company,
        'Website: ' . ($website !== '' ? $website : '(not provided)'),
        '',
        'What they want to improve:',
        $goals,
        '',
        'Source: ' . $source,
        'Received: ' . date('c'),
        '',
        'Review the authenticated admin Enquiries inbox for the saved record.'
    ]);
    $headers = [
        'From: OrcaPulse Website <' . $notifyFrom . '>',
        'Reply-To: ' . $email,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Content-Type-Options: nosniff'
    ];
    if (!@mail($notifyTo, $subject, $body, implode("\\r\\n", $headers))) {
        error_log('OrcaPulse lead capture: enquiry saved, but email notification could not be sent.');
    }
}

lead_response('1');

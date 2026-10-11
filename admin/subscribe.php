<?php
/**
 * Email subscriber capture endpoint.
 *
 * POST { email } -> appends to data/subscribers.json (array of
 * {email, created, source} rows). Returns JSON.
 *
 * Honeypot + rate-limit + CSRF-exempt (public endpoint).
 * You can later migrate to Mailchimp / Buttondown / Beehiiv by swapping
 * this file for an API call. For now, admin can read + export subscribers
 * from admin/subscribers.php.
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

// Rate limit — 20 per IP per 15 min
if (!rate_limit('subscribe', 20, 900)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again shortly.']);
    exit;
}

// Honeypot
if (!empty($_POST['website_url'] ?? '')) {
    // Fake success for bots
    echo json_encode(['ok' => true]);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a valid email address.']);
    exit;
}

// Normalise + de-duplicate
$email = strtolower($email);
$file = DATA_DIR . '/subscribers.json';
$subs = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
foreach ($subs as $s) {
    if (($s['email'] ?? '') === $email) {
        echo json_encode(['ok' => true, 'alreadySubscribed' => true]);
        exit;
    }
}
$subs[] = [
    'email'   => $email,
    'created' => date('c'),
    'source'  => trim($_POST['source'] ?? 'unknown'),
    'ip_hash' => substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '')), 0, 16),
];
file_put_contents($file, json_encode($subs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
@chmod($file, 0600);

echo json_encode(['ok' => true]);

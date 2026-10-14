<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../index.html'); exit; }
// Simple honeypot
if (!empty($_POST['website_url'])) { header('Location: ../index.html?sent=1'); exit; }
if (function_exists('rate_limit')) rate_limit('lead', 10, 600);
$name = trim(strip_tags((string) ($_POST['name'] ?? '')));
$email = trim((string) ($_POST['email'] ?? ''));
$company = trim(strip_tags((string) ($_POST['company'] ?? '')));
$website = trim(strip_tags((string) ($_POST['website'] ?? '')));
$goals = trim(strip_tags((string) ($_POST['goals'] ?? '')));
$source = trim(strip_tags((string) ($_POST['source'] ?? 'home-work-form')));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $company === '' || $goals === '') {
    header('Location: ../index.html?sent=0'); exit;
}
$dir = DATA_DIR . '/leads';
if (!is_dir($dir)) @mkdir($dir, 0755, true);
$row = [
    'date'    => date('Y-m-d H:i:s'),
    'name'    => $name, 'email' => $email, 'company' => $company,
    'website' => $website, 'goals' => $goals, 'source' => $source,
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
];
$file = $dir . '/leads.json';
$all = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
$all[] = $row;
file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
header('Location: ../index.html?sent=1#work-with-us');

<?php
/**
 * Admin config. Edit the password here (first time only).
 * After first login, you can leave this file.
 */
$ADMIN_PASSWORD = 'orcapulse2026';  // CHANGE THIS before deploying

define('DATA_DIR', __DIR__ . '/../data');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('SITE_ROOT', dirname(__DIR__));

session_start();

function settings_load() {
    $file = DATA_DIR . '/settings.json';
    return json_decode(file_get_contents($file), true);
}

function settings_save($data) {
    file_put_contents(DATA_DIR . '/settings.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function blogs_load() {
    $file = DATA_DIR . '/blogs.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function blogs_save($data) {
    file_put_contents(DATA_DIR . '/blogs.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function require_login() {
    if (empty($_SESSION['logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

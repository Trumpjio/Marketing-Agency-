<?php
/**
 * Admin config.
 *
 * Set ORCAPULSE_ADMIN_PASSWORD in the server environment for first-time setup.
 * On successful first login it is migrated to a password hash in data/.auth.json.
 * Never store or commit a plaintext admin password in this file.
 */
$ADMIN_PASSWORD = getenv('ORCAPULSE_ADMIN_PASSWORD') ?: '';  // Set securely in server environment during initialization.
$ADMIN_EMAIL    = getenv('ORCAPULSE_ADMIN_EMAIL') ?: 'help@orcapulse.ai';  // where password-reset emails are sent

define('DATA_DIR', __DIR__ . '/../data');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('SITE_ROOT', dirname(__DIR__));
define('CONFIG_FILE', __FILE__);
define('AUTH_FILE', DATA_DIR . '/.auth.json');

// ---------- Secure session cookie BEFORE session_start() ----------
if (!defined('OP_NO_SESSION') && session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,          // require HTTPS when live
        'httponly' => true,             // no JS access to session cookie
        'samesite' => 'Lax',            // CSRF defence-in-depth
    ]);
    session_start();
}

/* ---------- Password reset helpers ---------- */

function admin_update_password($newPassword) {
    // Store the password hash in data/.auth.json; never rewrite this PHP file.
    return auth_set_password($newPassword);
}

function reset_tokens_load() {
    $f = DATA_DIR . '/reset_tokens.json';
    if (!file_exists($f)) return [];
    return json_decode(file_get_contents($f), true) ?: [];
}

function reset_tokens_save($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) return false;
    $path = DATA_DIR . '/reset_tokens.json';
    $tmp = tempnam(DATA_DIR, '.reset-');
    if ($tmp === false) return false;
    $written = file_put_contents($tmp, $json, LOCK_EX);
    if ($written === false) { @unlink($tmp); return false; }
    @chmod($tmp, 0600);
    if (!@rename($tmp, $path)) { @unlink($tmp); return false; }
    @chmod($path, 0600);
    return true;
}

function reset_token_create() {
    $tokens = reset_tokens_load();
    // Expire anything older than 1 hour
    $now = time();
    $tokens = array_filter($tokens, fn($t) => ($t['expires'] ?? 0) > $now);
    $token = bin2hex(random_bytes(24));
    $tokens[$token] = ['created' => $now, 'expires' => $now + 3600, 'used' => false];
    if (!reset_tokens_save($tokens)) return false;
    return $token;
}

function reset_token_consume($token) {
    if (!is_string($token) || $token === '') return false;
    $tokens = reset_tokens_load();
    if (empty($tokens[$token])) return false;
    $t = $tokens[$token];
    if (($t['used'] ?? false) || ($t['expires'] ?? 0) < time()) return false;
    $tokens[$token]['used'] = true;
    return reset_tokens_save($tokens);
}

function reset_token_valid($token) {
    if (!is_string($token) || $token === '') return false;
    $tokens = reset_tokens_load();
    if (empty($tokens[$token])) return false;
    $t = $tokens[$token];
    return !($t['used'] ?? false) && ($t['expires'] ?? 0) >= time();
}

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

/* ---------- Design settings ---------- */

function design_defaults() {
    return [
        'primary'        => '#635bff',   // brand indigo
        'secondary'      => '#18bfff',   // brand cyan
        'bg_page'        => '#ffffff',
        'bg_surface'     => '#ffffff',
        'bg_soft'        => '#fbfbfd',
        'text_primary'   => '#1d1d1f',
        'text_secondary' => '#6e6e73',
        'border'         => 'rgba(0, 0, 0, 0.08)',
        'btn_primary_bg'   => '#1d1d1f',
        'btn_primary_text' => '#ffffff',
        'btn_radius'       => 980,       // px
        'heading_font'   => 'Manrope',   // Manrope, Inter, Playfair Display, Space Grotesk, Fraunces
        'body_font'      => 'Inter',     // Inter, Space Grotesk, IBM Plex Sans, Manrope
        'body_size'      => 16,          // px
        'line_height'    => 1.6,
        'letter_spacing' => -0.005,      // em
        'heading_weight' => 700,
        'grid_enabled'   => 1,
        'grid_opacity'   => 0.028,       // 0 - 0.1
        'grid_color'     => '#000000',
        'guide_enabled'    => 1,           // 1 = show the floating OrcaPulse Guide icon
        'hints_enabled'    => 1,           // 1 = allow the icon to pop up contextual hints
        'guide_where'      => 'all',       // all | home | insights | off
        'guide_trigger'    => 'inactivity',// inactivity (2s scroll stop) | click (click-only, never auto) | error (only when a form error appears)
        'guide_tail'       => 1,           // 1 = show the small pointer tail under the face
        'guide_face_color' => '#F8F7F2',   // icon body colour
        'guide_eye_color'  => '#111316',   // icon eye + mouth colour
        'tip_style'        => 'card',      // card | minimal
        'guide_size'       => 'md',        // sm | md | lg
        // Background presets
        'bg_preset'        => 'grid',      // grid | plain | dots | gradient | custom
        'bg_custom_css'    => '',          // raw CSS appended when bg_preset == custom (admin-only)
    ];
}

function design_load() {
    $file = DATA_DIR . '/design.json';
    if (!file_exists($file)) return design_defaults();
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? array_merge(design_defaults(), $d) : design_defaults();
}

function design_save($data) {
    file_put_contents(DATA_DIR . '/design.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    design_write_css($data);
}

function design_write_css($d) {
    $d = array_merge(design_defaults(), $d);
    // Font families already defined elsewhere; we only override via CSS variables.
    $css  = "/* Generated by admin/design.php. Do not edit by hand — your changes will be overwritten. */\n";
    // Every page already loads Inter and Manrope. Any other chosen font is loaded here, only when it is chosen.
    $extra = [];
    foreach ([$d['heading_font'], $d['body_font']] as $f) {
        if (!in_array($f, ['Inter', 'Manrope'], true) && !in_array($f, $extra, true) && preg_match('/^[A-Za-z ]+$/', $f)) $extra[] = $f;
    }
    foreach ($extra as $f) {
        $css .= "@import url('https://fonts.googleapis.com/css2?family=" . str_replace(' ', '+', $f) . ":wght@400;500;600;700&display=swap');\n";
    }
    $css .= ":root {\n";
    $css .= "  --blue: " . $d['primary'] . ";\n";
    $css .= "  --cyan: " . $d['secondary'] . ";\n";
    $css .= "  --blue-deep: " . $d['primary'] . ";\n";
    $css .= "  --ap-bg: " . $d['bg_page'] . ";\n";
    $css .= "  --ap-bg-soft: " . $d['bg_soft'] . ";\n";
    $css .= "  --surface: " . $d['bg_surface'] . ";\n";
    $css .= "  --ap-ink: " . $d['text_primary'] . ";\n";
    $css .= "  --ink: " . $d['text_primary'] . ";\n";
    $css .= "  --ap-muted: " . $d['text_secondary'] . ";\n";
    $css .= "  --muted: " . $d['text_secondary'] . ";\n";
    $css .= "  --ap-line: " . $d['border'] . ";\n";
    $css .= "  --line: " . $d['border'] . ";\n";
    $css .= "}\n\n";
    $css .= "/* Base body typography */\n";
    $css .= "body { font-size: " . intval($d['body_size']) . "px; line-height: " . floatval($d['line_height']) . "; letter-spacing: " . floatval($d['letter_spacing']) . "em; font-family: '" . h($d['body_font']) . "', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }\n";
    $css .= "h1, h2, h3, .section-head h2, .hero h1 { font-family: '" . h($d['heading_font']) . "', 'Manrope', sans-serif; font-weight: " . intval($d['heading_weight']) . "; }\n";
    $css .= "\n/* Buttons */\n";
    $css .= ".btn { border-radius: " . intval($d['btn_radius']) . "px; }\n";
    $css .= ".btn--primary { background: " . $d['btn_primary_bg'] . "; color: " . $d['btn_primary_text'] . "; }\n";
    $css .= ".btn--primary:hover { background: " . $d['primary'] . "; }\n";
    $css .= ".cta .btn--primary { background: " . $d['btn_primary_bg'] . "; color: " . $d['btn_primary_text'] . "; }\n";
    $css .= ".cta .btn--primary:hover { background: " . $d['primary'] . "; }\n";
    $css .= "\n/* Background preset */\n";
    $preset = $d['bg_preset'] ?? 'grid';
    // Shared helpers
    $hex = ltrim($d['grid_color'], '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    $gr = hexdec(substr($hex, 0, 2));
    $gg = hexdec(substr($hex, 2, 2));
    $gb = hexdec(substr($hex, 4, 2));
    $op = max(0, min(0.1, floatval($d['grid_opacity'])));
    switch ($preset) {
        case 'plain':
            // No decoration at all
            $css .= ".page::before { display: none !important; }\n";
            break;
        case 'dots':
            $css .= ".page::before { background-image: radial-gradient(circle, rgba($gr, $gg, $gb, " . max($op, 0.05) . ") 1.2px, transparent 1.2px) !important; background-size: 24px 24px !important; }\n";
            break;
        case 'gradient':
            $css .= ".page::before { background-image: radial-gradient(1200px 600px at 10% 0%, rgba(" . hexdec(substr(ltrim($d['primary'],'#'),0,2)) . "," . hexdec(substr(ltrim($d['primary'],'#'),2,2)) . "," . hexdec(substr(ltrim($d['primary'],'#'),4,2)) . ",0.06), transparent 60%), radial-gradient(900px 500px at 90% 10%, rgba(" . hexdec(substr(ltrim($d['secondary'],'#'),0,2)) . "," . hexdec(substr(ltrim($d['secondary'],'#'),2,2)) . "," . hexdec(substr(ltrim($d['secondary'],'#'),4,2)) . ",0.06), transparent 60%) !important; background-size: auto !important; mask-image: none !important; -webkit-mask-image: none !important; }\n";
            break;
        case 'custom':
            $raw = (string)($d['bg_custom_css'] ?? '');
            // Strip anything that smells like script to keep CSP intact
            $raw = preg_replace('/<\s*\/?\s*(script|style)[^>]*>/i', '', $raw);
            $raw = str_replace(['expression(', 'javascript:'], '', $raw);
            $css .= "/* Custom background CSS (admin-authored) */\n" . $raw . "\n";
            break;
        case 'grid':
        default:
            if (!$d['grid_enabled']) {
                $css .= ".page::before { display: none !important; }\n";
            } else {
                $css .= ".page::before { background-image: linear-gradient(rgba($gr, $gg, $gb, $op) 1px, transparent 1px), linear-gradient(90deg, rgba($gr, $gg, $gb, $op) 1px, transparent 1px); }\n";
            }
            break;
    }
    file_put_contents(SITE_ROOT . '/styles/custom-design.css', $css);
    design_write_js($d);
}

function design_write_js($d) {
    $d = array_merge(design_defaults(), $d);
    $enabled   = empty($d['guide_enabled']) ? 'false' : 'true';
    $hints     = empty($d['hints_enabled']) ? 'false' : 'true';
    $tail      = empty($d['guide_tail'])    ? 'false' : 'true';
    $trigger   = json_encode($d['guide_trigger'] ?? 'inactivity');
    $where     = json_encode($d['guide_where'] ?? 'all');
    $faceBg    = json_encode($d['guide_face_color'] ?? '#F8F7F2');
    $faceInk   = json_encode($d['guide_eye_color']  ?? '#111316');
    $tipStyle  = json_encode($d['tip_style'] ?? 'card');
    $size      = json_encode($d['guide_size'] ?? 'md');
    $js  = "/* Generated by admin/design.php — do not edit by hand. */\n";
    $js .= "window.OP_GUIDE = { enabled: $enabled, hintsEnabled: $hints, trigger: $trigger, tail: $tail, where: $where, faceColor: $faceBg, eyeColor: $faceInk, tipStyle: $tipStyle, size: $size };\n";
    // Back-compat for the simple on/off flag main.js previously checked.
    $js .= "window.OP_GUIDE_ENABLED = $enabled;\n";
    file_put_contents(SITE_ROOT . '/scripts/custom-config.js', $js);
}

/* ---------- Authentication (password hashing + rate-limit + CSRF) ---------- */

function auth_load() {
    if (!file_exists(AUTH_FILE)) return [];
    $d = json_decode(file_get_contents(AUTH_FILE), true);
    return is_array($d) ? $d : [];
}

function auth_save($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) return false;
    $tmp = tempnam(DATA_DIR, '.auth-');
    if ($tmp === false) return false;
    $written = file_put_contents($tmp, $json, LOCK_EX);
    if ($written === false) { @unlink($tmp); return false; }
    @chmod($tmp, 0600);
    if (!@rename($tmp, AUTH_FILE)) { @unlink($tmp); return false; }
    @chmod(AUTH_FILE, 0600);
    return true;
}

/**
 * Check a submitted password. First successful login migrates the server-
 * configured initial password into a hash in data/.auth.json.
 */
function auth_check($submitted, $plainTextFallback) {
    if (!is_string($submitted)) return false;
    $auth = auth_load();
    // Prefer the stored hash
    if (!empty($auth['hash'])) {
        if (password_verify($submitted, $auth['hash'])) {
            // Rehash if PHP's defaults have moved on
            if (password_needs_rehash($auth['hash'], PASSWORD_DEFAULT)) {
                $auth['hash'] = password_hash($submitted, PASSWORD_DEFAULT);
                auth_save($auth);
            }
            return true;
        }
        return false;
    }
    // First-time setup requires an explicitly configured server-side password.
    if (!is_string($plainTextFallback) || $plainTextFallback === '' || !is_string($submitted)) return false;
    if (!hash_equals($plainTextFallback, $submitted)) return false;
    return auth_save(['hash' => password_hash($submitted, PASSWORD_DEFAULT)]);
}

/**
 * Change the stored password. Writes to data/.auth.json so admin/config.php
 * stays simple and never has to be rewritten by the web server.
 */
function auth_set_password($new) {
    if (!is_string($new) || strlen($new) < 10) return false;
    $auth = auth_load();
    $auth['hash'] = password_hash($new, PASSWORD_DEFAULT);
    return auth_save($auth);
}

/** Replace the old file-rewriting approach with the hash-store approach. */
if (!function_exists('admin_set_password')) {
    function admin_set_password($newPassword) {
        return auth_set_password($newPassword);
    }
}

/* ---------- CSRF ---------- */

function csrf_token() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}
function csrf_check() {
    $t = $_POST['_csrf'] ?? '';
    if (!$t || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $t)) {
        http_response_code(403);
        die('<!doctype html><meta charset=utf-8><title>Request rejected</title><body style="font:16px/1.5 system-ui;padding:2rem;max-width:40ch;margin:auto;color:#111"><h1>Request rejected</h1><p>Your form session expired or the request looked forged. Please go back and submit the form again.</p>');
    }
}

/* ---------- Rate limiting (file-based, per-IP) ---------- */

function rate_limit($bucket, $maxAttempts = 10, $windowSec = 900) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = DATA_DIR . '/.rate_' . preg_replace('/[^a-z0-9_]/', '', $bucket) . '.json';
    $now = time();
    $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    // Prune anything older than the window
    foreach ($data as $k => $hits) {
        $data[$k] = array_values(array_filter($hits, fn($t) => $t > $now - $windowSec));
        if (empty($data[$k])) unset($data[$k]);
    }
    $data[$ip] = $data[$ip] ?? [];
    if (count($data[$ip]) >= $maxAttempts) {
        file_put_contents($file, json_encode($data));
        return false;
    }
    $data[$ip][] = $now;
    file_put_contents($file, json_encode($data));
    @chmod($file, 0600);
    return true;
}

/** Clear rate-limit hits for the current IP in this bucket (on successful auth). */
function rate_limit_clear($bucket) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = DATA_DIR . '/.rate_' . preg_replace('/[^a-z0-9_]/', '', $bucket) . '.json';
    if (!file_exists($file)) return;
    $data = json_decode(file_get_contents($file), true) ?: [];
    unset($data[$ip]);
    file_put_contents($file, json_encode($data));
}

/* ---------- Secure session regeneration ---------- */

function session_login() {
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
}

/* ---------- Honeypot: a hidden field name real users leave blank ---------- */

const HONEYPOT_FIELD = 'website_url';   // bots fill URL-shaped fields

function honeypot_field() {
    return '<div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;" aria-hidden="true"><label>Leave this field empty <input type="text" name="' . HONEYPOT_FIELD . '" value="" tabindex="-1" autocomplete="off"></label></div>';
}
function honeypot_tripped() {
    return !empty($_POST[HONEYPOT_FIELD]);
}

/* ---------- Safe file upload validation ---------- */

/**
 * Returns ['ok' => true, 'ext' => 'jpg'] or ['ok' => false, 'error' => '...']
 * Verifies the real MIME via finfo, not just the extension, and refuses
 * anything that could be executed.
 */
function validate_upload($file) {
    if (!$file || ($file['error'] ?? -1) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed.'];
    }
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'File is larger than 10 MB.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg','jpeg','png','gif','webp','svg'];
    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'error' => 'Only JPG, PNG, WebP, GIF or SVG allowed.'];
    }
    // Verify real content type
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
    $mime  = $finfo ? finfo_file($finfo, $file['tmp_name']) : (mime_content_type($file['tmp_name']) ?: '');
    if ($finfo) finfo_close($finfo);
    $okMimes = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml','text/plain']; // text/plain tolerated for SVG on some servers
    if (!in_array($mime, $okMimes, true)) {
        return ['ok' => false, 'error' => 'That file does not look like a real image (detected: ' . h($mime) . ').'];
    }
    // SVGs: strip <script> tags before accepting (prevents XSS via SVG)
    if ($ext === 'svg') {
        $svg = file_get_contents($file['tmp_name']);
        if (preg_match('/<\s*script/i', $svg) || preg_match('/on\w+\s*=/i', $svg) || stripos($svg, 'javascript:') !== false) {
            return ['ok' => false, 'error' => 'SVG contains script content. Export the SVG without scripts and try again.'];
        }
    }
    return ['ok' => true, 'ext' => $ext];
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

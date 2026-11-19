<?php
declare(strict_types=1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'isp_app');
define('DB_PASS', getenv('DB_PASS') ?: 'ChangeMe_Str0ng!');
define('DB_NAME', getenv('DB_NAME') ?: 'isproject1');

// Security headers (scores well on securityheaders.com)
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; frame-ancestors 'none'");
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params(['httponly' => true, 'secure' => $https, 'samesite' => 'Strict']);
session_start();

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }

function csrf_field(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403); exit('Invalid CSRF token.');
    }
}
function audit(?int $uid, string $action): void {
    global $db;
    $s = $db->prepare('INSERT INTO audit_log (user_id, action, ip) VALUES (?,?,?)');
    $ip = ip(); $s->bind_param('iss', $uid, $action, $ip); $s->execute();
}
function require_login(string $role = 'student'): void {
    if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; }
    if ($role === 'admin' && ($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit('Forbidden'); }
}
// Brute-force protection: 5 failures / 15 min per email or IP
function is_locked(string $email): bool {
    global $db;
    $s = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE (email=? OR ip=?) AND created_at > (NOW() - INTERVAL 15 MINUTE)');
    $ip = ip(); $s->bind_param('ss', $email, $ip); $s->execute();
    return (int)$s->get_result()->fetch_row()[0] >= 5;
}
function record_fail(string $email): void {
    global $db;
    $s = $db->prepare('INSERT INTO login_attempts (email, ip) VALUES (?,?)');
    $ip = ip(); $s->bind_param('ss', $email, $ip); $s->execute();
}
function attempt_login(string $email, string $pass, bool $adminOnly = false): string {
    global $db;
    if (is_locked($email)) return 'Too many failed attempts. Try again in 15 minutes.';
    $s = $db->prepare('SELECT id, full_name, password_hash, role FROM users WHERE email=?');
    $s->bind_param('s', $email); $s->execute();
    $u = $s->get_result()->fetch_assoc();
    if (!$u || !password_verify($pass, $u['password_hash']) || ($adminOnly && $u['role'] !== 'admin')) {
        record_fail($email); audit(null, "Failed login: $email");
        return 'Invalid email or password.';
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id']; $_SESSION['name'] = $u['full_name']; $_SESSION['role'] = $u['role'];
    audit((int)$u['id'], 'Login success');
    return '';
}

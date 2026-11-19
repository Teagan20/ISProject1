<?php // Usage: php create_admin.php admin@yourdomain.com 'StrongPass123'
require __DIR__ . '/../inc/bootstrap.php';
[, $email, $pass] = $argv + [null, null, null];
if (!$email || !$pass) exit("Usage: php create_admin.php email password\n");
$hash = password_hash($pass, PASSWORD_DEFAULT); $n = 'Administrator'; $ph = '0000000000';
$s = $db->prepare("INSERT INTO users (full_name,email,phone,password_hash,role) VALUES (?,?,?,?,'admin')");
$s->bind_param('ssss', $n, $email, $ph, $hash); $s->execute(); echo "Admin created.\n";

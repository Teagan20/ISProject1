<?php require '../inc/bootstrap.php'; require '../inc/layout.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $error = attempt_login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if (!$error) { header('Location: dashboard.php'); exit; }
}
head('Login'); ?>
<div class="card"><h2>Login</h2>
<?php if (isset($_GET['registered'])) echo '<div class="ok">Account created. Please log in.</div>';
if ($error) echo '<div class="err">' . e($error) . '</div>'; ?>
<form method="post"><?= csrf_field() ?>
<label for="e">Email</label><input id="e" type="email" name="email" required maxlength="120">
<label for="p">Password</label><input id="p" type="password" name="password" required>
<button type="submit">Log in</button></form></div>
<?php foot();

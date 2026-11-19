<?php require '../inc/bootstrap.php'; require '../inc/layout.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name  = trim($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!preg_match("/^[\p{L} '\-]{2,60}$/u", $name)) $errors[] = 'Name must be 2-60 letters.';
    if (!$email) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Phone must be exactly 10 digits.';
    if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $pass)) $errors[] = 'Password needs 8+ chars with upper, lower and a number.';
    if (!$errors) {
        $s = $db->prepare('SELECT id FROM users WHERE email=?'); $s->bind_param('s', $email); $s->execute();
        if ($s->get_result()->num_rows) $errors[] = 'Email already registered.';
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);   // salted bcrypt hash
            $s = $db->prepare('INSERT INTO users (full_name,email,phone,password_hash) VALUES (?,?,?,?)');
            $s->bind_param('ssss', $name, $email, $phone, $hash); $s->execute();
            audit((int)$db->insert_id, 'Registered');
            header('Location: login.php?registered=1'); exit;
        }
    }
}
head('Register'); ?>
<div class="card"><h2>Register</h2>
<?php foreach ($errors as $er) echo '<div class="err">' . e($er) . '</div>'; ?>
<form method="post"><?= csrf_field() ?>
<label for="n">Full name</label>
<input id="n" name="full_name" required minlength="2" maxlength="60" pattern="[A-Za-z' \-]{2,60}" placeholder=" " value="<?= e($_POST['full_name'] ?? '') ?>">
<label for="e">Email</label>
<input id="e" type="email" name="email" required maxlength="120" placeholder=" " value="<?= e($_POST['email'] ?? '') ?>">
<label for="p">Phone (10 digits)</label>
<input id="p" type="tel" name="phone" required pattern="[0-9]{10}" title="10 digits, e.g. 0712345678" placeholder=" ">
<label for="pw">Password</label>
<input id="pw" type="password" name="password" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="8+ characters with upper, lower and a number" placeholder=" ">
<button type="submit">Create account</button></form></div>
<?php foot();

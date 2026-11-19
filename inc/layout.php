<?php
function head(string $title): void { ?>
<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | CampusDesk</title><link rel="stylesheet" href="style.css"></head><body>
<nav><a class="brand" href="index.php">🎓 CampusDesk</a>
<?php if (!empty($_SESSION['uid']) && ($_SESSION['role'] ?? '') !== 'admin'): ?><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout (<?= e($_SESSION['name']) ?>)</a>
<?php elseif (empty($_SESSION['uid'])): ?><a href="login.php">Login</a><a href="register.php">Register</a><?php endif; ?></nav><main>
<?php }
function foot(): void { echo '</main></body></html>'; }

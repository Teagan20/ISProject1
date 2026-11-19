<?php require '../inc/bootstrap.php'; require '../inc/layout.php';
$error = '';
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    csrf_check();
    $error = attempt_login(trim($_POST['email']), $_POST['password'] ?? '', true);
    if (!$error) { header('Location: index.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close'])) {
    require_login('admin'); csrf_check();
    $id = (int)$_POST['close'];
    $s = $db->prepare("UPDATE tickets SET status='closed' WHERE id=?"); $s->bind_param('i', $id); $s->execute();
    audit((int)$_SESSION['uid'], "Closed ticket #$id"); header('Location: index.php'); exit;
}
head('Admin');
if (($_SESSION['role'] ?? '') !== 'admin'): ?>
<div class="card"><h2>🔒 Admin login</h2><?php if ($error) echo '<div class="err">' . e($error) . '</div>'; ?>
<form method="post"><?= csrf_field() ?><label>Email</label><input type="email" name="email" required>
<label>Password</label><input type="password" name="password" required><button>Log in</button></form></div>
<?php else:
$stats = $db->query("SELECT (SELECT COUNT(*) FROM users) u,(SELECT COUNT(*) FROM tickets) t,(SELECT COUNT(*) FROM tickets WHERE status='open') o")->fetch_assoc();
$tix = $db->query('SELECT t.id,t.subject,t.priority,t.status,u.email FROM tickets t JOIN users u ON u.id=t.user_id ORDER BY t.id DESC LIMIT 50');
$log = $db->query('SELECT action,ip,created_at FROM audit_log ORDER BY id DESC LIMIT 20'); ?>
<div class="card"><h2>Admin panel <a class="small" href="?logout=1">(logout)</a></h2>
<p><b><?= (int)$stats['u'] ?></b> users · <b><?= (int)$stats['t'] ?></b> tickets · <b><?= (int)$stats['o'] ?></b> open</p></div>
<div class="card"><h3>Tickets</h3><table><tr><th>#</th><th>Subject</th><th>User</th><th>Priority</th><th>Status</th><th></th></tr>
<?php while ($r = $tix->fetch_assoc()): ?><tr><td><?= $r['id'] ?></td><td><?= e($r['subject']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['priority']) ?></td><td><?= e($r['status']) ?></td>
<td><?php if ($r['status'] === 'open'): ?><form method="post" style="margin:0"><?= csrf_field() ?><button name="close" value="<?= $r['id'] ?>" style="margin:0;padding:4px 10px">Close</button></form><?php endif; ?></td></tr><?php endwhile; ?></table></div>
<div class="card"><h3>Audit log (latest 20)</h3><table><tr><th>Action</th><th>IP</th><th>Time</th></tr>
<?php while ($r = $log->fetch_assoc()): ?><tr><td><?= e($r['action']) ?></td><td><?= e($r['ip']) ?></td><td class="small"><?= e($r['created_at']) ?></td></tr><?php endwhile; ?></table></div>
<?php endif; foot();

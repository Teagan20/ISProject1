<?php require '../inc/bootstrap.php'; require '../inc/layout.php'; require_login();
$uid = (int)$_SESSION['uid']; $errors = []; $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $subject = trim($_POST['subject'] ?? ''); $msg = trim($_POST['message'] ?? ''); $pr = $_POST['priority'] ?? '';
    if (strlen($subject) < 3 || strlen($subject) > 100) $errors[] = 'Subject must be 3-100 characters.';
    if (strlen($msg) < 10 || strlen($msg) > 2000) $errors[] = 'Message must be 10-2000 characters.';
    if (!in_array($pr, ['low','medium','high'], true)) $errors[] = 'Invalid priority.';
    if (!$errors) {
        $s = $db->prepare('INSERT INTO tickets (user_id,subject,message,priority) VALUES (?,?,?,?)');
        $s->bind_param('isss', $uid, $subject, $msg, $pr); $s->execute();
        audit($uid, 'Created ticket'); $ok = 'Ticket submitted.';
    }
}
$s = $db->prepare('SELECT subject, priority, status, created_at FROM tickets WHERE user_id=? ORDER BY id DESC');
$s->bind_param('i', $uid); $s->execute(); $rows = $s->get_result();
head('Dashboard'); ?>
<div class="card"><h2>New ticket</h2>
<?php foreach ($errors as $er) echo '<div class="err">' . e($er) . '</div>'; if ($ok) echo '<div class="ok">' . e($ok) . '</div>'; ?>
<form method="post"><?= csrf_field() ?>
<label for="s">Subject</label><input id="s" name="subject" required minlength="3" maxlength="100">
<label for="m">Message</label><textarea id="m" name="message" rows="4" required minlength="10" maxlength="2000"></textarea>
<label for="pr">Priority</label><select id="pr" name="priority"><option>low</option><option selected>medium</option><option>high</option></select>
<button type="submit">Submit</button></form></div>
<div class="card"><h2>My tickets</h2><table><tr><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th></tr>
<?php while ($r = $rows->fetch_assoc()): ?>
<tr><td><?= e($r['subject']) ?></td><td><span class="badge"><?= e($r['priority']) ?></span></td><td><?= e($r['status']) ?></td><td class="small"><?= e($r['created_at']) ?></td></tr>
<?php endwhile; ?></table></div>
<?php foot();

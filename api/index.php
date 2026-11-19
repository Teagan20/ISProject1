<?php require '../inc/bootstrap.php';
header('Content-Type: application/json');
$r = $db->query("SELECT COUNT(*) total, COALESCE(SUM(status='open'),0) o, COALESCE(SUM(status='closed'),0) c FROM tickets")->fetch_assoc();
echo json_encode(['service' => 'CampusDesk API', 'version' => '1.0', 'status' => 'ok',
  'tickets' => ['total' => (int)$r['total'], 'open' => (int)$r['o'], 'closed' => (int)$r['c']],
  'time' => gmdate('c')], JSON_PRETTY_PRINT);

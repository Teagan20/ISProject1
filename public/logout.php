<?php require '../inc/bootstrap.php';
if (!empty($_SESSION['uid'])) audit((int)$_SESSION['uid'], 'Logout');
$_SESSION = []; session_destroy(); header('Location: index.php');

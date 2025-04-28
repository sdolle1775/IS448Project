<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_POST['user_id'] ?? null;
if ($user_id) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
}
header('Location: changestudent.php');
exit();
?>

<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$class_id = $_POST['class_id'] ?? null;
if ($class_id) {
    $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ? AND user_id = ?');
    $stmt->execute([$class_id, $_SESSION['user_id']]);
}
header('Location: gradecalc_home.php');
exit();
?>

<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit(); 
}

$assignment_id = $_POST['assignment_id'] ?? null;
if ($assignment_id) {
    $stmt = $pdo->prepare('DELETE FROM assignments WHERE id = ?');
    $stmt->execute([$assignment_id]);
}
header('Location: gradecalc_home.php');
exit();
?>

<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not authorized');
}

$class_id = $_GET['class_id'] ?? null;
$query = $_GET['query'] ?? '';

if (!$class_id) {
    http_response_code(400);
    exit('Missing class_id');
}

$stmt = $pdo->prepare('SELECT * FROM assignments WHERE class_id = ? AND name LIKE ?');
$stmt->execute([$class_id, "%$query%"]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($assignments);

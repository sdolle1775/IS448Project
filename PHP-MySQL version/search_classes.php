<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not authorized');
}

$search = $_GET['query'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM classes WHERE user_id = ? AND name LIKE ?');
$stmt->execute([$_SESSION['user_id'], "%$search%"]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($classes);

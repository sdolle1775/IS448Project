<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$class_id = $_GET['class_id'] ?? null;
if (!$class_id) { die('Class ID not provided.'); }
$stmt = $pdo->prepare('SELECT * FROM categories WHERE class_id = ?');
$stmt->execute([$class_id]);
$categories = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $score = (float)$_POST['score'];
    $total_points = (float)$_POST['total_points'];
    if (empty($name)) {
        $error = "Assignment name is required.";
    } else {
        $stmt = $pdo->prepare('INSERT INTO assignments (class_id, name, category, score, total_points) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$class_id, $name, $category, $score, $total_points]);
        header("Location: gradecalc_home.php");
		exit();
    }
}
?>
<html><head><link rel="stylesheet" href="gradecalc_styles.css"></head><body>
<h1>Create a New Assignment</h1>
<form method="post">
Assignment Name: <input type="text" name="name" required><br><br>
Category: <select name="category" required>
<?php foreach ($categories as $cat): ?>
<option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
<?php endforeach; ?>
</select><br><br>
Score Earned: <input type="number" name="score" step="0.01" required><br><br>
Total Points: <input type="number" name="total_points" step="0.01" required><br><br>
<button class="orangebutton" type="submit">Create Assignment</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body></html>
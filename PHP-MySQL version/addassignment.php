<?php
session_start();
require 'db.php';

// Handle AJAX Name Check with Class Context
if (isset($_GET['check_username'], $_GET['class_id'])) {
    $assignment_name = trim($_GET['check_username']);
    $class_id = (int) $_GET['class_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE class_id = ? AND name = ?");
    $stmt->execute([$class_id, $assignment_name]);
    $count = $stmt->fetchColumn();
    echo ($count > 0) ? "Assignment is already entered." : "New Assignment.";
    exit(); // Stop further processing
}

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
<html>
<head>
    <link rel="stylesheet" href="gradecalc_styles.css">
</head>
<body>
<script>
    function creation() {
        return confirm("Have you carefully entered the new assignment information?");
    }

    function check(str) {
        if (str.length == 0) {
            document.getElementById("txtHint").innerHTML = "";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xhr.open("GET", "addassignment.php?class_id=<?= $class_id ?>&check_username=" + encodeURIComponent(str), true);
        xhr.send();
    }
</script>

<h1 id="change">Create a New Assignment</h1>
<script>
    document.getElementById("change").style.fontSize = "30pt";
    document.getElementById("change").style.color = "green";
</script>

<form method="post" onsubmit="return creation()">
    Assignment Name: <input type="text" name="name" required oninput="check(this.value)"><br><br>
    <span id="txtHint"></span><br><br>

    Category:
    <select name="category" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    Score Earned: <input type="number" name="score" step="0.01" required><br><br>
    Total Points: <input type="number" name="total_points" step="0.01" required><br><br>
    
    <button class="orangebutton" type="submit">Create Assignment</button>
</form>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>

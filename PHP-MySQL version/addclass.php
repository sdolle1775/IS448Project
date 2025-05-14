<?php
session_start();
require 'db.php';

// Handle AJAX Name Check
if (isset($_GET['check_username'])) {
    $username = trim($_GET['check_username']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE name = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();
    echo ($count > 0) ? "Class is already entered." : "New Class.";
    exit(); // Stop further processing
}

// Handle Form Submission
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $categories = [
        [trim($_POST['category1_name']), (float)$_POST['category1_weight']],
        [trim($_POST['category2_name']), (float)$_POST['category2_weight']],
        [trim($_POST['category3_name']), (float)$_POST['category3_weight']],
        [trim($_POST['category4_name']), (float)$_POST['category4_weight']],
        [trim($_POST['category5_name']), (float)$_POST['category5_weight']]
    ];
    $total_weight = array_sum(array_column($categories, 1));
    if (empty($name) || $total_weight != 100) {
        $error = "All weights must total 100%.";
    } else {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO classes (user_id, name) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $name]);
        $class_id = $pdo->lastInsertId();
        $cat_stmt = $pdo->prepare('INSERT INTO categories (class_id, name, weight) VALUES (?, ?, ?)');
        foreach ($categories as [$cat_name, $cat_weight]) {
            if (!empty($cat_name)) $cat_stmt->execute([$class_id, $cat_name, $cat_weight]);
        }
        $pdo->commit();
        header('Location: gradecalc_home.php');
        exit();
    }
}
?>
<html><head><link rel="stylesheet" href="gradecalc_styles.css"></head><body>
    <script>
    // Create Class Validation
    function creation() {
        return confirm("Have you carefully entered the new class information?");
    }

    // AJAX Name Availability Check
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
        xhr.open("GET", "addclass.php?check_username=" + encodeURIComponent(str), true);
        xhr.send();
    }
    </script>
<h1 id="change">Create a New Class</h1>
<script>
    // Changing color and size
    document.getElementById("change").style.fontSize = "30pt";
    document.getElementById("change").style.color = "blue";
</script>
<form method="post" onsubmit="return creation()">
Class Name: <input type="text" name="name" required oninput="check(this.value)"><br><br>

<span id="txtHint"></span><br><br>
<?php for ($i = 1; $i <= 5; $i++): ?>
Category <?= $i ?> Name: <input type="text" name="category<?= $i ?>_name"> Weight (%): <input type="number" name="category<?= $i ?>_weight" step="0.01"><br><br>
<?php endfor; ?>
<button class="orangebutton" type="submit">Create Class</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body></html>

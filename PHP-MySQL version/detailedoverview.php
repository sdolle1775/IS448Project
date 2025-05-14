<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$class_id = $_POST['class_id'] ?? null;
if (!$class_id) { die('Class ID not provided.'); }

$categories_stmt = $pdo->prepare('SELECT * FROM categories WHERE class_id = ?');
$categories_stmt->execute([$class_id]);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$assignments_stmt = $pdo->prepare('SELECT * FROM assignments WHERE class_id = ?');
$assignments_stmt->execute([$class_id]);
$assignments = $assignments_stmt->fetchAll(PDO::FETCH_ASSOC);

$category_totals = [];
$category_scores = [];
foreach ($assignments as $assignment) {
    $cat = $assignment['category'];
    if (!isset($category_totals[$cat])) {
        $category_totals[$cat] = 0;
        $category_scores[$cat] = 0;
    }
    $category_scores[$cat] += $assignment['score'];
    $category_totals[$cat] += $assignment['total_points'];
}
?>
<html>
<head><link rel="stylesheet" href="gradecalc_styles.css"></head>
<body>
<div class="container">
<div style="margin-left: 400;margin-right: auto;">
<h1 id="change">Detailed Class Overview</h1>
<script>
    document.getElementById("change").style.fontSize = "30pt";
    document.getElementById("change").style.color = "blue";
</script>
</div>
<div style="margin-left: auto;margin-right: 200;">
<form action="gradecalc_home.php" method="post">
    <button class="orangebutton" type="submit">Home</button>
</form>
</div>
</div>
<img class="designborder" src="designborder.png" alt="border" width="1800" height="40">
<div style="text-align: center; margin-bottom: 20px;">
    <input type="text" id="categorySearchBox" placeholder="Search for a category..." style="width: 300px; padding: 5px;">
</div>

<h2>Percentages per Category</h2>
<table>
<tr><th>Category</th><th>Percentage</th></tr>
<?php foreach ($categories as $cat): ?>
<tr>
<td><?= htmlspecialchars($cat['name']) ?></td>
<td>
<?php
    $name = $cat['name'];
    if (isset($category_scores[$name]) && $category_totals[$name] > 0) {
        echo number_format(($category_scores[$name] / $category_totals[$name]) * 100, 2) . '%';
    } else {
        echo 'N/A';
    }
?>
</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Category Weights</h2>
<table>
<tr><th>Category</th><th>Weight (%)</th></tr>
<?php foreach ($categories as $cat): ?>
<tr>
<td><?= htmlspecialchars($cat['name']) ?></td>
<td><?= htmlspecialchars($cat['weight']) ?>%</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Points Earned vs Points Possible per Category</h2>
<table>
<tr><th>Category</th><th>Points Earned</th><th>Points Possible</th></tr>
<?php foreach ($categories as $cat): ?>
<tr>
<td><?= htmlspecialchars($cat['name']) ?></td>
<td><?= $category_scores[$cat['name']] ?? 0 ?></td>
<td><?= $category_totals[$cat['name']] ?? 0 ?></td>
</tr>
<?php endforeach; ?>
</table>
<form method="post" action="classoverview.php">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <button class="orangebutton" type="submit">Class Overview</button>
</form>
<br>
<img class="designborder" src="designborder2.png" alt="border2" width="1800" height="40">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#categorySearchBox').on('input', function() {
        var query = $(this).val().toLowerCase();
        
        // Search all tables that list categories
        $('table').each(function() {
            var table = $(this);
            table.find('tr').each(function(index) {
                if (index === 0) return; // Skip header row
                var categoryName = $(this).find('td:first').text().toLowerCase();
                if (categoryName.includes(query) && query.length > 0) {
                    $(this).show().css('background-color', 'yellow');
                } else {
                    $(this).show().css('background-color', '');
                    if (query.length > 0 && !categoryName.includes(query)) {
                        $(this).hide();
                    }
                }
            });
        });
    });
});
</script>

</body>
</html>

<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM classes WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();

function calculate_overall_grade($pdo, $class_id) {
    $stmt = $pdo->prepare('SELECT * FROM assignments WHERE class_id = ?');
    $stmt->execute([$class_id]);
    $assignments = $stmt->fetchAll();

    $categories_stmt = $pdo->prepare('SELECT * FROM categories WHERE class_id = ?');
    $categories_stmt->execute([$class_id]);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

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

    $overall_percentage = 0;
    foreach ($categories as $cat) {
        $name = $cat['name'];
        $weight = $cat['weight'];
        if (isset($category_scores[$name]) && $category_totals[$name] > 0) {
            $percent = ($category_scores[$name] / $category_totals[$name]) * 100;
            $overall_percentage += ($percent * ($weight / 100));
        }
    }
    return number_format($overall_percentage, 2);
}

function getColorClass($percent) {
    if ($percent >= 90) return 'style="color:green;"';
    if ($percent >= 70) return 'style="color:orange;"';
    return 'style="color:red;"';
}
?>
<html>
<head>
<link rel="stylesheet" href="gradecalc_styles.css">
<title>GradeCalc Home</title>
</head>
<body>
<h1>Class Grade Calculator</h1>
<div class="container" >
<div class="brownbox">
Logged in as: <?= htmlspecialchars($_SESSION['username']) ?>
</div>
<div class="orangebutton">
<form action="changestudent.php" method="get">
    <button class="orangebutton" type="submit">Manage Students</button>
</form>
</div>
</div>
<img class="designborder" src="designborder.png" alt="border" width="1800" height="40">
<?php if (empty($classes)): ?>
<p>No classes found. Add one below!</p>
<?php else: ?>
<div style="text-align: center; margin-bottom: 20px;">
    <input type="text" id="searchBox" placeholder="Search for a class..." style="width: 300px; padding: 5px;">
</div>
<table>
<tr><th>Class Name</th><th>Overall Grade</th><th>Class Overview</th><th>Delete</th></tr>
<?php foreach ($classes as $class): ?>
<tr>
<td><?= htmlspecialchars($class['name']) ?></td>
<td><span <?= getColorClass(calculate_overall_grade($pdo, $class['id'])) ?>><?= calculate_overall_grade($pdo, $class['id']) ?>%</span></td>
<td>
<form method="post" action="classoverview.php" style="display:inline;">
<input type="hidden" name="class_id" value="<?= $class['id'] ?>">
<button class="orangebutton" type="submit">Details</button>
</form>
</td>
<td>
<form method="post" action="delete_class.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this class?');">
<input type="hidden" name="class_id" value="<?= $class['id'] ?>">
<button class ="redbutton" type="submit">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<br>
<div class="orangebutton" style="margin-left: auto;margin-right: auto;">
<form action="addclass.php" method="get">
    <button class="orangebutton" type="submit">Create Class +</button>
</form>
</div>

<br>
<img class="designborder" src="designborder2.png" alt="border2" width="1800" height="40">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#searchBox').on('input', function() {
        var query = $(this).val();
        if (query.length < 1) {
            $('table tr').show(); // Show all rows if query is empty
            return;
        }

        $.get('search_classes.php', { query: query }, function(data) {
            $('table tr').hide(); // Hide all rows first
            $('table tr:first').show(); // Keep header visible

            data.forEach(function(classItem) {
                $('table tr').each(function(index) {
                    if (index === 0) return; // Skip header
                    var className = $(this).find('td:first').text();
                    if (className === classItem.name) {
                        $(this).show().css('background-color', 'yellow');
                    }
                });
            });
        }, 'json');
    });
});
</script>
</body>
</html>

<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$class_id = $_POST['class_id'] ?? null;
if (!$class_id) {
    die('Class ID not provided.');
}

try {
    $stmt = $pdo->prepare('SELECT * FROM assignments WHERE class_id = ? ORDER BY category');
    $stmt->execute([$class_id]);
    $assignments = $stmt->fetchAll();

    $categories_stmt = $pdo->prepare('SELECT * FROM categories WHERE class_id = ?');
    $categories_stmt->execute([$class_id]);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database query failed: ' . $e->getMessage());
}

// Calculate overall and per-category grades
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

function getColorClass($percent) {
    if ($percent >= 90) return 'style="color:green;"';
    if ($percent >= 70) return 'style="color:orange;"';
    return 'style="color:red;"';
}
?>
<html>
<head><link rel="stylesheet" href="gradecalc_styles.css"></head>
<body>
<div class="container">
<div style="margin-left: 440;margin-right: auto;">
<h1 id="change">Class Overview</h1>
<script>
    document.getElementById("change").style.fontSize = "30pt";
    document.getElementById("change").style.color = "red";
</script>
</div>
<div style="margin-left: auto;margin-right: 200;">
<form action="gradecalc_home.php" method="post">
    <button class="orangebutton" type="submit">Home</button>
</form>
</div>
</div>
<img class="designborder" src="designborder.png" alt="border" width="1800" height="40">
<h2>Current Overall Grade: <span <?= getColorClass($overall_percentage) ?>><?= number_format($overall_percentage, 2) ?>%</span></h2>
<div style="text-align: center; margin-bottom: 20px;">
    <input type="text" id="assignmentSearchBox" placeholder="Search for an assignment..." style="width: 300px; padding: 5px;">
</div>
<?php if (empty($assignments)): ?>
<p>No assignments found for this class.</p>
<?php else: ?>
<?php $current_category = ''; ?>
<?php foreach ($assignments as $assignment): ?>
    <?php if ($current_category !== $assignment['category']): ?>
        <?php if ($current_category !== '') echo "</table><br>"; ?>
        <h2><?= htmlspecialchars($assignment['category']) ?>
            (<span <?php
                if (isset($category_scores[$assignment['category']]) && $category_totals[$assignment['category']] > 0) {
                    $percent = ($category_scores[$assignment['category']] / $category_totals[$assignment['category']]) * 100;
                    echo getColorClass($percent) . ">" . number_format($percent, 2);
                } else {
                    echo 'style="color:gray;">N/A';
                }
            ?>%)</span>
        </h2>
        <table>
        <th>Assignment</th><th>Score</th><th>Total Points</th><th>Delete</th>
        <?php $current_category = $assignment['category']; ?>
    <?php endif; ?>
<tr>
<td><?= htmlspecialchars($assignment['name']) ?></td>
<td><?= $assignment['score'] ?></td>
<td><?= $assignment['total_points'] ?></td>
<td>
<form method="post" action="delete_assignment.php" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
    <button class="redbutton" type="submit">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<br><br>
<div class="container">
<div style="margin-left: auto;margin-right: auto;">
<form action="addassignment.php" method="get">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <button class="orangebutton" type="submit">Add Assignment</button>
</form>
</div>
<div style="margin-left: auto;margin-right: 400;">
<form method="post" action="detailedoverview.php">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <button class="orangebutton" type="submit">Detailed Overview</button>
</form>
</div>
</div>
<br>
<img class="designborder" src="designborder2.png" alt="border2" width="1800" height="40">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#assignmentSearchBox').on('input', function() {
        var query = $(this).val();
        var classId = <?= json_encode($class_id) ?>;
        if (query.length < 1) {
            $('table tr').show(); // Show all if empty
            return;
        }

        $.get('search_assignments.php', { class_id: classId, query: query }, function(data) {
            $('table tr').hide(); // Hide all
            $('table tr:first').show(); // Show headers

            data.forEach(function(item) {
                $('table tr').each(function() {
                    var assignmentName = $(this).find('td:first').text();
                    if (assignmentName === item.name) {
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

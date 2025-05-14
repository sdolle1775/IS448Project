
<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll();
?>
<html>
<head>
    <title> Changing Student </title>
    <link rel="stylesheet" href="gradecalc_styles.css">
    <script> 
        //Validating form data
        function validate(){
            let valid1 = confirm("Are you sure you want to delete this student?");
            return valid1;
        }
        function loggingout(){
            let valid2 = confirm("Are you sure you want to LOGOUT!?!");
            return valid2;
        }
    </script>
</head>
<body>
<div class="container">
<div class="brownbox" style="margin-left: 400;margin-right: 10;">
Logged in as: <?= htmlspecialchars($_SESSION['username']) ?>
</div>
<form action="logout.php" method="post" onsubmit="return loggingout()">
    <button class="orangebutton" type="submit">Logout</button>
</form>
<div class="orangebutton" style="margin-left: auto;margin-right: 200;">
<form action="gradecalc_home.php" method="post">
    <button class="orangebutton" type="submit">Home</button>
</form>
</div>
</div>

<h1 id="header">Change Students</h1>
    <script>
        //Changing header style
        document.getElementById("header").style.color = "gold";
        document.getElementById("header").style.fontSize = "30pt";
    </script>
<img class="designborder" src="designborder.png" alt="border" width="1800" height="40">
<table>
<tr><th>Username</th><th>Email</th><th>Year</th><th>Actions</th></tr>
<?php foreach ($users as $user): ?>
<tr>
<td><?= htmlspecialchars($user['username']) ?></td>
<td><?= htmlspecialchars($user['email']) ?></td>
<td><?= htmlspecialchars($user['year']) ?></td>
<td>
<form name="deletestudent" method="post" action="deletestudent.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
<input type="hidden" name="user_id" value="<?= $user['id'] ?>">
<button class="redbutton" type="submit">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<br>
<div class="orangebutton" style="margin-left: auto;margin-right: auto;">
<form action="createstudent.php" method="get">
    <button class="orangebutton" type="submit">Create New Student</button>
</form>
</div>
<br>
<img class="designborder" src="designborder2.png" alt="border2" width="1800" height="40">
</body>
</html>

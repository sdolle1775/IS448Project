<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $year = $_POST['year'];

    if (empty($username) || empty($password) || empty($email) || empty($year)) {
        $error = "All fields are required.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, email, year) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $password_hash, $email, $year]);
            header('Location: gradecalc_home.php');
            exit();
        } catch (PDOException $e) {
            $error = "Error creating student: " . $e->getMessage();
        }
    }
}
?>
<html>
<head><link rel="stylesheet" href="gradecalc_styles.css"></head>
<body>
<h1>Create Student</h1>
<form method="post">
    Username: <input type="text" name="username" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Year:
    <select name="year" required>
        <option value="Freshman">Freshman</option>
        <option value="Sophomore">Sophomore</option>
        <option value="Junior">Junior</option>
        <option value="Senior">Senior</option>
    </select><br><br>
    <button class="orangebutton" type="submit">Create Student</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
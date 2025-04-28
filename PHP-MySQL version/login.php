<?php
session_start();
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: gradecalc_home.php');
        exit();
    } elseif (!$user) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, email, year) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $password_hash, $username . '@example.com', 'Freshman']);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        header('Location: gradecalc_home.php');
        exit();
    } else {
        $error = "Invalid login. Try again.";
    }
}
?>
<html><head><link rel="stylesheet" href="gradecalc_styles.css"></head><body>
<h1>Login</h1>
<form method="post">
Username (UMBC ID): <input type="text" name="username" pattern="[A-Za-z]{2}[0-9]{5}" required>
Password: <input type="password" name="password" required><br><br>
<button type="submit">Login</button>
</form>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body></html>
<?php
session_start();
require 'db.php';

// Handle AJAX Username Check
if (isset($_GET['check_username'])) {
    $username = trim($_GET['check_username']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();
    echo ($count > 0) ? "Name has already been taken." : "Name is available.";
    exit(); // Stop further processing
}

// Handle Form Submission
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

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
<head>
    <link rel="stylesheet" href="gradecalc_styles.css">
    <script>
    // Create Student Validation
    function creation() {
        return confirm("Have you carefully entered the new student's information?");
    }

    // AJAX Username Availability Check
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
        xhr.open("GET", "createstudent.php?check_username=" + encodeURIComponent(str), true);
        xhr.send();
    }
    </script>
</head>
<body>
<h1 id="change">Create Student</h1>
<script>
    // Changing color and size
    document.getElementById("change").style.fontSize = "30pt";
    document.getElementById("change").style.color = "gold";
</script>

<form method="post" onsubmit="return creation()">
    Username: <input type="text" name="username" pattern="[A-Za-z]{2}[0-9]{5}" required onkeyup="check(this.value)"><br><br>
    <span id="txtHint"></span><br><br>
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

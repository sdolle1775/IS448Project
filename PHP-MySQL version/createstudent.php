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
<head>
    <link rel="stylesheet" href="gradecalc_styles.css">
    <script>
        //Create Student Validation
        function creation(){
            let valid = confirm("Have you carefully entered the new student's information?");
            return valid;
        }
        //Notifies user of any taken student names
        function check(str){
            if(str.length == 0){
                document.getElementById("txtHint").innerHTML = "";
                return;
            }
            else{
                var message = new XMLHttpRequest();
                message.onreadystatechange = function(){
                    if(this.readyState == 4 && this.status == 200){
                        document.getElementById("txtHint").innerHTML = "Name has already been taken.";
                    }
                }
            }
        }
    </script>
</head>
<body>
<h1 id="change">Create Student</h1>
    <script>
        //Changing color and size
        document.getElementIdBy("change").style.fontSize = "30pt";
        document.getElementIdBy("change").style.color = "gold";
    </script>
<form method="post" onsubmit="return creation()">
    Username: <input type="text" name="username" required onkeyup="check(this.value)"><br><br>
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
<p> Note: <span id="txtHint"></span></p>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>

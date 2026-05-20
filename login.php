<?php
session_start();
include 'config/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Use PDO prepared statement (SAFE)
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if ($user) {

        if (password_verify($password, $user['password'])) {

            $_SESSION['admin'] = $user['username'];

            header("Location: dashboard.php");
            exit;

        } else {
            $msg = "Invalid login credentials";
        }

    } else {
        $msg = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>

<style>
body{
    font-family:Arial;
    background:#eee;
    display:flex;
    align-items:center;
    justify-content:center;
    height:100vh;
    margin:0;
}

.card{
    background:#fff;
    padding:30px;
    width:350px;
    border-radius:8px;
    box-shadow:0 0 10px rgba(0,0,0,0.2);
}

input,button{
    width:100%;
    padding:10px;
    margin-top:10px;
    outline:none;
}

button{
    background:#28a745;
    color:#fff;
    border:none;
    cursor:pointer;
    border-radius:5px;
}
</style>

</head>

<body>

<div class="card">

<h2>Admin Login</h2>

<form method="post">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<p><?php echo $msg; ?></p>

</div>

</body>
</html>
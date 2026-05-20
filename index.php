<?php
include 'config/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if admin already exists
    $check = $pdo->query("SELECT COUNT(*) FROM admin");
    $count = $check->fetchColumn();

    if ($count == 0) {

        // Insert admin safely using prepared statement
        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

        $msg = "Admin created successfully";

    } else {
        $msg = "Admin already exists";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ImhoTech System</title>

<style>
body{
    font-family: Arial;
    background:#f4f4f4;
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
    background:#007bff;
    color:#fff;
    border:none;
    cursor:pointer;
    border-radius:5px;
}

a{
    display:inline-block;
    margin-top:10px;
    text-decoration:none;
    padding:8px;
    background:#28a745;
    color:#fff;
    border-radius:5px;
}
</style>

</head>

<body>

<div class="card">

<h2>Create Admin</h2>

<form method="post">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Create Admin</button>
</form>

<p><?php echo $msg; ?></p>

<a href="login.php">Go to Login</a>

</div>

</body>
</html>
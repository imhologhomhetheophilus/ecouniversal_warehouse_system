<?php
include 'config/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $pdo->query("SELECT COUNT(*) FROM admin");
    $count = $check->fetchColumn();

    if ($count == 0) {

        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

        $msg = "Admin created successfully";

    } else {
        $msg = "Admin already exists";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Admin</title>

<style>

/* =========================
   MOBILE FIRST DESIGN
========================= */

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    padding:15px;
}

/* card */
.card{
    width:100%;
    max-width:380px;
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
}

/* title */
h2{
    text-align:center;
    margin-bottom:15px;
    font-size:1.4rem;
}

/* inputs */
input{
    width:100%;
    padding:14px;
    margin-top:10px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:1rem;
    box-sizing:border-box;
}

/* button */
button{
    width:100%;
    padding:14px;
    margin-top:15px;
    border:none;
    border-radius:8px;
    background:#007bff;
    color:#fff;
    font-size:1rem;
    font-weight:600;
    cursor:pointer;
}

button:active{
    transform:scale(0.98);
}

/* message */
p{
    text-align:center;
    margin-top:12px;
    font-size:0.95rem;
    color:#333;
}

/* login link */
a{
    display:block;
    text-align:center;
    margin-top:10px;
    padding:12px;
      background:#007bff;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-size:0.95rem;
}

/* TABLET */
@media(min-width:600px){
    .card{
        padding:25px;
    }

    h2{
        font-size:1.6rem;
    }
}

/* DESKTOP */
@media(min-width:992px){
    .card{
        max-width:420px;
    }
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

<p><?= htmlspecialchars($msg) ?></p>

<a href="login.php">Go to Login</a>

</div>

</body>
</html>
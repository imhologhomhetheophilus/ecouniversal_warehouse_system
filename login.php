<?php
session_start();
include 'config/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['admin'] = $user['username'];
        header("Location: dashboard.php");
        exit;

    } else {
        $msg = "Invalid login credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>

<style>

/* =========================
   MOBILE FIRST BASE STYLE
========================= */

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f1f3f6;

    /* mobile-safe centering */
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.card{
    width:100%;
    max-width:380px;
    background:#fff;
    padding:22px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

h2{
    text-align:center;
    margin-bottom:20px;
    font-size:1.5rem;
}

/* inputs */
input{
    width:100%;
    padding:12px;
    margin-top:12px;
    border:1px solid #ddd;
    border-radius:8px;
    font-size:1rem;
    outline:none;
}

input:focus{
    border-color:#28a745;
}

/* button */
button{
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    border-radius:8px;
    background:#28a745;
    color:#fff;
    font-size:1rem;
    cursor:pointer;
    transition:0.2s ease;
}

button:hover{
    background:#218838;
}

/* message */
p{
    text-align:center;
    margin-top:12px;
    color:red;
    font-size:0.95rem;
}

/* =========================
   TABLET & DESKTOP ENHANCEMENT
========================= */

@media (min-width: 768px){
    .card{
        padding:28px;
        max-width:420px;
    }

    h2{
        font-size:1.7rem;
    }
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
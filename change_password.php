<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$msg = "";

if (isset($_POST['change'])) {

    $user = $_SESSION['admin'];
    $new  = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = ?");
    $result = $stmt->execute([$new, $user]);

    $msg = $result
        ? "Password changed successfully!"
        : "Error updating password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Change Password</title>

<style>

/* =========================
   MOBILE FIRST DESIGN
========================= */

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    padding:15px;
}

/* Container */
.container{
    max-width:420px;
    margin:auto;
}

/* Back button */
.back-btn{
    display:block;
    text-align:center;
    margin:10px 0 20px;
    background:#222;
    color:#fff;
    padding:12px;
    border-radius:10px;
    text-decoration:none;
    font-size:0.95rem;
}

/* Card */
.card{
    background:#fff;
    padding:18px;
    border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
}

/* Title */
h2{
    text-align:center;
    font-size:1.3rem;
    margin-bottom:15px;
}

/* Input */
input{
    width:100%;
    padding:14px;
    margin-top:10px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:1rem;
    box-sizing:border-box;
}

/* Button */
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

/* Message */
.msg{
    text-align:center;
    margin-top:12px;
    font-size:0.95rem;
    color:green;
}

/* TABLET */
@media(min-width:600px){
    body{
        padding:25px;
    }

    .card{
        padding:25px;
    }

    h2{
        font-size:1.6rem;
    }
}

/* DESKTOP */
@media(min-width:992px){
    .container{
        max-width:450px;
    }
}

</style>

</head>

<body>

<div class="container">

    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

    <div class="card">

        <h2>Change Password</h2>

        <form method="post">
            <input type="password" name="new_password" placeholder="Enter New Password" required>
            <button type="submit" name="change">Update Password</button>
        </form>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
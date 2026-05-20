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

    // PDO safe update
    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = ?");
    $result = $stmt->execute([$new, $user]);

    if ($result) {
        $msg = "Password changed successfully!";
    } else {
        $msg = "Error updating password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Change Password</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}

.container{
    max-width:450px;
    margin:60px auto;
    padding:20px;
}

.card{
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 4px 20px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

button{
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    border-radius:6px;
    background:#007bff;
    color:#fff;
    font-size:15px;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#0056b3;
}

.msg{
    text-align:center;
    margin-top:10px;
    color:green;
}

.back-btn{
    display:block;
    text-align:center;
    margin-bottom:20px;
    text-decoration:none;
    background:#333;
    color:#fff;
    padding:10px;
    border-radius:6px;
    transition:0.3s;
}

.back-btn:hover{
    background:#555;
}

@media(max-width:500px){
    .container{
        margin:20px;
        padding:10px;
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
            <button type="submit" name="change">Change Password</button>
        </form>

        <?php if ($msg): ?>
            <p class="msg"><?php echo $msg; ?></p>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
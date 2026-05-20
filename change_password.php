<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit; }
include 'config/db.php';

$msg="";

if(isset($_POST['change'])){
    $user = $_SESSION['admin'];
    $new  = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    if($conn->query("UPDATE admin SET password='$new' WHERE username='$user'")){
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

/* Container */
.container{
    max-width:450px;
    margin:60px auto;
    padding:20px;
}

/* Card */
.card{
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 4px 20px rgba(0,0,0,0.1);
}

/* Title */
h2{
    text-align:center;
    margin-bottom:20px;
}

/* Inputs */
input{
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

/* Button */
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

/* Message */
.msg{
    text-align:center;
    margin-top:10px;
    color:green;
}

/* Back button */
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

/* Responsive */
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

    <!-- Back to Dashboard -->
    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

    <div class="card">
        <h2>Change Password</h2>

        <form method="post">
            <input type="password" name="new_password" placeholder="Enter New Password" required>
            <button name="change">Change Password</button>
        </form>

        <?php if($msg): ?>
            <p class="msg"><?php echo $msg; ?></p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
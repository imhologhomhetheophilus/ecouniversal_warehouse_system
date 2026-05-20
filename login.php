<?php
session_start();
include 'config/db.php';
$msg="";
if($_SERVER['REQUEST_METHOD']=="POST"){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $result=$conn->query("SELECT * FROM admin WHERE username='$username'");
    if($result->num_rows>0){
        $row=$result->fetch_assoc();
        if(password_verify($password,$row['password'])){
            $_SESSION['admin']=$username;
            header("Location: dashboard.php");
            exit;
        }
    }
    $msg="Invalid login";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Econ Universal Service LTD</title>
<style>
body{font-family:Arial;background:#eee;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.card{background:#fff;padding:30px;width:350px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.2);}
input,button{width:100%;padding:10px;margin-top:10px; outline:none}
button{background:#28a745;color:#fff;border:none;cursor:pointer; border-radius:5px}
</style>
</head>
<body>
<div class="card">
<h2>Admin Login</h2>
<form method="post">
<input name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<button>Login</button>
</form>
<p><?php echo $msg;?></p>
</div>
</body>
</html>
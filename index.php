<?php
include 'config/db.php';
$msg="";
if($_SERVER['REQUEST_METHOD']=="POST"){
    $username=$_POST['username'];
    $password=password_hash($_POST['password'],PASSWORD_DEFAULT);
    $check=$conn->query("SELECT * FROM admin");
    if($check->num_rows==0){
        $conn->query("INSERT INTO admin(username,password) VALUES('$username','$password')");
        $msg="Admin created successfully";
    } else { $msg="Admin already exists"; }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Econ Universal Service LTD</title>
<style>
body{font-family:Arial;background:#f4f4f4;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.card{background:#fff;padding:30px;width:350px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.2);}
input,button{width:100%;padding:10px;margin-top:10px; outline:none;}
button{background:#007bff;color:#fff;border:none;cursor:pointer; border-radius:5px;}
</style>
</head>
<body>
<div class="card">
<h2>Create Admin</h2>
<form method="post">
<input name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button>Create Admin</button>
</form>
<p><?php echo $msg;?></p>
<a href="login.php" style="background:#007bff;color:#fff;border:none;cursor:pointer; padding:8px; text-decoration:none; border-radius:5px;">Login</a>
</div>
</body>
</html>
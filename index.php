<?php
include 'config/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // check admin
    $check = $pdo->query("SELECT * FROM admin");
    $admins = $check->fetchAll();

    if (count($admins) == 0) {

        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

        $msg = "Admin created successfully";

    } else {
        $msg = "Admin already exists";
    }
}
?>
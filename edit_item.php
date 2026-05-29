<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(["status"=>"error"]);
    exit;
}

$id = (int) $_POST['id'];
$name = $_POST['name'];
$location = $_POST['location'];
$type = $_POST['type'];
$qty = (int) $_POST['qty'];

$stmt = $pdo->prepare("
    UPDATE items 
    SET name=?, location=?, type=?, qty=? 
    WHERE id=?
");

$stmt->execute([$name,$location,$type,$qty,$id]);

echo json_encode(["status"=>"success"]);
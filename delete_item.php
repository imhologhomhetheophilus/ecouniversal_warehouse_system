<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(["status"=>"error","msg"=>"Unauthorized"]);
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(["status"=>"success"]);
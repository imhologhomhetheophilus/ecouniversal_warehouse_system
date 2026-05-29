<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(["status"=>"error","msg"=>"Unauthorized"]);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo json_encode(["status"=>"error","msg"=>"Invalid ID"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
$stmt->execute([$id]);

header("Location: dashboard.php");
exit;
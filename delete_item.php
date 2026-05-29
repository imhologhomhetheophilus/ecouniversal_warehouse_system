<?php
session_start();
include 'config/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['admin'])) {
    echo json_encode(["status" => "error", "msg" => "Unauthorized"]);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(["status" => "error", "msg" => "Invalid ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}
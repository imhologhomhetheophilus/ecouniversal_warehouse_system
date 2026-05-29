<?php
session_start();
include 'config/db.php';

header("Content-Type: application/json");

/* ================= AUTH ================= */
if (!isset($_SESSION['admin'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "Unauthorized"
    ]);
    exit;
}

/* ================= METHOD CHECK ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "msg" => "POST request required"
    ]);
    exit;
}

/* ================= INPUTS ================= */
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$location = trim($_POST['location'] ?? '');
$type = trim($_POST['type'] ?? '');
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;

/* ================= VALIDATION ================= */
if (!$id || !$name || !$location || !$type) {
    echo json_encode([
        "status" => "error",
        "msg" => "Missing Fields"
    ]);
    exit;
}

/* ================= FETCH EXISTING ITEM ================= */
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$oldItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oldItem) {
    echo json_encode([
        "status" => "error",
        "msg" => "Item Not Found"
    ]);
    exit;
}

/* ================= IMAGE HANDLING FIX ================= */
$image = $oldItem['image'];

/* ONLY REPLACE IF NEW IMAGE IS UPLOADED */
if (!empty($_FILES['image']['tmp_name'])) {

    $dir = "uploads/";

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

    $image = $dir . uniqid("img_", true) . "." . $ext;

    move_uploaded_file($_FILES['image']['tmp_name'], $image);
}

/* ================= UPDATE ITEM ================= */
$stmt = $pdo->prepare("
    UPDATE items 
    SET name = ?, location = ?, type = ?, qty = ?, image = ?
    WHERE id = ?
");

$success = $stmt->execute([
    $name,
    $location,
    $type,
    $qty,
    $image,
    $id
]);

/* ================= RESPONSE ================= */
if ($success) {
    echo json_encode([
        "status" => "success",
        "msg" => "Updated successfully",
        "image" => $image
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Update failed"
    ]);
}
?>
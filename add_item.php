<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {

    echo json_encode([
        "status" => "error",
        "msg" => "Unauthorized"
    ]);

    exit;
}

/* ================= VALIDATE ================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "status" => "error",
        "msg" => "Invalid Request"
    ]);

    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$name = trim($_POST['name'] ?? '');

$location = trim($_POST['location'] ?? '');

$type = trim($_POST['type'] ?? '');

$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;

if (!$id || !$name || !$location || !$type) {

    echo json_encode([
        "status" => "error",
        "msg" => "Missing Fields"
    ]);

    exit;
}

/* ================= KEEP OLD IMAGE ================= */

$stmt = $pdo->prepare("
    SELECT image
    FROM items
    WHERE id = ?
");

$stmt->execute([$id]);

$oldItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oldItem) {

    echo json_encode([
        "status" => "error",
        "msg" => "Item Not Found"
    ]);

    exit;
}

$image = $oldItem['image'];

/* ================= UPDATE ITEM ================= */

$stmt = $pdo->prepare("
    UPDATE items
    SET
        name = ?,
        location = ?,
        type = ?,
        qty = ?,
        image = ?
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

if ($success) {

    echo json_encode([
        "status" => "success"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "msg" => "Update Failed"
    ]);
}
?>
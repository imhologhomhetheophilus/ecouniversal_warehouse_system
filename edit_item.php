<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

header("Content-Type: application/json");

/* ================= GET ITEM (FOR EDIT FORM LOAD) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(["status" => "error", "msg" => "Invalid ID"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(["status" => "error", "msg" => "Item not found"]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "data" => $item
    ]);
    exit;
}

/* ================= UPDATE ITEM ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(["status" => "error", "msg" => "Invalid ID"]);
        exit;
    }

    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $type = $_POST['type'] ?? '';
    $qty = (int)($_POST['qty'] ?? 0);

    /* GET CURRENT IMAGE */
    $stmt = $pdo->prepare("SELECT image FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        echo json_encode(["status" => "error", "msg" => "Item not found"]);
        exit;
    }

    $image = $current['image'];

    /* HANDLE NEW IMAGE (OPTIONAL) */
    if (!empty($_FILES['image']['tmp_name'])) {

        $dir = "uploads/";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = $dir . uniqid() . "." . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE items 
            SET name=?, location=?, type=?, qty=?, image=?
            WHERE id=?
        ");

        $stmt->execute([$name, $location, $type, $qty, $image, $id]);

        echo json_encode(["status" => "success"]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "msg" => $e->getMessage()
        ]);
    }

    exit;
}
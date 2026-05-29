<?php

session_start();
include 'config/db.php';

/* ================= AUTH ================= */

if (!isset($_SESSION['admin'])) {

    echo json_encode([
        "status" => "error",
        "msg" => "Unauthorized"
    ]);

    exit;
}

/* ================= VALIDATE ID ================= */

if (!isset($_GET['id']) || empty($_GET['id'])) {

    die("Invalid Item ID");
}

$id = (int) $_GET['id'];

/* ================= DELETE ================= */

$stmt = $pdo->prepare("
    DELETE FROM items
    WHERE id = ?
");

$stmt->execute([$id]);

/* ================= REDIRECT ================= */

header("Location: dashboard.php");
exit;
?>
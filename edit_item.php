<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* ================= GET ITEM ================= */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found");
}

/* ================= UPDATE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $stmt = $pdo->prepare("
        UPDATE items 
        SET name=?, location=?, type=?, qty=? 
        WHERE id=?
    ");

    $stmt->execute([$name, $location, $type, $qty, $id]);

    header("Location: dashboard.php");
    exit;
}
?>

<form method="POST">
    <input type="hidden" name="id" value="<?= $item['id'] ?>">

    <input name="name" value="<?= $item['name'] ?>" required>
    <input name="location" value="<?= $item['location'] ?>" required>
    <input name="type" value="<?= $item['type'] ?>" required>
    <input name="qty" value="<?= $item['qty'] ?>" required>

    <button type="submit">Update</button>
</form>
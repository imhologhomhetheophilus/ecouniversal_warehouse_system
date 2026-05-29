<?php

session_start();
include 'config/db.php';

/* ================= AUTH ================= */

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* ================= VALIDATE ID ================= */

if (!isset($_GET['id']) || empty($_GET['id'])) {

    die("Invalid Item ID");
}

$id = (int) $_GET['id'];

/* ================= FETCH ITEM ================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM items
    WHERE id = ?
");

$stmt->execute([$id]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {

    die("Item not found");
}

/* ================= UPDATE ================= */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $type = trim($_POST['type']);
    $qty = (int) $_POST['qty'];

    $stmt = $pdo->prepare("
        UPDATE items
        SET
            name = ?,
            location = ?,
            type = ?,
            qty = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $location,
        $type,
        $qty,
        $id
    ]);

    echo "
    <script>
        alert('Item updated successfully');
        window.location.href='dashboard.php';
    </script>
    ";

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Item</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}

.container{
    max-width:600px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

h2{
    margin-top:0;
}

input{
    width:100%;
    padding:12px;
    margin-top:12px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:14px;
    margin-top:18px;
    border:none;
    background:#007bff;
    color:#fff;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#0056b3;
}

.back{
    text-decoration:none;
    color:#007bff;
    display:inline-block;
    margin-bottom:15px;
}

</style>

</head>

<body>

<div class="container">

<a href="dashboard.php" class="back">
    ← Back to Dashboard
</a>

<h2>Edit Item</h2>

<form method="POST">

    <input
        type="text"
        name="name"
        value="<?= htmlspecialchars($item['name']) ?>"
        required
    >

    <input
        type="text"
        name="location"
        value="<?= htmlspecialchars($item['location']) ?>"
        required
    >

    <input
        type="text"
        name="type"
        value="<?= htmlspecialchars($item['type']) ?>"
        required
    >

    <input
        type="number"
        name="qty"
        value="<?= (int)$item['qty'] ?>"
        required
    >

    <button type="submit">
        Update Item
    </button>

</form>

</div>

</body>
</html>
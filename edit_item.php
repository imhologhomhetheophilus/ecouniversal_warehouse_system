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
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $type = trim($_POST['type']);
    $qty = (int)$_POST['qty'];

    /* keep old image */
    $image = $item['image'];

    /* handle new image upload */
    if (!empty($_FILES['image']['tmp_name'])) {

        $dir = "uploads/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = $dir . uniqid() . "." . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $stmt = $pdo->prepare("
        UPDATE items 
        SET name=?, location=?, type=?, qty=?, image=?
        WHERE id=?
    ");

    $stmt->execute([$name, $location, $type, $qty, $image, $id]);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
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
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    font-weight:bold;
    margin-bottom:5px;
}

input{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
}

img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:10px;
    margin-bottom:10px;
}

.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#007bff;
    color:#fff;
    cursor:pointer;
    font-size:16px;
}

.btn:hover{
    background:#0056b3;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#007bff;
}

@media(max-width:600px){
    .container{
        margin:20px;
    }
}
</style>

</head>

<body>

<div class="container">

<a class="back" href="dashboard.php">← Back to Dashboard</a>

<h2>Edit Item</h2>

<!-- CURRENT IMAGE -->
<div>
    <p><b>Current Image</b></p>
    <?php if($item['image']): ?>
        <img src="<?= $item['image'] ?>">
    <?php else: ?>
        <p>No image</p>
    <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $item['id'] ?>">

    <div class="form-group">
        <label>Name</label>
        <input name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Location</label>
        <input name="location" value="<?= htmlspecialchars($item['location']) ?>" required>
    </div>

    <div class="form-group">
        <label>Type</label>
        <input name="type" value="<?= htmlspecialchars($item['type']) ?>" required>
    </div>

    <div class="form-group">
        <label>Quantity</label>
        <input name="qty" type="number" value="<?= $item['qty'] ?>" required>
    </div>

    <div class="form-group">
        <label>Change Image (optional)</label>
        <input type="file" name="image">
    </div>

    <button class="btn" type="submit">Update Item</button>

</form>

</div>

</body>
</html>
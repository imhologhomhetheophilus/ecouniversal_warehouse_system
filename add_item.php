<?php
session_start();
include 'config/db.php';
include 'includes/upload.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* ================= LOCATIONS ================= */

$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

/* ================= HANDLE FORM ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $location = $_POST['location'] ?? '';
    $type = $_POST['type'] ?? '';
    $qty = (int)($_POST['qty'] ?? 0);

    if ($name === '' || $location === '' || $type === '') {
        die("Missing required fields");
    }

    $image = null;

    /* ================= CLOUDINARY UPLOAD ================= */

    if (!empty($_FILES['image']['tmp_name'])) {

        $image = uploadToCloudinary($_FILES['image']['tmp_name']);

        if (!$image) {
            die("Image upload failed");
        }
    }

    /* ================= INSERT INTO DATABASE ================= */

    $stmt = $pdo->prepare("
        INSERT INTO items (name, location, type, qty, image)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$name, $location, $type, $qty, $image]);

    /* ================= SUCCESS ================= */

    echo "<script>
        alert('Item added successfully');
        window.location.href='dashboard.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body{
            font-family:Arial;
            background:#f4f6f9;
            padding:20px;
        }

        .box{
            max-width:500px;
            background:#fff;
            padding:20px;
            border-radius:10px;
            margin:auto;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        }

        input, select{
            width:100%;
            padding:10px;
            margin-bottom:10px;
            border:1px solid #ccc;
            border-radius:6px;
        }

        button{
            width:100%;
            padding:10px;
            background:#007bff;
            color:#fff;
            border:none;
            border-radius:6px;
            cursor:pointer;
        }

        button:hover{
            background:#0056b3;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>Add Item</h2>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="name" placeholder="Item Name" required>

        <select name="location" required>
            <option value="">Select Location</option>
            <?php foreach($locations as $loc): ?>
                <option value="<?= $loc ?>"><?= $loc ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="type" placeholder="Type" required>

        <input type="number" name="qty" placeholder="Quantity" required>

        <input type="file" name="image" accept="image/*">

        <button type="submit">Add Item</button>

    </form>

</div>

</body>
</html>
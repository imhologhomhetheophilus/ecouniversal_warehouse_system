<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* CACHE ITEMS (FAST AUTOCOMPLETE) */
$itemNames = json_decode(file_get_contents("cache/items.json"), true);
if(!$itemNames){
    $stmt = $pdo->query("SELECT DISTINCT name FROM items ORDER BY name");
    $itemNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if(!is_dir("cache")) mkdir("cache", 0777, true);
    file_put_contents("cache/items.json", json_encode($itemNames));
}

/* LOCATIONS */
$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $image = null;

    /* FAST UPLOAD */
    if (!empty($_FILES['image']['tmp_name'])) {

        $dir = "uploads/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = $dir . uniqid() . "." . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    /* SINGLE FAST INSERT */
    $stmt = $pdo->prepare("
        INSERT INTO items (name, location, type, qty, image)
        VALUES (:name, :location, :type, :qty, :image)
    ");

    $stmt->execute([
        ':name' => $name,
        ':location' => $location,
        ':type' => $type,
        ':qty' => $qty,
        ':image' => $image
    ]);

    /* UPDATE CACHE */
    $itemNames[] = $name;
    file_put_contents("cache/items.json", json_encode(array_values(array_unique($itemNames))));

    echo "<script>
        alert('Item added successfully');
        window.location.href='dashboard.php';
    </script>";
    exit;
}
?>
<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* ================= SAFE CACHE FIX ================= */

if (!is_dir("cache")) {
    mkdir("cache", 0777, true);
}

if (!file_exists("cache/items.json")) {
    file_put_contents("cache/items.json", json_encode([]));
}

$itemNames = json_decode(file_get_contents("cache/items.json"), true);

if (!$itemNames || !is_array($itemNames)) {

    $stmt = $pdo->query("SELECT DISTINCT name FROM items ORDER BY name");
    $itemNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    file_put_contents(
        "cache/items.json",
        json_encode($itemNames)
    );
}

/* ================= LOCATIONS ================= */

$locations = [
    'Ware Shop2',
    'Warehouse MD',
    'Warehouse Handle',
    'Warehouse MD Opposite',
    'Warehouse Down',
    'Warehouse Upstair',
    'Warehouse Kugbo',
    'Warehouse Karu',
    'Shop 1',
    'Pannel Shop',
    'Shop 2',
    'Deidei Warehouse',
    'Deidei Shop'
];

/* ================= ADD ITEM ================= */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $image = null;

    /* IMAGE UPLOAD */
    if (!empty($_FILES['image']['tmp_name'])) {

        $dir = "uploads/";

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $image = $dir . uniqid() . "." . $ext;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $image
        );
    }

    /* INSERT ITEM */
    $stmt = $pdo->prepare("
        INSERT INTO items
        (name, location, type, qty, image)
        VALUES
        (:name, :location, :type, :qty, :image)
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

    $itemNames = array_values(array_unique($itemNames));

    file_put_contents(
        "cache/items.json",
        json_encode($itemNames)
    );

    echo "
    <script>
        alert('Item added successfully');
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

<title>Add Item</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}

.container{
    max-width:700px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

h2{
    margin-top:0;
    color:#222;
}

input,
select{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:14px;
    border:none;
    background:#007bff;
    color:#fff;
    border-radius:8px;
    margin-top:15px;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#0056b3;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#007bff;
}

.preview{
    width:100px;
    height:100px;
    object-fit:cover;
    border-radius:10px;
    margin-top:10px;
    display:none;
}

@media(max-width:768px){

    .container{
        margin:10px;
        padding:18px;
    }

}

</style>
</head>

<body>

<div class="container">

<a href="dashboard.php" class="back">← Back to Dashboard</a>

<h2>Add Inventory Item</h2>

<form method="POST" enctype="multipart/form-data">

    <input
        type="text"
        name="name"
        list="itemList"
        placeholder="Item Name"
        required
    >

    <datalist id="itemList">
        <?php foreach($itemNames as $item): ?>
            <option value="<?= htmlspecialchars($item) ?>">
        <?php endforeach; ?>
    </datalist>

    <select name="location" required>
        <option value="">Select Location</option>

        <?php foreach($locations as $loc): ?>

            <option value="<?= $loc ?>">
                <?= $loc ?>
            </option>

        <?php endforeach; ?>

    </select>

    <input
        type="text"
        name="type"
        placeholder="Item Type"
        required
    >

    <input
        type="number"
        name="qty"
        placeholder="Quantity"
        required
    >

    <input
        type="file"
        name="image"
        accept="image/*"
        onchange="previewImage(event)"
    >

    <img id="preview" class="preview">

    <button type="submit">
        Add Item
    </button>

</form>

</div>

<script>

function previewImage(event){

    let file = event.target.files[0];

    if(file){

        let reader = new FileReader();

        reader.onload = function(e){

            let img = document.getElementById('preview');

            img.src = e.target.result;
            img.style.display = 'block';
        }

        reader.readAsDataURL(file);
    }
}

</script>

</body>
</html>
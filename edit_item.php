<?php
session_start();

include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* -------------------------
   Validate ID
--------------------------*/
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

/* -------------------------
   Fetch item (PDO)
--------------------------*/
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found");
}

$msg = "";

/* -------------------------
   Fetch item names
--------------------------*/
$stmt = $pdo->query("SELECT DISTINCT name FROM items ORDER BY name");
$itemNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* -------------------------
   Handle update
--------------------------*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $image = $item['image'];

    /* ---------------------
       Upload new image
    ----------------------*/
    if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {

        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image = $target_dir . time() . "_" . basename($_FILES['image']['name']);

        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    /* ---------------------
       Update query (PDO)
    ----------------------*/
    $stmt = $pdo->prepare("
        UPDATE items
        SET name = ?, location = ?, type = ?, qty = ?, image = ?
        WHERE id = ?
    ");

    $stmt->execute([$name, $location, $type, $qty, $image, $id]);

    echo "<script>
        alert('Item updated successfully');
        window.location.href='dashboard.php';
    </script>";
    exit;
}

/* -------------------------
   Locations
--------------------------*/
$locs = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Item</title>

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    margin:0;
    padding:20px;
}

.container{
    max-width:600px;
    margin:0 auto;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

form{
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

input,select,button{
    padding:12px;
    margin:10px 0;
    width:100%;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:1em;
}

button{
    background:#007bff;
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    background:#0056b3;
}

a{
    text-align:center;
    display:block;
    margin-top:15px;
    text-decoration:none;
    color:#007bff;
}

img.current-img{
    max-width:120px;
    border-radius:6px;
    margin:10px 0;
}
</style>

</head>

<body>

<div class="container">

<h2>Edit Item</h2>

<form method="post" enctype="multipart/form-data">

<label>Item Name</label>
<input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>

<label>Location</label>
<select name="location" required>
    <option value="">Select Location</option>
    <?php foreach ($locs as $l): ?>
        <option value="<?= htmlspecialchars($l) ?>"
            <?= $item['location'] == $l ? 'selected' : '' ?>>
            <?= htmlspecialchars($l) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Type</label>
<select name="type" required>
    <option value="Packet" <?= $item['type'] == 'Packet' ? 'selected' : '' ?>>Packet</option>
    <option value="Pieces" <?= $item['type'] == 'Pieces' ? 'selected' : '' ?>>Pieces</option>
    <option value="Carton" <?= $item['type'] == 'Carton' ? 'selected' : '' ?>>Carton</option>
    <option value="Roll" <?= $item['type'] == 'Roll' ? 'selected' : '' ?>>Roll</option>
</select>

<label>Quantity</label>
<input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" required>

<?php if ($item['image']): ?>
    <label>Current Image</label>
    <img src="<?= htmlspecialchars($item['image']) ?>" class="current-img">
<?php endif; ?>

<label>Change Image</label>
<input type="file" name="image" accept="image/*">

<button type="submit">Update Item</button>

</form>

<a href="dashboard.php">Back to Dashboard</a>

</div>

</body>
</html>
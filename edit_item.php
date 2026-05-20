<?php
session_start();

include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $image = $item['image'];

    if (!empty($_FILES['image']['name'])) {

        $dir = "uploads/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $image = $dir . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $stmt = $pdo->prepare("
        UPDATE items
        SET name=?, location=?, type=?, qty=?, image=?
        WHERE id=?
    ");

    $stmt->execute([$name, $location, $type, $qty, $image, $id]);

    echo "<script>
        alert('Item updated successfully');
        window.location.href='dashboard.php';
    </script>";
    exit;
}

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

/* =========================
   MOBILE FIRST
========================= */

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    padding:15px;
}

/* container */
.container{
    max-width:520px;
    margin:auto;
}

/* title */
h2{
    text-align:center;
    font-size:1.4rem;
    margin-bottom:15px;
}

/* form card */
form{
    background:#fff;
    padding:18px;
    border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
}

/* labels */
label{
    display:block;
    margin-top:12px;
    font-size:0.9rem;
    font-weight:600;
}

/* inputs */
input,select,button{
    width:100%;
    padding:14px;
    margin-top:6px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:1rem;
    box-sizing:border-box;
}

/* button */
button{
    margin-top:18px;
    background:#007bff;
    color:#fff;
    border:none;
    font-weight:600;
}

button:active{
    transform:scale(0.98);
}

/* image preview */
img.current-img{
    width:100%;
    max-width:160px;
    display:block;
    margin-top:10px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

/* back link */
a{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#007bff;
    text-decoration:none;
    font-size:0.95rem;
}

/* =========================
   TABLET
========================= */
@media(min-width:600px){
    body{padding:25px;}
    h2{font-size:1.7rem;}
    form{padding:25px;}
}

/* =========================
   DESKTOP
========================= */
@media(min-width:992px){
    .container{max-width:600px;}
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
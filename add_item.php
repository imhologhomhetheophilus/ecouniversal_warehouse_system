<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$msg = "";

/* -----------------------------
   Fetch existing item names
------------------------------*/
$itemNames = [];

$stmt = $pdo->query("SELECT DISTINCT name FROM items ORDER BY name");
$itemNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* -----------------------------
   Locations
------------------------------*/
$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

/* -----------------------------
   Handle POST
------------------------------*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $image = '';

    /* -------------------------
       Upload Image
    --------------------------*/
    if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {

        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image = $target_dir . time() . "_" . basename($_FILES['image']['name']);

        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    /* -------------------------
       Insert using PDO
    --------------------------*/
    $stmt = $pdo->prepare("
        INSERT INTO items (name, location, type, qty, image)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$name, $location, $type, $qty, $image]);

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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Imhotech Inventory System</title>

<style>
body{
    margin:0;
    padding:20px;
    font-family:Arial;
    background:#f4f6f9;
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

select,input,button{
    padding:12px;
    margin:10px 0;
    width:100%;
    border-radius:6px;
    border:1px solid #ccc;
    box-sizing:border-box;
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

/* Autocomplete */
.autocomplete-items{
    position:absolute;
    border:1px solid #d4d4d4;
    z-index:99;
    background:#fff;
    max-height:200px;
    overflow-y:auto;
}

.autocomplete-items div{
    padding:10px;
    cursor:pointer;
}

.autocomplete-items div:hover{
    background:#e9e9e9;
}
</style>

</head>

<body>

<div class="container">

<h2>Add New Item</h2>

<form method="post" enctype="multipart/form-data" autocomplete="off">

<label>Item Name</label>
<input type="text" id="itemName" name="name" required>

<label>Location</label>
<select name="location" required>
    <option value="">Select Location</option>
    <?php foreach ($locations as $loc): ?>
        <option value="<?= htmlspecialchars($loc) ?>">
            <?= htmlspecialchars($loc) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Type</label>
<select name="type" required>
    <option value="Packet">Packet</option>
    <option value="Pieces">Pieces</option>
    <option value="Carton">Carton</option>
    <option value="Roll">Roll</option>
</select>

<label>Quantity</label>
<input type="number" name="qty" min="1" required>

<label>Image (optional)</label>
<input type="file" name="image" accept="image/*">

<button type="submit">Add Item</button>

</form>

<a href="dashboard.php">Back to Dashboard</a>

</div>

<script>
const items = <?= json_encode($itemNames) ?>;

function autocomplete(inp, arr) {
    let currentFocus;

    inp.addEventListener("input", function () {
        let val = this.value;

        closeAllLists();

        if (!val) return false;

        let list = document.createElement("DIV");
        list.className = "autocomplete-items";
        this.parentNode.appendChild(list);

        arr.forEach(function (item) {
            if (item.toLowerCase().startsWith(val.toLowerCase())) {

                let div = document.createElement("DIV");
                div.innerHTML = "<strong>" + item.substr(0, val.length) + "</strong>" + item.substr(val.length);
                div.innerHTML += "<input type='hidden' value='" + item + "'>";

                div.addEventListener("click", function () {
                    inp.value = this.getElementsByTagName("input")[0].value;
                    closeAllLists();
                });

                list.appendChild(div);
            }
        });
    });

    function closeAllLists() {
        document.querySelectorAll(".autocomplete-items").forEach(el => el.remove());
    }

    document.addEventListener("click", closeAllLists);
}

autocomplete(document.getElementById("itemName"), items);
</script>

</body>
</html>
<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$msg = "";

/* Items */
$stmt = $pdo->query("SELECT DISTINCT name FROM items ORDER BY name");
$itemNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* Locations */
$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $qty = (int)$_POST['qty'];

    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $dir = "uploads/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $image = $dir . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

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
<title>Imhotech Inventory</title>

<style>
/* =========================
   MOBILE FIRST DESIGN
========================= */

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    padding:15px;
}

/* Container */
.container{
    width:100%;
    max-width:520px;
    margin:auto;
}

/* Title */
h2{
    text-align:center;
    font-size:1.4rem;
    margin-bottom:15px;
}

/* Card form */
form{
    background:#fff;
    padding:18px;
    border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
}

/* Inputs */
label{
    display:block;
    margin-top:12px;
    font-size:0.9rem;
    font-weight:600;
}

input,select,button{
    width:100%;
    padding:14px;
    margin-top:6px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:1rem;
    box-sizing:border-box;
}

/* Button */
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

/* Back link */
a{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#007bff;
    text-decoration:none;
}

/* AUTOCOMPLETE */
.autocomplete-items{
    position:absolute;
    background:#fff;
    border:1px solid #ddd;
    max-height:180px;
    overflow-y:auto;
    z-index:1000;
    width:100%;
    border-radius:0 0 8px 8px;
}

.autocomplete-items div{
    padding:12px;
    cursor:pointer;
    font-size:0.95rem;
}

.autocomplete-items div:hover{
    background:#f1f1f1;
}

/* =========================
   TABLET
========================= */
@media (min-width: 600px){
    body{
        padding:25px;
    }

    h2{
        font-size:1.7rem;
    }

    form{
        padding:25px;
    }
}

/* =========================
   DESKTOP
========================= */
@media (min-width: 992px){
    .container{
        max-width:600px;
    }

    form{
        padding:30px;
    }

    input,select,button{
        padding:12px;
    }
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
    <option>Packet</option>
    <option>Pieces</option>
    <option>Carton</option>
    <option>Roll</option>
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

function autocomplete(inp, arr){
    inp.addEventListener("input", function(){
        closeAll();

        if(!this.value) return;

        const box = document.createElement("div");
        box.className = "autocomplete-items";
        this.parentNode.appendChild(box);

        arr.forEach(item=>{
            if(item.toLowerCase().startsWith(this.value.toLowerCase())){
                const div = document.createElement("div");
                div.textContent = item;

                div.onclick = ()=>{
                    inp.value = item;
                    closeAll();
                };

                box.appendChild(div);
            }
        });
    });

    function closeAll(){
        document.querySelectorAll(".autocomplete-items").forEach(e=>e.remove());
    }

    document.addEventListener("click", closeAll);
}

autocomplete(document.getElementById("itemName"), items);
</script>

</body>
</html>
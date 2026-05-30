<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* FETCH ITEMS */
$stmt = $pdo->query("
    SELECT id, name, location, qty, type, image
    FROM items
    WHERE qty > 0
    ORDER BY name ASC
");

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* LOCATIONS */
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

$msg = "";

/* HANDLE TRANSFER */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        $pdo->beginTransaction();

        $item_id = (int)$_POST['item_id'];
        $to_loc = trim($_POST['to_loc']);
        $qty = (int)$_POST['qty'];

        /* GET SOURCE ITEM */
        $stmt = $pdo->prepare("
            SELECT *
            FROM items
            WHERE id = ?
        ");

        $stmt->execute([$item_id]);

        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception("Item not found");
        }

        if ($qty <= 0) {
            throw new Exception("Invalid quantity");
        }

        if ($item['qty'] < $qty) {
            throw new Exception("Insufficient stock");
        }

        if ($item['location'] == $to_loc) {
            throw new Exception("Cannot transfer to same location");
        }

        /* REMOVE STOCK FROM SOURCE */
        $stmt = $pdo->prepare("
            UPDATE items
            SET qty = qty - ?
            WHERE id = ?
        ");

        $stmt->execute([$qty, $item_id]);

        /* CHECK DESTINATION ITEM */
        $stmt = $pdo->prepare("
            SELECT id
            FROM items
            WHERE name = ?
            AND location = ?
            LIMIT 1
        ");

        $stmt->execute([
            $item['name'],
            $to_loc
        ]);

        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {

            /* UPDATE DESTINATION */
            $stmt = $pdo->prepare("
                UPDATE items
                SET qty = qty + ?
                WHERE id = ?
            ");

            $stmt->execute([
                $qty,
                $existing['id']
            ]);

        } else {

            /* CREATE NEW DESTINATION ITEM */
            $stmt = $pdo->prepare("
                INSERT INTO items
                (name, location, type, qty, image)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $item['name'],
                $to_loc,
                $item['type'],
                $qty,
                $item['image']
            ]);
        }

        /* DELETE SOURCE ITEM IF EMPTY */
        $stmt = $pdo->prepare("
            DELETE FROM items
            WHERE id = ?
            AND qty <= 0
        ");

        $stmt->execute([$item_id]);

        /* LOG TRANSFER */
        $stmt = $pdo->prepare("
            INSERT INTO transfers
            (item, from_loc, to_loc, qty, date)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $item['name'],
            $item['location'],
            $to_loc,
            $qty
        ]);

        $pdo->commit();

        echo "
        <script>
            alert('Transfer successful');
            window.location.href='dashboard.php';
        </script>
        ";

        exit;

    } catch(Exception $e) {

        $pdo->rollBack();

        $msg = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Transfer Item</title>

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
}

.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#007bff;
}

input,
select{
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
    margin-top:15px;
    border:none;
    background:#007bff;
    color:#fff;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#0056b3;
}

.error{
    background:#ffebee;
    color:#c62828;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.item-preview{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:10px;
    background:#f7f7f7;
    padding:10px;
    border-radius:8px;
}

.item-preview img{
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:8px;
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

<a href="dashboard.php" class="back">
    ← Back to Dashboard
</a>

<h2>Transfer Inventory Item</h2>

<?php if($msg): ?>

<div class="error">
    <?= htmlspecialchars($msg) ?>
</div>

<?php endif; ?>

<form method="POST">

    <select name="item_id" required id="itemSelect">

        <option value="">
            Select Item
        </option>

        <?php foreach($items as $item): ?>

        <option
            value="<?= $item['id'] ?>"
            data-location="<?= htmlspecialchars($item['location']) ?>"
        >
            <?= htmlspecialchars($item['name']) ?>
            -
            <?= htmlspecialchars($item['location']) ?>
            (Qty: <?= (int)$item['qty'] ?>)
        </option>

        <?php endforeach; ?>

    </select>

    <input
        type="text"
        id="fromLocation"
        placeholder="From Location"
        readonly
    >

    <select name="to_loc" required>

        <option value="">
            Select Destination
        </option>

        <?php foreach($locations as $loc): ?>

        <option value="<?= $loc ?>">
            <?= $loc ?>
        </option>

        <?php endforeach; ?>

    </select>

    <input
        type="number"
        name="qty"
        min="1"
        placeholder="Quantity"
        required
    >

    <button type="submit">
        Transfer Item
    </button>

</form>

</div>

<script>

const itemSelect = document.getElementById('itemSelect');
const fromLocation = document.getElementById('fromLocation');

itemSelect.addEventListener('change', function(){

    const option = this.options[this.selectedIndex];

    fromLocation.value =
        option.getAttribute('data-location') || '';

});

</script>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

$msg = "";

/* -------------------------
   Fetch items
--------------------------*/
$stmt = $pdo->query("SELECT * FROM items ORDER BY name");
$items = $stmt->fetchAll();

/* -------------------------
   Locations
--------------------------*/
$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

/* -------------------------
   Handle transfer
--------------------------*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $item_id = (int) $_POST['item_id'];
    $from_loc = $_POST['from_loc'];
    $to_loc = $_POST['to_loc'];
    $qty = (int) $_POST['qty'];

    /* -------------------------
       Get item
    --------------------------*/
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if ($item && $item['qty'] >= $qty) {

        /* -------------------------
           Subtract from source
        --------------------------*/
        $new_qty_from = $item['qty'] - $qty;

        $stmt = $pdo->prepare("UPDATE items SET qty = ? WHERE id = ?");
        $stmt->execute([$new_qty_from, $item_id]);

        /* -------------------------
           Add to destination
        --------------------------*/
        $stmt = $pdo->prepare("
            SELECT * FROM items
            WHERE name = ? AND location = ?
        ");
        $stmt->execute([$item['name'], $to_loc]);
        $dest_item = $stmt->fetch();

        if ($dest_item) {

            $new_qty_to = $dest_item['qty'] + $qty;

            $stmt = $pdo->prepare("UPDATE items SET qty = ? WHERE id = ?");
            $stmt->execute([$new_qty_to, $dest_item['id']]);

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO items (name, location, type, qty, image)
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

        /* -------------------------
           Record transfer
        --------------------------*/
        $stmt = $pdo->prepare("
            INSERT INTO transfers (item, from_loc, to_loc, qty, date)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $item['name'],
            $from_loc,
            $to_loc,
            $qty
        ]);

        echo "<script>
            alert('Transfer Successful');
            window.location.href='dashboard.php';
        </script>";
        exit;

    } else {
        $msg = "Insufficient quantity in source location!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transfer Item</title>

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    margin:0;
    padding:20px;
}

.container{
    max-width:500px;
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

.msg{
    color:red;
    text-align:center;
    margin-bottom:10px;
}

a{
    text-align:center;
    display:block;
    margin-top:15px;
    text-decoration:none;
    color:#007bff;
}
</style>

</head>

<body>

<div class="container">

<h2>Transfer Item</h2>

<?php if ($msg): ?>
    <div class="msg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post">

<label>Item</label>
<select name="item_id" required>
    <option value="">Select Item</option>
    <?php foreach ($items as $i): ?>
        <option value="<?= $i['id'] ?>">
            <?= htmlspecialchars($i['name']) ?>
            (<?= $i['qty'] ?> in <?= htmlspecialchars($i['location']) ?>)
        </option>
    <?php endforeach; ?>
</select>

<label>From Location</label>
<select name="from_loc" required>
    <option value="">Select Source</option>
    <?php foreach ($locations as $loc): ?>
        <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
    <?php endforeach; ?>
</select>

<label>To Location</label>
<select name="to_loc" required>
    <option value="">Select Destination</option>
    <?php foreach ($locations as $loc): ?>
        <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
    <?php endforeach; ?>
</select>

<label>Quantity</label>
<input type="number" name="qty" min="1" required>

<button type="submit">Transfer</button>

</form>

<a href="dashboard.php">Back to Dashboard</a>

</div>

</body>
</html>
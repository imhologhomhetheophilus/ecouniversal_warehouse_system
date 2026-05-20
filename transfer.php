<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

$msg = "";

$stmt = $pdo->query("SELECT * FROM items ORDER BY name");
$items = $stmt->fetchAll();

$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $item_id = (int) $_POST['item_id'];
    $from_loc = $_POST['from_loc'];
    $to_loc = $_POST['to_loc'];
    $qty = (int) $_POST['qty'];

    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if ($item && $item['qty'] >= $qty) {

        $stmt = $pdo->prepare("UPDATE items SET qty = qty - ? WHERE id = ?");
        $stmt->execute([$qty, $item_id]);

        $stmt = $pdo->prepare("SELECT * FROM items WHERE name = ? AND location = ?");
        $stmt->execute([$item['name'], $to_loc]);
        $dest_item = $stmt->fetch();

        if ($dest_item) {

            $stmt = $pdo->prepare("UPDATE items SET qty = qty + ? WHERE id = ?");
            $stmt->execute([$qty, $dest_item['id']]);

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

/* =========================
   MOBILE FIRST DESIGN
========================= */

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    padding:16px;
}

.container{
    width:100%;
    max-width:520px;
    margin:auto;
}

/* Title */
h2{
    text-align:center;
    margin:10px 0 18px;
    font-size:1.4rem;
}

/* Card form */
form{
    background:#fff;
    padding:18px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

/* Labels */
label{
    display:block;
    margin-top:12px;
    font-size:0.95rem;
    font-weight:600;
    color:#333;
}

/* Inputs */
select, input{
    width:100%;
    padding:12px;
    margin-top:6px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:1rem;
    outline:none;
}

select:focus, input:focus{
    border-color:#007bff;
}

/* Button */
button{
    width:100%;
    padding:13px;
    margin-top:16px;
    border:none;
    border-radius:8px;
    background:#007bff;
    color:#fff;
    font-size:1rem;
    font-weight:600;
    cursor:pointer;
}

button:hover{
    background:#0056b3;
}

/* Message */
.msg{
    background:#ffe5e5;
    color:#b00020;
    padding:10px;
    border-radius:8px;
    text-align:center;
    margin-bottom:12px;
}

/* Back link */
a{
    display:block;
    text-align:center;
    margin-top:14px;
    color:#007bff;
    text-decoration:none;
    font-size:0.95rem;
}

/* =========================
   DESKTOP IMPROVEMENT
========================= */

@media(min-width:768px){
    h2{
        font-size:1.7rem;
    }

    form{
        padding:25px;
    }
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
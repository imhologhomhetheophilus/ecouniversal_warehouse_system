<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/* CACHE ITEMS */
$stmt = $pdo->query("SELECT id, name, location, qty, type, image FROM items");
$items = $stmt->fetchAll();

$locations = [
    'Ware Shop2','Warehouse MD','Warehouse Handle','Warehouse MD Opposite',
    'Warehouse Down','Warehouse Upstair','Warehouse Kugbo','Warehouse Karu',
    'Shop 1','Pannel Shop','Shop 2','Deidei Warehouse','Deidei Shop'
];

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        $pdo->beginTransaction();

        $item_id = (int)$_POST['item_id'];
        $from_loc = $_POST['from_loc'];
        $to_loc = $_POST['to_loc'];
        $qty = (int)$_POST['qty'];

        /* GET ITEM (FAST) */
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item || $item['qty'] < $qty) {
            throw new Exception("Insufficient stock");
        }

        /* DECREASE STOCK */
        $stmt = $pdo->prepare("UPDATE items SET qty = qty - ? WHERE id = ?");
        $stmt->execute([$qty, $item_id]);

        /* CHECK DESTINATION */
        $stmt = $pdo->prepare("
            SELECT id FROM items
            WHERE name = ? AND location = ?
            LIMIT 1
        ");
        $stmt->execute([$item['name'], $to_loc]);
        $dest = $stmt->fetch();

        if ($dest) {
            $stmt = $pdo->prepare("UPDATE items SET qty = qty + ? WHERE id = ?");
            $stmt->execute([$qty, $dest['id']]);
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

        /* LOG TRANSFER */
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

        $pdo->commit();

        echo "<script>
            alert('Transfer Successful');
            window.location.href='dashboard.php';
        </script>";
        exit;

    } catch(Exception $e){
        $pdo->rollBack();
        $msg = $e->getMessage();
    }
}
?>
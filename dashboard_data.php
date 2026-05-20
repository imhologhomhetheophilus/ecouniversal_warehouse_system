<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

include 'config/db.php';

header('Content-Type: application/json');

/* -------------------------
   Fetch items
--------------------------*/
$stmt = $pdo->query("SELECT * FROM items ORDER BY name ASC");
$items = $stmt->fetchAll();

/* -------------------------
   Low stock
--------------------------*/
$lowStock = [];

foreach ($items as $item) {
    if ((int)$item['qty'] < 10) {
        $lowStock[] = $item;
    }
}

/* -------------------------
   Location summary (chart)
--------------------------*/
$stmt = $pdo->query("
    SELECT location, SUM(qty) AS totalQty
    FROM items
    GROUP BY location
");

$chart = [
    'labels' => [],
    'data' => []
];

while ($row = $stmt->fetch()) {
    $chart['labels'][] = $row['location'];
    $chart['data'][] = (int)$row['totalQty'];
}

/* -------------------------
   Recent transfers
--------------------------*/
$stmt = $pdo->query("
    SELECT * FROM transfers
    ORDER BY date DESC
    LIMIT 1000
");

$transfers = $stmt->fetchAll();

/* -------------------------
   Summary calculations
--------------------------*/
$totalQty = 0;
$totalWarehouses = 0;
$totalShops = 0;

foreach ($items as $item) {
    $totalQty += (int)$item['qty'];

    if (stripos($item['location'], 'warehouse') !== false) {
        $totalWarehouses++;
    }

    if (stripos($item['location'], 'shop') !== false) {
        $totalShops++;
    }
}

/* -------------------------
   Return JSON response
--------------------------*/
echo json_encode([
    'items' => $items,
    'lowStock' => $lowStock,
    'chart' => $chart,
    'transfers' => $transfers,
    'totalQty' => $totalQty,
    'totalWarehouses' => $totalWarehouses,
    'totalShops' => $totalShops
]);

exit;
?>
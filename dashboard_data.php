<?php
include 'config/db.php';
header("Content-Type: application/json");

/* ================= ITEMS ================= */
$items = $pdo->query("
    SELECT id, name, location, type, qty, image
    FROM items
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= TRANSFERS ================= */
$transfers = $pdo->query("
    SELECT item, from_loc, to_loc, qty, date
    FROM transfers
    ORDER BY date DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= SUMMARY ================= */
$totalQty = 0;
$warehouses = [];
$shops = [];

foreach ($items as $i) {
    $totalQty += (int)$i['qty'];

    if (stripos($i['location'], 'warehouse') !== false) {
        $warehouses[$i['location']] = true;
    } else {
        $shops[$i['location']] = true;
    }
}

/* ================= LOW STOCK ================= */
$lowStock = array_values(array_filter($items, function($i){
    return (int)$i['qty'] <= 5;
}));

/* ================= CHART DATA ================= */
$chartLabels = [];
$chartData = [];

foreach ($items as $i) {
    $loc = $i['location'];

    if (!isset($chartData[$loc])) {
        $chartData[$loc] = 0;
        $chartLabels[] = $loc;
    }

    $chartData[$loc] += (int)$i['qty'];
}

/* ================= RESPONSE ================= */
echo json_encode([
    "items" => $items,
    "transfers" => $transfers,

    "totalQty" => $totalQty,
    "totalWarehouses" => count($warehouses),
    "totalShops" => count($shops),

    "lowStock" => $lowStock,

    "chart" => [
        "labels" => array_values($chartLabels),
        "data" => array_values($chartData)
    ]
]);
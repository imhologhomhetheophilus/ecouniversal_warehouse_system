<?php

session_start();
include 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode([
        "error" => "Unauthorized"
    ]);
    exit;
}

/* ================= ITEMS ================= */

$stmt = $pdo->query("
    SELECT
        id,
        name,
        location,
        type,
        qty,
        image
    FROM items
    ORDER BY id DESC
");

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= TOTAL QTY ================= */

$stmt = $pdo->query("
    SELECT SUM(qty) as totalQty
    FROM items
");

$totalQty = $stmt->fetch(PDO::FETCH_ASSOC)['totalQty'] ?? 0;

/* ================= COUNTS ================= */

$totalWarehouses = 0;
$totalShops = 0;

foreach($items as $i){

    if(stripos($i['location'], 'warehouse') !== false){
        $totalWarehouses++;
    }

    if(stripos($i['location'], 'shop') !== false){
        $totalShops++;
    }
}

/* ================= LOW STOCK ================= */

$stmt = $pdo->query("
    SELECT *
    FROM items
    WHERE qty <= 5
    ORDER BY qty ASC
");

$lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= CHART DATA ================= */

$stmt = $pdo->query("
    SELECT
        location,
        SUM(qty) as total
    FROM items
    GROUP BY location
");

$chartRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];

foreach($chartRows as $row){

    $labels[] = $row['location'];
    $data[] = (int)$row['total'];
}

/* ================= TRANSFERS ================= */

$stmt = $pdo->query("
    SELECT *
    FROM transfers
    ORDER BY date DESC
    LIMIT 20
");

$transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= OUTPUT ================= */

echo json_encode([

    "totalQty" => (int)$totalQty,

    "totalWarehouses" => $totalWarehouses,

    "totalShops" => $totalShops,

    "items" => $items,

    "lowStock" => $lowStock,

    "transfers" => $transfers,

    "chart" => [
        "labels" => $labels,
        "data" => $data
    ]

]);
<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode([
        'error' => 'Not logged in'
    ]);
    exit;
}

include 'config/db.php';

try {

    /* -------------------------
       ITEMS
    --------------------------*/
    $stmt = $pdo->query("SELECT * FROM items ORDER BY name ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* -------------------------
       LOW STOCK
    --------------------------*/
    $lowStock = array_values(array_filter($items, function($item) {
        return (int)$item['qty'] < 10;
    }));

    /* -------------------------
       LOCATION SUMMARY (FAST SQL RESULT)
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

    foreach ($stmt as $row) {
        $chart['labels'][] = $row['location'];
        $chart['data'][] = (int)$row['totalQty'];
    }

    /* -------------------------
       TRANSFERS
    --------------------------*/
    $stmt = $pdo->query("
        SELECT * FROM transfers
        ORDER BY date DESC
        LIMIT 1000
    ");

    $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* -------------------------
       SUMMARY (OPTIMIZED LOOP)
    --------------------------*/
    $totalQty = 0;
    $totalWarehouses = 0;
    $totalShops = 0;

    foreach ($items as $item) {

        $qty = (int)$item['qty'];
        $totalQty += $qty;

        $location = strtolower($item['location']);

        if (strpos($location, 'warehouse') !== false) {
            $totalWarehouses++;
        }

        if (strpos($location, 'shop') !== false) {
            $totalShops++;
        }
    }

    /* -------------------------
       RESPONSE
    --------------------------*/
    echo json_encode([
        'status' => 'success',
        'items' => $items,
        'lowStock' => $lowStock,
        'chart' => $chart,
        'transfers' => $transfers,
        'totalQty' => $totalQty,
        'totalWarehouses' => $totalWarehouses,
        'totalShops' => $totalShops
    ]);

} catch (Exception $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

exit;
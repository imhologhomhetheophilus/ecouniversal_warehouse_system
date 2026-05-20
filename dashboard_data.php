<?php
session_start();
if(!isset($_SESSION['admin'])){ exit(json_encode(['error'=>'Not logged in'])); }
include 'config/db.php';
header('Content-Type: application/json');

// Fetch items
$items=[];
$res = $conn->query("SELECT * FROM items ORDER BY name ASC");
while($row = $res->fetch_assoc()){
    $items[] = $row;
}

// Low stock
$lowStock = [];
foreach($items as $item){
    if($item['qty'] < 10){
        $lowStock[] = $item;
    }
}

// Locations summary
$locations = [];
$locRes = $conn->query("SELECT location, SUM(qty) as totalQty FROM items GROUP BY location");
$chart = ['labels'=>[], 'data'=>[]];
while($row = $locRes->fetch_assoc()){
    $chart['labels'][] = $row['location'];
    $chart['data'][] = (int)$row['totalQty'];
}

// Recent transfers
$transfers = [];
$tranRes = $conn->query("SELECT * FROM transfers ORDER BY date DESC LIMIT 1000");
while($row = $tranRes->fetch_assoc()){
    $transfers[] = $row;
}

// Summary
$totalQty = 0;
$totalWarehouses = 0;
$totalShops = 0;
foreach($chart['labels'] as $loc){
    if(stripos($loc,'warehouse')!==false) $totalWarehouses++;
    if(stripos($loc,'shop')!==false) $totalShops++;
}
foreach($items as $item){ $totalQty += (int)$item['qty']; }

echo json_encode([
    'items'=>$items,
    'lowStock'=>$lowStock,
    'chart'=>$chart,
    'transfers'=>$transfers,
    'totalQty'=>$totalQty,
    'totalWarehouses'=>$totalWarehouses,
    'totalShops'=>$totalShops
]);
exit;
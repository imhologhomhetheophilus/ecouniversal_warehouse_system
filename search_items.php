<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

/* --------------------------
   Auth guard
--------------------------*/
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Forbidden',
        'data' => []
    ]);
    exit;
}

include 'config/db.php';

/* --------------------------
   Get search query safely
--------------------------*/
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

/* --------------------------
   Prepare search pattern
--------------------------*/
$search = "%{$q}%";

/* --------------------------
   Query database (safe)
--------------------------*/
$stmt = $pdo->prepare("
    SELECT id, name, location, qty, image
    FROM items
    WHERE name LIKE ?
       OR location LIKE ?
    ORDER BY location ASC, name ASC
    LIMIT 10
");

$stmt->execute([$search, $search]);

/* --------------------------
   Build response
--------------------------*/
$results = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $results[] = [
        'id'       => (int)$row['id'],
        'name'     => $row['name'],
        'location' => $row['location'],
        'qty'      => (int)$row['qty'],
        'image'    => $row['image'] ?: null
    ];
}

/* --------------------------
   Output JSON
--------------------------*/
echo json_encode([
    'count' => count($results),
    'data'  => $results
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

exit;
?>
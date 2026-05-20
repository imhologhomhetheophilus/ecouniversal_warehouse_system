<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode([]);
    exit;
}

include 'config/db.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$results = [];

if ($q !== '') {

    $search = "%$q%";

    $stmt = $pdo->prepare("
        SELECT * FROM items
        WHERE name LIKE ?
           OR location LIKE ?
        ORDER BY location, name
        LIMIT 10
    ");

    $stmt->execute([$search, $search]);

    while ($row = $stmt->fetch()) {
        $results[] = [
            'name' => $row['name'],
            'location' => $row['location'],
            'qty' => (int)$row['qty'],
            'image' => $row['image'] ?? ''
        ];
    }
}

echo json_encode($results);
exit;
?>
<?php
session_start();
if(!isset($_SESSION['admin'])){ header("HTTP/1.1 403 Forbidden"); exit; }
include 'config/db.php';

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$results = [];

if($q !== ''){
    $res = $conn->query("SELECT * FROM items WHERE name LIKE '%$q%' OR location LIKE '%$q%' ORDER BY location,name LIMIT 10");
    while($row = $res->fetch_assoc()){
        $results[] = [
            'name'=>$row['name'],
            'location'=>$row['location'],
            'qty'=>$row['qty'],
            'image'=>$row['image'] ?: ''
        ];
    }
}

echo json_encode($results);
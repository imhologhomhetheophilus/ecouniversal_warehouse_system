<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

/* -------------------------
   Fetch transfers (PDO)
--------------------------*/
$stmt = $pdo->query("SELECT * FROM transfers ORDER BY date DESC");
$transfers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transfer History</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
}

.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
}

.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    background: #333;
    color: #fff;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
}

.back-btn:hover {
    background: #555;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

.table-wrapper {
    overflow-x: auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}

th {
    background: #007bff;
    color: #fff;
}

tr:nth-child(even) {
    background: #f9f9f9;
}

tr:hover {
    background: #e9f2ff;
}

/* Mobile */
@media(max-width:600px){
    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }

    thead {
        display: none;
    }

    tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
    }

    td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
        text-align: left;
    }
}
</style>

</head>

<body>

<div class="container">

<a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<h2>Transfer History</h2>

<div class="table-wrapper">

<table>
<thead>
<tr>
    <th>Item</th>
    <th>From</th>
    <th>To</th>
    <th>Qty</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

<?php foreach ($transfers as $t): ?>
<tr>
    <td data-label="Item"><?= htmlspecialchars($t['item']) ?></td>
    <td data-label="From"><?= htmlspecialchars($t['from_loc']) ?></td>
    <td data-label="To"><?= htmlspecialchars($t['to_loc']) ?></td>
    <td data-label="Qty"><?= (int)$t['qty'] ?></td>
    <td data-label="Date"><?= htmlspecialchars($t['date']) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>

</div>

</body>
</html>
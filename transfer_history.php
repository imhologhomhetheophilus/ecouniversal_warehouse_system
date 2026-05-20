<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

$stmt = $pdo->query("SELECT * FROM transfers ORDER BY date DESC");
$transfers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transfer History</title>

<style>

/* ======================
   MOBILE FIRST BASE
====================== */

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

.container {
    width: 100%;
    padding: 16px;
    max-width: 1100px;
    margin: auto;
}

/* Header */
h2 {
    text-align: center;
    margin: 15px 0;
    font-size: 1.4rem;
}

/* Back button */
.back-btn {
    display: inline-block;
    background: #333;
    color: #fff;
    padding: 10px 14px;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

/* ======================
   MOBILE CARD LAYOUT
====================== */

.transfer-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

/* Each record becomes a card */
.card {
    background: #fff;
    border-radius: 10px;
    padding: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

.card-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid #eee;
    font-size: 0.95rem;
}

.card-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: bold;
    color: #555;
}

/* ======================
   DESKTOP ENHANCEMENT
====================== */

@media (min-width: 768px) {

    h2 {
        font-size: 1.8rem;
    }

    .transfer-list {
        display: none; /* hide cards */
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    th, td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    th {
        background: #007bff;
        color: #fff;
    }

    tr:hover {
        background: #f1f7ff;
    }
}

</style>

</head>

<body>

<div class="container">

<a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<h2>Transfer History</h2>

<!-- ================= MOBILE VIEW ================= -->
<div class="transfer-list">

<?php foreach ($transfers as $t): ?>
<div class="card">

    <div class="card-row">
        <span class="label">Item</span>
        <span><?= htmlspecialchars($t['item']) ?></span>
    </div>

    <div class="card-row">
        <span class="label">From</span>
        <span><?= htmlspecialchars($t['from_loc']) ?></span>
    </div>

    <div class="card-row">
        <span class="label">To</span>
        <span><?= htmlspecialchars($t['to_loc']) ?></span>
    </div>

    <div class="card-row">
        <span class="label">Qty</span>
        <span><?= (int)$t['qty'] ?></span>
    </div>

    <div class="card-row">
        <span class="label">Date</span>
        <span><?= htmlspecialchars($t['date']) ?></span>
    </div>

</div>
<?php endforeach; ?>

</div>

<!-- ================= DESKTOP TABLE ================= -->
<table class="desktop-table">
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
    <td><?= htmlspecialchars($t['item']) ?></td>
    <td><?= htmlspecialchars($t['from_loc']) ?></td>
    <td><?= htmlspecialchars($t['to_loc']) ?></td>
    <td><?= (int)$t['qty'] ?></td>
    <td><?= htmlspecialchars($t['date']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>

</body>
</html>
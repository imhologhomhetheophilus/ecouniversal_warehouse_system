<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

/* =========================
   DELETE TRANSFER HISTORY
========================= */

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM transfers WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: transfer_history.php");
    exit;
}

/* =========================
   CLEAR ALL HISTORY
========================= */

if (isset($_GET['clear_all'])) {

    $pdo->query("DELETE FROM transfers");

    header("Location: transfer_history.php");
    exit;
}

/* =========================
   FETCH HISTORY
========================= */

$stmt = $pdo->query("SELECT * FROM transfers ORDER BY date DESC");
$transfers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Transfer History</title>

<style>

/* ======================
   RESET
====================== */

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f6f9;
}

/* ======================
   CONTAINER
====================== */

.container{
    width:100%;
    max-width:1200px;
    margin:auto;
    padding:16px;
}

/* ======================
   TOP SECTION
====================== */

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}

h2{
    margin:0;
    font-size:1.5rem;
}

/* ======================
   BUTTONS
====================== */

.back-btn,
.clear-btn,
.delete-btn{

    color:#fff;
    text-decoration:none;
    padding:10px 14px;
    border-radius:6px;
    display:inline-block;
    font-size:0.9rem;
}

.back-btn{
    background:#333;
}

.clear-btn{
    background:#dc3545;
}

.delete-btn{
    background:#ff4d4d;
}

.delete-btn:hover{
    background:#cc0000;
}

.clear-btn:hover{
    background:#b02a37;
}

/* ======================
   MOBILE CARDS
====================== */

.transfer-list{
    display:grid;
    grid-template-columns:1fr;
    gap:14px;
}

.card{
    background:#fff;
    border-radius:10px;
    padding:15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
}

.card-row{
    display:flex;
    justify-content:space-between;
    padding:8px 0;
    border-bottom:1px solid #eee;
    font-size:0.95rem;
}

.card-row:last-child{
    border-bottom:none;
}

.label{
    font-weight:bold;
    color:#555;
}

/* ======================
   DESKTOP TABLE
====================== */

.desktop-table{
    display:none;
}

@media (min-width:768px){

    .transfer-list{
        display:none;
    }

    .desktop-table{
        display:table;
        width:100%;
        border-collapse:collapse;
        background:#fff;
        border-radius:10px;
        overflow:hidden;
    }

    th,
    td{
        padding:14px;
        text-align:center;
        border-bottom:1px solid #eee;
    }

    th{
        background:#007bff;
        color:#fff;
    }

    tr:hover{
        background:#f1f7ff;
    }
}

</style>

</head>

<body>

<div class="container">

<!-- ======================
     TOP BAR
====================== -->

<div class="top-bar">

    <a href="dashboard.php" class="back-btn">
        ⬅ Back to Dashboard
    </a>

    <h2>Transfer History</h2>

    <a href="?clear_all=1"
       class="clear-btn"
       onclick="return confirm('Clear all transfer history?')">
       Clear All
    </a>

</div>

<!-- ======================
     MOBILE VIEW
====================== -->

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

    <div style="margin-top:12px;text-align:right;">

        <a href="?delete=<?= $t['id'] ?>"
           class="delete-btn"
           onclick="return confirm('Delete this transfer record?')">
           Delete
        </a>

    </div>

</div>

<?php endforeach; ?>

</div>

<!-- ======================
     DESKTOP TABLE
====================== -->

<table class="desktop-table">

<thead>

<tr>
    <th>Item</th>
    <th>From</th>
    <th>To</th>
    <th>Qty</th>
    <th>Date</th>
    <th>Action</th>
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

    <td>

        <a href="?delete=<?= $t['id'] ?>"
           class="delete-btn"
           onclick="return confirm('Delete this transfer record?')">
           Delete
        </a>

    </td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</body>
</html>
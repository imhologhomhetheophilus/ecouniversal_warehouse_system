<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Eco Universal Limited Warehouse Dashboard</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* ================= BASE ================= */
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family:Arial, sans-serif;
    background:#f4f6f9;
    overflow-x:hidden;
}

/* ================= SIDEBAR ================= */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:240px;
    height:100vh;
    background:#111;
    color:#fff;
    padding:20px;
    transition:0.3s ease;
    z-index:1002;
    overflow-y:auto;
}

.sidebar h3{
    margin-bottom:15px;
}

.sidebar a{
    display:block;
    padding:12px;
    color:#ccc;
    text-decoration:none;
    border-radius:6px;
    margin-bottom:5px;
    transition:0.2s;
}

.sidebar a:hover{
    background:#007bff;
    color:#fff;
}

/* ================= OVERLAY ================= */
.overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    display:none;
    z-index:1000;
}

.overlay.show{
    display:block;
}

/* ================= MENU BUTTON ================= */
.menu-btn{
    display:none;
    position:fixed;
    top:10px;
    left:10px;
    background:#111;
    color:#fff;
    padding:10px 14px;
    border-radius:6px;
    z-index:1100;
    cursor:pointer;
}

/* ================= MAIN ================= */
.main{
    margin-left:240px;
    padding:20px;
    transition:0.3s;
}

/* ================= MOBILE RESPONSIVE ================= */
@media(max-width:768px){

    .sidebar{
        left:-260px;
        width:260px;
    }

    .sidebar.open{
        left:0;
    }

    .menu-btn{
        display:block;
    }

    .main{
        margin-left:0;
        padding-top:60px;
    }

    body.menu-open{
        overflow:hidden;
    }
}

/* ================= rest stays unchanged ================= */
.summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:12px;
    margin-top:15px;
}

.card{
    background:#fff;
    padding:15px;
    border-radius:10px;
    margin-top:15px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:10px;
    border-bottom:1px solid #eee;
    text-align:center;
}

th{
    background:#007bff;
    color:#fff;
}

.item-img{
    width:40px;
    height:40px;
    object-fit:cover;
    border-radius:6px;
}

.search-box{
    position:relative;
}

#search{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
}

#search-dropdown{
    position:absolute;
    width:100%;
    background:#fff;
    border:1px solid #ddd;
    max-height:250px;
    overflow:auto;
    display:none;
    z-index:2000;
}

.btn{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    color:#fff;
    font-size:13px;
    margin:0 3px;
    text-decoration:none;
}

.edit-btn{ background:#28a745; }
.delete-btn{ background:#dc3545; }

.card-title{
    margin-bottom:12px;
    font-size:18px;
    text-align:center;
    text-transform:uppercase;
    background:red;
    color:#fff;
    padding:8px;
}

</style>
</head>

<body>

<div class="menu-btn">☰ Menu</div>
<div class="overlay"></div>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>Eco Universal Limited Warehouse</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_item.php">Add Item</a>
    <a href="transfer.php">Transfer</a>
    <a href="transfer_history.php">History</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<h2>Welcome <?= htmlspecialchars($_SESSION['admin']) ?></h2>

<!-- SEARCH -->
<div class="search-box">
    <input type="text" id="search" placeholder="Search items...">
    <div id="search-dropdown"></div>
</div>

<!-- SUMMARY -->
<div class="summary" id="summary"></div>

<!-- LOW STOCK -->
<div class="card" id="lowStock"></div>

<!-- CHARTS -->
<div class="card">
    <h3>Stock by Location</h3>
    <canvas id="barChart"></canvas>
</div>

<div class="card">
    <h3>Stock by Item Type</h3>
    <canvas id="typeChart"></canvas>
</div>

<!-- INVENTORY -->
<div class="card" id="inventory"></div>

<!-- TRANSFERS -->
<div class="card">
    <h3>Transfer History</h3>
    <table id="transferTable"></table>
</div>

</div>

<script>

let dataStore = {};
let barChart, typeChart;


/* ================= LOAD ================= */
function loadDashboard(){

$.getJSON("dashboard_data.php", function(data){

    dataStore = data || {};

    /* SUMMARY */
    $('#summary').html(`
        <div><h3>${data.totalQty ?? 0}</h3><p>Total Qty</p></div>
        <div><h3>${data.totalWarehouses ?? 0}</h3><p>Warehouses</p></div>
        <div><h3>${data.totalShops ?? 0}</h3><p>Shops</p></div>
    `);

    /* LOW STOCK */
  $('#lowStock').html(
    (data.lowStock?.length)
    ? `
        <h3 class="card-title">Low Stock</h3>
        ${data.lowStock.map(i => `
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #eee;">

                ${i.image
                    ? `<img src="${i.image}" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">`
                    : `<div style="width:40px;height:40px;background:#ddd;border-radius:6px;"></div>`
                }

                <div>
                    <div><b>${i.name}</b></div>
                    <small>${i.location} • Qty: ${i.qty}</small>
                </div>

            </div>
        `).join('')}
    `
    : `<h3>No Low Stock</h3>`
);

    /* INVENTORY */
    $('#inventory').html(`
        <h3>Inventory</h3>
        <table>
        <tr>
            <th>Img</th>
            <th>Name</th>
            <th>Location</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>

        ${(data.items || []).map(i => `
        <tr>
            <td>
                ${i.image ? `<img src="${i.image}" class="item-img">` : 'No Image'}
            </td>
            <td>${i.name}</td>
            <td>${i.location}</td>
            <td>${i.type}</td>
            <td>${i.qty}</td>
            <td>
                <a href="edit_item.php?id=${i.id}" class="btn edit-btn">Edit</a>
                <a href="delete_item.php?id=${i.id}" class="btn delete-btn"
                onclick="return confirm('Delete item?')">Delete</a>
            </td>
        </tr>
        `).join('')}
        </table>
    `);

    /* TRANSFERS */
    $('#transferTable').html(`
        <tr>
            <th>Item</th><th>From</th><th>To</th><th>Qty</th><th>Date</th>
        </tr>

        ${(data.transfers || []).map(t => `
        <tr>
            <td>${t.item}</td>
            <td>${t.from_loc}</td>
            <td>${t.to_loc}</td>
            <td>${t.qty}</td>
            <td>${t.date}</td>
        </tr>
        `).join('')}
    `);

    renderCharts(data);

});

}

/* ================= CHARTS ================= */
function renderCharts(data){

    let labels = data.chart?.labels || [];
    let values = data.chart?.data || [];

    // safely destroy charts
    if (barChart) {
        barChart.destroy();
        barChart = null;
    }

    if (typeChart) {
        typeChart.destroy();
        typeChart = null;
    }

    /* ================= BAR CHART ================= */
    barChart = new Chart(document.getElementById("barChart"), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: '#007bff'
            }]
        }
    });

    /* ================= TYPE CHART ================= */
    let typeMap = {};

    (data.items || []).forEach(i => {
        let key = i.type || 'Unknown';
        typeMap[key] = (typeMap[key] || 0) + parseInt(i.qty || 0);
    });

    typeChart = new Chart(document.getElementById("typeChart"), {
        type: 'bar',
        data: {
            labels: Object.keys(typeMap),
            datasets: [{
                label: 'Total Stock',
                data: Object.values(typeMap),
                backgroundColor: '#28a745'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
}
/* ================= SEARCH ================= */
$("#search").on("input", function(){

let val = this.value.toLowerCase();
if(!val){ $("#search-dropdown").hide(); return; }

let res = (dataStore.items||[]).filter(i =>
    i.name.toLowerCase().includes(val) ||
    i.location.toLowerCase().includes(val) ||
    i.type.toLowerCase().includes(val)
);

$("#search-dropdown").html(
    res.map(i=>`
    <div style="padding:10px;border-bottom:1px solid #eee">
        ${i.image ? `<img src="${i.image}" class="item-img">` : ''}
        <b>${i.name}</b><br>
        <small>${i.location} | ${i.qty}</small>
    </div>
    `).join('')
).show();

});

/* ================= MENU ================= */
$(".menu-btn").click(()=>{
    $(".sidebar").addClass("open");
    $(".overlay").addClass("show");
    $("body").addClass("menu-open");
});

$(".overlay").click(()=>{
    $(".sidebar").removeClass("open");
    $(".overlay").removeClass("show");
    $("body").removeClass("menu-open");
});

/* INIT */
loadDashboard();
setInterval(loadDashboard,15000);

</script>

</body>
</html>
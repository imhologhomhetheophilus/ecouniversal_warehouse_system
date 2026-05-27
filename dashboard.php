<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory Dashboard (FAST)</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:0;
    left:-260px;
    width:220px;
    height:100vh;
    background:#1e1e1e;
    color:#fff;
    padding:20px;
    transition:0.3s;
}
.sidebar.open{ left:0; }

.sidebar a{
    display:block;
    padding:10px;
    color:#ccc;
    text-decoration:none;
}
.sidebar a:hover{ background:#007bff; color:#fff; }

/* MENU */
.menu-btn{
    position:fixed;
    top:10px;
    left:10px;
    background:#222;
    color:#fff;
    padding:10px;
    border-radius:6px;
    z-index:2000;
}

/* OVERLAY */
.overlay{
    display:none;
    position:fixed;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
}
.overlay.show{ display:block; }

/* MAIN */
.main{
    padding:12px;
}

/* SUMMARY */
.summary{
    display:grid;
    grid-template-columns:1fr;
    gap:10px;
}
.summary div{
    background:#fff;
    padding:12px;
    border-radius:10px;
    text-align:center;
}

/* CARD */
.card{
    background:#fff;
    padding:12px;
    border-radius:10px;
    margin-top:12px;
}

/* TABLE */
.table-wrapper{ overflow-x:auto; }

table{
    width:100%;
    border-collapse:collapse;
    min-width:650px;
}

th,td{
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

/* SEARCH */
#search{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

#search-dropdown{
    position:absolute;
    width:100%;
    background:#fff;
    border:1px solid #ddd;
    display:none;
    max-height:250px;
    overflow:auto;
    z-index:999;
}

/* IMAGE */
.item-img{
    width:40px;
    height:40px;
    object-fit:cover;
    border-radius:6px;
}

/* BUTTONS */
.btn{
    padding:5px 8px;
    font-size:11px;
    border-radius:5px;
    color:#fff;
}
.btn-edit{ background:#007bff; border:none; }
.btn-delete{ background:#dc3545; border:none; }

canvas{
    width:100% !important;
    height:260px !important;
}

@media(min-width:768px){
    .sidebar{ left:0; }
    .main{ margin-left:240px; }
    .menu-btn{ display:none; }

    .summary{
        grid-template-columns:repeat(3,1fr);
    }
}
</style>
</head>

<body>

<div class="menu-btn">☰ Menu</div>
<div class="overlay"></div>

<div class="sidebar">
    <h3>Eco Universal</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_item.php">Add Item</a>
    <a href="transfer.php">Transfer</a>
    <a href="transfer_history.php">History</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

<h3>Welcome <?= htmlspecialchars($_SESSION['admin']) ?></h3>

<div style="position:relative;">
    <input type="text" id="search" placeholder="Search items...">
    <div id="search-dropdown"></div>
</div>

<div class="summary" id="summary"></div>

<div class="card" id="lowStock"></div>

<div class="card">
    <h3>Stock by Location</h3>
    <canvas id="barChart"></canvas>
</div>

<div class="card">
    <h3>Type Distribution</h3>
    <canvas id="pieChart"></canvas>
</div>

<div class="card table-wrapper" id="inventory"></div>

<!-- TRANSFER HISTORY (ADDED) -->
<div class="card table-wrapper" id="transfer-history"></div>

</div>

<script>

let dataStore = {};
let barChart, pieChart;

/* FULL LOAD (ONCE) */
function loadDashboard(){

$.get("dashboard_data.php", function(data){

    dataStore = data;

    renderAll(data);
    renderCharts(data);

});

}

/* FAST UPDATE ONLY (NO HEAVY LOAD) */
function fastUpdate(){

$.get("dashboard_fast.php", function(data){

    $('#summary').html(`
        <div><h3>${data.totalQty}</h3><p>Total Qty</p></div>
        <div><h3>${data.totalWarehouses}</h3><p>Warehouses</p></div>
        <div><h3>${data.totalShops}</h3><p>Shops</p></div>
    `);

    $('#lowStock').html(
        data.lowStock.length
        ? "<h4>Low Stock</h4>" + data.lowStock.map(i =>
            `<div style="color:red">${i.name} - ${i.qty}</div>`
          ).join('')
        : "No Low Stock"
    );

    $('#transfer-history').html(`
        <h3>Transfer History</h3>
        <table>
        <tr><th>Item</th><th>From</th><th>To</th><th>Qty</th><th>Date</th></tr>
        ${data.transfers.map(t=>`
            <tr>
                <td>${t.item}</td>
                <td>${t.from_loc}</td>
                <td>${t.to_loc}</td>
                <td>${t.qty}</td>
                <td>${t.date}</td>
            </tr>
        `).join('')}
        </table>
    `);

});

}

/* RENDER FULL UI */
function renderAll(data){

$('#summary').html(`
    <div><h3>${data.totalQty}</h3><p>Total Qty</p></div>
    <div><h3>${data.totalWarehouses}</h3><p>Warehouses</p></div>
    <div><h3>${data.totalShops}</h3><p>Shops</p></div>
`);

$('#lowStock').html(
    (data.lowStock||[]).map(i =>
        `<div style="color:red">${i.name} - ${i.qty}</div>`
    ).join('')
);

$('#inventory').html(`
    <h3>Inventory</h3>
    <table>
    <tr><th>Img</th><th>Name</th><th>Loc</th><th>Type</th><th>Qty</th></tr>
    ${(data.items||[]).map(i=>`
        <tr>
            <td>${i.image ? `<img src="${i.image}" class="item-img">` : ''}</td>
            <td>${i.name}</td>
            <td>${i.location}</td>
            <td>${i.type}</td>
            <td>${i.qty}</td>
        </tr>
    `).join('')}
    </table>
`);

$('#transfer-history').html(`
    <h3>Transfer History</h3>
    <table>
    <tr><th>Item</th><th>From</th><th>To</th><th>Qty</th><th>Date</th></tr>
    ${(data.transfers||[]).map(t=>`
        <tr>
            <td>${t.item}</td>
            <td>${t.from_loc}</td>
            <td>${t.to_loc}</td>
            <td>${t.qty}</td>
            <td>${t.date}</td>
        </tr>
    `).join('')}
    </table>
`);

}

/* CHARTS (NO DESTROY LOOP = FAST) */
function renderCharts(data){

let labels = data.chart?.labels || [];
let values = data.chart?.data || [];

let typeMap = {};
(data.items||[]).forEach(i=>{
    typeMap[i.type] = (typeMap[i.type]||0)+parseInt(i.qty||0);
});

if(!barChart){
    barChart = new Chart(document.getElementById("barChart"), {
        type:"bar",
        data:{ labels, datasets:[{ data:values, backgroundColor:"#007bff" }] }
    });
}else{
    barChart.data.labels = labels;
    barChart.data.datasets[0].data = values;
    barChart.update();
}

if(!pieChart){
    pieChart = new Chart(document.getElementById("pieChart"), {
        type:"pie",
        data:{
            labels:Object.keys(typeMap),
            datasets:[{ data:Object.values(typeMap), backgroundColor:["#007bff","#28a745","#ffc107","#dc3545"] }]
        }
    });
}else{
    pieChart.data.labels = Object.keys(typeMap);
    pieChart.data.datasets[0].data = Object.values(typeMap);
    pieChart.update();
}

}

/* SEARCH */
$("#search").on("input", function(){

let val = this.value.toLowerCase();

if(!val){
    $("#search-dropdown").hide();
    return;
}

let res = (dataStore.items||[]).filter(i =>
    i.name.toLowerCase().includes(val) ||
    i.location.toLowerCase().includes(val) ||
    i.type.toLowerCase().includes(val)
);

$("#search-dropdown").html(res.map(i=>`
<div style="display:flex;gap:10px;padding:8px;">
    ${i.image ? `<img src="${i.image}" class="item-img">` : ''}
    <div>
        <b>${i.name}</b><br>
        <small>${i.location} | ${i.qty}</small>
    </div>
</div>
`).join('')).show();

});

/* MENU */
$(".menu-btn").click(()=>$(".sidebar,.overlay").toggleClass("open show"));
$(".overlay").click(()=>$(".sidebar,.overlay").removeClass("open show"));

/* INIT */
loadDashboard();

/* FAST LOOP ONLY */
setInterval(fastUpdate,15000);

</script>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>

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
    background:#222;
    color:#fff;
    padding:20px;
    transition:0.3s;
    z-index:1500;
}
.sidebar.open{ left:0; }

.sidebar a{
    display:block;
    color:#ccc;
    padding:10px;
    text-decoration:none;
}
.sidebar a:hover{ background:#007bff; color:#fff; }

/* MENU */
.menu-btn{
    position:fixed;
    top:10px;
    left:10px;
    z-index:2000;
    background:#222;
    color:#fff;
    padding:10px;
    border-radius:6px;
}

/* OVERLAY */
.overlay{
    display:none;
    position:fixed;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    z-index:1400;
}
.overlay.show{ display:block; }

/* MAIN */
.main{
    padding:12px;
}

/* CARDS */
.card{
    background:#fff;
    padding:12px;
    margin-top:12px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* SEARCH */
.search-container{
    position:relative;
}
#search{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}
#search-dropdown{
    position:absolute;
    top:100%;
    left:0;
    right:0;
    background:#fff;
    border:1px solid #ddd;
    max-height:250px;
    overflow:auto;
    display:none;
    z-index:999;
}

/* TABLE */
.table-wrapper{
    overflow-x:auto;
}
table{
    width:100%;
    border-collapse:collapse;
    min-width:600px;
}
th,td{
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

/* IMAGE */
.item-img{
    width:40px;
    height:40px;
    object-fit:cover;
    border-radius:6px;
}

/* BUTTON */
.btn{
    padding:5px 8px;
    border-radius:5px;
    color:#fff;
    font-size:11px;
    text-decoration:none;
}
.btn-edit{ background:#007bff; }
.btn-delete{ background:#dc3545; }

/* CHART */
canvas{
    width:100% !important;
    height:260px !important;
}

/* DESKTOP */
@media(min-width:768px){
    .sidebar{ left:0; }
    .main{ margin-left:240px; padding:25px; }
    .menu-btn{ display:none; }
}
</style>
</head>

<body>

<div class="menu-btn">☰ Menu</div>
<div class="overlay"></div>

<div class="sidebar">
    <h3>Inventory</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_item.php">Add Item</a>
    <a href="transfer.php">Transfer</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

<h3>Welcome <?= htmlspecialchars($_SESSION['admin']) ?></h3>

<!-- SEARCH -->
<div class="search-container">
    <input type="text" id="search" placeholder="Search items...">
    <div id="search-dropdown"></div>
</div>

<!-- SUMMARY -->
<div class="card" id="summary"></div>

<!-- BAR -->
<div class="card">
    <h3>Stock by Location</h3>
    <canvas id="barChart"></canvas>
</div>

<!-- PIE -->
<div class="card">
    <h3>Type Distribution</h3>
    <canvas id="pieChart"></canvas>
</div>

<!-- TABLE -->
<div class="card table-wrapper" id="inventory"></div>

</div>

<script>

let dashboardData = { items: [] };
let barChartInstance = null;
let pieChartInstance = null;

/* LOAD DATA */
function loadDashboardData(){

$.get('dashboard_data.php', function(data){

    dashboardData = data || { items: [] };

    /* SUMMARY */
    $('#summary').html(`
        <b>Total Qty:</b> ${data.totalQty || 0}
    `);

    /* INVENTORY TABLE */
    $('#inventory').html(`
        <h3>Inventory</h3>
        <table>
            <tr>
                <th>Img</th><th>Name</th><th>Loc</th><th>Type</th><th>Qty</th>
            </tr>
            ${(data.items || []).map(i=>`
                <tr>
                    <td>${i.image ? `<img class="item-img" src="${i.image}">` : ''}</td>
                    <td>${i.name}</td>
                    <td>${i.location}</td>
                    <td>${i.type}</td>
                    <td>${i.qty}</td>
                </tr>
            `).join('')}
        </table>
    `);

    renderCharts(data);

});

}

/* CHARTS */
function renderCharts(data){

    const labels = (data.chart?.labels) || [];
    const values = (data.chart?.data) || [];

    /* TYPE DISTRIBUTION FIX (AUTO BUILD) */
    let typeMap = {};

    (data.items || []).forEach(i => {
        let type = (i.type || "Unknown").trim();
        let qty = parseInt(i.qty || 0);

        typeMap[type] = (typeMap[type] || 0) + qty;
    });

    const typeLabels = Object.keys(typeMap);
    const typeValues = Object.values(typeMap);

    if(typeLabels.length === 0){
        typeLabels.push("No Data");
        typeValues.push(1);
    }

    /* BAR */
    if(barChartInstance) barChartInstance.destroy();

    barChartInstance = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels.length ? labels : ["No Data"],
            datasets: [{
                label: 'Stock',
                data: values.length ? values : [0],
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive:true,
            maintainAspectRatio:false
        }
    });

    /* PIE */
    if(pieChartInstance) pieChartInstance.destroy();

    pieChartInstance = new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeValues,
                backgroundColor: [
                    '#007bff','#28a745','#ffc107',
                    '#dc3545','#6f42c1','#20c997'
                ]
            }]
        },
        options: {
            responsive:true,
            maintainAspectRatio:false
        }
    });
}

/* SEARCH (WITH IMAGE + TYPE + QTY) */
$('#search').on('input', function(){

    const term = this.value.toLowerCase();
    const box = $('#search-dropdown');

    if(!term){
        box.hide();
        return;
    }

    const res = (dashboardData.items || []).filter(i =>
        i.name.toLowerCase().includes(term) ||
        i.location.toLowerCase().includes(term) ||
        i.type.toLowerCase().includes(term)
    );

    box.html(res.map(i=>`
        <div style="display:flex;gap:10px;padding:10px;border-bottom:1px solid #eee;align-items:center;">

            ${i.image ? `
                <img src="${i.image}" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">
            ` : `
                <div style="width:40px;height:40px;background:#ddd;border-radius:6px;"></div>
            `}

            <div>
                <b>${i.name}</b><br>
                <small>${i.location} | ${i.type} | Qty: ${i.qty}</small>
            </div>

        </div>
    `).join(''));

    box.show();
});

/* MENU */
$('.menu-btn').on('click', function(){
    $('.sidebar').toggleClass('open');
    $('.overlay').toggleClass('show');
});

$('.overlay').on('click', function(){
    $('.sidebar').removeClass('open');
    $(this).removeClass('show');
});

/* INIT */
loadDashboardData();
setInterval(loadDashboardData, 15000);

</script>

</body>
</html>
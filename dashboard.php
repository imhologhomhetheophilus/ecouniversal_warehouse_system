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

/* BASE */
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f6f9;
}

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
    cursor:pointer;
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
    overflow:auto;
    z-index:1500;
}

.sidebar.open{ left:0; }

.sidebar a{
    display:block;
    color:#ccc;
    padding:10px;
    text-decoration:none;
}

.sidebar a:hover{
    background:#007bff;
    color:#fff;
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
    padding:15px;
}

/* GRID */
.summary-cards{
    display:grid;
    grid-template-columns:1fr;
    gap:15px;
}

.summary-card{
    background:#fff;
    padding:15px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.card{
    background:#fff;
    padding:15px;
    border-radius:10px;
    margin-top:15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:10px;
    border:1px solid #ddd;
    text-align:center;
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
    display:none;
    max-height:250px;
    overflow:auto;
    z-index:999;
}

.item-img{
    width:40px;
    height:40px;
    object-fit:cover;
    border-radius:6px;
}

.btn{
    padding:6px 10px;
    border-radius:5px;
    color:#fff;
    text-decoration:none;
    font-size:12px;
}

.btn-edit{ background:#007bff; }
.btn-delete{ background:#dc3545; }

@media(min-width:768px){
    .sidebar{ left:0; }
    .main{ margin-left:240px; padding:25px; }

    .summary-cards{
        grid-template-columns:repeat(3,1fr);
    }

    .menu-btn{ display:none; }
}

</style>
</head>

<body>

<div class="menu-btn">☰ Menu</div>
<div class="overlay"></div>

<div class="sidebar">
    <h2>Ecouniversal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_item.php">Add Item</a>
    <a href="transfer.php">Transfer</a>
    <a href="transfer_history.php">History</a>
    <a href="change_password.php">Password</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

<h2>Welcome <?= htmlspecialchars($_SESSION['admin']) ?></h2>

<div class="search-container">
    <input type="text" id="search" placeholder="Search items...">
    <div id="search-dropdown"></div>
</div>

<div class="summary-cards" id="summary-cards"></div>

<div class="card">
    <h3>Inventory Analysis</h3>
    <canvas id="barChart"></canvas>
</div>

<div class="card">
    <h3>Type Distribution</h3>
    <canvas id="pieChart"></canvas>
</div>

<div class="card" id="low-stock"></div>

<div class="card" id="inventory-section"></div>

<div class="card">
    <h3>Locations</h3>
    <table id="locations-table"></table>
</div>

<div class="card" id="recent-transfers"></div>

</div>

<script>

let dashboardData = { items: [] };
let barChartInstance = null;
let pieChartInstance = null;
let lastHash = null;

/* LOAD DASHBOARD */
function loadDashboardData(){

$.get('dashboard_data.php', function(data){

    const hash = JSON.stringify(data);
    if(hash === lastHash) return;
    lastHash = hash;

    dashboardData = data;

    /* SUMMARY */
    $('#summary-cards').html(`
        <div class="summary-card"><h2>${data.totalWarehouses||0}</h2><p>Warehouses</p></div>
        <div class="summary-card"><h2>${data.totalShops||0}</h2><p>Shops</p></div>
        <div class="summary-card"><h2>${data.totalQty||0}</h2><p>Total Qty</p></div>
    `);

    /* LOW STOCK */
    $('#low-stock').html(
        (data.lowStock||[]).map(i =>
            `<p style="color:red;">Low Stock: ${i.name} - ${i.qty} (${i.location})</p>`
        ).join('')
    );

    /* INVENTORY */
    $('#inventory-section').html(`
        <h3>Inventory</h3>
        <table>
        <tr>
            <th>Img</th><th>Name</th><th>Loc</th><th>Type</th><th>Qty</th><th>Action</th>
        </tr>
        ${(data.items||[]).map(i=>`
            <tr>
                <td>${i.image ? `<img class="item-img" src="${i.image}">` : ''}</td>
                <td>${i.name}</td>
                <td>${i.location}</td>
                <td>${i.type}</td>
                <td>${i.qty}</td>
                <td>
                    <a class="btn btn-edit" href="edit_item.php?id=${i.id}">Edit</a>
                    <a class="btn btn-delete" href="delete_item.php?id=${i.id}" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        `).join('')}
        </table>
    `);

    /* LOCATIONS TABLE */
    $('#locations-table').html(`
        <tr><th>Location</th><th>Total</th></tr>
        ${(data.chart.labels||[]).map((l,i)=>`
            <tr><td>${l}</td><td>${data.chart.data[i]}</td></tr>
        `).join('')}
    `);

    /* TRANSFERS */
    $('#recent-transfers').html(`
        <h3>Transfers</h3>
        <table>
        <tr><th>Item</th><th>From</th><th>To</th><th>Qty</th><th>Date</th></tr>
        ${(data.transfers||[]).map(x=>`
            <tr>
                <td>${x.item}</td>
                <td>${x.from_loc}</td>
                <td>${x.to_loc}</td>
                <td>${x.qty}</td>
                <td>${x.date}</td>
            </tr>
        `).join('')}
        </table>
    `);

    renderCharts(data);

});

}

/* CHARTS */
function renderCharts(data){

    // BAR
    const ctx1 = document.getElementById('barChart');

    if(barChartInstance) barChartInstance.destroy();

    barChartInstance = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: data.chart.labels,
            datasets: [{
                label: 'Stock',
                data: data.chart.data,
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive:true,
            animation:false
        }
    });

    // PIE
    const ctx2 = document.getElementById('pieChart');

    if(pieChartInstance) pieChartInstance.destroy();

    pieChartInstance = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: data.typeData?.labels || [],
            datasets: [{
                data: data.typeData?.data || [],
                backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545']
            }]
        },
        options: {
            responsive:true,
            animation:false
        }
    });
}

/* SEARCH (DEBOUNCED) */
let searchTimeout;

$('#search').on('input', function(){

clearTimeout(searchTimeout);

searchTimeout = setTimeout(()=>{

    const term = this.value.toLowerCase();
    const box = $('#search-dropdown');

    if(!term){
        box.hide();
        return;
    }

    const res = dashboardData.items.filter(i =>
        i.name.toLowerCase().includes(term) ||
        i.location.toLowerCase().includes(term)
    );

    box.html(res.map(i=>`
        <div><b>${i.name}</b><br><small>${i.location}</small></div>
    `).join(''));

    box.show();

},200);

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
setInterval(()=>{ if(!document.hidden) loadDashboardData(); }, 15000);

</script>

</body>
</html>
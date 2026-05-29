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

<title>Inventory Dashboard</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* ================= RESET ================= */

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial,sans-serif;
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
    background:#1e1e1e;
    color:#fff;
    padding:20px;
    overflow-y:auto;
    z-index:1000;
}

.sidebar h3{
    margin-top:0;
}

.sidebar a{
    display:block;
    padding:12px;
    margin-bottom:5px;
    border-radius:6px;
    color:#ccc;
    text-decoration:none;
    transition:0.2s;
}

.sidebar a:hover{
    background:#007bff;
    color:#fff;
}

/* ================= MENU BUTTON ================= */

.menu-btn{
    display:none;
    position:fixed;
    top:12px;
    left:12px;
    background:#222;
    color:#fff;
    padding:10px 14px;
    border-radius:6px;
    z-index:2000;
    cursor:pointer;
}

/* ================= OVERLAY ================= */

.overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.4);
    display:none;
    z-index:999;
}

.overlay.show{
    display:block;
}

/* ================= MAIN ================= */

.main{
    margin-left:240px;
    padding:20px;
}

/* ================= SEARCH ================= */

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
    top:100%;
    left:0;
    width:100%;
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    display:none;
    max-height:250px;
    overflow:auto;
    z-index:3000;
}

/* ================= SUMMARY ================= */

.summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-top:15px;
}

.summary div{
    background:#fff;
    padding:20px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* ================= CARD ================= */

.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* ================= TABLE ================= */

.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:700px;
}

th{
    background:#007bff;
    color:#fff;
}

th,
td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

/* ================= IMAGE ================= */

.item-img{
    width:45px;
    height:45px;
    border-radius:6px;
    object-fit:cover;
}

/* ================= BUTTONS ================= */

.btn{
    padding:7px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    color:#fff;
    text-decoration:none;
    font-size:13px;
    margin:0 2px;
}

.edit-btn{
    background:#28a745;
}

.delete-btn{
    background:#dc3545;
}

/* ================= CHART ================= */

canvas{
    width:100% !important;
    max-height:320px !important;
}

/* ================= MOBILE ================= */

@media(max-width:768px){

    .sidebar{
        left:-260px;
        transition:0.3s;
    }

    .sidebar.open{
        left:0;
    }

    .menu-btn{
        display:block;
    }

    .main{
        margin-left:0;
        padding-top:70px;
    }
}

</style>

</head>

<body>

<div class="menu-btn">
    ☰ Menu
</div>

<div class="overlay"></div>

<!-- SIDEBAR -->

<div class="sidebar">

    <h3>Eco Universal</h3>

    <a href="dashboard.php">Dashboard</a>

    <a href="add_item.php">Add Item</a>

    <a href="transfer.php">Transfer</a>

    <a href="transfer_history.php">History</a>

    <a href="logout.php">Logout</a>

</div>

<!-- MAIN -->

<div class="main">

<h2>
    Welcome <?= htmlspecialchars($_SESSION['admin']) ?>
</h2>

<!-- SEARCH -->

<div class="search-box">

    <input
        type="text"
        id="search"
        placeholder="Search items..."
    >

    <div id="search-dropdown"></div>

</div>

<!-- SUMMARY -->

<div class="summary" id="summary"></div>

<!-- LOW STOCK -->

<div class="card" id="lowStock"></div>

<!-- BAR CHART -->

<div class="card">

    <h3>Stock by Location</h3>

    <canvas id="barChart"></canvas>

</div>

<!-- PIE CHART -->

<div class="card">

    <h3>Type Distribution</h3>

    <canvas id="pieChart"></canvas>

</div>

<!-- INVENTORY -->

<div class="card table-wrapper" id="inventory"></div>

<!-- TRANSFER HISTORY -->

<div class="card table-wrapper">

    <h3>Transfer History</h3>

    <table id="transferTable"></table>

</div>

</div>

<script>

let dataStore = {};
let barChart = null;
let pieChart = null;

/* ================= LOAD DASHBOARD ================= */

function loadDashboard(){

    $.getJSON("dashboard_data.php", function(data){

        dataStore = data;

        /* SUMMARY */

        $('#summary').html(`
            <div>
                <h2>${data.totalQty || 0}</h2>
                <p>Total Qty</p>
            </div>

            <div>
                <h2>${data.totalWarehouses || 0}</h2>
                <p>Warehouses</p>
            </div>

            <div>
                <h2>${data.totalShops || 0}</h2>
                <p>Shops</p>
            </div>
        `);

        /* LOW STOCK */

        $('#lowStock').html(

            data.lowStock && data.lowStock.length

            ?

            `
            <h3>Low Stock Items</h3>

            ${data.lowStock.map(i => `
                <p style="color:red;">
                    ${i.name} - ${i.qty}
                </p>
            `).join('')}
            `

            :

            `<h3>No Low Stock</h3>`
        );

        /* INVENTORY */

        $('#inventory').html(`

            <h3>Inventory</h3>

            <table>

                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>

                ${(data.items || []).map(i => `

                <tr>

                    <td>
                        ${
                            i.image
                            ?
                            `<img src="${i.image}" class="item-img">`
                            :
                            `No Image`
                        }
                    </td>

                    <td>${i.name}</td>

                    <td>${i.location}</td>

                    <td>${i.type}</td>

                    <td>${i.qty}</td>

                    <td>

                        <a
                            href="edit_item.php?id=${i.id}"
                            class="btn edit-btn"
                        >
                            Edit
                        </a>

                        <a
                            href="delete_item.php?id=${i.id}"
                            class="btn delete-btn"
                            onclick="return confirm('Delete this item?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

                `).join('')}

            </table>
        `);

        /* TRANSFER TABLE */

        $('#transferTable').html(`

            <tr>
                <th>Item</th>
                <th>From</th>
                <th>To</th>
                <th>Qty</th>
                <th>Date</th>
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

    let labels = [];
    let values = [];

    if(data.chart && Array.isArray(data.chart.labels)){
        labels = data.chart.labels;
    }

    if(data.chart && Array.isArray(data.chart.data)){
        values = data.chart.data;
    }

    /* TYPE MAP */

    let typeMap = {};

    (data.items || []).forEach(i => {

        let type = i.type || 'Unknown';

        typeMap[type] =
            (typeMap[type] || 0)
            +
            parseInt(i.qty || 0);

    });

    /* DESTROY OLD */

    if(barChart){
        barChart.destroy();
    }

    if(pieChart){
        pieChart.destroy();
    }

    /* BAR */

    barChart = new Chart(
        document.getElementById("barChart"),
        {
            type:'bar',

            data:{
                labels:labels,

                datasets:[{
                    label:'Stock',
                    data:values,
                    backgroundColor:'#007bff'
                }]
            }
        }
    );

    /* PIE */

    pieChart = new Chart(
        document.getElementById("pieChart"),
        {
            type:'pie',

            data:{
                labels:Object.keys(typeMap),

                datasets:[{
                    data:Object.values(typeMap),

                    backgroundColor:[
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1'
                    ]
                }]
            }
        }
    );
}

/* ================= SEARCH ================= */

$("#search").on("input", function(){

    let val = $(this).val().toLowerCase();

    if(!val){

        $("#search-dropdown").hide();

        return;
    }

    let res = (dataStore.items || []).filter(i =>

        i.name.toLowerCase().includes(val)

        ||

        i.location.toLowerCase().includes(val)

        ||

        i.type.toLowerCase().includes(val)

    );

    $("#search-dropdown").html(

        res.map(i => `

        <div style="padding:10px;border-bottom:1px solid #eee;">

            <strong>${i.name}</strong>

            <br>

            <small>
                ${i.location} | Qty: ${i.qty}
            </small>

        </div>

        `).join('')

    ).show();

});

/* ================= MENU ================= */

$(".menu-btn").click(function(){

    $(".sidebar").addClass("open");

    $(".overlay").addClass("show");

});

$(".overlay").click(function(){

    $(".sidebar").removeClass("open");

    $(".overlay").removeClass("show");

});

/* ================= INIT ================= */

loadDashboard();

setInterval(loadDashboard,15000);

</script>

</body>
</html>
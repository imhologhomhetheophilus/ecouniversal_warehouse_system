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
/* ================= RESET ================= */
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

body{
    background:#f5f7fb;
    color:#111;
    overflow-x:hidden;
}

/* ================= LAYOUT ================= */
.main{
    margin-left:260px;
    padding:20px;
    transition:0.3s;
}

/* ================= SIDEBAR (MODERN GLASS) ================= */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:260px;
    height:100vh;
    background:linear-gradient(180deg,#0f172a,#111827);
    color:#fff;
    padding:20px;
    z-index:1002;
    transition:0.3s;
    overflow-y:auto;
}

.sidebar h3{
    font-size:18px;
    margin-bottom:20px;
    color:#fff;
}

.sidebar a{
    display:flex;
    padding:12px 14px;
    margin-bottom:6px;
    border-radius:10px;
    text-decoration:none;
    color:#cbd5e1;
    transition:0.2s;
    font-size:14px;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.08);
    color:#fff;
}

/* ================= MOBILE MENU ================= */
.menu-btn{
    display:none;
    position:fixed;
    top:12px;
    left:12px;
    background:#111827;
    color:#fff;
    padding:10px 12px;
    border-radius:10px;
    z-index:1100;
    cursor:pointer;
}

.overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.5);
    display:none;
    z-index:1000;
}

.overlay.show{ display:block; }

/* ================= CARDS ================= */
.card{
    background:#fff;
    border-radius:16px;
    padding:16px;
    margin-top:16px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
}

/* ================= SUMMARY KPI ================= */
.summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:12px;
    margin-top:15px;
}

.summary div{
    background:#fff;
    padding:16px;
    border-radius:14px;
    box-shadow:0 6px 16px rgba(0,0,0,0.05);
    text-align:center;
}

.summary h3{
    font-size:22px;
    color:#111827;
}

.summary p{
    font-size:12px;
    color:#6b7280;
}

/* ================= TABLE ================= */
table{
    width:100%;
    border-collapse:collapse;
    overflow:hidden;
    border-radius:12px;
}

th{
    background:#111827;
    color:#fff;
    padding:12px;
    font-size:13px;
}

td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:center;
    font-size:13px;
}

.item-img{
    width:40px;
    height:40px;
    border-radius:10px;
    object-fit:cover;
}

/* ================= SEARCH ================= */
.search-box{
    position:relative;
}

#search{
    width:100%;
    padding:14px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    outline:none;
    background:#fff;
}

#search-dropdown{
    position:absolute;
    width:100%;
    background:#fff;
    border-radius:12px;
    max-height:260px;
    overflow:auto;
    display:none;
    z-index:2000;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

/* ================= LOW STOCK MODERN ================= */
.card-title{
    font-size:14px;
    font-weight:600;
    margin-bottom:12px;
    color:#ef4444;
    text-transform:uppercase;
    letter-spacing:1px;
}

.low-stock-item{
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px;
    border-radius:10px;
    margin-bottom:8px;
    background:#fff5f5;
}

.low-stock-item b{
    font-size:13px;
}

.low-stock-item small{
    color:#6b7280;
    font-size:12px;
}

/* ================= BUTTONS ================= */
.btn{
    padding:6px 10px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    color:#fff;
    font-size:12px;
    text-decoration:none;
}

.edit-btn{ background:#22c55e; }
.delete-btn{ background:#ef4444; }

/* ================= MOBILE ================= */
@media(max-width:768px){

    .sidebar{
        left:-280px;
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

    table{
        font-size:12px;
    }
    /* ================= INVENTORY RESPONSIVE ================= */


    #inventory table,
    #inventory thead,
    #inventory tbody,
    #inventory th,
    #inventory td,
    #inventory tr{
        display:block;
        width:100%;
    }

    #inventory thead{
        display:none;
    }

    #inventory tr{
        background:#fff;
        margin-bottom:12px;
        border-radius:14px;
        box-shadow:0 6px 16px rgba(0,0,0,0.05);
        padding:10px;
    }

    #inventory td{
        text-align:left;
        padding:8px 10px;
        border:none;
        display:flex;
        justify-content:space-between;
        font-size:13px;
    }

    #inventory td::before{
        content:attr(data-label);
        font-weight:600;
        color:#6b7280;
    }

    #inventory .item-img{
        width:50px;
        height:50px;
        border-radius:10px;
    }

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
        <h3 class="card-title">Low Stock Alert</h3>
        ${data.lowStock.map(i => `
            <div class="low-stock-item">

                ${i.image
                    ? `<img src="${i.image}" class="item-img">`
                    : `<div style="width:40px;height:40px;background:#e5e7eb;border-radius:10px;"></div>`
                }

                <div>
                    <b>${i.name}</b><br>
                    <small>${i.location} • Qty: ${i.qty}</small>
                </div>

            </div>
        `).join('')}
    `
    : `<div class="card">No Low Stock</div>`
);

/* ================= INVENTORY ================= */
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

                <td data-label="Image">
                    ${i.image
                        ? `<img src="${i.image}" class="item-img">`
                        : 'No Image'
                    }
                </td>

                <td data-label="Name">${i.name ?? ''}</td>

                <td data-label="Location">${i.location ?? ''}</td>

                <td data-label="Type">${i.type ?? ''}</td>

                <td data-label="Qty">${i.qty ?? 0}</td>

                <td data-label="Action">
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
    $("body").css("overflow","hidden");
});

$(".overlay").click(()=>{
    $(".sidebar").removeClass("open");
    $(".overlay").removeClass("show");
    $("body").css("overflow","auto");
});

/* INIT */
loadDashboard();
setInterval(loadDashboard,15000);

</script>

</body>
</html>
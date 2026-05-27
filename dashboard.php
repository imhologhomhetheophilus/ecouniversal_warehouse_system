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

/* GLOBAL */
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

/* CARDS */
.card{
    background:#fff;
    padding:12px;
    border-radius:10px;
    margin-top:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
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
    width:100%;
    background:#fff;
    border:1px solid #ddd;
    display:none;
    max-height:250px;
    overflow:auto;
    z-index:999;
}

/* TABLE */
.table-wrapper{
    overflow-x:auto;
}

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
    cursor:pointer;
}
.btn-edit{ background:#007bff; border:none; }
.btn-delete{ background:#dc3545; border:none; }

/* LOW STOCK */
.low{
    color:red;
    font-weight:bold;
}

/* CANVAS */
canvas{
    width:100% !important;
    height:260px !important;
}

@media(min-width:768px){
    .sidebar{ left:0; }
    .main{ margin-left:240px; padding:25px; }
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
    <h3>Inventory System</h3>
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

</div>

<!-- EDIT MODAL -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;">
<div style="background:#fff;width:90%;max-width:400px;margin:100px auto;padding:20px;border-radius:10px;">

<h3>Edit Item</h3>

<input type="hidden" id="edit_id">

<input id="edit_name" placeholder="Name" style="width:100%;padding:10px;margin:5px 0;">
<input id="edit_location" placeholder="Location" style="width:100%;padding:10px;margin:5px 0;">
<input id="edit_type" placeholder="Type" style="width:100%;padding:10px;margin:5px 0;">
<input id="edit_qty" type="number" placeholder="Qty" style="width:100%;padding:10px;margin:5px 0;">

<button onclick="saveEdit()" style="width:100%;padding:10px;background:#007bff;color:#fff;border:none;">Save</button>
<button onclick="$('#editModal').hide()" style="width:100%;margin-top:10px;">Close</button>

</div>
</div>

<script>

let dataStore = { items: [] };
let barChart, pieChart;

/* LOAD DASHBOARD */
function loadDashboard(){

$.get("dashboard_data.php", function(data){

    dataStore = data;

    /* SUMMARY */
    $('#summary').html(`
        <div><h3>${data.totalQty}</h3><p>Total Qty</p></div>
        <div><h3>${data.totalWarehouses}</h3><p>Warehouses</p></div>
        <div><h3>${data.totalShops}</h3><p>Shops</p></div>
    `);

    /* LOW STOCK */
    $('#lowStock').html(
        (data.lowStock||[]).length
        ? "<h4>Low Stock</h4>" + data.lowStock.map(i =>
            `<div class="low">${i.name} - ${i.qty} (${i.location})</div>`
          ).join('')
        : "No Low Stock"
    );

    /* INVENTORY */
    $('#inventory').html(`
        <h3>Inventory</h3>
        <table>
        <tr>
            <th>Img</th><th>Name</th><th>Loc</th><th>Type</th><th>Qty</th><th>Action</th>
        </tr>

        ${(data.items||[]).map(i=>`
        <tr id="row-${i.id}">
            <td>${i.image ? `<img src="${i.image}" class="item-img">` : ''}</td>
            <td class="name">${i.name}</td>
            <td class="location">${i.location}</td>
            <td class="type">${i.type}</td>
            <td class="qty">${i.qty}</td>
            <td>
                <button class="btn btn-edit" onclick="openEdit(${i.id})">Edit</button>
                <button class="btn btn-delete" onclick="deleteItem(${i.id})">Del</button>
            </td>
        </tr>
        `).join('')}

        </table>
    `);

    renderCharts(data);

});

}

/* CHARTS */
function renderCharts(data){

let labels = data.chart?.labels || [];
let values = data.chart?.data || [];

let typeMap = {};
(data.items||[]).forEach(i=>{
    typeMap[i.type] = (typeMap[i.type]||0) + parseInt(i.qty||0);
});

if(barChart) barChart.destroy();
if(pieChart) pieChart.destroy();

/* BAR */
barChart = new Chart(document.getElementById("barChart"), {
    type:"bar",
    data:{
        labels,
        datasets:[{
            data:values,
            backgroundColor:"#007bff"
        }]
    }
});

/* PIE */
pieChart = new Chart(document.getElementById("pieChart"), {
    type:"pie",
    data:{
        labels:Object.keys(typeMap),
        datasets:[{
            data:Object.values(typeMap),
            backgroundColor:["#007bff","#28a745","#ffc107","#dc3545","#6f42c1"]
        }]
    }
});

}

/* SEARCH WITH IMAGE */
$("#search").on("input", function(){

let val = this.value.toLowerCase();
let box = $("#search-dropdown");

if(!val){ box.hide(); return; }

let res = (dataStore.items||[]).filter(i =>
    i.name.toLowerCase().includes(val) ||
    i.location.toLowerCase().includes(val) ||
    i.type.toLowerCase().includes(val)
);

box.html(res.map(i=>`
<div style="display:flex;gap:10px;padding:8px;border-bottom:1px solid #eee;">
    ${i.image ? `<img src="${i.image}" class="item-img">` : ''}
    <div>
        <b>${i.name}</b><br>
        <small>${i.type} | ${i.location} | Qty: ${i.qty}</small>
    </div>
</div>
`).join(''));

box.show();

});

/* DELETE */
function deleteItem(id){

if(!confirm("Delete item?")) return;

$.post("delete_item.php",{id},function(res){
    $("#row-"+id).fadeOut();
});

}

/* EDIT */
function openEdit(id){

let row = $("#row-"+id);

$("#edit_id").val(id);
$("#edit_name").val(row.find(".name").text());
$("#edit_location").val(row.find(".location").text());
$("#edit_type").val(row.find(".type").text());
$("#edit_qty").val(row.find(".qty").text());

$("#editModal").show();

}

/* SAVE EDIT */
function saveEdit(){

$.post("edit_item.php",{
    id:$("#edit_id").val(),
    name:$("#edit_name").val(),
    location:$("#edit_location").val(),
    type:$("#edit_type").val(),
    qty:$("#edit_qty").val()
},function(){

loadDashboard();
$("#editModal").hide();

});

}

/* MENU */
$(".menu-btn").click(()=>$(".sidebar,.overlay").toggleClass("open show"));
$(".overlay").click(()=>$(".sidebar,.overlay").removeClass("open show"));

/* INIT */
loadDashboard();
setInterval(loadDashboard,15000);

</script>

</body>
</html>
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

/* ===== RESET ===== */
*{box-sizing:border-box}

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}

/* ===== SIDEBAR ===== */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:240px;
    height:100vh;
    background:#1e1e1e;
    color:#fff;
    padding:20px;
    overflow:auto;
    z-index:1000;
}

.sidebar a{
    display:block;
    padding:10px;
    color:#ccc;
    text-decoration:none;
}

.sidebar a:hover{
    background:#007bff;
    color:#fff;
}

/* ===== MENU ===== */
.menu-btn{
    display:none;
    position:fixed;
    top:10px;
    left:10px;
    background:#222;
    color:#fff;
    padding:10px;
    border-radius:6px;
    z-index:2000;
}

/* MOBILE */
@media(max-width:768px){
    .sidebar{left:-260px;transition:0.3s}
    .sidebar.open{left:0}
    .menu-btn{display:block}
}

/* ===== MAIN ===== */
.main{
    margin-left:240px;
    padding:20px;
}

@media(max-width:768px){
    .main{margin-left:0}
}

/* ===== CARD ===== */
.card{
    background:#fff;
    padding:20px;
    margin-top:20px;
    border-radius:12px;
}

/* ===== TABLE ===== */
table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

/* ===== IMAGE ===== */
.item-img{
    width:45px;
    height:45px;
    object-fit:cover;
    border-radius:6px;
}

/* ===== BUTTONS ===== */
.btn{
    padding:6px 10px;
    border-radius:6px;
    color:#fff;
    border:none;
    cursor:pointer;
    margin:0 4px;
}

.edit-btn{background:#28a745}
.delete-btn{background:#dc3545}

</style>

</head>

<body>

<div class="menu-btn">☰</div>

<div class="sidebar">
    <h3>Inventory</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_item.php">Add Item</a>
    <a href="transfer.php">Transfer</a>
    <a href="transfer_history.php">History</a>
</div>

<div class="main">

<h2>Welcome <?= htmlspecialchars($_SESSION['admin']) ?></h2>

<input id="search" placeholder="Search items..." style="width:100%;padding:10px;margin-top:10px">

<div class="card" id="inventory"></div>

</div>

<script>

let dataStore = {};

/* LOAD DATA */
function loadDashboard(){

$.getJSON("dashboard_data.php", function(data){

    dataStore = data;

    let items = data.items || [];

    /* INVENTORY */
    $("#inventory").html(`
        <h3>Inventory</h3>
        <table>
        <tr>
            <th>Image</th><th>Name</th><th>Location</th>
            <th>Type</th><th>Qty</th><th>Action</th>
        </tr>

        ${items.map(i=>`
        <tr>
            <td>
                <img src="${i.image || 'assets/no-image.png'}" class="item-img">
            </td>
            <td>${i.name || ''}</td>
            <td>${i.location || ''}</td>
            <td>${i.type || ''}</td>
            <td>${i.qty || 0}</td>
            <td>
                <button class="btn edit-btn" onclick="editItem(${i.id})">Edit</button>
                <button class="btn delete-btn" onclick="deleteItem(${i.id})">Delete</button>
            </td>
        </tr>
        `).join('')}
        </table>
    `);

});

}

/* DELETE */
function deleteItem(id){

if(!confirm("Delete item?")) return;

fetch("delete_item.php",{
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body:JSON.stringify({id})
})
.then(r=>r.json())
.then(res=>{
    if(res.status==="success"){
        loadDashboard();
    }else{
        alert(res.msg);
    }
});

}

/* EDIT */
function editItem(id){

let item = dataStore.items.find(i=>i.id==id);
if(!item) return;

let name = prompt("Name",item.name);
let location = prompt("Location",item.location);
let type = prompt("Type",item.type);
let qty = prompt("Qty",item.qty);

fetch("edit_item.php",{
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body:JSON.stringify({id,name,location,type,qty})
})
.then(r=>r.json())
.then(res=>{
    if(res.status==="success"){
        loadDashboard();
    }else{
        alert(res.msg);
    }
});

}

/* MENU */
$(".menu-btn").click(()=>$(".sidebar").toggleClass("open"));

loadDashboard();

</script>

</body>
</html>
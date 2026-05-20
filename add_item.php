<?php
session_start();
include 'config/db.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit; }

$msg='';

// Fetch existing item names for dropdown/autocomplete
$itemNames = [];
$res = $conn->query("SELECT DISTINCT name FROM items ORDER BY name");
while($row = $res->fetch_assoc()) $itemNames[] = $row['name'];

// Locations
$locations = ['Ware Shop2','Warehouse  MD','Warehouse Handle','Warehouse MD Opposite','Warehouse  Down','Warehouse  Upstair','Warehouse  Kugbo','Warehouse Karu','Shop 1','Pannel Shop','Shop 2', 'Deidei Warehouse', 'Deidei Shop'];

if($_SERVER['REQUEST_METHOD']=='POST'){
    $name=$_POST['name'];
    $location=$_POST['location'];
    $type=$_POST['type'];
    $qty=(int)$_POST['qty'];
    $image='';

    if(isset($_FILES['image']) && $_FILES['image']['name']!=''){
        $target_dir="uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir,0777,true);
        $image=$target_dir.time()."_".$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'],$image);
    }

    // Fixed bind_param types: s = string, i = integer
    $stmt = $conn->prepare("INSERT INTO items(name,location,type,qty,image) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssis", $name, $location, $type, $qty, $image);
    $stmt->execute();

    echo "<script>alert('Item added successfully'); window.location.href='dashboard.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Econ Universal Service LTD</title>
<style>
body{margin:0;padding:20px;font-family:Arial;background:#f4f6f9;}
.container{max-width:600px;margin:0 auto;}
h2{text-align:center;margin-bottom:20px;}
form{background:#fff;padding:25px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
select,input,button{padding:12px;margin:10px 0;width:100%;border-radius:6px;border:1px solid #ccc;box-sizing:border-box;font-size:1em;}
button{background:#007bff;color:#fff;border:none;cursor:pointer;font-weight:bold;transition:0.3s;}
button:hover{background:#0056b3;}
.msg{color:red;text-align:center;margin-bottom:10px;}
a{text-align:center;display:block;margin-top:15px;text-decoration:none;color:#007bff;}

/* Autocomplete Dropdown */
.autocomplete-items{
    position:absolute;
    border:1px solid #d4d4d4;
    border-bottom:none;
    border-top:none;
    z-index:99;
    top:100%;
    left:0;
    right:0;
    background:#fff;
    max-height:200px;
    overflow-y:auto;
}
.autocomplete-items div{
    padding:10px;
    cursor:pointer;
    border-bottom:1px solid #ddd;
}
.autocomplete-items div:hover{background:#e9e9e9;}

@media(max-width:600px){
    form{padding:20px;}
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
<h2>Add New Item</h2>
<form method="post" enctype="multipart/form-data" autocomplete="off">
<label>Item Name</label>
<input type="text" id="itemName" name="name" required>

<label>Location</label>
<select name="location" required>
<option value="">Select Location</option>
<?php foreach($locations as $loc) echo "<option value='$loc'>$loc</option>"; ?>
</select>

<label>Type</label>
<select name="type" required>
<option value="Packet">Packet</option>
<option value="Pieces">Pieces</option>
<option value="Carton">Carton</option>
<option value="Roll">Roll</option>
</select>

<label>Quantity</label>
<input type="number" name="qty" min="1" required>

<label>Image (optional)</label>
<input type="file" name="image" accept="image/*">

<button type="submit">Add Item</button>
</form>
<a href="dashboard.php">Back to Dashboard</a>
</div>

<script>
// Autocomplete for item name
const items = <?=json_encode($itemNames)?>;
function autocomplete(inp, arr){
    let currentFocus;
    inp.addEventListener("input", function(){
        let val = this.value;
        closeAllLists();
        if(!val) return false;
        currentFocus = -1;
        let list = document.createElement("DIV");
        list.setAttribute("class","autocomplete-items");
        this.parentNode.appendChild(list);
        arr.forEach(function(item){
            if(item.substr(0,val.length).toUpperCase() == val.toUpperCase()){
                let itemDiv = document.createElement("DIV");
                itemDiv.innerHTML = "<strong>"+item.substr(0,val.length)+"</strong>"+item.substr(val.length);
                itemDiv.innerHTML += "<input type='hidden' value='"+item+"'>";
                itemDiv.addEventListener("click",function(){
                    inp.value = this.getElementsByTagName("input")[0].value;
                    closeAllLists();
                });
                list.appendChild(itemDiv);
            }
        });
    });

    inp.addEventListener("keydown", function(e){
        let x = document.querySelector(".autocomplete-items");
        if(x) x = x.getElementsByTagName("div");
        if(e.keyCode==40){currentFocus++; addActive(x);}
        else if(e.keyCode==38){currentFocus--; addActive(x);}
        else if(e.keyCode==13){e.preventDefault(); if(currentFocus>-1) if(x) x[currentFocus].click();}
    });

    function addActive(x){if(!x) return false; removeActive(x); if(currentFocus>=x.length) currentFocus=0; if(currentFocus<0) currentFocus=x.length-1; x[currentFocus].classList.add("autocomplete-active");}
    function removeActive(x){for(let i=0;i<x.length;i++) x[i].classList.remove("autocomplete-active");}
    function closeAllLists(elmnt){let x=document.getElementsByClassName("autocomplete-items"); for(let i=0;i<x.length;i++) if(elmnt!=x[i] && elmnt!=inp) x[i].parentNode.removeChild(x[i]);}
    document.addEventListener("click", function (e) {closeAllLists(e.target);});
}
autocomplete(document.getElementById("itemName"), items);
</script>
</body>
</html>
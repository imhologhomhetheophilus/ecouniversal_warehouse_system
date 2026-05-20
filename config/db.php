<?php
$host='localhost';
$user='root';
$pass='';
$db='warehouse_system_v4';
$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }
?>
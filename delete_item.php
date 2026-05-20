<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit; }
include 'config/db.php';

$id=$_GET['id'];
$conn->query("DELETE FROM items WHERE id='$id'");
header("Location: dashboard.php");
?>
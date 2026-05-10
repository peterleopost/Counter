<?php session_start(); if(!isset($_SESSION['login'])) header("Location:index.php"); ?>
<style>
body{margin:0;font-family:Arial;background:#f5f6fa;display:flex;}
.sidebar{width:220px;background:#2c3e50;color:#fff;padding:20px;height:100vh;}
.sidebar a{display:block;color:#ecf0f1;margin:10px 0;text-decoration:none;}
.main{flex:1;padding:20px;}
.card{background:#fff;padding:15px;margin-bottom:15px;border-radius:6px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{padding:8px;border-bottom:1px solid #ddd;}
.bar{height:6px;background:#3498db;}
</style>

<div class="sidebar">
<h3>Analytics</h3>
<a href="dashboard.php">Dashboard</a>
<a href="sites.php">Sites</a>
<a href="pages.php">Pages</a>
</div>

<div class="main">

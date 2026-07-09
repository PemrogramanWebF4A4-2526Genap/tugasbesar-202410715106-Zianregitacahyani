<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'buyer') {
    header("Location: login/login.php");
    exit;
}

$id = (int)$_GET['id'];

$mysqli->query("DELETE FROM cart WHERE id = $id");

$_SESSION['success'] = "Produk berhasil dihapus dari keranjang";

header("Location: keranjang.php");
exit;
?>
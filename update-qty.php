<?php
// update-qty.php (contoh logika inti)
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php'); exit;
}

 $id   = (int)$_GET['id'];
 $aksi = $_GET['aksi'];

if ($aksi === 'tambah') {
    $mysqli->query("UPDATE cart SET qty = qty + 1 WHERE id = $id");
} elseif ($aksi === 'kurang') {
    // Ambil qty saat ini
    $res = $mysqli->query("SELECT qty FROM cart WHERE id = $id");
    $data = $res->fetch_assoc();

    if ($data && $data['qty'] <= 1) {
        // Jika qty 1 → dikurangi jadi 0 → hapus otomatis
        $mysqli->query("DELETE FROM cart WHERE id = $id");
    } else {
        $mysqli->query("UPDATE cart SET qty = qty - 1 WHERE id = $id");
    }
}

header('Location: keranjang.php');
exit;
<?php
// =====================================================
// File: tambah-keranjang.php
// Deskripsi: Menambahkan produk ke keranjang belanja
// Hanya role: buyer
// =====================================================

session_start();

// =====================================================
// KONEKSI DATABASE (mysqli procedural)
// =====================================================
 require 'koneksi.php';

$conn = $mysqli;

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// =====================================================
// VALIDASI 1: USER BELUM LOGIN
// =====================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: login.php");
    exit;
}

// =====================================================
// VALIDASI 2: ROLE HARUS BUYER
// =====================================================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    $_SESSION['error'] = "Hanya buyer yang dapat menambahkan produk ke keranjang.";
    header("Location: index.php");
    exit;
}

// =====================================================
// AMBIL ID PRODUK DARI URL (?id=1)
// =====================================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID produk tidak valid.";
    header("Location: produk-user.php");
    exit;
}

 $product_id = (int) $_GET['id'];
 $username   = $_SESSION['username'];

// =====================================================
// CARI USER_ID BERDASARKAN SESSION USERNAME
// (cek di kolom name ATAU email agar fleksibel)
// =====================================================
 $stmt_user = mysqli_prepare($conn, "SELECT id FROM users WHERE name = ? OR email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_user, "ss", $username, $username);
mysqli_stmt_execute($stmt_user);
 $result_user = mysqli_stmt_get_result($stmt_user);

if (mysqli_num_rows($result_user) === 0) {
    $_SESSION['error'] = "Data user tidak ditemukan. Silakan login kembali.";
    header("Location: login/logout.php");
    exit;
}

 $user_data = mysqli_fetch_assoc($result_user);
 $user_id   = (int) $user_data['id'];
mysqli_stmt_close($stmt_user);

// =====================================================
// VALIDASI 3: PRODUK HARUS ADA DI DATABASE
// =====================================================
 $stmt_product = mysqli_prepare($conn, "SELECT id, stock FROM products WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_product, "i", $product_id);
mysqli_stmt_execute($stmt_product);
 $result_product = mysqli_stmt_get_result($stmt_product);

if (mysqli_num_rows($result_product) === 0) {
    $_SESSION['error'] = "Produk tidak ditemukan.";
    header("Location: produk-user.php");
    exit;
}

 $product_data = mysqli_fetch_assoc($result_product);
mysqli_stmt_close($stmt_product);

// =====================================================
// CEK APAKAH PRODUK SUDAH ADA DI CART USER
// =====================================================
 $stmt_check = mysqli_prepare($conn, "SELECT id, qty FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $product_id);
mysqli_stmt_execute($stmt_check);
 $result_check = mysqli_stmt_get_result($stmt_check);

 $success = false;

if (mysqli_num_rows($result_check) > 0) {
    // -----------------------------------------------------
    // KONDISI A: PRODUK SUDAH ADA -> UPDATE qty = qty + 1
    // -----------------------------------------------------
    $cart_data = mysqli_fetch_assoc($result_check);
    $cart_id   = (int) $cart_data['id'];
    $new_qty   = (int) $cart_data['qty'] + 1;

    $stmt_update = mysqli_prepare($conn, "UPDATE cart SET qty = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, "ii", $new_qty, $cart_id);
    $success = mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
} else {
    // -----------------------------------------------------
    // KONDISI B: PRODUK BELUM ADA -> INSERT BARU
    // -----------------------------------------------------
    $qty = 1;
    $stmt_insert = mysqli_prepare(
        $conn,
        "INSERT INTO cart (user_id, product_id, qty, created_at) VALUES (?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt_insert, "iii", $user_id, $product_id, $qty);
    $success = mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);
}

mysqli_stmt_close($stmt_check);
mysqli_close($conn);

// =====================================================
// SET PESAN & REDIRECT KE produk-user.php
// =====================================================
if ($success) {
    $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang";
} else {
    $_SESSION['error'] = "Terjadi kesalahan saat menambahkan produk ke keranjang.";
}

header("Location: produk-user.php");
exit;
?>
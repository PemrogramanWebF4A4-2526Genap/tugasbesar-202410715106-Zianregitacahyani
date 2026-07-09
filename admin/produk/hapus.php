<?php
require '../login/session-admin.php';
require '../koneksi.php';

 $upload_dir = '../image/produk/';

// Ambil ID dari URL
 $id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?status=gagal');
    exit;
}

// Ambil data produk (untuk mendapatkan nama file gambar)
 $query  = "SELECT image FROM products WHERE id = $id";
 $result = mysqli_query($mysqli, $query);

if (mysqli_num_rows($result) == 0) {
    // Produk tidak ditemukan
    header('Location: index.php?status=gagal');
    exit;
}

 $product = mysqli_fetch_assoc($result);

// Hapus data dari database
 $delete = mysqli_query($mysqli, "DELETE FROM products WHERE id = $id");

if ($delete) {
    // Hapus file gambar dari folder
    if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
        unlink($upload_dir . $product['image']);
    }
    header('Location: index.php?status=hapus_sukses');
    exit;
} else {
    header('Location: index.php?status=gagal');
    exit;
}
?>
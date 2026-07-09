<?php
// kategori/hapus.php
require '../login/session-admin.php';
require '../koneksi.php';

// Ambil id dari URL
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Cek apakah data ada
    $cek = mysqli_query($mysqli, "SELECT * FROM categories WHERE id = $id");
    if (mysqli_num_rows($cek) > 0) {
        // Hapus data
        $query = "DELETE FROM categories WHERE id = $id";
        if (mysqli_query($mysqli, $query)) {
            header("Location: index.php?pesan=Data kategori berhasil dihapus&type=success");
            exit;
        } else {
            header("Location: index.php?pesan=Gagal menghapus data: " . mysqli_error($mysqli) . "&type=danger");
            exit;
        }
    } else {
        header("Location: index.php?pesan=Data kategori tidak ditemukan&type=danger");
        exit;
    }
} else {
    header("Location: index.php?pesan=ID tidak valid&type=danger");
    exit;
}
?>
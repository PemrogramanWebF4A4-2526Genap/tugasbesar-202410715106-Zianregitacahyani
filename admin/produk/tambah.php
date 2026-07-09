<?php
require '../../login/session-admin.php';
require '../../koneksi.php';

// Konfigurasi upload
 $upload_dir  = '../image/produk/';
 $allowed_ext = ['jpg', 'jpeg', 'png'];
 $max_size    = 2 * 1024 * 1024; // 2MB
 $errors      = [];

// Ambil data kategori untuk dropdown
 $cats_result = mysqli_query($mysqli, "SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (int)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $seller_id   = 1; // otomatis
    $image_name  = '';

    // ===== Validasi =====
    if ($name === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }
    if ($description === '') {
        $errors[] = 'Deskripsi wajib diisi.';
    }
    if ($price <= 0) {
        $errors[] = 'Harga harus lebih besar dari 0.';
    }
    if ($stock < 0) {
        $errors[] = 'Stok tidak boleh negatif.';
    }
    if ($category_id <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }

    // ===== Validasi & Upload Gambar =====
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Gambar produk wajib diupload.';
    } else {
        $file     = $_FILES['image'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi error saat upload gambar.';
        } elseif (!in_array($file_ext, $allowed_ext)) {
            $errors[] = 'Format gambar harus: jpg, jpeg, atau png.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Ukuran gambar maksimal 2MB.';
        } else {
            // Buat nama file unik
            $image_name = time() . '_' . preg_replace('/\s+/', '_', strtolower($file['name']));

            // Pastikan folder ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $image_name)) {
                $errors[] = 'Gagal mengupload gambar ke server.';
                $image_name = '';
            }
        }
    }

    // ===== Simpan ke Database =====
    if (empty($errors)) {
        $name_esc = mysqli_real_escape_string($mysqli, $name);
        $desc_esc = mysqli_real_escape_string($mysqli, $description);

        $query = "INSERT INTO products (seller_id, name, description, price, stock, category_id, image) 
                  VALUES ($seller_id, '$name_esc', '$desc_esc', $price, $stock, $category_id, '$image_name')";

        if (mysqli_query($mysqli, $query)) {
            header('Location: index.php?status=sukses');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data: ' . mysqli_error($mysqli);
            // Hapus gambar yang sudah terupload jika query gagal
            if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                unlink($upload_dir . $image_name);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Toko Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle"></i> Tambah Produk</h2>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="bi bi-exclamation-triangle"></i> Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                           placeholder="Masukkan nama produk" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" 
                              placeholder="Masukkan deskripsi produk" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="price" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" 
                                   min="0" placeholder="0" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" 
                               value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" 
                               min="0" placeholder="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($c = mysqli_fetch_assoc($cats_result)): ?>
                                <option value="<?= $c['id'] ?>" 
                                    <?= (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gambar Produk <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control" 
                           accept="image/jpeg,image/jpg,image/png" required>
                    <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB.</small>
                </div>

                <div class="alert alert-info py-2">
                    <small><i class="bi bi-info-circle"></i> <strong>seller_id</strong> akan otomatis diset ke <strong>1</strong>.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Produk
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
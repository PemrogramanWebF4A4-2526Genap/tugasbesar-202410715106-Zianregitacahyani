<?php
require '../login/session-admin.php';
require '../koneksi.php';

// Konfigurasi upload
 $upload_dir  = '../image/produk/';
 $allowed_ext = ['jpg', 'jpeg', 'png'];
 $max_size    = 2 * 1024 * 1024; // 2MB
 $errors      = [];

// Ambil ID dari URL
 $id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?status=gagal');
    exit;
}

// Ambil data produk lama
 $query = "SELECT * FROM products WHERE id = $id";
 $result = mysqli_query($mysqli, $query);
if (mysqli_num_rows($result) == 0) {
    header('Location: index.php?status=gagal');
    exit;
}
 $product = mysqli_fetch_assoc($result);

// Ambil data kategori untuk dropdown
 $cats_result = mysqli_query($mysqli, "SELECT * FROM categories ORDER BY name ASC");

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (int)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $image_name  = $product['image']; // Default: gunakan gambar lama

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

    // ===== Upload Gambar Baru (jika ada) =====
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['image'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = 'Format gambar harus: jpg, jpeg, atau png.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Ukuran gambar maksimal 2MB.';
        } else {
            $new_image = time() . '_' . preg_replace('/\s+/', '_', strtolower($file['name']));

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_image)) {
                // Hapus gambar lama
                if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                    unlink($upload_dir . $product['image']);
                }
                $image_name = $new_image;
            } else {
                $errors[] = 'Gagal mengupload gambar baru.';
            }
        }
    }
    // Jika tidak upload gambar baru, tetap pakai $product['image']

    // ===== Update Database =====
    if (empty($errors)) {
        $name_esc = mysqli_real_escape_string($mysqli, $name);
        $desc_esc = mysqli_real_escape_string($mysqli, $description);
        $img_esc  = mysqli_real_escape_string($mysqli, $image_name);

        $query = "UPDATE products SET 
                    name        = '$name_esc',
                    description = '$desc_esc',
                    price       = $price,
                    stock       = $stock,
                    category_id = $category_id,
                    image       = '$img_esc'
                  WHERE id = $id";

        if (mysqli_query($mysqli, $query)) {
            header('Location: index.php?status=edit_sukses');
            exit;
        } else {
            $errors[] = 'Gagal memperbarui data: ' . mysqli_error($mysqli);
        }
    }
}

// Siapkan nilai form (POST > DB)
 $form = [
    'name'        => $_POST['name']        ?? $product['name'],
    'description' => $_POST['description'] ?? $product['description'],
    'price'       => $_POST['price']       ?? $product['price'],
    'stock'       => $_POST['stock']       ?? $product['stock'],
    'category_id' => $_POST['category_id'] ?? $product['category_id'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Toko Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square"></i> Edit Produk</h2>
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
                           value="<?= htmlspecialchars($form['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($form['description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="price" class="form-control" 
                                   value="<?= htmlspecialchars($form['price']) ?>" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" 
                               value="<?= htmlspecialchars($form['stock']) ?>" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($c = mysqli_fetch_assoc($cats_result)): ?>
                                <option value="<?= $c['id'] ?>" 
                                    <?= ($form['category_id'] == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Gambar Saat Ini -->
                <div class="mb-3">
                    <label class="form-label">Gambar Saat Ini</label><br>
                    <?php 
                    $currentImage = $upload_dir . $product['image'];
                    if (!empty($product['image']) && file_exists($currentImage)): 
                    ?>
                        <img src="<?= $currentImage ?>" width="140" class="img-thumbnail">
                        <p class="text-muted small mt-1"><?= htmlspecialchars($product['image']) ?></p>
                    <?php else: ?>
                        <span class="text-muted"><i class="bi bi-image"></i> Tidak ada gambar</span>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ganti Gambar <small class="text-muted">(opsional)</small></label>
                    <input type="file" name="image" class="form-control" 
                           accept="image/jpeg,image/jpg,image/png">
                    <small class="text-muted">
                        Kosongkan jika tidak ingin mengganti gambar. Format: JPG, JPEG, PNG. Maks 2MB.
                    </small>
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save"></i> Update Produk
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
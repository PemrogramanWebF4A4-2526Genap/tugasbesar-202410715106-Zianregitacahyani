<?php
// kategori/edit.php
require '../login/session-admin.php';
require '../koneksi.php';

// Ambil id dari URL
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data lama berdasarkan id
 $query = "SELECT * FROM categories WHERE id = $id";
 $result = mysqli_query($mysqli, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php?pesan=Data kategori tidak ditemukan&type=danger");
    exit;
}

 $data = mysqli_fetch_assoc($result);

 $name        = $data['name'];
 $description = $data['description'];
 $error       = '';

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validasi nama tidak boleh kosong
    if ($name == '') {
        $error = "Nama kategori wajib diisi!";
    } else {
        // Cek duplikasi nama (kecuali data ini sendiri)
        $cek = mysqli_query($mysqli, "SELECT * FROM categories WHERE name = '" . mysqli_real_escape_string($mysqli, $name) . "' AND id != $id");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Nama kategori sudah digunakan oleh kategori lain!";
        } else {
            // Update data
            $query_update = "UPDATE categories SET 
                                name        = '" . mysqli_real_escape_string($mysqli, $name) . "',
                                description = '" . mysqli_real_escape_string($mysqli, $description) . "'
                             WHERE id = $id";

            if (mysqli_query($mysqli, $query_update)) {
                header("Location: index.php?pesan=Data kategori berhasil diperbarui&type=success");
                exit;
            } else {
                $error = "Gagal memperbarui data: " . mysqli_error($mysqli);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori - Toko Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Kategori</h4>
                </div>
                <div class="card-body">

                    <!-- Notifikasi Error -->
                    <?php if ($error != ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($name) ?>" 
                                   placeholder="Masukkan nama kategori" 
                                   required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Masukkan deskripsi kategori (opsional)"><?= htmlspecialchars($description) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Update
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
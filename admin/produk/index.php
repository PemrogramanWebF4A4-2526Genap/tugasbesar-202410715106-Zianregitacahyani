<?php
require '../../login/session-admin.php';
require '../../koneksi.php';

// Ambil data produk dengan JOIN ke tabel categories
$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id DESC";
$result = mysqli_query($mysqli, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - Toko Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        table tbody tr {
            transition: 0.25s;
        }

        table tbody tr:hover {
            background: #eaf6ff;
            transform: scale(1.002);
        }

        .img-thumbnail {
            border-radius: 15px;
        }

        .card {
            overflow: hidden;
        }

        .card {
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(126, 200, 255, 0.15) !important;
        }

        table tbody tr {
            height: 110px;
        }

        td strong {
            font-size: 1.4rem;
        }

        small.text-muted {
            font-size: 0.95rem;
        }
    </style>
</head>

<body style="background:#f5fbff;">
    <div class="container-xl py-4">
        <div class="mb-4">

            <a href="../index.php"
                class="text-decoration-none fw-semibold mb-3 d-inline-block"
                style="color:#6ea8fe;">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h2 class="fw-bold text-primary mb-1">
                        🧴 Daftar Produk
                    </h2>

                    <p class="text-muted mb-0">
                        Kelola seluruh produk AquaGas
                    </p>

                </div>
                <a href="tambah.php" class="btn rounded-4 px-4 py-2 fw-semibold"
                    style="
                    background:#7ec8ff;
                    border:none;
                    color:white;
                    ">
                    <i class="bi bi-plus-circle"></i> Tambah Produk
                </a>
            </div>

            <!-- Alert Notifikasi -->
            <?php if (isset($_GET['status'])): ?>
                <?php
                $messages = [
                    'sukses'         => ['type' => 'success', 'text' => '✅ Produk berhasil ditambahkan!'],
                    'edit_sukses'    => ['type' => 'success', 'text' => '✅ Produk berhasil diperbarui!'],
                    'hapus_sukses'   => ['type' => 'success', 'text' => '✅ Produk berhasil dihapus!'],
                    'gagal'          => ['type' => 'danger',  'text' => '❌ Terjadi kesalahan. Silakan coba lagi.'],
                ];
                $status = $_GET['status'];
                if (isset($messages[$status])):
                ?>
                    <div class="alert alert-<?= $messages[$status]['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $messages[$status]['text'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div
                class="card border-0 shadow rounded-4"
                style="background:white;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead
                                style="
                            background:#dff2ff;
                            color:#2b4c7e;
                            ">
                                <tr>
                                    <th width="50">No</th>
                                    <th width="100">Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center">Stok</th>
                                    <th width="160" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $imagePath = '../../image/produk/' . $row['image'];
                                        if (!empty($row['image']) && file_exists($imagePath)) {
                                            $imageSrc = $imagePath;
                                        } else {
                                            $imageSrc = 'https://via.placeholder.com/80x80?text=No+Image';
                                        }
                                ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-center">
                                                <img src="<?= $imageSrc ?>"
                                                    alt="<?= htmlspecialchars($row['name']) ?>"
                                                    width="90"
                                                    class="img-thumbnail">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= substr(htmlspecialchars($row['description']), 0, 50) ?>...</small>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-3 py-2"
                                                    style="
                                                background:#dff2ff;
                                                color:#2b4c7e;
                                                ">
                                                    <?= htmlspecialchars($row['category_name'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td class="text-end">Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <?php if ($row['stock'] > 0): ?>
                                                    <span class="badge bg-success"><?= $row['stock'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Habis</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="edit.php?id=<?= $row['id'] ?>"
                                                    class="btn btn px-3 py-2 rounded-4"
                                                    style="
                                                        background:#ffe9a8;
                                                        border:none;
                                                        " title="Edit">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id'] ?>"
                                                    class="btn btn px-3 py-2 rounded-4"
                                                    style="
                                                        background:#ffb3b3;
                                                        border:none;
                                                        color:#8b0000;
                                                        "
                                                    onclick="return confirm('Yakin ingin menghapus produk ini?')"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">Belum ada produk.</p>
                              </td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
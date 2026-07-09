<?php
// kategori/index.php
require '../../login/session-admin.php';
require '../../koneksi.php';

// Ambil pesan notifikasi dari URL (jika ada)
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$pesan_type = isset($_GET['type']) ? $_GET['type'] : '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Kategori - Toko Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f5fbff;
        }

        .card {
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(126, 200, 255, 0.15) !important;
        }

        table tbody tr {
            height: 90px;
            transition: 0.3s;
        }

        table tbody tr:hover {
            background: #eef8ff;
        }
    </style>
</head>

<body style="background:#f5fbff;">

    <div class="container py-4">

        <!-- HEADER -->
        <div class="mb-4">

            <a href="../index.php"
                class="text-decoration-none fw-semibold mb-3 d-inline-block"
                style="color:#6ea8fe;">
                <i class="bi bi-arrow-left"></i>
                Kembali ke Dashboard
            </a>

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h1 class="fw-bold text-primary mb-1">
                        🏷️ Data Kategori
                    </h1>

                    <p class="text-muted mb-0">
                        Kelola seluruh kategori produk AquaGas
                    </p>
                </div>

                <a href="tambah.php"
                    class="btn rounded-4 px-4 py-2 fw-semibold"
                    style="
                background:#9acbff;
                border:none;
                color:white;
            ">

                    <i class="bi bi-plus-circle"></i>
                    Tambah Kategori

                </a>

            </div>


            <!-- NOTIFIKASI -->
            <?php if ($pesan != ''): ?>
                <div class="alert alert-<?= htmlspecialchars($pesan_type) ?> alert-dismissible fade show rounded-4" role="alert">
                    <?= htmlspecialchars($pesan) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>


            <!-- TABLE CARD -->
            <div class="card border-0 shadow rounded-4 overflow-hidden">

                <div class="card-body p-4">

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead
                                style="
                        background:#dff2ff;
                        color:#355c7d;
                        ">

                                <tr>
                                    <th width="60" class="text-center">No</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th width="180" class="text-center">Aksi</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php
                                $no = 1;
                                $query = "SELECT * FROM categories ORDER BY id DESC";
                                $result = mysqli_query($mysqli, $query);

                                if (mysqli_num_rows($result) > 0) {

                                    while ($row = mysqli_fetch_assoc($result)) {
                                ?>

                                        <tr>

                                            <td class="text-center">
                                                <?= $no++ ?>
                                            </td>

                                            <td>
                                                <strong class="fs-5">
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </strong>
                                            </td>

                                            <td class="text-muted">
                                                <?= htmlspecialchars($row['description']) ?>
                                            </td>

                                            <td class="text-center">

                                                <a
                                                    href="edit.php?id=<?= (int)$row['id'] ?>"
                                                    class="btn rounded-4 px-3"
                                                    style="
                                            background:#ffe9a8;
                                            border:none;
                                            ">

                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit

                                                </a>


                                                <a
                                                    href="hapus.php?id=<?= (int)$row['id'] ?>"
                                                    class="btn rounded-4 px-3"
                                                    style="
                                            background:#ffb3b3;
                                            border:none;
                                            color:#8b0000;
                                            "
                                                    onclick="return confirm('Yakin ingin menghapus kategori ini?');">

                                                    <i class="bi bi-trash"></i>
                                                    Hapus

                                                </a>

                                            </td>

                                        </tr>

                                    <?php
                                    }
                                } else {
                                    ?>

                                    <tr>

                                        <td colspan="4"
                                            class="text-center text-muted py-5">

                                            <i class="bi bi-inbox fs-1"></i>

                                            <p class="mt-3">
                                                Belum ada data kategori
                                            </p>

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
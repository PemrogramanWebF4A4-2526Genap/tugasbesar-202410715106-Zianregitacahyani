<?php
// kategori/tambah.php
require '../../login/session-admin.php';
require '../../koneksi.php';

// Inisialisasi variabel & error
 $name        = '';
 $description = '';
 $error       = '';
 $sukses      = '';

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validasi nama kategori tidak boleh kosong
    if ($name == '') {
        $error = "Nama kategori wajib diisi!";
    } else {
        // Cek apakah nama kategori sudah ada
        $cek = mysqli_query($mysqli, "SELECT * FROM categories WHERE name = '" . mysqli_real_escape_string($mysqli, $name) . "'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Nama kategori sudah digunakan, silakan gunakan nama lain!";
        } else {
            // Simpan ke database
            $query = "INSERT INTO categories (name, description) VALUES (
                        '" . mysqli_real_escape_string($mysqli, $name) . "',
                        '" . mysqli_real_escape_string($mysqli, $description) . "'
                     )";

            if (mysqli_query($mysqli, $query)) {
                header("Location: index.php?pesan=Data kategori berhasil ditambahkan&type=success");
                exit;
            } else {
                $error = "Gagal menambahkan data: " . mysqli_error($mysqli);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <title>Tambah Kategori - AquaGas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>

        body{
            background:#f5fbff;
            min-height:100vh;
        }

        .main-card{
            border:none;
            border-radius:30px;
            overflow:hidden;
            box-shadow:0 15px 40px rgba(120,180,255,.15);
        }

        .header-area{
            background:linear-gradient(
                180deg,
                #edf7ff,
                #e4f2ff
            );

            padding:40px;
        }

        .header-icon{
            width:80px;
            height:80px;
            background:white;
            border-radius:50%;
            display:flex;
            justify-content:center;
            align-items:center;
            font-size:35px;
            color:#4a86ff;
            box-shadow:0 5px 20px rgba(0,0,0,.08);
        }

        .header-title{
            color:#2563eb;
            font-size:42px;
            font-weight:700;
        }

        .header-subtitle{
            color:#64748b;
        }

        .form-control{
            border-radius:18px;
            border:2px solid #dbeafe;
            padding:15px;
        }

        .form-control:focus{
            border-color:#93c5fd;
            box-shadow:0 0 0 .25rem rgba(147,197,253,.2);
        }

        .btn-back{
            background:#e2e8f0;
            border:none;
            border-radius:18px;
            padding:12px 28px;
            font-weight:600;
        }

        .btn-save{
            background:linear-gradient(
                90deg,
                #7dbdff,
                #5fa8ff
            );

            border:none;
            color:white;
            border-radius:18px;
            padding:12px 30px;
            font-weight:600;
        }

        .btn-back:hover,
        .btn-save:hover{
            transform:translateY(-2px);
            transition:.3s;
        }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="col-lg-9 mx-auto">

        <div class="card main-card">

            <!-- HEADER -->

            <div class="header-area">

                <div class="d-flex align-items-center gap-4">

                    <div class="header-icon">
                        <i class="bi bi-tag-fill"></i>
                    </div>

                    <div>

                        <h1 class="header-title mb-2">
                            Tambah Kategori
                        </h1>

                        <p class="header-subtitle mb-0">
                            Tambahkan kategori baru untuk produk AquaGas
                        </p>

                    </div>

                </div>

            </div>


            <!-- BODY -->

            <div class="card-body p-5">

                <?php if($error!=''): ?>

                    <div class="alert alert-danger rounded-4">

                        <?= htmlspecialchars($error) ?>

                    </div>

                <?php endif; ?>


                <form method="POST">

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            Nama Kategori
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="name"
                            value="<?= htmlspecialchars($name) ?>"
                            placeholder="Masukkan nama kategori"
                            required>

                    </div>


                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            Deskripsi

                        </label>

                        <textarea
                            class="form-control"
                            name="description"
                            rows="5"
                            placeholder="Masukkan deskripsi kategori (opsional)"><?= htmlspecialchars($description) ?></textarea>

                    </div>


                    <div class="d-flex justify-content-between mt-5">

                        <a
                            href="index.php"
                            class="btn btn-back">

                            <i class="bi bi-arrow-left"></i>
                            Kembali

                        </a>


                        <button
                            type="submit"
                            class="btn btn-save">

                            <i class="bi bi-floppy"></i>
                            Simpan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
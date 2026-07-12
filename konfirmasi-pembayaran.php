<?php
// ============================================
// File: konfirmasi-pembayaran.php
// Deskripsi: Halaman konfirmasi pembayaran oleh buyer
// ============================================

// Mulai session
session_start();

// Include file koneksi database (mysqli OOP)
// Asumsi: di koneksi.php menggunakan variabel $mysqli
require 'koneksi.php';
require 'mail/kirim-email.php';

// ============================================
// CEK LOGIN & ROLE BUYER
// ============================================
// Cek apakah user sudah login berdasarkan $_SESSION['login']
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Cek role user, hanya buyer yang boleh akses
if ($_SESSION['role'] !== 'buyer') {
    echo "Akses ditolak! Halaman ini khusus untuk buyer.";
    exit;
}

// Simpan username dari session
$username = $_SESSION['username'];

// ============================================
// AMBIL BUYER_ID DARI TABEL USERS
// ============================================
// Query untuk mendapatkan ID user berdasarkan nama atau email (username)
$query_user = "SELECT id FROM users WHERE name = ? OR email = ?";
$stmt_user = $mysqli->prepare($query_user);
$stmt_user->bind_param("ss", $username, $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// Jika user tidak ditemukan di database
if ($result_user->num_rows == 0) {
    die("Data user tidak ditemukan!");
}

$data_user = $result_user->fetch_assoc();
$buyer_id = $data_user['id']; // Ambil ID buyer
$stmt_user->close();

// ============================================
// AMBIL PARAMETER DARI URL (GET)
// ============================================
// Ambil order_id dan metode dari URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$metode = isset($_GET['metode']) ? $_GET['metode'] : '';

// Validasi metode pembayaran
$metode_valid = ['COD', 'Transfer Bank', 'E-Wallet'];
if (!in_array($metode, $metode_valid)) {
    die("Metode pembayaran tidak valid!");
}

// ============================================
// CEK DUPLIKASI PEMBAYARAN
// ============================================
// Cek apakah order_id sudah ada di tabel payments untuk mencegah double insert
$query_cek = "SELECT id FROM payments WHERE order_id = ?";
$stmt_cek = $mysqli->prepare($query_cek);
$stmt_cek->bind_param("i", $order_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$pembayaran_sudah_ada = ($result_cek->num_rows > 0);
$stmt_cek->close();

// ============================================
// AMBIL DATA ORDER DARI DATABASE
// ============================================
// Ambil data order sekaligus memastikan order ini milik buyer yang login
$query_order = "SELECT * FROM orders WHERE id = ? AND buyer_id = ?";
$stmt_order = $mysqli->prepare($query_order);
$stmt_order->bind_param("ii", $order_id, $buyer_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows == 0) {
    die("Pesanan tidak ditemukan atau bukan milik Anda!");
}

$order = $result_order->fetch_assoc();
$stmt_order->close();

// ============================================
// PROSES FORM KONFIRMASI (POST)
// ============================================
$pesan_error = "";
$pesan_sukses = "";

// Hanya proses POST jika pembayaran belum ada (belum double)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$pembayaran_sudah_ada) {

    // Validasi upload bukti untuk Transfer Bank & E-Wallet
    if ($metode != 'COD') {
        if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] == UPLOAD_ERR_NO_FILE) {
            $pesan_error = "Bukti pembayaran wajib diupload untuk metode " . $metode;
        } else {
            $folder_upload = "image/bukti/";

            // Buat folder jika belum ada
            if (!is_dir($folder_upload)) {
                mkdir($folder_upload, 0777, true);
            }

            $nama_file_asli = $_FILES['bukti']['name'];
            $ukuran_file    = $_FILES['bukti']['size'];
            $tmp_file       = $_FILES['bukti']['tmp_name'];

            // Ambil ekstensi file
            $ekstensi = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
            $ekstensi_valid = ['jpg', 'jpeg', 'png'];

            // Validasi ekstensi
            if (!in_array($ekstensi, $ekstensi_valid)) {
                $pesan_error = "Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.";
            }
            // Validasi ukuran (max 2MB)
            elseif ($ukuran_file > 2 * 1024 * 1024) {
                $pesan_error = "Ukuran file terlalu besar! Maksimal 2MB.";
            }
            // Jika lolos validasi, upload file
            else {
                // Generate nama file unik
                $nama_file_baru = "bukti_" . time() . "." . $ekstensi;
                $path_file = $folder_upload . $nama_file_baru;

                if (move_uploaded_file($tmp_file, $path_file)) {
                    $nama_file_simpan = $nama_file_baru;
                } else {
                    $pesan_error = "Gagal mengupload file! Coba lagi.";
                }
            }
        }
    }

    // ============================================
    // INSERT KE TABEL PAYMENTS
    // ============================================
    if (empty($pesan_error)) {
        // Set nilai proof
        $proof = ($metode == 'COD') ? NULL : $nama_file_simpan;
        $status = 'pending';

        $query_insert = "INSERT INTO payments (order_id, payment_method, proof, status) 
                         VALUES (?, ?, ?, ?)";
        $stmt_insert = $mysqli->prepare($query_insert);

        // Bind parameter: i=integer, s=string, s=string(bisa NULL), s=string
        $stmt_insert->bind_param("isss", $order_id, $metode, $proof, $status);

        if ($stmt_insert->execute()) {
            // Ambil email buyer
            $queryEmail = "SELECT name, email FROM users WHERE id = ?";
            $stmtEmail = $mysqli->prepare($queryEmail);
            $stmtEmail->bind_param("i", $buyer_id);
            $stmtEmail->execute();
            $dataEmail = $stmtEmail->get_result()->fetch_assoc();
            $stmtEmail->close();
            $hasilBuyer = kirimEmail(
                $dataEmail['email'],
                $dataEmail['name'],
                'Konfirmasi Pembayaran AquaGas',
                '
                <div style="max-width:600px;margin:auto;font-family:Arial,sans-serif;background:#ffffff;border:1px solid #e5e5e5;border-radius:10px;overflow:hidden">

                    <div style="background:#2563eb;color:white;padding:20px;text-align:center">
                        <h2 style="margin:0;">💧 AquaGas</h2>
                        <p style="margin:5px 0 0;">Konfirmasi Pembayaran</p>
                    </div>

                    <div style="padding:25px">

                        <h3>Halo ' . $dataEmail['name'] . ' 👋</h3>

                        <p>Terima kasih, pembayaran Anda telah kami terima.</p>

                        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                            <tr>
                                <td><b>Order ID</b></td>
                                <td>:#' . $order_id . '</td>
                            </tr>

                            <tr>
                                <td><b>Status</b></td>
                                <td>Menunggu Verifikasi</td>
                            </tr>

                            <tr>
                                <td><b>Metode</b></td>
                                <td>' . $metode . '</td>
                            </tr>
                        </table>

                        <p>
                            Pembayaran Anda akan segera diverifikasi oleh admin.
                            Setelah diverifikasi, status pesanan akan diperbarui.
                        </p>

                        <br>

                        <div style="text-align:center">
                            <a href="http://localhost/TugasBESARPemweb/pesanan-saya.php"
                            style="background:#2563eb;color:white;padding:12px 22px;
                            text-decoration:none;border-radius:6px;">
                            Lihat Pesanan
                            </a>
                        </div>

                    </div>

                    <div style="background:#f5f5f5;padding:15px;text-align:center;font-size:13px;color:#666;">
                        Email ini dikirim otomatis oleh <b>AquaGas</b>.<br>
                        Mohon tidak membalas email ini.
                    </div>

                </div>
                '
            );
            // Ambil email seller
            $querySeller = "SELECT name, email FROM users WHERE role='seller' LIMIT 1";
            $dataSeller = $mysqli->query($querySeller)->fetch_assoc();

            kirimEmail(
                $dataSeller['email'],
                $dataSeller['name'],
                'Pesanan Baru AquaGas',
                '
                <div style="max-width:600px;margin:auto;font-family:Arial,sans-serif;background:#ffffff;border:1px solid #ddd;border-radius:10px;overflow:hidden">

                    <div style="background:#16a34a;color:white;padding:20px;text-align:center">
                        <h2 style="margin:0;">📦 Pesanan Baru AquaGas</h2>
                    </div>

                    <div style="padding:25px">

                        <h3>Halo ' . $dataSeller['name'] . ' 👋</h3>

                        <p>Terdapat pembayaran baru yang perlu diverifikasi.</p>

                        <table style="width:100%;border-collapse:collapse">

                            <tr>
                                <td><b>Order ID</b></td>
                                <td>:#' . $order_id . '</td>
                            </tr>

                            <tr>
                                <td><b>Metode</b></td>
                                <td>' . $metode . '</td>
                            </tr>

                        </table>

                        <br>

                        <div style="text-align:center">
                            <a href="http://localhost/TugasBESARPemweb/Penjual/pesanan.php"
                            style="background:#16a34a;color:white;padding:12px 22px;
                            text-decoration:none;border-radius:6px;">
                            Verifikasi Pesanan
                            </a>
                        </div>

                    </div>

                    <div style="background:#f5f5f5;padding:15px;text-align:center;font-size:13px;color:#666;">
                        AquaGas Notification System
                    </div>

                </div>
                '
            );
            $stmt_insert->close();

            // Redirect ke riwayat pesanan dengan parameter success=1
            header("Location: pesanan-saya.php?success=1");
            exit;
        } else {
            $pesan_error = "Gagal menyimpan konfirmasi pembayaran: " . $mysqli->error;
        }
        $stmt_insert->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Toko Online</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 30px;
            padding-bottom: 30px;
        }

        .card-konfirmasi {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .btn-konfirmasi {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
        }

        .btn-konfirmasi:hover {
            opacity: 0.9;
            color: white;
        }

        .info-item {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <div class="card card-konfirmasi">
                    <div class="card-header card-header-custom text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-credit-card-2-front"></i> Konfirmasi Pembayaran
                        </h4>
                    </div>

                    <div class="card-body p-4">

                        <!-- Jika pembayaran sudah pernah diupload, tampilkan pesan ini -->
                        <?php if ($pembayaran_sudah_ada): ?>
                            <div class="alert alert-warning text-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Konfirmasi pembayaran untuk Order #<?php echo $order['id']; ?> sudah pernah dikirim.</strong>
                                <br>
                                Mohon menunggu verifikasi dari admin.
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <a href="pesanan-saya.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Riwayat Pesanan
                                </a>
                            </div>

                            <!-- Jika belum ada pembayaran, tampilkan form -->
                        <?php else: ?>

                            <!-- Alert Error -->
                            <?php if (!empty($pesan_error)): ?>
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><?php echo $pesan_error; ?></div>
                                </div>
                            <?php endif; ?>

                            <!-- Alert Info untuk COD -->
                            <?php if ($metode == 'COD'): ?>
                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <div>
                                        Metode <strong>COD (Cash On Delivery)</strong> dipilih.
                                        Pembayaran dilakukan saat barang diterima.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Order ID</small>
                                        <h5 class="mb-0">#<?php echo $order['id']; ?></h5>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">Total Pembayaran</small>
                                        <h5 class="mb-0 text-success">
                                            Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item">
                                <small class="text-muted">Metode Pembayaran</small>
                                <h6 class="mb-0">
                                    <?php
                                    if ($metode == 'COD') {
                                        echo '<i class="bi bi-truck"></i> COD (Cash On Delivery)';
                                    } elseif ($metode == 'Transfer Bank') {
                                        echo '<i class="bi bi-bank"></i> Transfer Bank';
                                    } elseif ($metode == 'E-Wallet') {
                                        echo '<i class="bi bi-wallet2"></i> E-Wallet';
                                    }
                                    ?>
                                </h6>
                            </div>

                            <form action="" method="POST" enctype="multipart/form-data">
                                <?php if ($metode != 'COD'): ?>
                                    <div class="mb-3">
                                        <label for="bukti" class="form-label fw-bold">
                                            <i class="bi bi-cloud-upload"></i> Upload Bukti Pembayaran
                                        </label>
                                        <input type="file" class="form-control" id="bukti" name="bukti"
                                            accept="image/jpeg,image/jpg,image/png" required>
                                        <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB.</small>
                                    </div>

                                    <?php if ($metode == 'Transfer Bank'): ?>
                                        <div class="alert alert-warning py-2">
                                            <small>
                                                <i class="bi bi-info-circle"></i> Transfer ke: <br>
                                                <strong>Bank BCA - 1234567890 (a.n. Toko Online)</strong>
                                            </small>
                                        </div>
                                    <?php elseif ($metode == 'E-Wallet'): ?>
                                        <div class="alert alert-warning py-2">
                                            <small>
                                                <i class="bi bi-info-circle"></i> Transfer ke: <br>
                                                <strong>OVO/DANA/GoPay - 081234567890 (a.n. Toko Online)</strong>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-success py-3">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <strong>Pembayaran COD</strong><br>
                                        Anda tidak perlu upload bukti pembayaran. Pembayaran dilakukan tunai saat barang diterima.
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-konfirmasi btn-lg">
                                        <i class="bi bi-check-circle"></i> Simpan Konfirmasi
                                    </button>
                                    <a href="pesanan-saya.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>

                        <?php endif; // Penutup if($pembayaran_sudah_ada) 
                        ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
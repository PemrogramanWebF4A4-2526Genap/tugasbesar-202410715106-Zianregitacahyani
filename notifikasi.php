<?php

/**
 * =============================================================
 * notifikasi.php
 * Halaman Notifikasi untuk Buyer - Toko Online PHP Native
 * =============================================================
 * Fitur:
 *  - Hanya buyer yang sudah login yang boleh mengakses
 *  - Menampilkan semua notifikasi user berdasarkan user_id
 *  - Urut dari terbaru ke terlama
 *  - Tombol "Tandai Semua Dibaca"
 *  - Badge Baru (merah) / Dibaca (hijau)
 *  - Empty state dengan icon inbox
 *  - Prepared statement mysqli OOP
 * =============================================================
 */

// =====================================================
// 1. MEMULAI SESSION
// =====================================================
session_start();

// =====================================================
// 2. KONEKSI DATABASE (mysqli OOP)
// =====================================================
// Ganti konfigurasi sesuai setting database Anda
require 'koneksi.php';

// =====================================================
// 3. CEK LOGIN BUYER
// =====================================================
// Jika session username belum ada, berarti buyer belum login
// Maka redirect ke halaman login
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'buyer') {
    header("Location: login/login.php");
    exit;
}

// Ambil username dari session
$username = $_SESSION['username'];

// =====================================================
// 4. AMBIL USER_ID BERDASARKAN USERNAME
// =====================================================
// Gunakan prepared statement untuk mencegah SQL Injection
$stmt_user = $mysqli->prepare("
    SELECT id
    FROM users
    WHERE name = ? OR email = ?
    LIMIT 1
");
$stmt_user->bind_param("ss", $username, $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// Jika user tidak ditemukan (misal session palsu), logout otomatis
if ($result_user->num_rows === 0) {
    session_destroy();
    header("Location: login/login.php");
    exit;
}

// Ambil data user sebagai associative array
$user_data   = $result_user->fetch_assoc();
$user_id     = (int) $user_data['id']; // cast ke integer untuk keamanan
$stmt_user->close();

// =====================================================
// 5. PROSES "TANDAI SEMUA DIBACA"
// =====================================================
// Jika buyer menekan tombol "Tandai Semua Dibaca"
// Form akan mengirim parameter POST action = mark_all_read
$flash_message = ""; // variabel untuk pesan notifikasi aksi

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    // Update semua notifikasi user yang is_read = 0 menjadi 1
    $stmt_mark = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt_mark->bind_param("i", $user_id);

    if ($stmt_mark->execute()) {
        // Cek berapa baris yang terpengaruh
        $affected = $stmt_mark->affected_rows;
        if ($affected > 0) {
            $flash_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-1"></i> ' . $affected . ' notifikasi berhasil ditandai dibaca.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
        } else {
            $flash_message = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-1"></i> Tidak ada notifikasi baru untuk ditandai.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
        }
    } else {
        $flash_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i> Gagal menandai notifikasi.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
    }
    $stmt_mark->close();

    // Redirect untuk menghindari resubmit form saat refresh
    header("Location: notifikasi.php");
    exit;
}

// =====================================================
// 6. AMBIL SEMUA NOTIFIKASI USER
// =====================================================
// Urutkan dari terbaru (created_at DESC)
$stmt_notif = $mysqli->prepare("
    SELECT id, user_id, message, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
");
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();

// Simpan semua notifikasi ke array
$notifications = [];
while ($row = $result_notif->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt_notif->close();

// Hitung jumlah notifikasi belum dibaca (untuk badge di header)
$unread_count = 0;
foreach ($notifications as $n) {
    if ((int)$n['is_read'] === 0) {
        $unread_count++;
    }
}

// =====================================================
// 7. FUNGSI HELPER: FORMAT WAKTU RELATIF
// =====================================================
// Contoh: "2 jam lalu", "3 hari lalu"
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $diff      = time() - $timestamp;

    if ($diff < 60) {
        return "Baru saja";
    } elseif ($diff < 3600) {
        $menit = floor($diff / 60);
        return $menit . " menit lalu";
    } elseif ($diff < 86400) {
        $jam = floor($diff / 3600);
        return $jam . " jam lalu";
    } elseif ($diff < 604800) {
        $hari = floor($diff / 86400);
        return $hari . " hari lalu";
    } else {
        // Lebih dari seminggu, tampilkan tanggal lengkap
        return date("d M Y, H:i", $timestamp);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Toko Online</title>

    <!-- ===================================================== -->
    <!-- BOOTSTRAP 5 CSS -->
    <!-- ===================================================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ===================================================== -->
    <!-- BOOTSTRAP ICONS (untuk ikon inbox, bell, dll) -->
    <!-- ===================================================== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- ===================================================== -->
    <!-- CUSTOM CSS tambahan -->
    <!-- ===================================================== -->
    <style>
        /* Background gradient lembut */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4ecf2 100%);
            min-height: 100vh;
        }

        /* Card utama */
        .notif-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        /* Setiap item notifikasi */
        .notif-item {
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 10px;
            background-color: #fff;
        }

        /* Notifikasi yang belum dibaca (highlight lembut biru) */
        .notif-item.unread {
            border-left-color: #dc3545;
            background-color: #fff8f9;
        }

        /* Notifikasi yang sudah dibaca */
        .notif-item.read {
            border-left-color: #198754;
            background-color: #ffffff;
        }

        /* Hover effect */
        .notif-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Icon di empty state */
        .empty-icon {
            font-size: 5rem;
            color: #cdd5df;
        }

        /* Avatar bulat untuk ikon notifikasi */
        .notif-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notif-icon-circle.unread {
            background-color: #fde8eb;
            color: #dc3545;
        }

        .notif-icon-circle.read {
            background-color: #e2f3ea;
            color: #198754;
        }

        /* Header dengan bell icon */
        .header-title i {
            color: #0d6efd;
        }

        /* ===========================
   Badge Notifikasi Navbar
=========================== */

        .notif-link {
            position: relative;
        }

        .notif-badge {
            position: absolute;
            top: -4px;
            right: -10px;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background: #ff4d6d;
            color: #fff;
            border: 2px solid #8ec5ff;
            box-shadow: 0 2px 8px rgba(255, 77, 109, .35);
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <!-- ===================================================== -->
    <!-- KONTEN UTAMA -->
    <!-- ===================================================== -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">

                <!-- Header halaman -->
                <div class="d-flex justify-content-between align-items-center mb-4 header-title">
                    <h3 class="fw-bold mb-0">
                        <i class="bi bi-bell-fill me-2"></i>Notifikasi
                    </h3>
                    <!-- Tombol kembali ke index.php -->
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <!-- Tampilkan flash message jika ada -->
                <?php echo $flash_message; ?>

                <!-- Card utama berisi list notifikasi -->
                <div class="card notif-card">
                    <div class="card-body p-4">

                        <!-- Baris atas: info jumlah + tombol mark all read -->
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <span class="text-muted">
                                    Total: <strong><?php echo count($notifications); ?></strong> notifikasi
                                </span>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $unread_count; ?> belum dibaca</span>
                                <?php endif; ?>
                            </div>

                            <!-- Form Tombol "Tandai Semua Dibaca" -->
                            <?php if ($unread_count > 0): ?>
                                <form method="POST" action="" onsubmit="return confirm('Tandai semua notifikasi sebagai dibaca?');">
                                    <input type="hidden" name="action" value="mark_all_read">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- ===================================================== -->
                        <!-- KONDISI: JIKA BELUM ADA NOTIFIKASI -->
                        <!-- ===================================================== -->
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-5">
                                <!-- Icon inbox besar -->
                                <i class="bi bi-inbox-fill empty-icon"></i>
                                <h5 class="mt-3 text-muted">Belum ada notifikasi</h5>
                                <p class="text-muted small">
                                    Notifikasi tentang pesanan, promo, dan update akan muncul di sini.
                                </p>
                                <a href="index.php" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="bi bi-bag me-1"></i> Mulai Belanja
                                </a>
                            </div>

                            <!-- ===================================================== -->
                            <!-- KONDISI: JIKA ADA NOTIFIKASI -->
                            <!-- ===================================================== -->
                        <?php else: ?>
                            <div class="notif-list">
                                <?php foreach ($notifications as $notif):
                                    // Tentukan class berdasarkan status baca
                                    $is_read   = (int) $notif['is_read'];
                                    $row_class = $is_read === 0 ? 'unread' : 'read';
                                    $icon_class = $is_read === 0 ? 'unread' : 'read';
                                    $icon_name  = $is_read === 0 ? 'bi-bell-fill' : 'bi-bell';
                                ?>
                                    <!-- Item Notifikasi -->
                                    <div class="notif-item <?php echo $row_class; ?>">
                                        <div class="d-flex align-items-start gap-3">

                                            <!-- Lingkaran icon -->
                                            <div class="notif-icon-circle <?php echo $icon_class; ?>">
                                                <i class="bi <?php echo $icon_name; ?>"></i>
                                            </div>

                                            <!-- Konten notifikasi -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                                    <h6 class="mb-1 fw-bold">
                                                        Notifikasi Pesanan
                                                    </h6>

                                                    <!-- Badge Baru / Dibaca -->
                                                    <?php if ($is_read === 0): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-dot"></i> Baru
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check2"></i> Dibaca
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Pesan notifikasi -->
                                                <p class="mb-1 text-muted small">
                                                    <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                                                </p>

                                                <!-- Waktu relatif -->
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?php echo timeAgo($notif['created_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Catatan kaki -->
                <p class="text-center text-muted small mt-4 mb-0">
                    <i class="bi bi-shield-check me-1"></i>
                    Notifikasi Anda bersifat pribadi dan hanya bisa dilihat oleh Anda.
                </p>

            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- BOOTSTRAP 5 JS BUNDLE -->
    <!-- ===================================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ===================================================== -->
    <!-- SCRIPT TAMBAHAN (opsional) -->
    <!-- ===================================================== -->
    <script>
        // Auto-dismiss alert setelah 3 detik
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 3000);
    </script>

</body>

</html>

<?php
// =====================================================
// 8. TUTUP KONEKSI DATABASE
// =====================================================
$mysqli->close();
?>
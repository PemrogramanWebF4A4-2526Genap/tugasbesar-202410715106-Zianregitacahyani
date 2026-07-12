<?php
// =============================================
// File: Penjual/pesanan.php
// Deskripsi: Halaman manajemen pesanan untuk seller
// Project: Toko Online PHP Native + MySQL (mysqli OOP)
// =============================================

// Mulai session untuk mengakses data login
session_start();

// Include file koneksi database
// File koneksi.php menghasilkan object mysqli bernama $mysqli
require_once '../koneksi.php';
require_once '../mail/kirim-email.php';

// =============================================
// 1. CEK HAK AKSES
// Hanya seller yang boleh mengakses halaman ini
// Cek apakah user sudah login dan role-nya = 'seller'
// =========================<?php
// =============================================
// File: Penjual/pesanan.php
// Deskripsi: Halaman manajemen pesanan untuk seller
// Project: Toko Online PHP Native + MySQL (mysqli OOP)
// =============================================


// Include file koneksi database
// File koneksi.php menghasilkan object mysqli bernama $mysqli
require_once '../koneksi.php';

// =============================================
// 1. CEK HAK AKSES
// Hanya seller yang boleh mengakses halaman ini
// Cek apakah user sudah login dan role-nya = 'seller'
// =============================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'seller') {
    // Jika belum login atau bukan seller, redirect ke halaman login
    header("Location: ../login/login.php");
    exit;
}

// Ambil nama seller dari session untuk ditampilkan di header
$seller_name = $_SESSION['username'] ?? 'Seller';

// =============================================
// 2. PROSES UPDATE STATUS PESANAN
// Jika seller mengklik tombol "Update" pada form
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {

    // Ambil data dari form
    $order_id   = (int) $_POST['order_id'];       // ID pesanan yang akan diupdate
    $new_status = $_POST['status'];                // Status baru yang dipilih

    // Validasi: pastikan status yang dikirim adalah salah satu dari 4 status yang valid
    $allowed_statuses = ['pending', 'diproses', 'dikirim', 'selesai'];

    if (in_array($new_status, $allowed_statuses)) {
        // Query UPDATE status pesanan menggunakan prepared statement (mysqli OOP)
        $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);

        // Eksekusi query
        if ($stmt->execute()) {

            // ==========================
            // AMBIL BUYER_ID PESANAN
            // ==========================
            $qBuyer = $mysqli->prepare("
        SELECT buyer_id
        FROM orders
        WHERE id = ?
    ");

            $qBuyer->bind_param("i", $order_id);
            $qBuyer->execute();

            $resultBuyer = $qBuyer->get_result();
            $buyer = $resultBuyer->fetch_assoc();

            if ($buyer) {

                $buyer_id = $buyer['buyer_id'];

                // Ambil data buyer
                $qEmail = $mysqli->prepare("
SELECT u.name, u.email
FROM orders o
JOIN users u ON o.buyer_id = u.id
WHERE o.id = ?
");

                $qEmail->bind_param("i", $order_id);
                $qEmail->execute();
                $dataBuyer = $qEmail->get_result()->fetch_assoc();
                $qEmail->close();

                if ($dataBuyer) {

                    kirimEmail(
                        $dataBuyer['email'],
                        $dataBuyer['name'],
                        'Status Pesanan AquaGas',
                        '
                        <h2>Halo ' . $dataBuyer['name'] . ' 👋</h2>

                        <p>Status pesanan Anda telah diperbarui.</p>

                        <p><strong>Order ID:</strong> #' . $order_id . '</p>

                        <p><strong>Status:</strong> ' . ucfirst($new_status) . '</p>

                        <br>

                        <p>Terima kasih telah berbelanja di AquaGas.</p>

                        <p><b>Tim AquaGas</b></p>
                        '
                    );
                }

                // Pesan notifikasi
                $message = "Status pesanan #$order_id berubah menjadi '$new_status'.";

                // Simpan ke tabel notifications
                $qNotif = $mysqli->prepare("
            INSERT INTO notifications
            (user_id, message, is_read)
            VALUES (?, ?, 0)
        ");

                $qNotif->bind_param(
                    "is",
                    $buyer_id,
                    $message
                );

                $qNotif->execute();
                $qNotif->close();
            }

            $qBuyer->close();

            $stmt->close();

            header("Location: pesanan.php?success=1");
            exit;
        }
        // Tutup statement
        $stmt->close();
    }
}

// =============================================
// 3. AMBIL SEMUA DATA PESANAN
// JOIN dengan tabel users (pembeli) dan payments (pembayaran)
// Urutkan berdasarkan id DESC (pesanan terbaru di atas)
// =============================================
$query_orders = "
    SELECT 
        o.id               AS order_id,
        o.total_amount     AS total_amount,
        o.status           AS order_status,
        o.created_at       AS created_at,
        u.name             AS buyer_name,
        p.payment_method   AS payment_method,
        p.proof            AS payment_proof,
        p.status           AS payment_status
    FROM orders o
    -- JOIN dengan tabel users untuk mendapatkan nama pembeli
    LEFT JOIN users u ON o.buyer_id = u.id
    -- JOIN dengan tabel payments untuk mendapatkan info pembayaran
    LEFT JOIN payments p ON o.id = p.order_id
    -- Urutkan dari pesanan terbaru ke terlama
    ORDER BY o.id DESC
";

// Eksekusi query utama
$result_orders = $mysqli->query($query_orders);
$orders = [];

// Simpan hasil query ke dalam array $orders
if ($result_orders && $result_orders->num_rows > 0) {
    while ($row = $result_orders->fetch_assoc()) {
        $orders[] = $row;
    }
}

// =============================================
// 4. AMBIL DETAIL ITEM PRODUK UNTUK SETIAP PESANAN
// JOIN order_items dengan products untuk mendapatkan nama dan gambar produk
// =============================================
foreach ($orders as &$order) {
    $order_id = $order['order_id'];

    // Query untuk mengambil daftar produk pada pesanan tertentu
    $query_items = "
        SELECT 
            oi.quantity    AS quantity,
            oi.price       AS price,
            pr.name        AS product_name,
            pr.image       AS product_image
        FROM order_items oi
        -- JOIN dengan tabel products untuk mendapatkan info produk
        LEFT JOIN products pr ON oi.product_id = pr.id
        WHERE oi.order_id = ?
    ";

    // Gunakan prepared statement untuk keamanan
    $stmt_items = $mysqli->prepare($query_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();

    // Simpan daftar item ke dalam array items pada order tersebut
    $items = [];
    while ($item = $result_items->fetch_assoc()) {
        $items[] = $item;
    }

    // Tambahkan items ke dalam data order
    $order['items'] = $items;
    $stmt_items->close();
}
// Hapus reference untuk mencegah bug tak terduga
unset($order);

// =============================================
// HITUNG STATISTIK DARI DATA YANG SUDAH ADA
// =============================================
$total_pesanan = count($orders);
$pending_count = 0;
$dikirim_count = 0;
$selesai_count = 0;
foreach ($orders as $o) {
    if ($o['order_status'] === 'pending') $pending_count++;
    if ($o['order_status'] === 'dikirim') $dikirim_count++;
    if ($o['order_status'] === 'selesai') $selesai_count++;
}

// =============================================
// FUNGSI HELPER: BADGE WARNA UNTUK STATUS PESANAN
// Mengembalikan HTML badge Bootstrap sesuai status
// =============================================
function getStatusBadge($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'diproses':
            return '<span class="badge bg-info text-dark">Diproses</span>';
        case 'dikirim':
            return '<span class="badge bg-primary">Dikirim</span>';
        case 'selesai':
            return '<span class="badge bg-success">Selesai</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

// =============================================
// FUNGSI HELPER: BADGE WARNA UNTUK STATUS PEMBAYARAN
// =============================================
function getPaymentStatusBadge($status)
{
    switch (strtolower($status ?? '')) {
        case 'paid':
        case 'lunas':
        case 'verified':
        case 'success':
        case 'confirmed':
            return '<span class="badge bg-success">' . htmlspecialchars(ucfirst($status)) . '</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark">' . htmlspecialchars(ucfirst($status)) . '</span>';
        case 'failed':
        case 'gagal':
        case 'rejected':
            return '<span class="badge bg-danger">' . htmlspecialchars(ucfirst($status)) . '</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status ?? '-') . '</span>';
    }
}
?>

<!-- =============================================
     5. TAMPILAN HTML + BOOTSTRAP 5
     ============================================= -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Toko Online</title>

    <!-- Bootstrap 5 CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* ---------- Body & Background ---------- */
        body {
            background-color: #f5f9ff;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* ---------- Panel Seller ---------- */
        .seller-panel {
            max-width: 1320px;
            margin: 0 auto;
            padding: 28px 0;
        }

        /* ---------- Stat Cards ---------- */
        .stat-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(126, 200, 255, 0.15);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(126, 200, 255, 0.22);
        }

        .stat-card .card-body {
            padding: 22px 20px;
        }

        .stat-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 0.73rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .stat-number {
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.15;
            color: #1e293b;
        }

        /* ---------- Order Card ---------- */
        .order-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(126, 200, 255, 0.15);
            overflow: hidden;
        }

        .order-card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e0ecff;
            padding: 20px 26px;
        }

        .order-card-header h5 {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.05rem;
        }

        .count-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 5px 14px;
            border-radius: 20px;
        }

        /* ---------- Tabel Modern ---------- */
        .order-table thead th {
            background-color: #dbeafe;
            color: #1e3a5f;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 2px solid #bfdbfe;
            padding: 14px 16px;
            white-space: normal;
            min-width: 160px;
            line-height: 1.5;
        }

        .order-table tbody td {
            padding: 18px;
            vertical-align: top;
            min-width: 140px;
            white-space: normal;
        }

        .order-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .order-table tbody tr:hover {
            background-color: #f0f7ff;
        }

        .order-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ---------- Badge Pastel ---------- */
        .order-table .badge {
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.76rem;
            letter-spacing: 0.2px;
            display: inline-block;
        }

        .badge.bg-warning {
            background-color: #fef3c7 !important;
            color: #92400e !important;
        }

        .badge.bg-info {
            background-color: #dbeafe !important;
            color: #1e40af !important;
        }

        .badge.bg-primary {
            background-color: #dbeafe !important;
            color: #1d4ed8 !important;
        }

        .badge.bg-success {
            background-color: #d1fae5 !important;
            color: #065f46 !important;
        }

        .badge.bg-danger {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
        }

        .badge.bg-secondary {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
        }

        /* ---------- Gambar Produk ---------- */
        .product-img {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e0ecff;
        }

        /* ---------- Item List ---------- */
        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .item-list li {
            padding: 7px 0;
            border-bottom: 1px solid #f0f4fa;
            display: flex;
            align-items: center;
        }

        .item-list li:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .item-list li:first-child {
            padding-top: 0;
        }

        .item-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.84rem;
            display: block;
            line-height: 1.3;
        }

        .item-detail {
            color: #6b7280;
            font-size: 0.78rem;
        }

        /* ---------- Bukti Transfer ---------- */
        .proof-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e0ecff;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .proof-thumb:hover {
            transform: scale(1.1);
            border-color: #72b7ff;
            box-shadow: 0 4px 16px rgba(114, 183, 255, 0.35);
        }

        .proof-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #f0f7ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .proof-btn:hover {
            background-color: #dbeafe;
            color: #1e40af;
            border-color: #93c5fd;
        }

        /* ---------- Form Select & Button ---------- */
        .form-select-sm {
            border-radius: 12px;
            border: 2px solid #e0ecff;
            font-size: 0.82rem;
            padding: 6px 12px;
            font-weight: 500;
            color: #334155;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-color: #ffffff;
        }

        .form-select-sm:focus {
            border-color: #72b7ff;
            box-shadow: 0 0 0 3px rgba(114, 183, 255, 0.2);
            outline: none;
        }

        .btn-update {
            background-color: #72b7ff;
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 7px 16px;
            transition: all 0.25s ease;
            letter-spacing: 0.2px;
        }

        .btn-update:hover {
            background-color: #5aa3f5;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(114, 183, 255, 0.4);
        }

        .btn-update:active {
            transform: translateY(0);
        }

        /* ---------- Alert ---------- */
        .alert-toast {
            border: none;
            border-radius: 16px;
            padding: 16px 22px;
            font-size: 0.88rem;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        /* ---------- Page Header ---------- */
        .page-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: #1e293b;
        }

        .page-subtitle {
            font-size: 0.88rem;
            color: #6b7280;
            font-weight: 400;
        }

        .seller-badge {
            background: linear-gradient(135deg, #dbeafe, #e0ecff);
            color: #1d4ed8;
            padding: 8px 18px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ---------- Empty State ---------- */
        .empty-state {
            padding: 70px 20px;
        }

        .empty-icon {
            width: 90px;
            height: 90px;
            border-radius: 24px;
            background-color: #eef5ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #93c5fd;
            margin-bottom: 16px;
        }

        /* ---------- ID Pesanan ---------- */
        .order-id {
            color: #1d4ed8;
            font-weight: 800;
            font-size: 0.9rem;
        }

        /* ---------- Total Harga ---------- */
        .order-total {
            color: #059669;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 991.98px) {
            .seller-panel {
                padding: 20px 0;
            }
        }

        @media (max-width: 767.98px) {
            .stat-card .card-body {
                padding: 16px 14px;
            }

            .stat-icon-box {
                width: 44px;
                height: 44px;
                font-size: 1.2rem;
                border-radius: 12px;
            }

            .stat-number {
                font-size: 1.3rem;
            }

            .stat-label {
                font-size: 0.68rem;
            }

            .order-card-header {
                padding: 16px 18px;
                flex-direction: column;
                align-items: flex-start !important;
                gap: 8px;
            }

            .order-table thead th {
                padding: 10px 10px;
                font-size: 0.68rem;
            }

            .order-table tbody td {
                padding: 12px 10px;
                font-size: 0.8rem;
            }

            .proof-thumb {
                width: 40px;
                height: 40px;
            }

            .product-img {
                width: 34px;
                height: 34px;
            }

            .btn-update {
                padding: 6px 12px;
                font-size: 0.78rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .order-table-wrapper {
                overflow-x: auto;
                border-radius: 18px;
            }

            .order-table {
                min-width: 1500px;
                border-collapse: separate;
                border-spacing: 0;
            }
        }
    </style>
</head>

<body>

    <!-- ===== NAVBAR ATAS (dari file navbar.php) ===== -->
    <?php include '../navbar.php'; ?>

    <!-- ===== KONTEN UTAMA ===== -->
    <div class="seller-panel">
        <div class="container">

            <!-- Header halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title mb-1">
                        <i class="bi bi-bag-check-fill" style="color:#72b7ff"></i>
                        Manajemen Pesanan
                    </h1>
                    <p class="page-subtitle mb-0">Kelola semua pesanan yang masuk ke toko Anda</p>
                </div>
                <div class="d-none d-sm-block">
                    <span class="seller-badge">
                        <i class="bi bi-shop"></i>
                        <?= htmlspecialchars($seller_name) ?>
                    </span>
                </div>
            </div>

            <!-- Notifikasi sukses setelah update status -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success alert-toast alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    Status pesanan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ===== 4 KARTU STATISTIK ===== -->
            <div class="row g-3 mb-4">
                <!-- Total Pesanan -->
                <div class="col-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon-box" style="background-color:#dbeafe;color:#2563eb">
                                <i class="bi bi-bag"></i>
                            </div>
                            <div>
                                <div class="stat-label">Total Pesanan</div>
                                <div class="stat-number"><?= $total_pesanan ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pending -->
                <div class="col-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon-box" style="background-color:#fef3c7;color:#d97706">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="stat-label">Pending</div>
                                <div class="stat-number"><?= $pending_count ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Dikirim -->
                <div class="col-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon-box" style="background-color:#e0e7ff;color:#4f46e5">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <div class="stat-label">Dikirim</div>
                                <div class="stat-number"><?= $dikirim_count ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Selesai -->
                <div class="col-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon-box" style="background-color:#d1fae5;color:#059669">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div>
                                <div class="stat-label">Selesai</div>
                                <div class="stat-number"><?= $selesai_count ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== CARD TABEL PESANAN ===== -->
            <div class="card order-card">
                <div class="order-card-header d-flex align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2" style="color:#72b7ff"></i>Daftar Semua Pesanan
                    </h5>
                    <span class="count-badge ms-3"><?= count($orders) ?> pesanan</span>
                </div>
                <div class="card-body p-0">

                    <?php if (empty($orders)): ?>
                        <!-- Tampilan jika belum ada pesanan -->
                        <div class="empty-state text-center">
                            <div class="empty-icon mx-auto">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h5 class="text-muted fw-semibold mb-2">Belum Ada Pesanan</h5>
                            <p class="text-muted mb-0" style="font-size:0.88rem">Pesanan dari pembeli akan muncul di sini</p>
                        </div>
                    <?php else: ?>

                        <!-- Tabel responsif (bisa scroll horizontal di layar kecil) -->
                        <div class="table-responsive order-table-wrapper">
                            <table class="table order-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>
                                            ID <br> Pesanan
                                        </th>
                                        <th>
                                            Nama <br> Pembeli
                                        </th>
                                        <th>
                                            Tanggal <br> Pesanan
                                        </th>
                                        <th>
                                            Total <br> Belanja
                                        </th>
                                        <th>
                                            Status <br> Pesanan
                                        </th>
                                        <th>
                                            Metode<br>Pembayaran
                                        </th>
                                        <th>
                                            Status<br>Pembayaran
                                        </th>
                                        <th>
                                            Bukti<br>Transfer
                                        </th>
                                        <th>
                                            Produk<br> Dibeli
                                        </th>
                                        <th>
                                            Update<br> Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <!-- Kolom: ID Pesanan -->
                                            <td>
                                                <span class="order-id">#<?= $order['order_id'] ?></span>
                                            </td>

                                            <!-- Kolom: Nama Pembeli -->
                                            <td>
                                                <i class="bi bi-person-circle" style="color:#72b7ff"></i>
                                                <?= htmlspecialchars($order['buyer_name'] ?? '-') ?>
                                            </td>

                                            <!-- Kolom: Tanggal Pesanan -->
                                            <td>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3"></i>
                                                    <?= date('d/m/Y', strtotime($order['created_at'])) ?><br>
                                                    <i class="bi bi-clock"></i>
                                                    <?= date('H:i', strtotime($order['created_at'])) ?>
                                                </small>
                                            </td>

                                            <!-- Kolom: Total Belanja -->
                                            <td>
                                                <span class="order-total">
                                                    Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                                                </span>
                                            </td>

                                            <!-- Kolom: Status Pesanan (badge berwarna) -->
                                            <td>
                                                <?= getStatusBadge($order['order_status']) ?>
                                            </td>

                                            <!-- Kolom: Metode Pembayaran -->
                                            <td>
                                                <?php if (!empty($order['payment_method'])): ?>
                                                    <i class="bi bi-credit-card"></i>
                                                    <?= htmlspecialchars($order['payment_method']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Kolom: Status Pembayaran (badge berwarna) -->
                                            <td>
                                                <?= getPaymentStatusBadge($order['payment_status']) ?>
                                            </td>

                                            <!-- Kolom: Bukti Transfer (jika ada) -->
                                            <td style="min-width:170px; text-align:center;">
                                                <?php if (!empty($order['payment_proof'])): ?>
                                                    <a href="../image/bukti/<?= htmlspecialchars($order['payment_proof']) ?>">
                                                        <img src="../image/bukti/<?= htmlspecialchars($order['payment_proof']) ?>" class="proof-thumb">
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic" style="font-size:0.8rem">Tidak ada</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Kolom: Daftar Produk yang Dibeli -->
                                            <td style="min-width:250px;">
                                                <?php if (!empty($order['items'])): ?>
                                                    <ul class="item-list">
                                                        <?php foreach ($order['items'] as $item): ?>
                                                            <li>
                                                                <!-- Gambar produk (jika ada) -->
                                                                <?php if (!empty($item['product_image'])): ?>
                                                                    <img src="../image/produk/<?= htmlspecialchars($item['product_image']) ?>"
                                                                        alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                                        class="product-img me-2" ;">
                                                                <?php else: ?>
                                                                    <div class="product-img me-2 d-flex align-items-center justify-content-center" style="background:#eef5ff">
                                                                        <i class="bi bi-image text-muted" style="font-size:0.85rem"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <!-- Info produk: nama, qty, harga -->
                                                                <div>
                                                                    <span class="item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                                                    <span class="item-detail">
                                                                        <?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                                                    </span>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted">Tidak ada produk</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Kolom: Form Update Status Pesanan -->
                                            <td style="min-width:180px;">
                                                <!-- Form untuk mengupdate status pesanan -->
                                                <form method="POST" action="" class="d-grid gap-2">
                                                    <!-- Hidden input: ID pesanan yang akan diupdate -->
                                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">

                                                    <!-- Dropdown pilihan status -->
                                                    <select name="status" class="form-select form-select-sm mb-2">
                                                        <option value="pending"
                                                            <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>
                                                            Pending
                                                        </option>
                                                        <option value="diproses"
                                                            <?= $order['order_status'] === 'diproses' ? 'selected' : '' ?>>
                                                            Diproses
                                                        </option>
                                                        <option value="dikirim"
                                                            <?= $order['order_status'] === 'dikirim' ? 'selected' : '' ?>>
                                                            Dikirim
                                                        </option>
                                                        <option value="selesai"
                                                            <?= $order['order_status'] === 'selesai' ? 'selected' : '' ?>>
                                                            Selesai
                                                        </option>
                                                    </select>

                                                    <!-- Tombol Update -->
                                                    <button type="submit" name="update_status" value="1"
                                                        class="btn btn-update w-100">
                                                        <i class="bi bi-arrow-repeat"></i> Update
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- End table-responsive -->

                    <?php endif; ?>
                    <!-- End if empty orders -->

                </div>
                <!-- End card-body -->
            </div>
            <!-- End card -->

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (termasuk Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>


</html>

<?php
// =============================================
// TUTUP KONEKSI DATABASE
// =============================================
$mysqli->close();
?>
<?php
/*
=====================================================================
  FILE NAME   : pesanan-saya.php
  DESCRIPTION : Menampilkan daftar pesanan milik buyer yang login.
                Setiap pesanan dapat dilihat detail produknya.
  AUTHOR      : -
  STACK       : PHP Native + MySQL (mysqli OOP) + Bootstrap 5
=====================================================================
*/

// =====================================================
// 1. START SESSION & INCLUDE KONEKSI DATABASE
// =====================================================
session_start();

// Memanggil file koneksi yang mendefinisikan object $mysqli
require_once 'koneksi.php';

// =====================================================
// 2. CEK AKSES: HANYA BUYER YANG BOLEH MENGAKSES
// =====================================================
// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login/login.php');
    exit;
}

// Jika role bukan buyer, tolak akses halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    // Tampilkan pesan tolak sederhana
    echo "<div class='container py-5 text-center'>
            <h3>Akses Ditolak</h3>
            <p>Halaman ini hanya untuk <strong>buyer</strong>.</p>
            <a href='index.php' class='btn btn-primary'>Kembali ke Beranda</a>
          </div>";
    exit;
}

// =====================================================
// 3. AMBIL DATA USER YANG LOGIN
// =====================================================
// Username bisa berisi name atau email, jadi kita cek keduanya
$username = $_SESSION['username'];

$stmtUser = $mysqli->prepare("SELECT id, name FROM users WHERE name = ? OR email = ?");
$stmtUser->bind_param('ss', $username, $username);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

// Jika user tidak ditemukan, logout paksa
if ($resultUser->num_rows === 0) {
    session_destroy();
    header('Location: login/login.php');
    exit;
}

$userData   = $resultUser->fetch_assoc();
$buyerId    = $userData['id'];
$buyerName  = $userData['name'];
$stmtUser->close();

// =====================================================
// 4. AMBIL SEMUA PESANAN MILIK BUYER
// =====================================================
// Kita JOIN dengan tabel payments (LEFT JOIN) karena
// pembayaran bisa saja belum ada / belum diupload.
$queryOrders = "
    SELECT 
        o.id              AS order_id,
        o.total_amount,
        o.status          AS order_status,
        o.created_at,
        p.payment_method,
        p.proof,
        p.status          AS payment_status
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.buyer_id = ?
    ORDER BY o.created_at DESC
";

$stmtOrders = $mysqli->prepare($queryOrders);
$stmtOrders->bind_param('i', $buyerId);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();

// Simpan seluruh pesanan ke array agar bisa di-loop di HTML
$orders = [];
while ($row = $resultOrders->fetch_assoc()) {
    $orders[] = $row;
}
$stmtOrders->close();

// =====================================================
// 5. FUNGSI BANTU: BADGE STATUS PESANAN
// =====================================================
// Mengembalikan kelas badge Bootstrap sesuai status pesanan
function badgeStatusPesanan($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'diproses':
            return 'bg-primary';
        case 'dikirim':
            return 'bg-info text-dark';
        case 'selesai':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}

// =====================================================
// 6. FUNGSI BANTU: BADGE STATUS PEMBAYARAN
// =====================================================
function badgeStatusPembayaran($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'verified':
            return 'bg-success';
        case 'rejected':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// =====================================================
// 7. FUNGSI BANTU: FORMAT RUPIAH & TANGGAL
// =====================================================
function formatRupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function formatTanggal($tanggal)
{
    $ts = strtotime($tanggal);
    // Format: 12 Mei 2025, 14.30
    return date('d M Y, H.i', $ts);
}

// =====================================================
// 8. AMBIL DATA DETAIL UNTUK MODAL (JIKA ?detail=ID)
// =====================================================
// Saat user klik "Lihat Detail", kita buka modal yang
// menampilkan item-item produk dalam pesanan tersebut.
$detailOrderId = isset($_GET['detail']) ? (int)$_GET['detail'] : 0;
$detailData    = null;     // Menyimpan info header pesanan
$detailItems   = [];       // Menyimpan daftar item produk
$detailPayment = null;     // Menyimpan info pembayaran + bukti

if ($detailOrderId > 0) {

    // --- 8a. Ambil header pesanan (pastikan milik buyer yang login) ---
    $stmtDetail = $mysqli->prepare("
        SELECT id, total_amount, status, created_at
        FROM orders
        WHERE id = ? AND buyer_id = ?
        LIMIT 1
    ");
    $stmtDetail->bind_param('ii', $detailOrderId, $buyerId);
    $stmtDetail->execute();
    $resDetail = $stmtDetail->get_result();

    if ($resDetail->num_rows > 0) {
        $detailData = $resDetail->fetch_assoc();

        // --- 8b. Ambil item produk beserta info produk ---
        $stmtItems = $mysqli->prepare("
            SELECT 
                oi.quantity,
                oi.price,
                p.id       AS product_id,
                p.name     AS product_name,
                p.image    AS product_image
            FROM order_items oi
            INNER JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $stmtItems->bind_param('i', $detailOrderId);
        $stmtItems->execute();
        $resItems = $stmtItems->get_result();

        while ($item = $resItems->fetch_assoc()) {
            $detailItems[] = $item;
        }
        $stmtItems->close();

        // --- 8c. Ambil info pembayaran + bukti transfer ---
        $stmtPay = $mysqli->prepare("
            SELECT payment_method, proof, status
            FROM payments
            WHERE order_id = ?
            LIMIT 1
        ");
        $stmtPay->bind_param('i', $detailOrderId);
        $stmtPay->execute();
        $resPay = $stmtPay->get_result();

        if ($resPay->num_rows > 0) {
            $detailPayment = $resPay->fetch_assoc();
        }
        $stmtPay->close();
    }
    $stmtDetail->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Toko Online</title>

    <!-- =================================================
         BOOTSTRAP 5 CSS + BOOTSTRAP ICONS
         ================================================= -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ====== STYLE CUSTOM: KONSISTEN DENGAN checkout.php ====== */
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
        }

        body {
            background-color: #f4f6fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Header gradient sesuai warna utama */
        .page-header {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            padding: 40px 0 60px;
            border-radius: 0 0 24px 24px;
        }

        /* Kartu pesanan */
        .order-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Tombol utama */
        .btn-indigo {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
        }

        .btn-indigo:hover {
            background-color: var(--primary-dark);
            color: #fff;
        }

        .btn-outline-indigo {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background: #fff;
        }

        .btn-outline-indigo:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        /* Gambar produk di modal */
        .product-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        /* Banner bukti transfer di modal */
        .proof-image {
            max-width: 100%;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        /* Empty state */
        .empty-state i {
            font-size: 4rem;
            color: #c7d2fe;
        }
    </style>
</head>

<body>

    <!-- =====================================================
     HEADER HALAMAN
     ===================================================== -->
    <section class="page-header">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-bag-check-fill me-2"></i>Pesanan Saya
                    </h2>
                    <p class="mb-0 opacity-75">
                        Halo, <strong><?= htmlspecialchars($buyerName); ?></strong> — berikut daftar pesanan kamu.
                    </p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="index.php" class="btn btn-light me-2 mb-2 mb-md-0">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                    <a href="produk-user.php" class="btn btn-outline-light">
                        <i class="bi bi-bag me-1"></i> Belanja Lagi
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================
     KONTEN UTAMA
     ===================================================== -->
    <div class="container" style="margin-top:-30px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <?php if (empty($orders)): ?>
                    <!-- ============ EMPTY STATE: BELUM ADA PESANAN ============ -->
                    <div class="card order-card text-center py-5">
                        <div class="card-body empty-state">
                            <i class="bi bi-box-seam"></i>
                            <h4 class="mt-3 fw-bold">Belum ada pesanan</h4>
                            <p class="text-muted">Kamu belum pernah melakukan pesanan. Yuk mulai belanja sekarang!</p>
                            <a href="produk-user.php" class="btn btn-indigo px-4 mt-2">
                                <i class="bi bi-cart-plus me-1"></i> Mulai Belanja
                            </a>
                        </div>
                    </div>
                <?php else: ?>

                    <!-- ============ HEADER JUMLAH PESANAN ============ -->
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0 me-2">Total Pesanan:</h5>
                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            <?= count($orders); ?> pesanan
                        </span>
                    </div>

                    <!-- ============ LOOPING DAFTAR PESANAN ============ -->
                    <?php foreach ($orders as $order): ?>
                        <?php
                        // Siapkan variabel tampilan agar lebih ringkas di HTML
                        $orderId         = $order['order_id'];
                        $totalAmount     = $order['total_amount'];
                        $orderStatus     = $order['order_status'];
                        $createdAt       = $order['created_at'];
                        $paymentMethod   = $order['payment_method'];
                        $proof           = $order['proof'];
                        $paymentStatus   = $order['payment_status'];

                        // Tentukan teks metode pembayaran (jika kosong = belum ada)
                        $metodeText = $paymentMethod
                            ? htmlspecialchars($paymentMethod)
                            : '<span class="text-muted fst-italic">Belum ada</span>';

                        // Tentukan teks status pembayaran
                        $payStatusText = $paymentStatus
                            ? ucfirst($paymentStatus)
                            : 'Belum ada';

                        // Tentukan kelas badge
                        $badgeOrder = badgeStatusPesanan($orderStatus);
                        $badgePay   = badgeStatusPembayaran($paymentStatus);
                        ?>

                        <div class="card order-card mb-3">
                            <div class="card-body p-3 p-md-4">

                                <!-- BARIS 1: ID PESANAN + STATUS -->
                                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="text-muted small">ID Pesanan</span>
                                        <h5 class="mb-0 fw-bold text-dark">#<?= $orderId; ?></h5>
                                    </div>
                                    <div class="text-md-end mt-2 mt-md-0">
                                        <span class="text-muted small d-block">Status Pesanan</span>
                                        <span class="badge <?= $badgeOrder; ?> rounded-pill px-3 py-2">
                                            <?= htmlspecialchars(ucfirst($orderStatus)); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- BARIS 2: INFO TAMBAHAN -->
                                <div class="row g-3">
                                    <div class="col-6 col-md-4">
                                        <div class="bg-light rounded-3 p-3 h-100">
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-calendar3 me-1"></i>Tanggal Pesanan
                                            </div>
                                            <div class="fw-semibold">
                                                <?= formatTanggal($createdAt); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="bg-light rounded-3 p-3 h-100">
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-credit-card me-1"></i>Metode Pembayaran
                                            </div>
                                            <div class="fw-semibold">
                                                <?= $metodeText; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="bg-light rounded-3 p-3 h-100">
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-cash-stack me-1"></i>Total Belanja
                                            </div>
                                            <div class="fw-bold text-primary">
                                                <?= formatRupiah($totalAmount); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BARIS 3: STATUS PEMBAYARAN + TOMBOL DETAIL -->
                                <hr class="my-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small me-2">Status Pembayaran:</span>
                                        <span class="badge <?= $badgePay; ?> rounded-pill px-3 py-2">
                                            <?= htmlspecialchars($payStatusText); ?>
                                        </span>
                                    </div>

                                    <!-- Tombol Lihat Detail membuka modal via ?detail=ID -->
                                    <a href="?detail=<?= $orderId; ?>" class="btn btn-indigo btn-sm mt-2 mt-md-0">
                                        <i class="bi bi-eye me-1"></i> Lihat Detail
                                    </a>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- =====================================================
     MODAL DETAIL PESANAN
     Modal ini hanya dirender ketika ?detail=ID ada
     dan data pesanan ditemukan.
     ===================================================== -->
    <?php if ($detailData !== null): ?>
        <div class="modal fade show" id="modalDetail" tabindex="-1"
            aria-modal="true" role="dialog"
            style="display:block; background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">

                    <!-- MODAL HEADER -->
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="bi bi-receipt me-2"></i>
                            Detail Pesanan #<?= $detailData['id']; ?>
                        </h5>
                        <a href="pesanan-saya.php" class="btn-close btn-close-white" aria-label="Close"></a>
                    </div>

                    <!-- MODAL BODY -->
                    <div class="modal-body p-4">

                        <!-- INFO HEADER PESANAN -->
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Tanggal</div>
                                <div class="fw-semibold">
                                    <?= formatTanggal($detailData['created_at']); ?>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Status Pesanan</div>
                                <span class="badge <?= badgeStatusPesanan($detailData['status']); ?> rounded-pill px-3 py-2">
                                    <?= htmlspecialchars(ucfirst($detailData['status'])); ?>
                                </span>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="text-muted small">Total Belanja</div>
                                <div class="fw-bold text-primary">
                                    <?= formatRupiah($detailData['total_amount']); ?>
                                </div>
                            </div>
                        </div>

                        <!-- INFO PEMBAYARAN + BUKTI TRANSFER -->
                        <div class="card bg-light border-0 rounded-3 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-credit-card-2-front me-1 text-primary"></i>
                                    Informasi Pembayaran
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small">Metode</div>
                                        <div class="fw-semibold">
                                            <?= $detailPayment
                                                ? htmlspecialchars(ucfirst($detailPayment['payment_method']))
                                                : '<span class="text-muted fst-italic">Belum ada</span>'; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Status</div>
                                        <span class="badge <?= $detailPayment
                                                                ? badgeStatusPembayaran($detailPayment['status'])
                                                                : 'bg-secondary'; ?> rounded-pill px-3 py-2">
                                            <?= $detailPayment
                                                ? htmlspecialchars(ucfirst($detailPayment['status']))
                                                : 'Belum ada'; ?>
                                        </span>
                                    </div>

                                    <!-- TAMPILKAN BUKTI TRANSFER JIKA ADA -->
                                    <?php if ($detailPayment && !empty($detailPayment['proof'])): ?>
                                        <div class="col-12 mt-2">
                                            <div class="text-muted small mb-2">Bukti Transfer:</div>
                                            <img src="image/bukti/<?= htmlspecialchars($detailPayment['proof']); ?>"
                                                alt="Bukti Transfer"
                                                class="proof-image"
                                                style="max-height:300px;">
                                        </div>
                                    <?php else: ?>
                                        <div class="col-12 mt-2">
                                            <div class="text-muted fst-italic small">
                                                <i class="bi bi-image me-1"></i> Metode COD tidak memerlukan bukti pembayaran.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- DAFTAR ITEM PRODUK DALAM PESANAN -->
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-box-seam me-1 text-primary"></i>
                            Produk yang Dipesan
                        </h6>

                        <?php if (empty($detailItems)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Tidak ada item produk pada pesanan ini.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Variabel untuk memastikan total item sama dengan total_amount
                                        $grandTotalItem = 0;

                                        foreach ($detailItems as $item):
                                            $qty       = (int)$item['quantity'];
                                            $price     = (float)$item['price'];
                                            $subtotal  = $qty * $price;
                                            $grandTotalItem += $subtotal;

                                            // Cek file gambar produk, jika tidak ada gunakan placeholder
                                            $imagePath = 'image/produk/' . $item['product_image'];
                                            if (empty($item['product_image']) || !file_exists($imagePath)) {
                                                $imagePath = 'image/produk/no-image.png'; // Placeholder jika gambar tidak ada
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= htmlspecialchars($imagePath); ?>"
                                                            alt="<?= htmlspecialchars($item['product_name']); ?>"
                                                            class="product-thumb me-3">
                                                        <div>
                                                            <div class="fw-semibold">
                                                                <?= htmlspecialchars($item['product_name']); ?>
                                                            </div>
                                                            <small class="text-muted">
                                                                ID Produk: <?= (int)$item['product_id']; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?= $qty; ?></td>
                                                <td class="text-end"><?= formatRupiah($price); ?></td>
                                                <td class="text-end fw-semibold">
                                                    <?= formatRupiah($subtotal); ?>
                                                    <?php if ($detailData['status'] == 'selesai'): ?>
                                                        <br><br>
                                                        <a href="review.php?product_id=<?= $item['product_id']; ?>"
                                                            class="btn btn-primary btn-sm"
                                                            target="_blank">

                                                            REVIEW

                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total</th>
                                            <th class="text-end text-primary">
                                                <?= formatRupiah($grandTotalItem); ?>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- MODAL FOOTER -->
                    <div class="modal-footer">
                        <a href="pesanan-saya.php" class="btn btn-outline-indigo">
                            <i class="bi bi-arrow-left me-1"></i> Tutup
                        </a>
                        <a href="invoice.php?order_id=<?= $detailData['id']; ?>"
                            class="btn btn-success"
                            target="_blank">

                            <i class="bi bi-file-earmark-pdf"></i>
                            Invoice
                        </a>
                        <a href="produk-user.php" class="btn btn-indigo">
                            <i class="bi bi-bag me-1"></i> Belanja Lagi
                        </a>
                    </div>

                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- =====================================================
     FOOTER BAWAH
     ===================================================== -->
    <footer class="text-center text-muted py-4 mt-5">
        <small>&copy; <?= date('Y'); ?> Toko Online — PHP Native + MySQL (mysqli OOP).</small>
    </footer>

    <!-- =====================================================
     BOOTSTRAP 5 JS BUNDLE
     ===================================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    /*
=====================================================================
  PENJELASAN ALUR FILE INI (UNTUK MAHASISWA)
=====================================================================
1. Session dicek: hanya buyer yang sudah login yang boleh akses.
2. Data user diambil dari tabel users berdasarkan name/email.
3. Semua pesanan milik buyer diambil dari tabel orders dengan
   LEFT JOIN payments (karena payment belum tentu ada).
4. Setiap pesanan ditampilkan dalam card Bootstrap dengan info
   lengkap: ID, tanggal, total, status, metode & status pembayaran.
5. Tombol "Lihat Detail" mengarah ke ?detail=ID. Saat parameter
   detail tersedia, modal otomatis ditampilkan berisi:
     - Header info pesanan
     - Info pembayaran + bukti transfer (jika ada)
     - Tabel item produk: foto, nama, qty, harga, subtotal
6. File ini menggunakan mysqli OOP (prepare, bind_param, execute,
   get_result, fetch_assoc) — bukan PDO.
7. Warna utama #4f46e5 diterapkan konsisten seperti checkout.php.
=====================================================================
*/
    ?>
</body>

</html>
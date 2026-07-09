<?php
// =============================================================
// checkout.php
// Halaman checkout untuk buyer melakukan pemesanan
// Project: Toko Online PHP Native + MySQL (mysqli OOP)
// =============================================================

// Mulai session untuk mengakses data login user
session_start();

// Panggil file koneksi database (menghasilkan object $mysqli)
require 'koneksi.php';

// =============================================================
// 1. VALIDASI LOGIN DAN ROLE BUYER
// =============================================================
// Cek apakah user sudah login (session 'login' harus true)
// dan role-nya harus 'buyer'. Jika tidak, redirect ke login.
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login/login.php");
    exit;
}

// Hanya buyer yang boleh akses halaman checkout
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    // Jika seller/admin mencoba akses, redirect ke dashboard masing-masing
    header("Location: login/login.php");
    exit;
}

// =============================================================
// 2. CARI USER_ID BERDASARKAN USERNAME DI SESSION
// =============================================================
$username = $_SESSION['username'];

// Query untuk mendapatkan id user berdasarkan username (yang biasanya = email)
$stmtUser = $mysqli->prepare("SELECT id, name FROM users WHERE name = ? OR email = ? LIMIT 1");
$stmtUser->bind_param("ss", $username, $username);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

// Jika user tidak ditemukan, redirect ke login
if ($resultUser->num_rows === 0) {
    header("Location: login/login.php");
    exit;
}

$userData   = $resultUser->fetch_assoc();
$user_id    = $userData['id'];
$user_name  = $userData['name'];
$stmtUser->close();

// =============================================================
// 3. AMBIL SEMUA ITEM CART MILIK USER (JOIN KE TABEL PRODUCTS)
// =============================================================
// Ambil data cart milik user, join dengan tabel products
// untuk mendapatkan nama produk, harga, gambar, dan stok
$queryCart = "SELECT 
                c.id        AS cart_id,
                c.product_id,
                c.qty,
                p.name      AS product_name,
                p.price,
                p.image,
                p.stock
              FROM cart c
              INNER JOIN products p ON c.product_id = p.id
              WHERE c.user_id = ?
              ORDER BY c.created_at DESC";

$stmtCart = $mysqli->prepare($queryCart);
$stmtCart->bind_param("i", $user_id);
$stmtCart->execute();
$resultCart = $stmtCart->get_result();

// Simpan semua item cart ke dalam array agar bisa di-loop di view
$cartItems = [];
while ($row = $resultCart->fetch_assoc()) {
    $cartItems[] = $row;
}
$stmtCart->close();

// =============================================================
// 4. VALIDASI: JIKA CART KOSONG, TIDAK BISA CHECKOUT
// =============================================================
if (count($cartItems) === 0) {
    // Redirect ke halaman keranjang dengan pesan
    header("Location: keranjang.php?empty=1");
    exit;
}

// =============================================================
// 5. HITUNG TOTAL BELANJA
// =============================================================
$total_amount = 0;

foreach ($cartItems as $item) {
    $subtotal     = $item['price'] * $item['qty'];
    $total_amount += $subtotal;
}

// Ongkir sementara (default)
$ongkir = 10000;

// Total akhir
$grand_total = $total_amount + $ongkir;

// =============================================================
// 8-12. PROSES FORM SUBMIT (BUAT PESANAN)
// =============================================================
// Variabel untuk menyimpan pesan error
$error = "";

// Cek apakah form disubmit via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data dari form
    $alamat         = trim($_POST['alamat'] ?? '');
    $metode_bayar   = trim($_POST['metode_pembayaran'] ?? '');

    // Validasi input
    if ($alamat === '' || $metode_bayar === '') {
        $error = "Alamat pengiriman dan metode pembayaran wajib diisi.";
    } else {

        // Mulai transaction untuk menjaga konsistensi data
        $mysqli->begin_transaction();

        try {

            // ---------------------------------------------------------
            // 8. INSERT KE TABEL orders (status default: pending)
            // ---------------------------------------------------------
            $status_default = 'pending';
            $ongkir = 10000;

            $stmtOrder = $mysqli->prepare("
                INSERT INTO orders (
                    buyer_id,
                    total_amount,
                    shipping_address,
                    payment_method,
                    shipping_cost,
                    status,
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, NOW())
              ");

            $stmtOrder->bind_param(
                "idssis",
                $user_id,
                $grand_total,
                $alamat,
                $metode_bayar,
                $ongkir,
                $status_default
            );
            $stmtOrder->execute();

            // ---------------------------------------------------------
            // 9. AMBIL ID ORDER TERAKHIR MENGGUNAKAN insert_id
            // ---------------------------------------------------------
            $order_id = $mysqli->insert_id;
            $stmtOrder->close();

            // ---------------------------------------------------------
            // 10. LOOP SEMUA CART USER LALU INSERT KE order_items
            // ---------------------------------------------------------
            $stmtItem = $mysqli->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $product_id = $item['product_id'];
                $qty        = $item['qty'];
                $price      = $item['price'];

                $stmtItem->bind_param("iiii", $order_id, $product_id, $qty, $price);
                $stmtItem->execute();

                // (Opsional) Update stok produk: kurangi stok sesuai qty
                $stmtUpdateStock = $mysqli->prepare("
                    UPDATE products SET stock = stock - ? WHERE id = ?
                ");
                $stmtUpdateStock->bind_param("ii", $qty, $product_id);
                $stmtUpdateStock->execute();
                $stmtUpdateStock->close();
            }
            $stmtItem->close();

            // ---------------------------------------------------------
            // 11. HAPUS SEMUA ITEM CART MILIK USER SETELAH BERHASIL
            // ---------------------------------------------------------
            $stmtDelete = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmtDelete->bind_param("i", $user_id);
            $stmtDelete->execute();
            $stmtDelete->close();

            // Commit transaction jika semua proses berhasil
            $mysqli->commit();

            // ---------------------------------------------------------
            // 12. REDIRECT KE HALAMAN KONFIRMASI PEMBAYARAN
            // ---------------------------------------------------------
            header("Location: konfirmasi-pembayaran.php?order_id=" . $order_id . "&metode=" . urlencode($metode_bayar));
            exit;
        } catch (Exception $e) {
            // Rollback jika ada error
            $mysqli->rollback();
            $error = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Online</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .navbar-brand {
            font-weight: 700;
            color: #4f46e5 !important;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
            font-weight: 500;
            padding: 12px 30px;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }

        .total-box {
            background-color: #4f46e5;
            color: white;
            border-radius: 12px;
            padding: 20px;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #4f46e5;
            border-radius: 3px;
        }

        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }

        .breadcrumb-custom a {
            text-decoration: none;
            color: #6b7280;
        }

        .breadcrumb-custom a:hover {
            color: #4f46e5;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px;
            border-color: #e5e7eb;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }
    </style>
</head>

<body>

    <!-- ===================== NAVBAR ===================== -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-bag-check-fill me-2"></i>TokoOnline
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="keranjang.php">
                            <i class="bi bi-cart3"></i> Keranjang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesanan-saya.php">Pesanan</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="login/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="container py-5">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                <li class="breadcrumb-item"><a href="keranjang.php">Keranjang</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-credit-card-2-front fs-2 text-primary me-3"></i>
            <div>
                <h3 class="mb-0 fw-bold">Checkout</h3>
                <p class="text-muted mb-0">Selesaikan pesanan Anda</p>
            </div>
        </div>

        <!-- Alert Error -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <!-- Form Checkout -->
        <form action="" method="POST">
            <div class="row g-4">

                <!-- ============ KOLOM KIRI: FORM PENGIRIMAN ============ -->
                <div class="col-lg-7">

                    <!-- Card: Alamat Pengiriman -->
                    <div class="card p-4 mb-4">
                        <h5 class="section-title">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>Alamat Pengiriman
                        </h5>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Penerima</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user_name); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="4"
                                placeholder="Masukkan alamat lengkap (jalan, kota, kode pos, dll)..." required></textarea>
                        </div>
                    </div>

                    <!-- Card: Metode Pembayaran -->
                    <div class="card p-4">
                        <h5 class="section-title">
                            <i class="bi bi-wallet2 text-primary me-2"></i>Metode Pembayaran
                        </h5>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Pilih Metode <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran" class="form-select" required>
                                <option value="">-- Pilih Metode Pembayaran --</option>
                                <option value="Transfer Bank">
                                    <i class="bi bi-bank"></i> Transfer Bank (BCA, BNI, Mandiri)
                                </option>
                                <option value="COD">COD (Cash On Delivery)</option>
                                <option value="E-Wallet">E-Wallet (OVO, GoPay, DANA)</option>
                            </select>
                        </div>

                        <!-- Info metode pembayaran -->
                        <div class="alert alert-info mt-3 mb-0" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Detail instruksi pembayaran akan ditampilkan setelah pesanan dibuat.
                        </div>
                    </div>
                </div>

                <!-- ============ KOLOM KANAN: RINGKASAN PESANAN ============ -->
                <div class="col-lg-5">

                    <!-- Card: Daftar Produk -->
                    <div class="card p-4 mb-4">
                        <h5 class="section-title">
                            <i class="bi bi-bag text-primary me-2"></i>Produk Dipesan
                        </h5>

                        <!-- Tabel produk yang akan dibeli -->
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Produk</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <?php $subtotal = $item['price'] * $item['qty']; ?>
                                        <tr>
                                            <!-- Info produk (gambar + nama) -->
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="image/produk/<?= htmlspecialchars($item['image']); ?>"
                                                        alt="product" class="product-img"
                                                        onerror="this.src='https://via.placeholder.com/60'">
                                                    <div>
                                                        <div class="fw-medium small">
                                                            <?= htmlspecialchars($item['product_name']); ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            Stok: <?= (int)$item['stock']; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Quantity -->
                                            <td class="text-center fw-medium">
                                                <?= (int)$item['qty']; ?>
                                            </td>
                                            <!-- Harga satuan -->
                                            <td class="text-end">
                                                Rp <?= number_format($item['price'], 0, ',', '.'); ?>
                                            </td>
                                            <!-- Subtotal -->
                                            <td class="text-end fw-medium">
                                                Rp <?= number_format($subtotal, 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Card: Total Belanja -->
                    <div class="card p-4 mb-4">
                        <h5 class="section-title">
                            <i class="bi bi-receipt text-primary me-2"></i>Ringkasan Belanja
                        </h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Item</span>
                            <span class="fw-medium"><?= count($cartItems); ?> produk</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-medium">Rp <?= number_format($total_amount, 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Ongkir</span>
                            <span class="fw-medium">
                                Rp <?= number_format($ongkir, 0, ',', '.'); ?>
                            </span>
                        </div>
                        <hr>
                        <!-- Total belanja -->
                        <div class="total-box d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small>Total Belanja</small>
                                <h4 class="mb-0 fw-bold">
                                    Rp <?= number_format($grand_total, 0, ',', '.'); ?>
                                </h4>
                            </div>
                            <i class="bi bi-bag-check fs-1"></i>
                        </div>
                    </div>

                    <!-- Tombol Buat Pesanan -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check2-circle me-2"></i>Buat Pesanan
                        </button>
                    </div>

                    <!-- Link kembali ke keranjang -->
                    <div class="text-center mt-3">
                        <a href="keranjang.php" class="text-decoration-none text-muted">
                            <i class="bi bi-arrow-left"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- ===================== FOOTER ===================== -->
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="text-muted mb-0">
                &copy; <?= date('Y'); ?> TokoOnline. Dibuat dengan
                <i class="bi bi-heart-fill text-danger"></i> menggunakan PHP Native + MySQL
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
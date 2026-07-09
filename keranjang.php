<?php
session_start();

// ===========================================
// 1. CEK LOGIN & ROLE BUYER
// ===========================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    // Jika bukan buyer, arahkan sesuai role-nya
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

require 'koneksi.php';

// ===========================================
// 2. CARI USER_ID BERDASARKAN SESSION USERNAME
// ===========================================
$username = $_SESSION['username'];
$stmtUser = $mysqli->prepare("SELECT id FROM users WHERE name = ? OR email = ? LIMIT 1");
$stmtUser->bind_param('ss', $username, $username);
$stmtUser->execute();
$resUser = $stmtUser->get_result();

if ($resUser->num_rows === 0) {
    die('User tidak ditemukan. Silakan logout lalu login kembali.');
}

$dataUser = $resUser->fetch_assoc();
$user_id = $dataUser['id'];

// ===========================================
// SAFETY: HAPUS OTOMATIS ITEM DENGAN QTY <= 0
// (Mengantisipasi jika qty menjadi 0)
// ===========================================
$mysqli->query("DELETE FROM cart WHERE user_id = $user_id AND qty <= 0");

// ===========================================
// 3. AMBIL DATA KERANJANG (JOIN cart + products)
// ===========================================
$queryCart = "SELECT
cart.id AS cart_id,
cart.qty,
products.id AS product_id,
products.name,
products.price,
products.image,
products.stock
FROM cart
JOIN products ON cart.product_id = products.id
WHERE cart.user_id = ?
ORDER BY cart.created_at DESC";

$stmtCart = $mysqli->prepare($queryCart);
$stmtCart->bind_param('i', $user_id);
$stmtCart->execute();
$resultCart = $stmtCart->get_result();

$totalBelanja = 0;
$no = 1;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - AquaGas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        :root {
            --aq-bg: #f5fbff;
            --aq-primary: #4da6ff;
            --aq-primary-dark: #2b8fd9;
            --aq-primary-light: #e0f0ff;
            --aq-baby-blue: #7ec8f8;
            --aq-baby-light: #d6eeff;
            --aq-card: #ffffff;
            --aq-text: #1e3a5f;
            --aq-text-muted: #7a9bbd;
            --aq-green: #5ec269;
            --aq-green-light: #e8f8ea;
            --aq-yellow: #f5c542;
            --aq-yellow-light: #fef9e7;
            --aq-red-pastel: #f28b8b;
            --aq-red-light: #fde8e8;
            --aq-shadow: 0 8px 32px rgba(77, 166, 255, 0.10);
            --aq-shadow-hover: 0 12px 40px rgba(77, 166, 255, 0.18);
            --aq-radius: 25px;
        }

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--aq-bg);
            min-height: 100vh;
            color: var(--aq-text);
        }

        .aq-card {
            background: var(--aq-card);
            border: none;
            border-radius: var(--aq-radius);
            box-shadow: var(--aq-shadow);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .aq-card:hover {
            box-shadow: var(--aq-shadow-hover);
        }

        .product-img {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--aq-baby-light);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .product-img:hover {
            transform: scale(1.1);
            border-color: var(--aq-primary);
        }

        .table thead tr {
            background: linear-gradient(135deg, #a8d8f5 0%, #7ec8f8 50%, #a8d8f5 100%);
            color: #1e3a5f;
            text-transform: uppercase;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-radius: 25px 25px 0 0;
        }

        .table thead th {
            border-bottom: none;
            padding: 16px 12px;
        }

        .table thead th:first-child {
            border-top-left-radius: 25px;
        }

        .table thead th:last-child {
            border-top-right-radius: 25px;
        }

        .table tbody tr {
            transition: background 0.25s ease;
        }

        .table tbody tr:hover {
            background-color: var(--aq-primary-light) !important;
        }

        .table td {
            vertical-align: middle;
            padding: 18px 12px;
            border-color: var(--aq-baby-light);
        }

        .btn-action {
            width: 38px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin: 0 3px;
            font-size: 1rem;
            transition: all 0.25s ease;
            border: none;
            text-decoration: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-tambah {
            background: var(--aq-green-light);
            color: var(--aq-green);
        }

        .btn-tambah:hover {
            background: var(--aq-green);
            color: #fff;
        }

        .btn-kurang {
            background: var(--aq-yellow-light);
            color: #c9960a;
        }

        .btn-kurang:hover {
            background: var(--aq-yellow);
            color: #fff;
        }

        .btn-hapus {
            background: var(--aq-red-light);
            color: var(--aq-red-pastel);
        }

        .btn-hapus:hover {
            background: var(--aq-red-pastel);
            color: #fff;
        }

        .qty-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--aq-primary-light), var(--aq-baby-light));
            color: var(--aq-primary-dark);
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 14px;
            padding: 6px 18px;
            min-width: 50px;
            border: 2px solid var(--aq-baby-light);
        }

        .total-card {
            background: var(--aq-card);
            border-radius: var(--aq-radius);
            box-shadow: var(--aq-shadow);
            padding: 28px 32px;
            border: 2px solid var(--aq-baby-light);
            position: relative;
            overflow: hidden;
        }

        .total-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--aq-baby-blue), var(--aq-primary), var(--aq-baby-blue));
            border-radius: 25px 25px 0 0;
        }

        .total-nominal {
            color: var(--aq-primary-dark);
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .btn-aq-primary {
            background: linear-gradient(135deg, var(--aq-primary), var(--aq-primary-dark));
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 12px 32px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-aq-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(77, 166, 255, 0.35);
            color: #fff;
        }

        .btn-aq-outline {
            background: var(--aq-card);
            color: var(--aq-primary-dark);
            border: 2px solid var(--aq-baby-blue);
            border-radius: 16px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-aq-outline:hover {
            background: var(--aq-primary-light);
            border-color: var(--aq-primary);
            color: var(--aq-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(77, 166, 255, 0.15);
        }

        .empty-icon {
            font-size: 7rem;
            filter: grayscale(0.3);
            opacity: 0.6;
        }

        .aq-footer {
            background: linear-gradient(135deg, #1e3a5f 0%, #2b5278 100%);
            color: rgba(255, 255, 255, 0.85);
            padding: 20px 0;
            margin-top: 60px;
            font-size: 0.9rem;
        }

        .page-header {
            background: var(--aq-card);
            border-radius: var(--aq-radius);
            box-shadow: var(--aq-shadow);
            padding: 28px 32px;
            margin-bottom: 28px;
            border-left: 5px solid var(--aq-primary);
        }

        .page-header h3 {
            font-weight: 800;
            color: var(--aq-text);
            font-size: 1.5rem;
        }

        .page-header p {
            color: var(--aq-text-muted);
            font-size: 0.92rem;
            margin-bottom: 0;
        }

        .subtotal-text {
            color: var(--aq-primary-dark);
            font-weight: 700;
        }

        .price-text {
            color: var(--aq-text);
            font-weight: 600;
        }

        .stock-text {
            color: var(--aq-text-muted);
            font-size: 0.82rem;
        }

        .no-text {
            color: var(--aq-text-muted);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px 20px;
            }

            .page-header h3 {
                font-size: 1.2rem;
            }

            .total-card {
                padding: 20px 20px;
            }

            .total-nominal {
                font-size: 1.3rem;
            }

            .btn-aq-primary {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .btn-aq-outline {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>

    <?php require "navbar.php"; ?>

    <div class="container mb-5" style="margin-top: 28px;">

        <!-- HEADER -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h3 class="mb-1">🛒 Keranjang Belanja AquaGas</h3>
                    <p>Periksa kembali produk pilihan Anda sebelum melanjutkan ke proses checkout.</p>
                </div>
                <a href="produk-user.php" class="btn-aq-outline">
                    <i class="bi bi-arrow-left"></i> Lanjut Belanja
                </a>
            </div>
        </div>

        <?php if ($resultCart->num_rows === 0): ?>
            <!-- ========== KERANJANG KOSONG ========== -->
            <div class="aq-card text-center p-5">
                <div class="card-body py-5">
                    <div class="empty-icon mb-3">🛒</div>
                    <h3 class="fw-bold mt-2 mb-2" style="color: var(--aq-text);">Keranjang Anda Masih Kosong</h3>
                    <p class="mb-4" style="color: var(--aq-text-muted); max-width: 400px; margin-left: auto; margin-right: auto;">
                        Belum ada produk di keranjang. Yuk mulai belanja sekarang!
                    </p>
                    <a href="produk-user.php" class="btn-aq-primary btn-lg">
                        <i class="bi bi-bag-plus"></i> Mulai Belanja
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- ========== TABEL KERANJANG ========== -->
            <div class="aq-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr class="text-center">
                                    <th class="ps-4">No</th>
                                    <th>Gambar</th>
                                    <th class="text-start">Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultCart->fetch_assoc()):
                                    // 5. RUMUS SUBTOTAL = HARGA × QTY
                                    $subtotal = $row['price'] * $row['qty'];
                                    // 6. AKUMULASI TOTAL BELANJA
                                    $totalBelanja += $subtotal;

                                    // 12. CEK GAMBAR PRODUK + PLACEHOLDER
                                    $imagePath = 'image/produk/' . $row['image'];
                                    if (empty($row['image']) || !file_exists($imagePath)) {
                                        $imagePath = 'https://via.placeholder.com/70x70?text=No+Image';
                                    }
                                ?>
                                    <tr class="text-center">
                                        <td class="ps-4 no-text"><?= $no++; ?></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($imagePath) ?>"
                                                alt="<?= htmlspecialchars($row['name']) ?>"
                                                class="product-img"
                                                onerror="this.src='https://via.placeholder.com/70x70?text=No+Image'">
                                        </td>
                                        <td class="text-start">
                                            <div class="fw-semibold" style="color: var(--aq-text);"><?= htmlspecialchars($row['name']); ?></div>
                                            <small class="stock-text">Stok: <?= (int)$row['stock']; ?></small>
                                        </td>
                                        <td class="price-text">Rp <?= number_format($row['price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="qty-badge"><?= (int)$row['qty']; ?></span>
                                        </td>
                                        <td class="subtotal-text">
                                            Rp <?= number_format($subtotal, 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <!-- 7. TOMBOL TAMBAH QTY -->
                                                <a href="update-qty.php?id=<?= (int)$row['cart_id']; ?>&aksi=tambah"
                                                    class="btn-action btn-tambah"
                                                    title="Tambah Qty">
                                                    <i class="bi bi-plus-lg"></i>
                                                </a>
                                                <!-- 7. TOMBOL KURANG QTY -->
                                                <a href="update-qty.php?id=<?= (int)$row['cart_id']; ?>&aksi=kurang"
                                                    class="btn-action btn-kurang"
                                                    title="Kurangi Qty"
                                                    onclick="return confirm('Kurangi jumlah item ini?')">
                                                    <i class="bi bi-dash-lg"></i>
                                                </a>
                                                <!-- 7. TOMBOL HAPUS -->
                                                <a href="hapus-keranjang.php?id=<?= (int)$row['cart_id']; ?>"
                                                    class="btn-action btn-hapus"
                                                    title="Hapus dari Keranjang"
                                                    onclick="return confirm('Yakin ingin menghapus item ini dari keranjang?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========== TOTAL BELANJA & TOMBOL AKSI ========== -->
            <div class="row mt-4 g-3">
                <div class="col-md-6 d-flex align-items-center">
                    <!-- 10. TOMBOL LANJUT BELANJA -->
                    <a href="produk-user.php" class="btn-aq-outline">
                        <i class="bi bi-arrow-left-circle"></i> Lanjut Belanja
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="total-card d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <p class="mb-1" style="color: var(--aq-text-muted); font-weight: 600; font-size: 0.9rem;">Total Belanja</p>
                            <!-- 9. TAMPILKAN TOTAL BELANJA -->
                            <div class="total-nominal">
                                Rp <?= number_format($totalBelanja, 0, ',', '.'); ?>
                            </div>
                        </div>
                        <!-- 10. TOMBOL CHECKOUT -->
                        <a href="checkout.php" class="btn-aq-primary">
                            <i class="bi bi-credit-card-2-front"></i> Checkout
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer class="aq-footer text-center">
        <small>© <?= date('Y') ?> AquaGas - Air Galon & LPG Delivery</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./fontawesome-free-7.2.0-web/js/all.min.js"></script>

</body>

</html>
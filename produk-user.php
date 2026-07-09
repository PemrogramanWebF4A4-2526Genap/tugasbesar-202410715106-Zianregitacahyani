<?php
// =====================================================
// File: produk-user.php
// Deskripsi: Halaman buyer untuk menampilkan semua produk
// =====================================================

// Memanggil file koneksi database (berisi $mysqli)
require 'koneksi.php';

// Memulai session untuk pengecekan login user
session_start();

// -----------------------------------------------------
// KEAMANAN: Cek apakah user sudah login
// Jika belum login, redirect ke halaman login
// -----------------------------------------------------
if (!isset($_SESSION['login'])) {
    header("Location: login/login.php");
    exit;
}

if ($_SESSION['role'] != 'buyer') {
    header("Location: index.php");
    exit;
}

// (Opsional) Cek role user, hanya buyer yang boleh akses
// if ($_SESSION['role'] != 'buyer') { header("Location: admin.php"); exit; }

// -----------------------------------------------------
// QUERY: Mengambil semua produk + nama kategori
// Menggunakan LEFT JOIN agar produk tanpa kategori tetap muncul
// -----------------------------------------------------
$query = "SELECT p.*, c.name AS category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
ORDER BY p.id DESC";

$result = $mysqli->query($query);

// Fungsi bantu untuk format Rupiah
function rupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Produk - AquaGas</title>

    <!-- Bootstrap 5 CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts - Inter untuk tampilan modern -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --aqua-bg: #f5fbff;
            --aqua-primary: #0a7cff;
            --aqua-primary-dark: #065fcb;
            --aqua-primary-light: #e0f0ff;
            --aqua-baby-blue: #b8dcff;
            --aqua-baby-blue-soft: #d6ecff;
            --aqua-text: #1a2b42;
            --aqua-text-muted: #6b839e;
            --aqua-white: #ffffff;
            --aqua-grey: #94a3b8;
            --aqua-grey-bg: #e8edf3;
            --aqua-shadow: 0 4px 24px rgba(10, 124, 255, 0.10);
            --aqua-shadow-hover: 0 12px 40px rgba(10, 124, 255, 0.18);
            --aqua-radius: 25px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--aqua-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--aqua-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ============================================
HERO SECTION
============================================ */
        .hero-section {
            background: linear-gradient(135deg, #0a7cff 0%, #3da0ff 40%, #7ec4ff 100%);
            padding: 60px 0 70px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .hero-desc {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.88);
            font-weight: 400;
            line-height: 1.7;
            max-width: 480px;
        }

        .hero-illustration {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .hero-illustration .illust-bubble {
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, 0.13);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            border: 2px solid rgba(255, 255, 255, 0.18);
        }

        .hero-illustration .illust-bubble i {
            font-size: 6rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .hero-illustration .float-badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            padding: 10px 16px;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-illustration .float-badge.badge-top {
            top: 10px;
            right: 0px;
        }

        .hero-illustration .float-badge.badge-bottom {
            bottom: 20px;
            left: -10px;
        }

        /* ============================================
PRODUK HEADER
============================================ */
        .produk-header {
            padding: 48px 0 12px;
        }

        .produk-header h2 {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--aqua-text);
            margin-bottom: 6px;
        }

        .produk-header p {
            font-size: 1rem;
            color: var(--aqua-text-muted);
            font-weight: 400;
        }

        .produk-header .header-line {
            width: 50px;
            height: 4px;
            background: var(--aqua-primary);
            border-radius: 4px;
            margin-top: 14px;
        }

        /* ============================================
NOTIFIKASI
============================================ */
        .alert-aqua {
            background: var(--aqua-primary-light);
            color: var(--aqua-primary-dark);
            border: 1px solid var(--aqua-baby-blue);
            border-radius: 16px;
            font-weight: 500;
            font-size: 0.92rem;
        }

        .alert-aqua .btn-close {
            filter: invert(30%) sepia(90%) saturate(2000%) hue-rotate(210deg);
        }

        /* ============================================
PRODUCT CARD
============================================ */
        .product-card {
            background: var(--aqua-white);
            border: none;
            border-radius: var(--aqua-radius);
            overflow: hidden;
            box-shadow: var(--aqua-shadow);
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--aqua-shadow-hover);
        }

        .product-img-wrapper {
            position: relative;
            background: linear-gradient(145deg, #f0f7ff 0%, #e8f3ff 100%);
            height: 210px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: var(--aqua-radius) var(--aqua-radius) 0 0;
        }

        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-img-wrapper img {
            transform: scale(1.05);
        }

        .stock-badge-float {
            position: absolute;
            top: 14px;
            left: 14px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.02em;
        }

        .stock-badge-float.badge-tersedia {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .stock-badge-float.badge-habis {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .product-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px 20px 22px;
        }

        .category-badge {
            background: var(--aqua-baby-blue-soft);
            color: var(--aqua-primary);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
            width: fit-content;
            letter-spacing: 0.01em;
        }

        .product-card .product-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--aqua-text);
            margin-bottom: 6px;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card .product-desc {
            font-size: 0.82rem;
            color: var(--aqua-text-muted);
            line-height: 1.55;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card .product-price {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--aqua-primary);
            margin-bottom: 6px;
        }

        .product-card .product-stock-text {
            font-size: 0.78rem;
            color: var(--aqua-text-muted);
            margin-bottom: 16px;
        }

        /* ============================================
TOMBOL
============================================ */
        .btn-aqua-cart {
            background: var(--aqua-primary);
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            margin-top: auto;
        }

        .btn-aqua-cart:hover {
            background: var(--aqua-primary-dark);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(10, 124, 255, 0.35);
        }

        .btn-aqua-cart:active {
            transform: translateY(0);
        }

        .btn-aqua-disabled {
            background: var(--aqua-grey-bg);
            color: var(--aqua-grey);
            border: none;
            border-radius: 16px;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: auto;
            cursor: not-allowed;
            opacity: 0.75;
        }

        /* ============================================
EMPTY STATE
============================================ */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state .empty-icon {
            width: 120px;
            height: 120px;
            background: var(--aqua-baby-blue-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .empty-state .empty-icon i {
            font-size: 3rem;
            color: var(--aqua-baby-blue);
        }

        .empty-state h4 {
            font-weight: 700;
            color: var(--aqua-text);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--aqua-text-muted);
            font-size: 0.95rem;
        }

        /* ============================================
FOOTER
============================================ */
        .footer-aqua {
            background: var(--aqua-text);
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            padding: 24px 16px;
            margin-top: auto;
            font-size: 0.85rem;
            font-weight: 400;
        }

        .footer-aqua span {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
        }

        /* ============================================
RESPONSIVE
============================================ */
        @media (max-width: 767.98px) {
            .hero-section {
                padding: 40px 0 50px;
                text-align: center;
            }

            .hero-title {
                font-size: 1.75rem;
            }

            .hero-desc {
                max-width: 100%;
                margin: 0 auto;
            }

            .hero-illustration {
                margin-top: 30px;
            }

            .hero-illustration .illust-bubble {
                width: 170px;
                height: 170px;
            }

            .hero-illustration .illust-bubble i {
                font-size: 4rem;
            }

            .hero-illustration .float-badge {
                display: none;
            }

            .produk-header {
                padding: 32px 0 8px;
            }

            .produk-header h2 {
                font-size: 1.4rem;
            }

            .product-img-wrapper {
                height: 180px;
            }
        }

        @media (max-width: 575.98px) {
            .product-card .card-body {
                padding: 16px 16px 18px;
            }
        }
    </style>
</head>

<body>

    <!-- =====================================================
NAVBAR CUSTOM
===================================================== -->
    <?php require "navbar.php"; ?>

    <!-- =====================================================
HERO SECTION
===================================================== -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <h1 class="hero-title">🛍️ Belanja Produk AquaGas</h1>
                    <p class="hero-desc">
                        Pilih produk air galon dan gas LPG berkualitas untuk kebutuhan rumah Anda. Pengiriman cepat, harga terjangkau, dan pelayanan terpercaya.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="hero-illustration">
                        <div class="float-badge badge-top">
                            <i class="bi bi-truck"></i> Pengiriman Cepat
                        </div>
                        <div class="float-badge badge-bottom">
                            <i class="bi bi-shield-check"></i> Terjamin
                        </div>
                        <div class="illust-bubble">
                            <i class="bi bi-shop-window"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================
KONTEN PRODUK
===================================================== -->
    <div class="container">

        <!-- Header Produk Modern -->
        <div class="produk-header">
            <h2>Daftar Produk</h2>
            <p>Temukan produk terbaik AquaGas</p>
            <div class="header-line"></div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <!-- Notifikasi sukses -->
            <div class="alert alert-aqua alert-dismissible fade show mt-4 mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <!-- Grid Produk -->
            <div class="row g-4 py-4 pb-5">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // -------------------------------------------------
                    // Cek file gambar pada folder image/produk/
                    // Jika tidak ditemukan, gunakan placeholder
                    // -------------------------------------------------
                    $gambarPath = 'image/produk/' . $row['image'];
                    if (!empty($row['image']) && file_exists($gambarPath)) {
                        $gambarUrl = $gambarPath;
                    } else {
                        $gambarUrl = "https://via.placeholder.com/300x200?text=No+Image";
                    }

                    // Tentukan badge stok
                    if ($row['stock'] > 0) {
                        $badgeText = 'Tersedia';
                        $stockFloatClass = 'badge-tersedia';
                        $stockFloatIcon = 'bi-check-circle-fill';
                    } else {
                        $badgeText = 'Habis';
                        $stockFloatClass = 'badge-habis';
                        $stockFloatIcon = 'bi-x-circle-fill';
                    }
                    ?>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                        <!-- Card Produk -->
                        <div class="card product-card">
                            <!-- Gambar Produk -->
                            <div class="product-img-wrapper">
                                <img src="<?= htmlspecialchars($gambarUrl) ?>"
                                    alt="<?= htmlspecialchars($row['name']) ?>">
                                <!-- Badge Stok Float -->
                                <span class="stock-badge-float <?= $stockFloatClass ?>">
                                    <i class="bi <?= $stockFloatIcon ?>"></i>
                                    <?= $badgeText ?>
                                </span>
                            </div>

                            <div class="card-body">
                                <!-- Kategori Baby Blue -->
                                <span class="category-badge">
                                    <i class="bi bi-tag me-1"></i>
                                    <?= htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori') ?>
                                </span>

                                <!-- Nama Produk -->
                                <h5 class="product-name">
                                    <?= htmlspecialchars($row['name']) ?>
                                </h5>

                                <!-- Deskripsi singkat -->
                                <p class="product-desc">
                                    <?= htmlspecialchars(strlen($row['description']) > 80
                                        ? substr($row['description'], 0, 80) . '...'
                                        : $row['description']) ?>
                                </p>

                                <!-- Harga -->
                                <div class="product-price">
                                    <?= rupiah($row['price']) ?>
                                </div>

                                <?php

                                $reviewQuery = "
    SELECT
        AVG(rating) AS avg_rating,
        COUNT(*) AS total_review
    FROM reviews
    WHERE product_id = {$row['id']}
";

                                $reviewResult = $mysqli->query($reviewQuery);
                                $reviewData = $reviewResult->fetch_assoc();

                                ?>

                                <?php if ($reviewData['total_review'] > 0): ?>

                                    <div class="mb-2">

                                        <small class="text-warning">

                                            <?= str_repeat("⭐", round($reviewData['avg_rating'])) ?>

                                        </small>

                                        <small class="text-muted">

                                            (<?= $reviewData['total_review'] ?> Review)

                                        </small>

                                    </div>

                                <?php else: ?>

                                    <div class="mb-2">

                                        <small class="text-muted">
                                            Belum ada review
                                        </small>

                                    </div>

                                <?php endif; ?>

                                <!-- Stok Info -->
                                <?php

                                $lastReviewQuery = "
                                    SELECT comment
                                    FROM reviews
                                    WHERE product_id = {$row['id']}
                                    ORDER BY id DESC
                                    LIMIT 1
                                ";
                                
                                $lastReviewResult = $mysqli->query($lastReviewQuery);

                                if ($lastReviewResult && $lastReviewResult->num_rows > 0) {

                                    $lastReview = $lastReviewResult->fetch_assoc();
                                ?>

                                    <div class="small text-muted mb-3">

                                        <i>"<?= htmlspecialchars($lastReview['comment']) ?>"</i>

                                    </div>

                                <?php } ?>
                                <div class="product-stock-text">
                                    <i class="bi bi-archive me-1"></i>Stok: <?= (int)$row['stock'] ?>
                                </div>

                                <!-- Tombol Tambah ke Keranjang -->
                                <?php if ($row['stock'] > 0): ?>
                                    <a href="tambah-keranjang.php?id=<?= (int)$row['id'] ?>"
                                        class="btn btn-aqua-cart">
                                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-aqua-disabled" disabled>
                                        <i class="bi bi-cart-x"></i> Stok Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h4>Belum ada produk tersedia</h4>
                <p>Silakan kembali lagi nanti.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- =====================================================
FOOTER AQUAGAS
===================================================== -->
    <footer class="footer-aqua">
        © <?= date('Y') ?> <span>AquaGas</span> - Air Galon & LPG Delivery
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./fontawesome-free-7.2.0-web/js/all.min.js"></script>

</body>

</html>
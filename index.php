<?php
require 'login/session.php';

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'seller') {
        header("Location: Penjual/pesanan.php");
        exit;
    }

    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - AquaGas</title>
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./fontawesome-free-7.2.0-web/css/all.min.css">
    <style>
        :root {
            --bg-main: #f5fbff;
            --hero-from: #eef8ff;
            --hero-to: #dff2ff;
            --radius-hero: 30px;
            --radius-card: 25px;
            --shadow-soft: 0 8px 32px rgba(100, 180, 255, 0.10);
            --shadow-card: 0 6px 24px rgba(100, 180, 255, 0.08);
            --accent: #3b9eff;
            --accent-hover: #2b8ae6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-main);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item.active {
            color: var(--accent);
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Hero */
        .hero-box {
            background: linear-gradient(135deg, var(--hero-from), var(--hero-to));
            border-radius: var(--radius-hero);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(100, 180, 255, 0.12);
            transition: box-shadow 0.3s ease;
        }

        .hero-box:hover {
            box-shadow: 0 12px 40px rgba(100, 180, 255, 0.16);
        }

        .hero-box h1 {
            color: #1a3a5c;
            font-size: 2.2rem;
            line-height: 1.25;
        }

        .hero-box p {
            color: #5a7fa0;
            line-height: 1.7;
        }

        .hero-box .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            border-radius: 16px;
            padding: 14px 32px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(59, 158, 255, 0.3);
        }

        .hero-box .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(59, 158, 255, 0.4);
        }

        .hero-img {
            max-height: 320px;
            filter: drop-shadow(0 12px 24px rgba(59, 158, 255, 0.15));
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        /* Menu Cepat */
        .section-title {
            color: #1a3a5c;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .menu-card {
            border: none;
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
            background: #ffffff;
            transition: all 0.3s ease;
            cursor: pointer;
            padding: 2rem 1.25rem;
        }

        .menu-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 36px rgba(100, 180, 255, 0.18);
        }

        .menu-card .fa-3x {
            transition: transform 0.3s ease;
        }

        .menu-card:hover .fa-3x {
            transform: scale(1.15);
        }

        .menu-card h5 {
            color: #1a3a5c;
            font-weight: 700;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .menu-card p {
            color: #8aa8c2;
            font-size: 0.88rem;
            margin-bottom: 0;
        }

        .text-primary-custom {
            color: #3b9eff !important;
        }

        .text-success-custom {
            color: #34c77b !important;
        }

        .text-warning-custom {
            color: #f5a623 !important;
        }

        .text-danger-custom {
            color: #f06569 !important;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .hero-box h1 {
                font-size: 1.65rem;
            }

            .hero-img {
                max-height: 200px;
                margin-top: 1.5rem;
            }

            .menu-card {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>

<body>

    <?php require "navbar.php"; ?>

    <div class="container py-4">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="pt-4 pb-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-home me-1"></i> Home
                </li>
            </ol>
        </nav>

        <!-- Hero Section -->
        <div class="hero-box p-4 p-md-5 mb-5">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h1 class="fw-bold mb-3">
                        Halo, <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?> 👋
                    </h1>
                    <p class="fs-5 mb-4">
                        Selamat datang di <strong>AquaGas</strong>. Pesan air galon dan gas LPG dengan mudah dan cepat.
                    </p>
                    <a href="produk-user.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                    </a>
                </div>
                <div class="col-md-6 text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3082/3082024.png"
                        alt="AquaGas Illustration" class="img-fluid hero-img">
                </div>
            </div>
        </div>

        <!-- Menu Cepat -->
        <h3 class="section-title mb-4">✨ Menu Cepat</h3>

        <div class="row g-3 g-md-4 mb-5 pb-4">

            <div class="col-6 col-md-3">
                <a href="produk-user.php" class="text-decoration-none">
                    <div class="card menu-card text-center">
                        <i class="fas fa-store fa-3x text-primary-custom mb-3"></i>
                        <h5>Belanja</h5>
                        <p>Lihat semua produk</p>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="keranjang.php" class="text-decoration-none">
                    <div class="card menu-card text-center">
                        <i class="fas fa-shopping-cart fa-3x text-success-custom mb-3"></i>
                        <h5>Keranjang</h5>
                        <p>Kelola pesanan Anda</p>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="pesanan-saya.php" class="text-decoration-none">
                    <div class="card menu-card text-center">
                        <i class="fas fa-box fa-3x text-warning-custom mb-3"></i>
                        <h5>Pesanan Saya</h5>
                        <p>Tracking pesanan</p>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="pesanan-saya.php"
                    class="text-decoration-none">
                    <div class="card menu-card text-center">
                        <i class="fas fa-star fa-3x text-danger-custom mb-3"></i>
                        <h5>Review</h5>
                        <p class="text-muted">
                            Beri review produk yang sudah dibeli
                        </p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="./fontawesome-free-7.2.0-web/js/all.min.js"></script>

</body>

</html>
<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit;
}

require "../koneksi.php";

$totalProduk = mysqli_num_rows(mysqli_query($mysqli, "SELECT * FROM products"));
$totalKategori = mysqli_num_rows(mysqli_query($mysqli, "SELECT * FROM categories"));
$totalUser = mysqli_num_rows(mysqli_query($mysqli, "SELECT * FROM users"));
$totalPesanan = mysqli_num_rows(mysqli_query($mysqli, "SELECT * FROM orders"));
$recentOrders = mysqli_query($mysqli, "
    SELECT *
    FROM orders
    ORDER BY id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — AquGas Admin</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ==================== VARIABEL ==================== */
        :root {
            --baby-50: #f0f9ff;
            --baby-100: #e0f2fe;
            --baby-200: #bae6fd;
            --baby-300: #7dd3fc;
            --baby-400: #38bdf8;
            --baby-500: #0ea5e9;
            --baby-600: #0284c7;
            --baby-700: #0369a1;
            --baby-800: #075985;
            --baby-900: #0c4a6e;

            --green-soft: #d1fae5;
            --green-mid: #34d399;
            --green: #10b981;

            --amber-soft: #fef3c7;
            --amber-mid: #fbbf24;
            --amber: #f59e0b;

            --red-soft: #fee2e2;
            --red-mid: #f87171;
            --red: #ef4444;

            --card-radius: 18px;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 6px 20px rgba(56, 189, 248, 0.07);
            --card-shadow-hover: 0 4px 8px rgba(0, 0, 0, 0.06), 0 12px 32px rgba(56, 189, 248, 0.13);
            --transition: 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* ==================== BODY ==================== */
        body {
            background-color: var(--baby-50);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #334155;
        }

        /* ==================== KONTEN UTAMA ==================== */
        .main-content {
            padding: 2rem 1.5rem 3rem;
            max-width: 1280px;
            margin: 0 auto;
            animation: fadeUp 0.5s ease-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ==================== WELCOME SECTION ==================== */
        .welcome-section {
            position: relative;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 50%, #7dd3fc 100%);
            border-radius: var(--card-radius);
            padding: 2rem 2.2rem;
            margin-bottom: 1.8rem;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -30px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .welcome-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: 80px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
        }

        .welcome-title {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--baby-800);
            margin-bottom: 0.35rem;
            position: relative;
            z-index: 1;
        }

        .welcome-subtitle {
            font-size: 0.92rem;
            color: var(--baby-700);
            position: relative;
            z-index: 1;
            font-weight: 500;
        }

        /* ==================== STAT CARDS ==================== */
        .stat-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(186, 230, 253, 0.4);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 4px 0 0 4px;
            transition: width var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        .stat-card:hover::after {
            width: 6px;
        }

        .stat-card.card-blue::after {
            background: var(--baby-400);
        }

        .stat-card.card-sky::after {
            background: var(--baby-500);
        }

        .stat-card.card-cyan::after {
            background: #06b6d4;
        }

        .stat-card.card-indigo::after {
            background: #818cf8;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            transition: transform var(--transition);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.08) rotate(-3deg);
        }

        .stat-icon.icon-blue {
            background: #dbeafe;
            color: #3b82f6;
        }

        .stat-icon.icon-sky {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .stat-icon.icon-cyan {
            background: #cffafe;
            color: #06b6d4;
        }

        .stat-icon.icon-indigo {
            background: #e0e7ff;
            color: #818cf8;
        }

        .stat-number {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--baby-900);
            line-height: 1.1;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 0.15rem;
        }

        .stat-trend {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.15em 0.5em;
            border-radius: 6px;
        }

        .stat-trend.up {
            background: var(--green-soft);
            color: var(--green);
        }

        .stat-trend.down {
            background: var(--red-soft);
            color: var(--red);
        }

        /* ==================== QUICK ACTIONS ==================== */
        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--baby-800);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--baby-400);
            font-size: 1.1rem;
        }

        .action-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.35rem 1rem;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(186, 230, 253, 0.3);
            text-decoration: none;
            transition: all var(--transition);
            display: block;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-shadow-hover);
            border-color: var(--baby-300);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            color: var(--baby-600);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
            transition: all var(--transition);
        }

        .action-card:hover .action-icon {
            background: linear-gradient(135deg, var(--baby-400), var(--baby-500));
            color: #ffffff;
            transform: scale(1.06);
        }

        .action-label {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--baby-800);
        }

        .action-desc {
            font-size: 0.73rem;
            color: #94a3b8;
            margin-top: 0.2rem;
        }

        /* ==================== TABEL PESANAN ==================== */
        .table-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(186, 230, 253, 0.4);
            overflow: hidden;
        }

        .table-card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--baby-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .table-card-header h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--baby-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .table-card-header h5 i {
            color: var(--baby-400);
        }

        .table-aqugas {
            margin: 0;
            font-size: 0.87rem;
        }

        .table-aqugas thead th {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            color: var(--baby-800);
            font-weight: 650;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.85rem 1rem;
            border-bottom: 2px solid var(--baby-200);
            white-space: nowrap;
        }

        .table-aqugas tbody td {
            padding: 0.8rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        .table-aqugas tbody tr {
            transition: background-color var(--transition);
        }

        .table-aqugas tbody tr:hover {
            background-color: #f8fdff;
        }

        .table-aqugas tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge status */
        .badge-status {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.3em 0.7em;
            border-radius: 7px;
            letter-spacing: 0.02em;
        }

        .badge-selesai {
            background: var(--green-soft);
            color: var(--green);
        }

        .badge-diproses {
            background: #dbeafe;
            color: #3b82f6;
        }

        .badge-menunggu {
            background: var(--amber-soft);
            color: var(--amber);
        }

        .badge-dibatalkan {
            background: var(--red-soft);
            color: var(--red);
        }

        /* Link lihat selengkapnya */
        .link-see-all {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--baby-500);
            text-decoration: none;
            transition: color var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .link-see-all:hover {
            color: var(--baby-700);
        }

        /* ==================== FOOTER ==================== */
        .dashboard-footer {
            text-align: center;
            padding: 2rem 1rem 1rem;
            color: #94a3b8;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .dashboard-footer span {
            color: var(--baby-400);
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 1.2rem 1rem 2rem;
            }

            .welcome-section {
                padding: 1.5rem 1.3rem;
            }

            .welcome-title {
                font-size: 1.25rem;
            }

            .stat-number {
                font-size: 1.35rem;
            }

            .table-card-header {
                padding: 1rem 1.2rem;
            }

            .table-aqugas thead th,
            .table-aqugas tbody td {
                padding: 0.65rem 0.75rem;
            }

            /* Tabel scroll horizontal di mobile */
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 575.98px) {
            .stat-card {
                padding: 1.2rem;
            }

            .stat-icon {
                width: 42px;
                height: 42px;
                font-size: 1.15rem;
                border-radius: 12px;
            }
        }
    </style>
</head>

<body>

    <!-- ========== NAVBAR ========== -->
    <?php require "navbar.php"; ?>

    <!-- ========== KONTEN DASHBOARD ========== -->
    <main class="main-content">

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">
                Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?> 👋
            </h1>
            <p class="welcome-subtitle">
                Berikut ringkasan data toko Anda hari ini. Kelola semuanya dari satu tempat.
            </p>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">

            <!-- Total Produk -->
            <div class="col-6 col-lg-3">
                <div class="stat-card card-blue">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon icon-blue">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?= $totalProduk ?></div>
                            <div class="stat-label">Total Produk</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Pesanan -->
            <div class="col-6 col-lg-3">
                <div class="stat-card card-sky">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon icon-sky">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?= $totalPesanan ?></div>
                            <div class="stat-label">Total Pesanan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total User -->
            <div class="col-6 col-lg-3">
                <div class="stat-card card-cyan">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="stat-icon icon-cyan">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?= $totalUser ?></div>
                            <div class="stat-label">Total User</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pendapatan -->
            <div class="col-6 col-lg-3">
                <div class="stat-card card-indigo">

                    <div class="d-flex align-items-center gap-3 mb-2">

                        <div class="stat-icon icon-indigo">
                            <i class="bi bi-tags"></i>
                        </div>

                        <div>
                            <div class="stat-number">
                                <?= $totalKategori ?>
                            </div>

                            <div class="stat-label">
                                Total Kategori
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Quick Actions -->
            <div class="mb-4">
                <div class="section-title">
                    <i class="bi bi-lightning-charge"></i>
                    Quick Actions
                </div>
                <div class="row g-3">

                    <div class="col-6 col-md-3">
                        <a href="produk/tambah.php" class="action-card">
                            <div class="action-icon">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div class="action-label">Tambah Produk</div>
                            <div class="action-desc">Input produk baru</div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="kategori/tambah.php" class="action-card">
                            <div class="action-icon">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <div class="action-label">Tambah Kategori</div>
                            <div class="action-desc">Buat kategori baru</div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="pesanan/index.php" class="action-card">
                            <div class="action-icon">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="action-label">Lihat Pesanan</div>
                            <div class="action-desc">Monitor semua pesanan</div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="user/index.php" class="action-card">
                            <div class="action-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <div class="action-label">Kelola User</div>
                            <div class="action-desc">Atur data pengguna</div>
                        </a>
                    </div>

                </div>
            </div>

            <!-- Tabel Pesanan Terbaru -->
            <div class="table-card">
                <div class="table-card-header">
                    <h5><i class="bi bi-clock-history"></i> Pesanan Terbaru</h5>
                    <a href="pesanan/index.php" class="link-see-all">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-aqugas">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            while ($row = mysqli_fetch_assoc($recentOrders)) {
                            ?>

                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        #<?= $row['id'] ?>
                                    </td>

                                    <td>
                                        <?= $row['created_at'] ?>
                                    </td>

                                    <td>

                                        <?php
                                        if ($row['status'] == 'pending') {
                                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                                        } elseif ($row['status'] == 'diproses') {
                                            echo '<span class="badge bg-info text-dark">Diproses</span>';
                                        } elseif ($row['status'] == 'dikirim') {
                                            echo '<span class="badge bg-primary">Dikirim</span>';
                                        } elseif ($row['status'] == 'selesai') {
                                            echo '<span class="badge bg-success">Selesai</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">'
                                                . $row['status'] .
                                                '</span>';
                                        }
                                        ?>

                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="dashboard-footer">
                <span>&copy;</span> 2025 AquGas Admin &mdash; Panel Manajemen Toko Online
            </div>

    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
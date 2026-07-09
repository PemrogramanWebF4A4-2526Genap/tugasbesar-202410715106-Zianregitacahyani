<?php
require '../../login/session-admin.php';
require '../../koneksi.php';

 $totalRevenue = 0;
 $totalPesanan = 0;
 $totalUser = 0;
 $totalProduk = 0;
 $pending = 0;
 $dikirim = 0;
 $selesai = 0;
 $produkTerlaris = [];
 $pesananTerbaru = [];

// Total Revenue
 $revQ = $mysqli->query("SELECT COALESCE(SUM(total_amount), 0) AS revenue FROM orders");
if ($revQ) {
    $revRow = $revQ->fetch_assoc();
    $totalRevenue = (int)$revRow['revenue'];
}

// Total Pesanan
 $ordQ = $mysqli->query("SELECT COUNT(*) AS total FROM orders");
if ($ordQ) {
    $totalPesanan = (int)$ordQ->fetch_assoc()['total'];
}

// Total User
 $usrQ = $mysqli->query("SELECT COUNT(*) AS total FROM users");
if ($usrQ) {
    $totalUser = (int)$usrQ->fetch_assoc()['total'];
}

// Total Produk
 $prdQ = $mysqli->query("SELECT COUNT(*) AS total FROM products");
if ($prdQ) {
    $totalProduk = (int)$prdQ->fetch_assoc()['total'];
}

// Statistik Status
 $stQ = $mysqli->query("SELECT status, COUNT(*) AS total FROM orders GROUP BY status");
if ($stQ) {
    while ($stRow = $stQ->fetch_assoc()) {
        $st = strtolower($stRow['status']);
        if ($st === 'pending') $pending = (int)$stRow['total'];
        elseif ($st === 'dikirim') $dikirim = (int)$stRow['total'];
        elseif ($st === 'selesai') $selesai = (int)$stRow['total'];
    }
}

// Produk Terlaris
 $bestQ = $mysqli->query("
    SELECT p.name, SUM(oi.quantity) AS total_terjual
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY total_terjual DESC
    LIMIT 5
");
if ($bestQ) {
    while ($b = $bestQ->fetch_assoc()) {
        $produkTerlaris[] = $b;
    }
}

// Pesanan Terbaru
 $recQ = $mysqli->query("
    SELECT o.*, u.name AS buyer_name
    FROM orders o
    LEFT JOIN users u ON o.buyer_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
if ($recQ) {
    while ($r = $recQ->fetch_assoc()) {
        $pesananTerbaru[] = $r;
    }
}

function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function statusBadge($status)
{
    $st = strtolower(trim($status));
    if ($st === 'pending') {
        return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>';
    } elseif ($st === 'dikirim') {
        return '<span class="badge bg-purple text-white" style="background-color:#7c3aed;"><i class="bi bi-truck me-1"></i>Dikirim</span>';
    } elseif ($st === 'selesai') {
        return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
    }
    return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report & Analytics — AquaGas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --blue-lightest: #f5fbff;
            --blue-light: #dff2ff;
            --blue-mid: #9acbff;
            --blue-accent: #3a8fd4;
            --blue-dark: #1a5f99;
        }

        body {
            background-color: var(--blue-lightest);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        .page-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1.5rem 3rem;
        }

        /* Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--blue-dark);
            margin-bottom: 0.25rem;
        }

        .page-header p {
            color: #5a8aad;
            font-size: 0.95rem;
            margin: 0;
        }

        /* Action Buttons */
        .action-bar {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn-kembali {
            background-color: var(--blue-mid);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-kembali:hover {
            background-color: var(--blue-accent);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(58, 143, 212, 0.3);
        }

        .btn-refresh {
            background-color: #fff;
            color: var(--blue-accent);
            border: 2px solid var(--blue-light);
            border-radius: 10px;
            padding: 0.6rem 1.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-refresh:hover {
            border-color: var(--blue-mid);
            background-color: var(--blue-lightest);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(154, 203, 255, 0.3);
        }

        .btn-refresh:active .bi-arrow-clockwise {
            animation: spin-once 0.5s ease;
        }

        @keyframes spin-once {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Stat Cards */
        .stat-card {
            background: #fff;
            border: 1px solid var(--blue-light);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.25s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--blue-mid), var(--blue-accent));
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(154, 203, 255, 0.25);
            border-color: var(--blue-mid);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1rem;
        }

        .stat-icon.revenue {
            background-color: #dff2ff;
            color: var(--blue-accent);
        }

        .stat-icon.orders {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .stat-icon.users {
            background-color: #fff3e0;
            color: #e65100;
        }

        .stat-icon.products {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .stat-label {
            font-size: 0.82rem;
            color: #7a9bb5;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.35rem;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a2e3d;
            line-height: 1.2;
        }

        /* Section Card */
        .section-card {
            background: #fff;
            border: 1px solid var(--blue-light);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .section-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--blue-light);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            background: linear-gradient(135deg, #fff 0%, var(--blue-lightest) 100%);
        }

        .section-card-header .icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--blue-light);
            color: var(--blue-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .section-card-header h5 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--blue-dark);
        }

        .section-card-body {
            padding: 1.5rem;
        }

        /* Status Progress */
        .status-item {
            margin-bottom: 1.25rem;
        }

        .status-item:last-child {
            margin-bottom: 0;
        }

        .status-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.45rem;
        }

        .status-meta .label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #2c4a5e;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .status-meta .count {
            font-size: 0.88rem;
            font-weight: 700;
            color: #3a5f7a;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: var(--blue-lightest);
        }

        .progress-bar-pending {
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
        }

        .progress-bar-dikirim {
            background: linear-gradient(90deg, #a78bfa, #7c3aed);
        }

        .progress-bar-selesai {
            background: linear-gradient(90deg, #34d399, #059669);
        }

        /* Best Selling */
        .best-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 0;
            border-bottom: 1px solid #f0f6fa;
        }

        .best-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .best-item:first-child {
            padding-top: 0;
        }

        .best-rank {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .best-rank.r1 { background: #fff7ed; color: #ea580c; }
        .best-rank.r2 { background: #f0f4ff; color: #4f6ef7; }
        .best-rank.r3 { background: #f0fdf4; color: #16a34a; }
        .best-rank.r4 { background: #f5f5f5; color: #737373; }
        .best-rank.r5 { background: #f5f5f5; color: #737373; }

        .best-info {
            flex: 1;
            min-width: 0;
        }

        .best-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1a2e3d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .best-qty {
            font-size: 0.78rem;
            color: #7a9bb5;
            margin-top: 0.1rem;
        }

        .best-bar-wrap {
            width: 120px;
            flex-shrink: 0;
        }

        .best-bar {
            height: 8px;
            border-radius: 4px;
            background: var(--blue-lightest);
            overflow: hidden;
        }

        .best-bar-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--blue-mid), var(--blue-accent));
            transition: width 0.6s ease;
        }

        /* Table */
        .table-custom {
            margin: 0;
        }

        .table-custom thead th {
            background: var(--blue-lightest);
            color: var(--blue-dark);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.85rem 1rem;
            border-bottom: 2px solid var(--blue-light);
            white-space: nowrap;
        }

        .table-custom tbody td {
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            color: #2c4a5e;
            vertical-align: middle;
            border-bottom: 1px solid #f0f6fa;
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background-color: rgba(223, 242, 255, 0.4);
        }

        .order-id-cell {
            font-weight: 700;
            color: var(--blue-accent);
            font-family: 'Courier New', monospace;
            font-size: 0.84rem;
        }

        .amount-cell {
            font-weight: 600;
            color: #1a2e3d;
        }

        .date-cell {
            font-size: 0.82rem;
            color: #7a9bb5;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #7a9bb5;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            display: block;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-wrapper {
                padding: 1.25rem 1rem 2rem;
            }

            .page-header h1 {
                font-size: 1.4rem;
            }

            .stat-value {
                font-size: 1.3rem;
            }

            .best-bar-wrap {
                width: 80px;
            }

            .section-card-body {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.82rem;
            }
        }

        /* Fade In Animation */
        .fade-up {
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }

        .fade-up:nth-child(1) { animation-delay: 0.05s; }
        .fade-up:nth-child(2) { animation-delay: 0.1s; }
        .fade-up:nth-child(3) { animation-delay: 0.15s; }
        .fade-up:nth-child(4) { animation-delay: 0.2s; }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-animate {
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.5s ease forwards;
            animation-delay: 0.3s;
        }

        .section-animate-delay {
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.5s ease forwards;
            animation-delay: 0.4s;
        }

        .section-animate-delay2 {
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.5s ease forwards;
            animation-delay: 0.5s;
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- Header -->
    <div class="page-header">
        <h1><i class="bi bi-graph-up-arrow me-2"></i>Report & Analytics</h1>
        <p>Statistik dan laporan penjualan AquaGas</p>
    </div>

    <!-- Action Buttons -->
    <div class="action-bar">
        <a href="../index.php" class="btn-kembali">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <button class="btn-refresh" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh Data
        </button>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card fade-up">
                <div class="stat-icon revenue">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value"><?= formatRupiah($totalRevenue) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card fade-up">
                <div class="stat-icon orders">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div class="stat-label">Total Pesanan</div>
                <div class="stat-value"><?= number_format($totalPesanan, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card fade-up">
                <div class="stat-icon users">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-label">Total User</div>
                <div class="stat-value"><?= number_format($totalUser, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card fade-up">
                <div class="stat-icon products">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-label">Total Produk</div>
                <div class="stat-value"><?= number_format($totalProduk, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- Status Pesanan + Produk Terlaris -->
    <div class="row g-3 mb-4">
        <!-- Status Pesanan -->
        <div class="col-lg-5">
            <div class="section-card section-animate" style="height:100%;">
                <div class="section-card-header">
                    <div class="icon-box"><i class="bi bi-clipboard-data"></i></div>
                    <h5>Status Pesanan</h5>
                </div>
                <div class="section-card-body">
                    <?php
                    $maxStatus = max($pending, $dikirim, $selesai, 1);
                    ?>
                    <div class="status-item">
                        <div class="status-meta">
                            <span class="label"><i class="bi bi-clock-fill text-warning"></i> Pending</span>
                            <span class="count"><?= number_format($pending, 0, ',', '.') ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-pending" role="progressbar" style="width: <?= ($pending / $maxStatus) * 100 ?>%"></div>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-meta">
                            <span class="label"><i class="bi bi-truck-fill" style="color:#7c3aed;"></i> Dikirim</span>
                            <span class="count"><?= number_format($dikirim, 0, ',', '.') ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-dikirim" role="progressbar" style="width: <?= ($dikirim / $maxStatus) * 100 ?>%"></div>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-meta">
                            <span class="label"><i class="bi bi-check-circle-fill text-success"></i> Selesai</span>
                            <span class="count"><?= number_format($selesai, 0, ',', '.') ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-selesai" role="progressbar" style="width: <?= ($selesai / $maxStatus) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produk Terlaris -->
        <div class="col-lg-7">
            <div class="section-card section-animate-delay" style="height:100%;">
                <div class="section-card-header">
                    <div class="icon-box"><i class="bi bi-trophy"></i></div>
                    <h5>Produk Terlaris</h5>
                </div>
                <div class="section-card-body">
                    <?php if (empty($produkTerlaris)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada data penjualan</p>
                        </div>
                    <?php else: ?>
                        <?php
                        $maxSold = $produkTerlaris[0]['total_terjual'] ?? 1;
                        $rankClasses = ['r1', 'r2', 'r3', 'r4', 'r5'];
                        foreach ($produkTerlaris as $idx => $item):
                        ?>
                            <div class="best-item">
                                <div class="best-rank <?= $rankClasses[$idx] ?>"><?= $idx + 1 ?></div>
                                <div class="best-info">
                                    <div class="best-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="best-qty"><?= number_format((int)$item['total_terjual'], 0, ',', '.') ?> unit terjual</div>
                                </div>
                                <div class="best-bar-wrap">
                                    <div class="best-bar">
                                        <div class="best-bar-fill" style="width: <?= ((int)$item['total_terjual'] / $maxSold) * 100 ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pesanan Terbaru -->
    <div class="section-card section-animate-delay2">
        <div class="section-card-header">
            <div class="icon-box"><i class="bi bi-receipt"></i></div>
            <h5>Pesanan Terbaru</h5>
        </div>
        <div class="table-responsive">
            <?php if (empty($pesananTerbaru)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada pesanan</p>
                </div>
            <?php else: ?>
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>No Pesanan</th>
                            <th>Nama Pembeli</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesananTerbaru as $row): ?>
                            <tr>
                                <td class="order-id-cell">#<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($row['buyer_name'] ?? 'Guest') ?></td>
                                <td class="amount-cell"><?= formatRupiah($row['total_amount']) ?></td>
                                <td><?= statusBadge($row['status']) ?></td>
                                <td class="date-cell"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
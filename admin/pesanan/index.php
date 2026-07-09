<?php
require '../../login/session-admin.php';
require '../../koneksi.php';

$query = "SELECT
            o.id,
            u.name,
            o.total_amount,
            o.status,
            o.created_at
          FROM orders o
          LEFT JOIN users u ON o.buyer_id = u.id
          ORDER BY o.id DESC";
$result = $mysqli->query($query);
$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

function getStatusBadge($status)
{
    $map = [
        'pending'  => ['bg' => '#fff8e1', 'color' => '#f9a825', 'icon' => 'bi-clock-fill',       'label' => 'Pending'],
        'dikirim'  => ['bg' => '#f3e5f5', 'color' => '#8e24aa', 'icon' => 'bi-truck',             'label' => 'Dikirim'],
        'selesai'  => ['bg' => '#e8f5e9', 'color' => '#43a047', 'icon' => 'bi-check-circle-fill', 'label' => 'Selesai'],
    ];
    $s = $map[$status] ?? $map['pending'];
    return '<span style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:50px;font-size:13px;font-weight:600;background:' . $s['bg'] . ';color:' . $s['color'] . ';">
                <i class="bi ' . $s['icon'] . '"></i>' . $s['label'] . '
            </span>';
}

function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($datetime)
{
    $tgl = date('d M Y', strtotime($datetime));
    $jam = date('H:i', strtotime($datetime));
    return '<div style="font-weight:600;font-size:14px;color:#1e293b;">' . $tgl . '</div>
            <div style="font-size:12px;color:#94a3b8;">' . $jam . ' WIB</div>';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pesanan - AquaGas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --baby-blue: #7ec8e3;
            --baby-blue-light: #b8e4f5;
            --baby-blue-pale: #e3f4fc;
            --baby-blue-ghost: #f0f9ff;
            --accent: #3a9fd8;
            --accent-dark: #2b7fb5;
            --bg-body: #f5fbff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-soft: #e2edf5;
            --card-radius: 30px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 40px 20px 60px;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── Back Link ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
            margin-bottom: 24px;
            transition: all 0.2s ease;
            padding: 8px 18px;
            border-radius: 50px;
            background: var(--baby-blue-ghost);
        }

        .back-link:hover {
            background: var(--baby-blue-pale);
            color: var(--accent-dark);
            transform: translateX(-3px);
        }

        /* ── Main Card ── */
        .main-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 40px rgba(126, 200, 227, 0.12), 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid var(--border-soft);
        }

        /* ── Card Header ── */
        .card-header-custom {
            background: linear-gradient(135deg, #7ec8e3 0%, #a8dbf0 40%, #c5e8f7 100%);
            padding: 36px 40px;
            position: relative;
            overflow: hidden;
        }

        .card-header-custom::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -40px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
        }

        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: 120px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .header-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f2b3c;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .header-subtitle {
            font-size: 14px;
            font-weight: 500;
            color: rgba(15, 43, 60, 0.6);
            position: relative;
            z-index: 1;
        }

        /* ── Card Body ── */
        .card-body-custom {
            padding: 32px 40px 40px;
        }

        /* ── Stats Row ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 30px;
        }

        .stat-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 18px;
            background: var(--baby-blue-ghost);
            border: 1px solid var(--border-soft);
            transition: all 0.25s ease;
        }

        .stat-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 18px rgba(126, 200, 227, 0.18);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value.small {
            font-size: 15px;
        }

        /* ── Table ── */
        .table-container {
            overflow-x: auto;
            border-radius: 20px;
            border: 1px solid var(--border-soft);
            background: #fff;
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 780px;
        }

        .table-custom thead th {
            background: var(--baby-blue-ghost);
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 16px 20px;
            border-bottom: 2px solid var(--border-soft);
            white-space: nowrap;
        }

        .table-custom thead th:first-child {
            border-radius: 20px 0 0 0;
        }

        .table-custom thead th:last-child {
            border-radius: 0 20px 0 0;
        }

        .table-custom tbody tr {
            height: 90px;
            transition: background 0.15s ease;
        }

        .table-custom tbody tr:hover {
            background: #eef8ff;
        }

        .table-custom tbody td {
            padding: 14px 20px;
            vertical-align: middle;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Cell Styles ── */
        .cell-no {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 13px;
            text-align: center;
            width: 55px;
        }

        .cell-id {
            font-weight: 700;
            color: var(--accent);
            font-size: 14px;
            white-space: nowrap;
        }

        .cell-id i {
            margin-right: 3px;
            font-size: 11px;
            opacity: 0.6;
        }

        .cell-pembeli {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar-sm {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--baby-blue-light), var(--baby-blue));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .cell-total {
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
        }

        /* ── Button Lihat Detail ── */
        .btn-detail {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 22px;
            border-radius: 50px;
            background: linear-gradient(135deg, var(--baby-blue), var(--accent));
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.2s ease;
            box-shadow: 0 2px 10px rgba(126, 200, 227, 0.35);
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(58, 159, 216, 0.4);
        }

        .btn-detail:active {
            transform: translateY(0);
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-icon {
            font-size: 72px;
            display: block;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .empty-subtitle {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            body {
                padding: 20px 12px 40px;
            }

            .card-header-custom {
                padding: 28px 24px;
            }

            .header-title {
                font-size: 20px;
            }

            .card-body-custom {
                padding: 24px 18px 28px;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-chip {
                padding: 12px 14px;
                gap: 10px;
            }

            .stat-value {
                font-size: 17px;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">

        <!-- Back Link -->
        <a href="../index.php" class="back-link">
            ← Kembali ke Dashboard
        </a>

        <!-- Main Card -->
        <div class="main-card">

            <!-- Header -->
            <div class="card-header-custom">
                <div class="header-title">📦 Monitoring Pesanan</div>
                <div class="header-subtitle">Kelola dan pantau seluruh pesanan AquaGas</div>
            </div>

            <!-- Body -->
            <div class="card-body-custom">

                <?php if (!empty($orders)) : ?>

                    <?php
                    $totalPesanan  = count($orders);
                    $totalPending  = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
                    $totalDikirim  = count(array_filter($orders, fn($o) => $o['status'] === 'dikirim'));
                    $totalSelesai  = count(array_filter($orders, fn($o) => $o['status'] === 'selesai'));
                    $totalRevenue  = array_sum(array_column($orders, 'total_amount'));
                    ?>

                    <!-- Stats -->
                    <div class="stats-row">
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:#e3f2fd;color:#1e88e5;">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $totalPesanan; ?></div>
                                <div class="stat-label">Total Pesanan</div>
                            </div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:#fff8e1;color:#f9a825;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $totalPending; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:#f3e5f5;color:#8e24aa;">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $totalDikirim; ?></div>
                                <div class="stat-label">Dikirim</div>
                            </div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:#e8f5e9;color:#43a047;">
                                <i class="bi bi-check2-all"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $totalSelesai; ?></div>
                                <div class="stat-label">Selesai</div>
                            </div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div>
                                <div class="stat-value small"><?= formatRupiah($totalRevenue); ?></div>
                                <div class="stat-label">Revenue</div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-container">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align:center;">No</th>
                                    <th>ID Pesanan</th>
                                    <th>Pembeli</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($orders as $row) :
                                    $initial = strtoupper(substr($row['name'] ?? 'U', 0, 1));
                                ?>
                                    <tr>
                                        <td class="cell-no"><?= $no++; ?></td>
                                        <td class="cell-id">
                                            <i class="bi bi-hash"></i>#<?= htmlspecialchars($row['id']); ?>
                                        </td>
                                        <td>
                                            <div class="cell-pembeli">
                                                <div class="avatar-sm"><?= htmlspecialchars($initial); ?></div>
                                                <span><?= htmlspecialchars($row['name'] ?? 'Tidak diketahui'); ?></span>
                                            </div>
                                        </td>
                                        <td class="cell-total"><?= formatRupiah($row['total_amount']); ?></td>
                                        <td><?= getStatusBadge($row['status']); ?></td>
                                        <td><?= formatTanggal($row['created_at']); ?></td>
                                        <td style="text-align:center;">
                                            <button class="btn-detail" onclick="alert('Halaman detail pesanan masih dalam pengembangan 🩵')">
                                                <i class="bi bi-eye"></i>
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else : ?>

                    <!-- Empty State -->
                    <div class="empty-state">
                        <span class="empty-icon">📦</span>
                        <div class="empty-title">Belum ada pesanan</div>
                        <div class="empty-subtitle">Pesanan pelanggan akan muncul di sini</div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
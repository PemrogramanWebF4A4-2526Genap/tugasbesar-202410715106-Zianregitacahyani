<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/index.php");
    exit();
}

require_once '../koneksi.php';

 $seller_id   = $_SESSION['user_id'];
 $seller_name = $_SESSION['username'];

// Total Produk
 $q = $mysqli->prepare("SELECT COUNT(*) AS total FROM products WHERE seller_id = ?");
 $q->bind_param("i", $seller_id);
 $q->execute();
 $total_produk = $q->get_result()->fetch_assoc()['total'];

// Total Pesanan (distinct order yang mengandung produk seller)
 $q = $mysqli->prepare("
    SELECT COUNT(DISTINCT o.id) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
");
 $q->bind_param("i", $seller_id);
 $q->execute();
 $total_pesanan = $q->get_result()->fetch_assoc()['total'];

// Total Pendapatan
 $q = $mysqli->prepare("
    SELECT COALESCE(SUM(oi.quantity * oi.price), 0) AS total
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
");
 $q->bind_param("i", $seller_id);
 $q->execute();
 $total_pendapatan = $q->get_result()->fetch_assoc()['total'];

// Produk Terlaris (Top 5)
 $q = $mysqli->prepare("
    SELECT p.id, p.name, p.image, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
 $q->bind_param("i", $seller_id);
 $q->execute();
 $produk_terlaris = $q->get_result();

// Pesanan Terbaru (5 terakhir)
 $q = $mysqli->prepare("
    SELECT DISTINCT o.id, o.total_amount, o.status, o.created_at, u.name AS buyer_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.buyer_id = u.id
    WHERE p.seller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
 $q->bind_param("i", $seller_id);
 $q->execute();
 $pesanan_terbaru = $q->get_result();

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
function statusBadge($status) {
    $map = [
        'menunggu_pembayaran' => 'warning',
        'sudah_dibayar'       => 'info',
        'diproses'            => 'primary',
        'dikirim'             => 'cyan',
        'selesai'             => 'success',
        'dibatalkan'          => 'danger'
    ];
    $cls = $map[strtolower($status)] ?? 'secondary';
    $label = str_replace('_', ' ', ucfirst($status));
    return "<span class='badge bg-{$cls} text-dark' style='font-size:.78rem'>{$label}</span>";
}
function navActive($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active fw-semibold' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Seller Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bb:#89CFF0;--bb-d:#5BA4D9;--bb-dd:#3A7FBF;
            --bb-l:#C5E3F6;--bb-ll:#EDF6FF;--bb-bg:#F4FAFF;
        }
        body { background: var(--bb-bg); min-height: 100vh; }

        .nav-seller {
            background: linear-gradient(135deg, var(--bb) 0%, var(--bb-d) 100%) !important;
            box-shadow: 0 4px 24px rgba(91,164,217,.28);
        }
        .nav-seller .navbar-brand { font-size: 1.15rem; letter-spacing: -.3px; }
        .nav-seller .nav-link {
            color: rgba(255,255,255,.8) !important;
            border-bottom: 2px solid transparent;
            padding: .5rem .85rem !important; margin: 0 2px;
            transition: all .2s; font-size: .92rem;
        }
        .nav-seller .nav-link:hover { color: #fff !important; border-bottom-color: rgba(255,255,255,.5); }
        .nav-seller .nav-link.active { color: #fff !important; border-bottom-color: #fff; }

        .stat-card {
            border: none; border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(91,164,217,.12); }
        .stat-icon {
            width: 54px; height: 54px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; flex-shrink: 0;
        }
        .sec-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.04); }
        .sec-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; }

        .tbl-seller thead th {
            background: var(--bb-ll); color: var(--bb-dd);
            font-weight: 600; font-size: .8rem;
            text-transform: uppercase; letter-spacing: .5px;
            border-bottom: 2px solid var(--bb-l);
        }
        .tbl-seller td { vertical-align: middle; font-size: .9rem; }
        .tbl-seller tbody tr { transition: background .15s; }
        .tbl-seller tbody tr:hover { background: var(--bb-ll); }

        .terlaris-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid #f0f4f8;
        }
        .terlaris-item:last-child { border-bottom: none; }
        .terlaris-item img { width: 42px; height: 42px; object-fit: cover; border-radius: 10px; }
        .rank-num {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; flex-shrink: 0;
        }
        .empty-state { color: var(--bb-l); }

        @media (max-width: 768px) {
            .stat-icon { width: 44px; height: 44px; font-size: 1.15rem; }
            .stat-card .fs-4 { font-size: 1.2rem !important; }
            .stat-card .fs-5 { font-size: .95rem !important; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark nav-seller sticky-top">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php">
            <i class="bi bi-shop-window me-2"></i>Seller Panel
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navSeller">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navSeller">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-2 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= navActive('index.php') ?>" href="index.php"><i class="bi bi-grid-1x2-fill me-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('produk.php') ?>" href="produk.php"><i class="bi bi-box-seam-fill me-1"></i>Produk Saya</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('pesanan.php') ?>" href="pesanan.php"><i class="bi bi-receipt me-1"></i>Pesanan</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3 mt-2 mt-lg-0">
                <span class="text-white-50 d-none d-md-inline" style="font-size:.88rem">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($seller_name) ?>
                </span>
                <a href="../login/logout.php" class="btn btn-outline-light btn-sm px-3" style="font-size:.85rem">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- CONTENT -->
<div class="container py-4">

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background:#EBF5FF;color:#4A90D9"><i class="bi bi-box-seam-fill"></i></div>
                        <div>
                            <div class="text-muted" style="font-size:.78rem">Total Produk</div>
                            <div class="fw-bold fs-4 mb-0"><?= $total_produk ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background:#E6F9F1;color:#22A06B"><i class="bi bi-receipt-cutoff"></i></div>
                        <div>
                            <div class="text-muted" style="font-size:.78rem">Total Pesanan</div>
                            <div class="fw-bold fs-4 mb-0"><?= $total_pesanan ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background:#FFF4E5;color:#E8920B"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="text-muted" style="font-size:.78rem">Pendapatan</div>
                            <div class="fw-bold fs-5 mb-0"><?= formatRupiah($total_pendapatan) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background:#F0EAFF;color:#7C5CFC"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="text-muted" style="font-size:.78rem">Akun</div>
                            <div class="fw-bold mb-0" style="font-size:1rem;color:#22A06B">Aktif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pesanan Terbaru + Produk Terlaris -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card sec-card">
                <div class="card-header px-4 pt-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2" style="color:var(--bb-d)"></i>Pesanan Terbaru</h6>
                        <a href="pesanan.php" class="text-decoration-none" style="color:var(--bb-d);font-size:.85rem">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if ($pesanan_terbaru->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover tbl-seller mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th class="d-none d-sm-table-cell">Pembeli</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($o = $pesanan_terbaru->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-semibold">#<?= $o['id'] ?></td>
                                    <td class="d-none d-sm-table-cell"><?= htmlspecialchars($o['buyer_name']) ?></td>
                                    <td><?= formatRupiah($o['total_amount']) ?></td>
                                    <td><?= statusBadge($o['status']) ?></td>
                                    <td class="text-muted" style="font-size:.82rem"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox empty-state" style="font-size:2.5rem"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada pesanan masuk</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sec-card h-100">
                <div class="card-header px-4 pt-3 pb-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-fire me-2" style="color:#E8920B"></i>Produk Terlaris</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if ($produk_terlaris->num_rows > 0):
                        $rank = 1;
                        $rankColors = ['background:#E8920B','background:#94A3B8','background:#CD7F32'];
                        while ($p = $produk_terlaris->fetch_assoc()): ?>
                    <div class="terlaris-item">
                        <div class="rank-num text-white <?= $rank > 3 ? 'bg-light text-muted' : '' ?>"
                             style="<?= $rank <= 3 ? $rankColors[$rank-1] : '' ?>"><?= $rank ?></div>
                        <?php if ($p['image'] && file_exists('../image/produk/' . $p['image'])): ?>
                            <img src="../image/produk/<?= $p['image'] ?>" alt="">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:42px;height:42px">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate"><?= htmlspecialchars($p['name']) ?></div>
                            <small class="text-muted"><?= $p['total_sold'] ?> terjual</small>
                        </div>
                    </div>
                    <?php $rank++; endwhile; else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bar-chart-line empty-state" style="font-size:2.5rem"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada data penjualan</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
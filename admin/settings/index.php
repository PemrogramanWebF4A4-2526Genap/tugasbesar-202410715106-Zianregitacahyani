<?php
require '../../login/session-admin.php';
require '../../koneksi.php';

// Ambil data settings
$query = "SELECT * FROM settings WHERE id = 1";
$result = mysqli_query($mysqli, $query);
$data = mysqli_fetch_assoc($result);

$success = false;

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name   = trim($_POST['store_name']);
    $whatsapp     = trim($_POST['whatsapp']);
    $address      = trim($_POST['address']);
    $shipping_cost = trim($_POST['shipping_cost']);

    $update = "UPDATE settings SET store_name=?, whatsapp=?, address=?, shipping_cost=? WHERE id=1";
    $stmt = mysqli_prepare($mysqli, $update);

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $store_name,
        $whatsapp,
        $address,
        $shipping_cost
    );

    if (mysqli_stmt_execute($stmt)) {
        $success = true;
        // Refresh data setelah update
        $result = mysqli_query($mysqli, $query);
        $data = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - AquaGas</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #f5fbff;
            --card-bg: #ffffff;
            --accent: #7ec8ff;
            --accent-hover: #5ab8ff;
            --accent-light: rgba(126, 200, 255, 0.12);
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --success-text: #065f46;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .admin-navbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .navbar-brand .brand-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            display: inline-block;
        }

        /* ── Breadcrumb ── */
        .breadcrumb-wrapper {
            padding: 1.25rem 0 0.5rem;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 0.82rem;
        }

        .breadcrumb-item a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--accent-hover);
        }

        .breadcrumb-item.active {
            color: var(--text-dark);
            font-weight: 500;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            color: #cbd5e1;
        }

        /* ── Page Header ── */
        .page-header {
            padding: 1rem 0 1.75rem;
        }

        .page-header h1 {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0;
            padding-left: 2rem;
        }

        /* ── Card ── */
        .settings-card {
            background: var(--card-bg);
            border-radius: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 8px 24px rgba(126, 200, 255, 0.06);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header-custom {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .card-header-custom .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--accent-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-hover);
            font-size: 1.05rem;
        }

        .card-header-custom h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .card-body-custom {
            padding: 2rem 1.75rem;
        }

        /* ── Form ── */
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .form-label i {
            color: var(--accent);
            font-size: 0.95rem;
        }

        .form-control {
            border: 1.5px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: #fafcff;
            transition: all 0.25s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-light);
            background: #fff;
            outline: none;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        .input-group-text {
            background: var(--accent-light);
            border: 1.5px solid var(--border-color);
            border-right: none;
            border-radius: 0.75rem 0 0 0.75rem;
            color: var(--accent-hover);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }

        .input-group .form-control:focus {
            border-color: var(--accent);
        }

        /* ── Buttons ── */
        .btn-accent {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 0.75rem;
            padding: 0.65rem 1.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-accent:hover {
            background: var(--accent-hover);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(126, 200, 255, 0.4);
        }

        .btn-accent:active {
            transform: translateY(0);
        }

        .btn-outline-secondary {
            border-radius: 0.75rem;
            padding: 0.65rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border: 1.5px solid var(--border-color);
            color: var(--text-muted);
            background: var(--card-bg);
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-outline-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: var(--text-dark);
        }

        /* ── Alert ── */
        .alert-success-custom {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            border-radius: 0.85rem;
            padding: 0.85rem 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
            animation: fadeSlideDown 0.35s ease;
        }

        .alert-success-custom .alert-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #a7f3d0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--success-text);
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .alert-success-custom span {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--success-text);
        }

        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--success-text);
            opacity: 0.5;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0;
            transition: opacity 0.2s;
        }

        .alert-close:hover {
            opacity: 1;
        }

        @keyframes fadeSlideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Divider ── */
        .form-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.5rem 0 1.5rem;
        }

        /* ── Footer note ── */
        .footer-note {
            text-align: center;
            padding: 2rem 0 1.5rem;
            color: var(--text-muted);
            font-size: 0.78rem;
        }

        /* ── Responsive ── */
        @media (max-width: 576px) {
            .card-body-custom {
                padding: 1.25rem 1.15rem;
            }

            .card-header-custom {
                padding: 1rem 1.15rem;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .page-header p {
                padding-left: 0;
                font-size: 0.82rem;
            }

            .btn-group-actions {
                flex-direction: column;
            }

            .btn-group-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="admin-navbar">
        <div class="container-xl d-flex align-items-center justify-content-between">
            <a href="../index.php" class="navbar-brand">
                <span class="brand-dot"></span>
                AquaGas Admin
            </a>
            <a href="../../login/logout.php" class="btn btn-sm btn-outline-secondary" style="font-size:0.78rem; padding:0.35rem 0.85rem; border-radius:0.5rem;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container-xl">

        <a
            href="../index.php"
            class="text-decoration-none text-primary fw-semibold d-inline-block mt-3">

            <i class="bi bi-arrow-left"></i>
            Kembali ke Dashboard

        </a>

        <!-- Breadcrumb -->
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">System Settings</li>
                </ol>
            </nav>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>⚙️ System Settings</h1>
            <p>Kelola informasi toko AquaGas</p>
        </div>

        <!-- Alert Sukses -->
        <?php if ($success): ?>
            <div class="alert-success-custom" id="alertSuccess">
                <div class="alert-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <span>Pengaturan berhasil disimpan!</span>
                <button class="alert-close" onclick="document.getElementById('alertSuccess').remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="settings-card mb-4">
            <div class="card-header-custom">
                <div class="icon-circle">
                    <i class="bi bi-gear"></i>
                </div>
                <h5>Informasi Toko</h5>
            </div>
            <div class="card-body-custom">
                <form method="POST" action="" id="formSettings" novalidate>

                    <!-- Nama Toko -->
                    <div class="mb-4">
                        <label for="store_name" class="form-label">
                            <i class="bi bi-shop"></i> Nama Toko
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="store_name"
                            name="store_name"
                            value="<?= htmlspecialchars($data['store_name'] ?? '') ?>"
                            placeholder="Masukkan nama toko"
                            required>
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div class="mb-4">
                        <label for="whatsapp" class="form-label">
                            <i class="bi bi-whatsapp"></i> Nomor WhatsApp
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="whatsapp"
                            name="whatsapp"
                            value="<?= htmlspecialchars($data['whatsapp'] ?? '') ?>"
                            placeholder="Contoh: 6281234567890"
                            required>
                        <div class="form-text" style="font-size:0.76rem; color:var(--text-muted); margin-top:0.35rem;">
                            Gunakan format kode negara tanpa tanda + atau spasi.
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- Alamat Toko -->
                    <div class="mb-4">
                        <label for="address" class="form-label">
                            <i class="bi bi-geo-alt"></i> Alamat Toko
                        </label>
                        <textarea
                            class="form-control"
                            id="address"
                            name="address"
                            rows="3"
                            placeholder="Masukkan alamat lengkap toko"
                            required><?= htmlspecialchars($data['address'] ?? '') ?></textarea>
                    </div>

                    <!-- Ongkir Default -->
                    <div class="mb-4">
                        <label for="shipping_cost" class="form-label">
                            <i class="bi bi-truck"></i> Ongkir Default
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="text"
                                class="form-control"
                                id="shipping_cost"
                                name="shipping_cost"
                                value="<?= htmlspecialchars($data['shipping_cost'] ?? '0') ?>"
                                placeholder="0"
                                required
                                oninput="formatRupiah(this)">
                        </div>
                        <div class="form-text" style="font-size:0.76rem; color:var(--text-muted); margin-top:0.35rem;">
                            Biaya pengiriman default yang akan digunakan saat checkout.
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- Buttons -->
                    <div class="d-flex gap-3 btn-group-actions" style="padding-top:0.5rem;">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house-door"></i>
                            Dashboard
                        </a>
                        <button type="submit" class="btn btn-accent" id="btnSubmit">
                            <i class="bi bi-check2-circle"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            &copy; <?= date('Y') ?> AquaGas &mdash; Admin Panel
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Format input Rupiah
        function formatRupiah(input) {
            let value = input.value.replace(/\D/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            let formatted = parseInt(value, 10).toLocaleString('id-ID');
            input.value = formatted;
        }

        // Hapus format Rupiah saat submit agar tersimpan angka murni
        document.getElementById('formSettings').addEventListener('submit', function(e) {
            const shippingInput = document.getElementById('shipping_cost');
            let rawValue = shippingInput.value.replace(/\./g, '').replace(/,/g, '');
            shippingInput.value = rawValue || '0';
        });

        // Auto-hide alert setelah 5 detik
        const alertEl = document.getElementById('alertSuccess');
        if (alertEl) {
            setTimeout(() => {
                alertEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                alertEl.style.opacity = '0';
                alertEl.style.transform = 'translateY(-8px)';
                setTimeout(() => alertEl.remove(), 400);
            }, 5000);
        }
    </script>
</body>

</html>
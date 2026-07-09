<?php
// ============================================================
// admin/navbar.php — Navbar Baby Blue AquGas Admin
// Session sudah dimulai di halaman yang meng-include file ini.
// Tidak ada koneksi database di file ini.
// ============================================================

// --- Deteksi kedalaman folder agar path relatif selalu benar ---
 $currentPath = $_SERVER['SCRIPT_NAME'];
 $adminPos    = strpos($currentPath, '/admin/');
 $afterAdmin  = substr($currentPath, $adminPos + 7);
 $depth       = substr_count(rtrim($afterAdmin, '/'), '/');
 $base        = str_repeat('../', $depth);

// --- Data admin dari session ---
 $adminName = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

// --- Definisi menu navigasi beserta logika active ---
 $menus = [
    [
        'label'  => 'Dashboard',
        'href'   => $base . 'index.php',
        'icon'   => 'bi-speedometer2',
        'active' => preg_match('#/admin/index\.php$#', $currentPath)
    ],
    [
        'label'  => 'Kelola Produk',
        'href'   => $base . 'produk/index.php',
        'icon'   => 'bi-box-seam',
        'active' => strpos($currentPath, '/admin/produk/') !== false
    ],
    [
        'label'  => 'Kelola Kategori',
        'href'   => $base . 'kategori/index.php',
        'icon'   => 'bi-tags',
        'active' => strpos($currentPath, '/admin/kategori/') !== false
    ],
    [
        'label'  => 'Kelola User',
        'href'   => $base . 'user/index.php',
        'icon'   => 'bi-people',
        'active' => strpos($currentPath, '/admin/user/') !== false
    ],
    [
        'label'  => 'Monitoring Pesanan',
        'href'   => $base . 'pesanan/index.php',
        'icon'   => 'bi-cart-check',
        'active' => strpos($currentPath, '/admin/pesanan/') !== false
    ],
    [
        'label'  => 'Report & Analytics',
        'href'   => $base . 'laporan/index.php',
        'icon'   => 'bi-bar-chart',
        'active' => strpos($currentPath, '/admin/laporan/') !== false
    ],
    [
        'label'  => 'System Settings',
        'href'   => $base . 'settings/index.php',
        'icon'   => 'bi-gear',
        'active' => strpos($currentPath, '/admin/settings/') !== false
    ],
];

 $logoutHref = $base . '../login/logout.php';
?>

<!-- ===================== NAVBAR HTML ===================== -->
<nav class="navbar navbar-expand-lg navbar-aqugas sticky-top">
    <div class="container-fluid px-3 px-lg-4">

        <!-- Brand Kiri -->
        <a class="navbar-brand d-flex align-items-center text-decoration-none" href="<?= $base; ?>index.php">
            <span class="brand-icon">💧</span>
            <span class="brand-text">AquGas <span class="brand-sub">Admin</span></span>
        </a>

        <!-- Tombol Hamburger (Mobile) -->
        <button class="navbar-toggler border-0 shadow-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMainContent"
                aria-controls="navMainContent" aria-expanded="false" aria-label="Toggle navigasi">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Konten yang Bisa Di-collapse -->
        <div class="collapse navbar-collapse" id="navMainContent">

            <!-- Menu Navigasi (Tengah) -->
            <ul class="navbar-nav nav-main-menu mx-auto gap-1">
                <?php foreach ($menus as $menu): ?>
                <li class="nav-item">
                    <a class="nav-link nav-menu-link <?= $menu['active'] ? 'active' : ''; ?>"
                       href="<?= $menu['href']; ?>">
                        <i class="bi <?= $menu['icon']; ?>"></i>
                        <span><?= $menu['label']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Bagian Kanan: Greeting + Logout -->
            <ul class="navbar-nav nav-right-menu flex-row align-items-center gap-3">

                <!-- Greeting (Hanya Desktop) -->
                <li class="nav-item d-none d-lg-flex flex-column align-items-end">
                    <span class="nav-greeting">Halo, <?= $adminName; ?></span>
                    <span class="badge badge-role">Administrator</span>
                </li>

                <!-- Garis Pembatas (Hanya Desktop) -->
                <li class="nav-item d-none d-lg-block">
                    <div class="nav-vdivider"></div>
                </li>

                <!-- Tombol Logout -->
                <li class="nav-item">
                    <a class="nav-link nav-logout-btn" href="<?= $logoutHref; ?>">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-lg-inline d-none">Logout</span>
                    </a>
                </li>

            </ul>

            <!-- Greeting (Hanya Mobile, di bawah menu) -->
            <div class="d-lg-none mt-3 pt-3 border-top border-white border-opacity-25">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="nav-greeting">Halo, <?= $adminName; ?></span>
                        <div class="mt-1">
                            <span class="badge badge-role">Administrator</span>
                        </div>
                    </div>
                    <a class="nav-logout-btn" href="<?= $logoutHref; ?>">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</nav>


<!-- ===================== NAVBAR STYLES ===================== -->
<style>
    /* --- Palette Warna Baby Blue --- */
    :root {
        --baby-50:  #f0f9ff;
        --baby-100: #e0f2fe;
        --baby-200: #bae6fd;
        --baby-300: #7dd3fc;
        --baby-400: #38bdf8;
        --baby-500: #0ea5e9;
        --baby-600: #0284c7;
        --baby-800: #075985;
        --baby-900: #0c4a6e;

        --soft-red:     #fca5a5;
        --soft-red-mid: #f87171;
        --red:          #ef4444;

        --nav-height: 64px;
        --radius:     10px;
        --transition: 0.22s ease;
    }

    /* --- Body: background utama seluruh halaman --- */
    body {
        background-color: var(--baby-50);
        min-height: 100vh;
    }

    /* --- Navbar Utama --- */
    .navbar-aqugas {
        background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 40%, #0ea5e9 100%);
        box-shadow: 0 2px 20px rgba(14, 165, 233, 0.22);
        padding: 0;
        min-height: var(--nav-height);
        animation: navSlideIn 0.45s cubic-bezier(0.22, 1, 0.36, 1);
    }

    @keyframes navSlideIn {
        from { transform: translateY(-100%); opacity: 0; }
        to   { transform: translateY(0);     opacity: 1; }
    }

    /* --- Brand --- */
    .brand-icon {
        font-size: 1.55rem;
        margin-right: 0.35rem;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.12));
    }

    .brand-text {
        font-size: 1.22rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.01em;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .brand-sub {
        font-weight: 400;
        opacity: 0.88;
        font-size: 1.05rem;
    }

    /* --- Toggler --- */
    .navbar-aqugas .navbar-toggler {
        color: #ffffff;
        padding: 0.35rem 0.55rem;
        line-height: 1;
        transition: transform var(--transition);
    }

    .navbar-aqugas .navbar-toggler:focus {
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    }

    .navbar-aqugas .navbar-toggler:hover {
        transform: scale(1.08);
    }

    /* --- Link Menu --- */
    .nav-menu-link {
        display: flex !important;
        align-items: center;
        gap: 0.4rem;
        padding: 0.48rem 0.85rem !important;
        color: rgba(255, 255, 255, 0.82) !important;
        font-size: 0.87rem;
        font-weight: 500;
        border-radius: var(--radius);
        transition: all var(--transition);
        white-space: nowrap;
        position: relative;
    }

    .nav-menu-link i {
        font-size: 0.98rem;
        transition: transform var(--transition);
    }

    .nav-menu-link:hover {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.16);
    }

    .nav-menu-link:hover i {
        transform: scale(1.12);
    }

    /* State aktif */
    .nav-menu-link.active {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.24);
        font-weight: 600;
        box-shadow: inset 0 -2.5px 0 rgba(255, 255, 255, 0.55);
    }

    .nav-menu-link.active i {
        transform: scale(1.05);
    }

    /* --- Greeting & Badge --- */
    .nav-greeting {
        color: rgba(255, 255, 255, 0.95);
        font-size: 0.84rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .badge-role {
        background-color: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        font-size: 0.63rem;
        font-weight: 600;
        padding: 0.22em 0.6em;
        border-radius: 6px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        backdrop-filter: blur(4px);
    }

    /* --- Garis Pembatas Vertikal --- */
    .nav-vdivider {
        width: 1px;
        height: 28px;
        background-color: rgba(255, 255, 255, 0.28);
        border-radius: 1px;
    }

    /* --- Tombol Logout --- */
    .nav-logout-btn {
        display: inline-flex !important;
        align-items: center;
        gap: 0.4rem;
        padding: 0.42rem 0.8rem !important;
        background-color: rgba(252, 165, 165, 0.85) !important;
        color: var(--red) !important;
        font-size: 0.84rem;
        font-weight: 600;
        border-radius: var(--radius);
        transition: all var(--transition);
        white-space: nowrap;
        text-decoration: none;
    }

    .nav-logout-btn i {
        font-size: 0.95rem;
    }

    .nav-logout-btn:hover {
        background-color: var(--red) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
    }

    /* ==================== RESPONSIVE ==================== */
    @media (max-width: 991.98px) {
        .navbar-aqugas .navbar-collapse {
            background: linear-gradient(180deg, #38bdf8 0%, #0ea5e9 100%);
            margin: 0 -0.75rem 0;
            padding: 0.85rem 0.75rem 1rem;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.25);
        }

        .nav-main-menu {
            gap: 0.2rem !important;
            margin: 0 !important;
        }

        .nav-menu-link {
            padding: 0.6rem 0.9rem !important;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .nav-right-menu {
            display: none !important; /* Sembunyikan versi desktop di mobile */
        }

        /* Logout di bagian bawah collapse mobile sudah di-handle oleh div d-lg-none */
    }

    @media (min-width: 992px) {
        .nav-main-menu {
            margin-left: 1.5rem !important;
        }
    }
</style>
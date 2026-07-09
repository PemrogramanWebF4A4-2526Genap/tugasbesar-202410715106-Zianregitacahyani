<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php

$unreadNotif = 0;

if (isset($_SESSION['login']) && $_SESSION['role'] == 'buyer') {

    include 'koneksi.php';

    $userId = $_SESSION['user_id'];

    $qNotif = mysqli_query(
        $mysqli,
        "SELECT COUNT(*) AS total
         FROM notifications
         WHERE user_id='$userId'
         AND is_read=0"
    );

    $dNotif = mysqli_fetch_assoc($qNotif);

    $unreadNotif = $dNotif['total'];
}

?>

<style>
    .navbar-custom {
        background: linear-gradient(135deg, #8ec5ff, #72b7ff);
        box-shadow: 0 8px 25px rgba(126, 200, 255, .25);
        padding: 14px 0;
    }

    .logo-brand {
        font-size: 1.6rem;
        font-weight: 700;
        color: white !important;
        text-decoration: none;
    }

    .nav-link-custom {
        color: white !important;
        font-weight: 500;
        padding: 10px 18px !important;
        border-radius: 12px;
        transition: 0.3s;
    }

    .nav-link-custom:hover {
        background: rgba(255, 255, 255, .2);
    }

    .search-box {
        border-radius: 15px;
        border: none;
    }

    .search-btn {
        border-radius: 15px;
        background: white;
        color: #4f9fff;
        border: none;
        font-weight: 600;
    }

    .search-btn:hover {
        background: #eef8ff;
    }

    .user-badge {
        background: rgba(255, 255, 255, .2);
        color: white;
        padding: 8px 15px;
        border-radius: 15px;
        font-size: .9rem;
        margin-right: 10px;
    }

    .logout-btn {
        background: #ffb3b3;
        color: #8b0000 !important;
        border-radius: 15px;
        padding: 8px 18px !important;
        font-weight: 600;
    }

    .logout-btn:hover {
        background: #ff9d9d;
    }
</style>


<nav class="navbar navbar-expand-lg navbar-custom sticky-top">

    <div class="container">

        <!-- LOGO -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') { ?>

            <a class="navbar-brand logo-brand" href="/TugasBESARPemweb/Penjual/index.php">
                💧 AquaGas
            </a>

        <?php } else { ?>

            <a class="navbar-brand logo-brand" href="/TugasBESARPemweb/index.php">
                💧 AquaGas
            </a>

        <?php } ?>

        <button
            class="navbar-toggler bg-white"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarSupportedContent">

            <!-- MENU -->
            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') { ?>

                        <a class="nav-link nav-link-custom"
                            href="/TugasBESARPemweb/Penjual/index.php">
                            🏠 Home
                        </a>

                    <?php } else { ?>

                        <a class="nav-link nav-link-custom"
                            href="/TugasBESARPemweb/index.php">
                            🏠 Home
                        </a>

                    <?php } ?>
                </li>

                <?php if ($_SESSION['role'] == 'buyer') { ?>

                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="produk-user.php">
                            🛍️ Belanja
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="keranjang.php">
                            🛒 Keranjang
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="pesanan-saya.php">
                            📦 Pesanan Saya
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-custom notif-link"
                            href="/TugasBESARPemweb/notifikasi.php">

                            🔔 Notifikasi

                            <?php if ($unreadNotif > 0) { ?>

                                <span class="notif-badge">
                                    <?= $unreadNotif ?>
                                </span>

                            <?php } ?>

                        </a>
                    </li>

                <?php } ?>

            </ul>


            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'buyer') { ?>

                <!-- SEARCH -->
                <form class="d-flex me-3"
                    method="GET"
                    action="/TugasBESARPemweb/produk-user.php">

                    <input
                        class="form-control me-2 search-box"
                        type="search"
                        name="keyword"
                        placeholder="Cari produk...">

                    <button
                        class="btn search-btn"
                        type="submit">

                        🔍

                    </button>

                </form>

            <?php } ?>


            <!-- USER -->
            <div class="d-flex align-items-center">

                <div class="user-badge">
                    👤 <?= $_SESSION['username'] ?>
                </div>

                <?php
                $logoutLink = "login/logout.php";

                if (strpos($_SERVER['PHP_SELF'], "/Penjual/") !== false) {
                    $logoutLink = "../login/logout.php";
                }
                ?>

                <a
                    href="/TugasBESARPemweb/login/logout.php"
                    class="nav-link logout-btn">

                    🚪 Logout

                </a>

            </div>

        </div>

    </div>

</nav>
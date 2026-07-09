<?php
// ============================================
// File   : review.php
// Fungsi : Halaman untuk BUYER memberikan review produk
//          yang sudah dibeli dan berstatus 'selesai'.
// Stack  : PHP Native + MySQLi OOP + Bootstrap 5
// ============================================

// ===== Aktifkan session =====
session_start();

// ===== Include koneksi database =====
// File koneksi.php harus menghasilkan object $mysqli (mysqli OOP)
require_once 'koneksi.php';

// ===========================================================
// [1] CEK LOGIN & ROLE
// Hanya buyer yang sudah login boleh mengakses halaman ini.
// ===========================================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    // Belum login -> tendang ke halaman login
    header("Location: login/login.php");
    exit;
}

// Cek role harus 'buyer'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    // Bukan buyer -> tendang ke index
    header("Location: index.php");
    exit;
}

// Simpan data session ke variabel lokal
$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// ===========================================================
// [2] AMBIL product_id DARI URL
// Contoh: review.php?product_id=1
// ===========================================================
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    die("Product ID tidak valid.");
}
$product_id = (int) $_GET['product_id'];

// ===========================================================
// [3] CARI USER_ID BERDASARKAN USERNAME (name ATAU email)
// ===========================================================
$stmt = $mysqli->prepare("SELECT id FROM users WHERE name = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$resultUser = $stmt->get_result();

if ($resultUser->num_rows === 0) {
    die("User tidak ditemukan.");
}

$userData = $resultUser->fetch_assoc();
$user_id  = (int) $userData['id'];
$stmt->close();

// ===========================================================
// [4] AMBIL INFO PRODUK (hanya untuk ditampilkan di form)
// ===========================================================
$stmt = $mysqli->prepare("SELECT id, name, price, image FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$resultProduct = $stmt->get_result();

if ($resultProduct->num_rows === 0) {
    die("Produk tidak ditemukan.");
}
$product = $resultProduct->fetch_assoc();
$stmt->close();

// ===========================================================
// [5] VALIDASI: BUYER PERNAH BELI PRODUK & STATUS 'selesai'
// JOIN orders + order_items
//   orders.buyer_id      = user login
//   order_items.product_id = product_id
//   orders.status        = 'selesai'
// ===========================================================
$stmt = $mysqli->prepare("
    SELECT o.id AS order_id
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.buyer_id = ?
      AND oi.product_id = ?
      AND o.status = 'selesai'
    LIMIT 1
");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$resultOrder = $stmt->get_result();
$bolehReview = ($resultOrder->num_rows > 0); // true jika pernah beli & selesai
$stmt->close();

// ===========================================================
// [6] CEK: USER SUDAH PERNAH REVIEW PRODUK INI?
// Jika sudah -> tidak boleh review lagi.
// ===========================================================
$stmt = $mysqli->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$resultReview = $stmt->get_result();
$sudahReview = ($resultReview->num_rows > 0);
$stmt->close();

// ===========================================================
// [7] PROSES SUBMIT FORM REVIEW (POST)
// ===========================================================
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bolehReview && !$sudahReview) {

    // Ambil & sanitasi input
    $rating  = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $comment = trim($_POST['comment'] ?? '');

    // Validasi sederhana
    if ($rating < 1 || $rating > 5) {
        $error = "Rating harus antara 1 sampai 5.";
    } elseif ($comment === '') {
        $error = "Komentar tidak boleh kosong.";
    } else {
        // ===========================================================
        // [8] INSERT REVIEW KE DATABASE (prepared statement OOP)
        // ===========================================================
        $stmt = $mysqli->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        // i = integer, i = integer, i = integer (rating), s = string (comment)
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);

        if ($stmt->execute()) {
            $stmt->close();

            // ===========================================================
            // [9] REDIRECT setelah berhasil simpan
            // ===========================================================
            header("Location: pesanan-saya.php?review=success");
            exit;
        } else {
            $error = "Gagal menyimpan review: " . htmlspecialchars($mysqli->error);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Produk - <?= htmlspecialchars($product['name']) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Background gradient halus */
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* ===== Star Rating Interaktif =====
       Trik: gunakan flex-direction: row-reverse agar urutan
       bintang visual (1-5) dari kiri ke kanan, walau DOM 5-1. */
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: .25rem;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 2.6rem;
            color: #dee2e6;
            transition: color .15s, transform .15s;
            line-height: 1;
        }

        .star-rating label::before {
            content: '\F588';
            font-family: 'bootstrap-icons';
        }

        /* Saat input di-check -> label setelahnya (DOM) berubah jadi star-fill */
        .star-rating input:checked~label::before {
            content: '\F586';
            color: #ffc107;
        }

        /* Efek hover */
        .star-rating label:hover,
        .star-rating label:hover~label {
            transform: scale(1.05);
        }

        .star-rating label:hover::before,
        .star-rating label:hover~label::before {
            content: '\F586';
            color: #ffc107;
        }

        .product-img {
            width: 84px;
            height: 84px;
            object-fit: cover;
            border-radius: 10px;
        }

        .card {
            border: none;
        }
    </style>
</head>

<body>

    <!-- ===== Navbar Sederhana ===== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-bag-heart-fill me-1"></i> Toko Online
            </a>
            <div class="d-flex align-items-center text-white">
                <i class="bi bi-person-circle me-1"></i>
                <span><?= htmlspecialchars($username) ?>
                    <span class="badge bg-light text-primary"><?= htmlspecialchars($role) ?></span>
                </span>
            </div>
        </div>
    </nav>

    <!-- ===== Konten Utama ===== -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <div class="card shadow rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <h3 class="fw-bold mb-1">
                            <i class="bi bi-chat-square-heart-fill text-primary"></i>
                            Beri Review Produk
                        </h3>
                        <p class="text-muted mb-4">Bagikan pengalamanmu setelah memakai produk ini.</p>

                        <!-- ===== Kartu Info Produk ===== -->
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3 border">

                            <?php if (!empty($product['image']) && file_exists('image/produk/' . $product['image'])): ?>

                                <img src="image/produk/<?= htmlspecialchars($product['image']) ?>"
                                    alt="product"
                                    class="product-img me-3">

                            <?php else: ?>

                                <div class="product-img bg-secondary d-flex align-items-center justify-content-center me-3">
                                    <i class="bi bi-image text-white fs-3"></i>
                                </div>

                            <?php endif; ?>
                            <div>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($product['name']) ?></h6>
                                <div class="text-primary fw-semibold">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                                <small class="text-muted">Product ID #<?= (int)$product['id'] ?></small>
                            </div>
                        </div>

                        <?php if (!$bolehReview): ?>
                            <!-- =========================================================
                 [A] BELUM BOLEH REVIEW (belum beli / status belum selesai)
                 ========================================================= -->
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div>Anda belum dapat memberikan review untuk produk ini.</div>
                            </div>
                            <a href="pesanan-saya.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Pesanan Saya
                            </a>

                        <?php elseif ($sudahReview): ?>
                            <!-- =========================================================
                 [B] SUDAH PERNAH REVIEW
                 ========================================================= -->
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                <div>Anda sudah memberikan review untuk produk ini.</div>
                            </div>
                            <a href="pesanan-saya.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Pesanan Saya
                            </a>

                        <?php else: ?>
                            <!-- =========================================================
                 [C] FORM REVIEW
                 ========================================================= -->

                            <?php if ($error): ?>
                                <div class="alert alert-danger d-flex align-items-center">
                                    <i class="bi bi-x-circle-fill me-2"></i>
                                    <div><?= $error ?></div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">

                                <!-- ===== Rating 1-5 Bintang ===== -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        Rating <span class="text-danger">*</span>
                                    </label>
                                    <div class="star-rating" id="starRating">
                                        <input type="radio" name="rating" id="star5" value="5" required>
                                        <label for="star5" title="5 bintang"></label>
                                        <input type="radio" name="rating" id="star4" value="4">
                                        <label for="star4" title="4 bintang"></label>
                                        <input type="radio" name="rating" id="star3" value="3">
                                        <label for="star3" title="3 bintang"></label>
                                        <input type="radio" name="rating" id="star2" value="2">
                                        <label for="star2" title="2 bintang"></label>
                                        <input type="radio" name="rating" id="star1" value="1">
                                        <label for="star1" title="1 bintang"></label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Pilih 1 (kurang puas) sampai 5 (sangat puas).</small>
                                </div>

                                <!-- ===== Komentar ===== -->
                                <div class="mb-4">
                                    <label for="comment" class="form-label fw-semibold">
                                        Komentar <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="comment" id="comment" rows="4"
                                        class="form-control"
                                        placeholder="Ceritakan kualitas, kemasan, kecepatan kirim, dll..."
                                        required></textarea>
                                </div>

                                <!-- ===== Tombol Aksi ===== -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-3">
                                        <i class="bi bi-send-fill me-1"></i> Simpan Review
                                    </button>
                                    <a href="pesanan-saya.php" class="btn btn-link text-decoration-none text-muted">
                                        <i class="bi bi-x-lg"></i> Batal
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
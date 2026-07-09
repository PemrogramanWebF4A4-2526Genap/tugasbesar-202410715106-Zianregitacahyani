<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login/index.php");
    exit();
}

require_once '../koneksi.php';

$seller_id   = $_SESSION['user_id'];
$seller_name = $_SESSION['username'];
$msg = '';
$msgType = '';

$uploadDir = '../image/produk/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// ========== TAMBAH PRODUK ==========
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (int)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);

    $image = '';
    $uploadOk = true;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $msg = 'Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.';
            $msgType = 'danger';
            $uploadOk = false;
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $msg = 'Ukuran gambar melebihi 2MB.';
            $msgType = 'danger';
            $uploadOk = false;
        } else {
            $image = time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }
    }

    if ($uploadOk && empty($msg)) {
        $q = $mysqli->prepare("INSERT INTO products (seller_id, name, description, price, stock, category_id, image) VALUES (?,?,?,?,?,?,?)");
        $q->bind_param("issiiis", $seller_id, $name, $description, $price, $stock, $category_id, $image);
        $q->execute();
        $msg = 'Produk berhasil ditambahkan.';
        $msgType = 'success';
    }
}

// ========== EDIT PRODUK ==========
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id          = (int)$_POST['id'];
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (int)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);

    // Verifikasi kepemilikan
    $chk = $mysqli->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
    $chk->bind_param("ii", $id, $seller_id);
    $chk->execute();
    $old = $chk->get_result()->fetch_assoc();

    if ($old) {
        $image = $old['image'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
                // Hapus gambar lama
                if ($image && file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
                $image = time() . '_' . rand(1000, 9999) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
        }

        $q = $mysqli->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE id = ? AND seller_id = ?");
        $q->bind_param("ssiiisii", $name, $description, $price, $stock, $category_id, $image, $id, $seller_id);
        $q->execute();
        $msg = 'Produk berhasil diperbarui.';
        $msgType = 'success';
    }
}

// ========== HAPUS PRODUK ==========
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];

    $chk = $mysqli->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
    $chk->bind_param("ii", $id, $seller_id);
    $chk->execute();
    $old = $chk->get_result()->fetch_assoc();

    if ($old) {
        if ($old['image'] && file_exists($uploadDir . $old['image'])) {
            unlink($uploadDir . $old['image']);
        }
        $q = $mysqli->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $q->bind_param("ii", $id, $seller_id);
        $q->execute();
        $msg = 'Produk berhasil dihapus.';
        $msgType = 'success';
    }
}

// ========== AMBIL DATA ==========
$categories = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");

$products = $mysqli->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.seller_id = ?
    ORDER BY p.id DESC
");
$products->bind_param("i", $seller_id);
$products->execute();
$res_products = $products->get_result();

function formatRupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function navActive($p)
{
    return basename($_SERVER['PHP_SELF']) === $p ? 'active fw-semibold' : '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Saya - Seller Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bb: #89CFF0;
            --bb-d: #5BA4D9;
            --bb-dd: #3A7FBF;
            --bb-l: #C5E3F6;
            --bb-ll: #EDF6FF;
            --bb-bg: #F4FAFF;
        }

        body {
            background: var(--bb-bg);
            min-height: 100vh;
        }

        .nav-seller {
            background: linear-gradient(135deg, var(--bb) 0%, var(--bb-d) 100%) !important;
            box-shadow: 0 4px 24px rgba(91, 164, 217, .28);
        }

        .nav-seller .navbar-brand {
            font-size: 1.15rem;
            letter-spacing: -.3px;
        }

        .nav-seller .nav-link {
            color: rgba(255, 255, 255, .8) !important;
            border-bottom: 2px solid transparent;
            padding: .5rem .85rem !important;
            margin: 0 2px;
            transition: all .2s;
            font-size: .92rem;
        }

        .nav-seller .nav-link:hover {
            color: #fff !important;
            border-bottom-color: rgba(255, 255, 255, .5);
        }

        .nav-seller .nav-link.active {
            color: #fff !important;
            border-bottom-color: #fff;
        }

        .sec-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
        }

        .tbl-seller thead th {
            background: var(--bb-ll);
            color: var(--bb-dd);
            font-weight: 600;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid var(--bb-l);
        }

        .tbl-seller td {
            vertical-align: middle;
            font-size: .9rem;
        }

        .tbl-seller tbody tr {
            transition: background .15s;
        }

        .tbl-seller tbody tr:hover {
            background: var(--bb-ll);
        }

        .btn-add {
            background: linear-gradient(135deg, var(--bb-d), var(--bb-dd));
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .55rem 1.4rem;
            font-size: .88rem;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-add:hover {
            color: #fff;
            box-shadow: 0 4px 16px rgba(58, 127, 191, .35);
            transform: translateY(-1px);
        }

        .btn-action {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all .15s;
            font-size: .9rem;
        }

        .btn-edit {
            background: #EBF5FF;
            color: #4A90D9;
        }

        .btn-edit:hover {
            background: #4A90D9;
            color: #fff;
        }

        .btn-del {
            background: #FFF0F0;
            color: #E03131;
        }

        .btn-del:hover {
            background: #E03131;
            color: #fff;
        }

        .img-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid var(--bb-l);
        }

        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
        }

        .modal-header {
            border-bottom: 1px solid #f0f0f0;
            padding: 1.2rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 1rem 1.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e0e7ee;
            padding: .6rem .9rem;
            font-size: .9rem;
            transition: border-color .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--bb-d);
            box-shadow: 0 0 0 3px rgba(91, 164, 217, .15);
        }

        .form-label {
            font-weight: 600;
            font-size: .85rem;
            color: #444;
        }

        .preview-img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 12px;
            border: 2px dashed var(--bb-l);
            background: var(--bb-ll);
            display: none;
        }

        .preview-img.show {
            display: block;
        }

        .stock-badge {
            padding: .2rem .65rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .stock-ok {
            background: #E6F9F1;
            color: #22A06B;
        }

        .stock-low {
            background: #FFF4E5;
            color: #E8920B;
        }

        .stock-empty {
            background: #FFF0F0;
            color: #E03131;
        }

        .empty-state {
            color: var(--bb-l);
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
                    <li class="nav-item"><a class="nav-link <?= navActive('index.php') ?>" href="index.php"><i class="bi bi-grid-1x2-fill me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= navActive('produk.php') ?>" href="produk.php"><i class="bi bi-box-seam-fill me-1"></i>Produk Saya</a></li>
                    <li class="nav-item"><a class="nav-link <?= navActive('pesanan.php') ?>" href="pesanan.php"><i class="bi bi-receipt me-1"></i>Pesanan</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 mt-2 mt-lg-0">
                    <span class="text-white-50 d-none d-md-inline" style="font-size:.88rem"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($seller_name) ?></span>
                    <a href="../login/logout.php" class="btn btn-outline-light btn-sm px-3" style="font-size:.85rem"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="container py-4">

        <!-- Alert -->
        <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show d-flex align-items-center border-0 rounded-3" style="font-size:.9rem">
                <i class="bi bi-<?= $msgType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
                <?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1" style="color:var(--bb-dd)"><i class="bi bi-box-seam-fill me-2"></i>Produk Saya</h5>
                <small class="text-muted">Kelola semua produk yang Anda jual</small>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalProduk" onclick="resetForm()">
                <i class="bi bi-plus-lg me-1"></i>Tambah Produk
            </button>
        </div>

        <!-- Tabel Produk -->
        <div class="card sec-card">
            <div class="card-body p-0">
                <?php if ($res_products->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover tbl-seller mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px">Gambar</th>
                                    <th>Nama Produk</th>
                                    <th class="d-none d-md-table-cell">Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th style="width:100px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = $res_products->fetch_assoc()):
                                    $stockCls = $p['stock'] <= 0 ? 'stock-empty' : ($p['stock'] <= 5 ? 'stock-low' : 'stock-ok');
                                    $stockLbl = $p['stock'] <= 0 ? 'Habis' : $p['stock'];
                                ?>
                                    <tr>
                                        <td>
                                            <?php if ($p['image'] && file_exists($uploadDir . $p['image'])): ?>
                                                <img src="<?= $uploadDir . $p['image'] ?>" class="img-thumb" alt="">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center img-thumb"><i class="bi bi-image text-muted"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                                            <small class="text-muted text-truncate d-block" style="max-width:220px"><?= htmlspecialchars(mb_substr($p['description'], 0, 60)) ?><?= mb_strlen($p['description']) > 60 ? '...' : '' ?></small>
                                        </td>
                                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                                        <td class="fw-semibold"><?= formatRupiah($p['price']) ?></td>
                                        <td><span class="stock-badge <?= $stockCls ?>"><?= $stockLbl ?></span></td>
                                        <td class="text-center">
                                            <button class="btn-action btn-edit me-1" title="Edit" onclick='editProduk(<?= json_encode($p) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn-action btn-del" title="Hapus" onclick="confirmDelete(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>')">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam empty-state" style="font-size:3rem"></i>
                        <p class="text-muted mt-3 mb-0">Anda belum memiliki produk.</p>
                        <button class="btn btn-add mt-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalProduk" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Produk Pertama
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH / EDIT -->
    <div class="modal fade" id="modalProduk" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="formProduk" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="formId" value="">
                    <div class="modal-header">
                        <h6 class="fw-bold mb-0" id="modalTitle">
                            <i class="bi bi-plus-circle me-2" style="color:var(--bb-d)"></i>Tambah Produk Baru
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="formName" class="form-control" placeholder="Masukkan nama produk" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" id="formCategory" class="form-select">
                                    <option value="0">-- Pilih --</option>
                                    <?php if ($categories): while ($cat = $categories->fetch_assoc()): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endwhile;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="price" id="formPrice" class="form-control" placeholder="0" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stock" id="formStock" class="form-control" placeholder="0" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" id="formDesc" class="form-control" rows="3" placeholder="Deskripsi produk..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Gambar Produk</label>
                                <input type="file" name="image" id="formImage" class="form-control" accept="image/*">
                                <div class="form-text">Format: JPG, PNG, GIF, WebP. Maks 2MB.</div>
                                <img id="previewImg" class="preview-img mt-2" alt="Preview">
                                <div id="currentImgWrap" class="mt-2 d-none">
                                    <small class="text-muted">Gambar saat ini:</small><br>
                                    <img id="currentImg" src="" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px">Batal</button>
                        <button type="submit" class="btn btn-add" id="formSubmitBtn">
                            <i class="bi bi-check-lg me-1"></i>Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:60px;height:60px;background:#FFF0F0">
                            <i class="bi bi-trash3-fill text-danger" style="font-size:1.5rem"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-2">Hapus Produk?</h6>
                    <p class="text-muted mb-0" style="font-size:.88rem">"<span id="hapusNama"></span>" akan dihapus permanen.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:10px">Batal</button>
                    <form method="POST" id="formHapus">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="hapusId">
                        <button type="submit" class="btn btn-danger px-4" style="border-radius:10px;font-size:.88rem">
                            <i class="bi bi-trash3 me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('formProduk').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('formId').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2" style="color:var(--bb-d)"></i>Tambah Produk Baru';
            document.getElementById('formSubmitBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan Produk';
            document.getElementById('previewImg').classList.remove('show');
            document.getElementById('currentImgWrap').classList.add('d-none');
        }

        function editProduk(p) {
            document.getElementById('formAction').value = 'edit';
            document.getElementById('formId').value = p.id;
            document.getElementById('formName').value = p.name;
            document.getElementById('formPrice').value = p.price;
            document.getElementById('formStock').value = p.stock;
            document.getElementById('formDesc').value = p.description || '';
            document.getElementById('formCategory').value = p.category_id || 0;
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square me-2" style="color:var(--bb-d)"></i>Edit Produk';
            document.getElementById('formSubmitBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Perbarui Produk';
            document.getElementById('previewImg').classList.remove('show');

            const wrap = document.getElementById('currentImgWrap');
            const img = document.getElementById('currentImg');
            if (p.image) {
                img.src = '../image/produk/' + p.image;
                wrap.classList.remove('d-none');
            } else {
                wrap.classList.add('d-none');
            }
            console.log(p);
            new bootstrap.Modal(document.getElementById('modalProduk')).show();
        }

        function confirmDelete(id, name) {
            document.getElementById('hapusId').value = id;
            document.getElementById('hapusNama').textContent = name;
            new bootstrap.Modal(document.getElementById('modalHapus')).show();
        }

        document.getElementById('formImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('previewImg');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.src = ev.target.result;
                    preview.classList.add('show');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.remove('show');
            }
        });
    </script>
</body>

</html>
<?php
require '../../login/session-admin.php';
require '../../koneksi.php';

// Handle delete
if (isset($_POST['hapus_id'])) {
    $id = (int) $_POST['hapus_id'];
    $check = mysqli_query($mysqli, "SELECT role FROM users WHERE id = $id");
    $data = mysqli_fetch_assoc($check);
    if ($data && $data['role'] !== 'admin') {
        mysqli_query($mysqli, "DELETE FROM users WHERE id = $id");
        header("Location: index.php?deleted=1");
        exit();
    } else {
        header("Location: index.php?error=forbidden");
        exit();
    }
}

$query  = mysqli_query($mysqli, "SELECT * FROM users ORDER BY id DESC");
$users  = [];
while ($row = mysqli_fetch_assoc($query)) {
    $users[] = $row;
}

$showToast = false;
$toastMsg  = '';
$toastType = '';

if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $showToast = true;
    $toastMsg  = 'Pengguna berhasil dihapus.';
    $toastType = 'success';
}
if (isset($_GET['error']) && $_GET['error'] == 'forbidden') {
    $showToast = true;
    $toastMsg  = 'Akun admin tidak dapat dihapus.';
    $toastType = 'danger';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User — AquaGas Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f5fbff;
            --bg-card: #ffffff;
            --primary: #3a9bd5;
            --primary-light: #e0f1fd;
            --primary-softer: #eef8ff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-soft: #e2eaf3;
            --shadow-card: 0 4px 32px rgba(58, 155, 213, 0.08), 0 1px 4px rgba(0, 0, 0, 0.04);
            --radius-card: 30px;
            --radius-inner: 18px;
            --radius-btn: 20px;
            --badge-admin-bg: #dbeafe;
            --badge-admin-text: #2563eb;
            --badge-user-bg: #dcfce7;
            --badge-user-text: #16a34a;
            --btn-danger-bg: #fecaca;
            --btn-danger-text: #dc2626;
            --btn-danger-hover: #fca5a5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-body);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 40px 20px 60px;
        }

        @media (min-width: 768px) {
            body {
                padding: 50px 40px 80px;
            }
        }

        @media (min-width: 1200px) {
            body {
                padding: 60px 60px 100px;
            }
        }

        /* ── Card ── */
        .card-main {
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
            padding: 40px 32px;
            max-width: 1100px;
            margin: 0 auto;
            animation: fadeUp .5s ease both;
        }

        @media (min-width: 768px) {
            .card-main {
                padding: 48px 44px;
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Header ── */
        .page-header {
            margin-bottom: 36px;
        }

        .page-header h1 {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        @media (min-width: 768px) {
            .page-header h1 {
                font-size: 2.1rem;
            }
        }

        .page-header p {
            font-size: 0.92rem;
            color: var(--text-muted);
            font-weight: 500;
            margin: 0;
        }

        /* ── Info bar ── */
        .info-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-light);
            border: 1px solid #bfdbfe;
            border-radius: var(--radius-inner);
            padding: 12px 18px;
            margin-bottom: 28px;
            font-size: 0.84rem;
            color: #1e40af;
            font-weight: 500;
        }

        .info-bar i {
            font-size: 1.1rem;
        }

        /* ── Table wrapper ── */
        .table-wrapper {
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-inner);
            overflow: hidden;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .table-custom thead {
            background: linear-gradient(135deg, #f0f8ff 0%, #e0f1fd 100%);
        }

        .table-custom thead th {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-soft);
            white-space: nowrap;
        }

        .table-custom tbody tr {
            transition: background 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background: var(--primary-softer);
        }

        .table-custom tbody td {
            padding: 16px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
            vertical-align: middle;
        }

        .table-custom tbody td:nth-child(1) {
            color: var(--text-muted);
            font-weight: 600;
            width: 50px;
            text-align: center;
        }

        /* ── Badges ── */
        .badge-role {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .badge-role.admin {
            background: var(--badge-admin-bg);
            color: var(--badge-admin-text);
        }

        .badge-role.user {
            background: var(--badge-user-bg);
            color: var(--badge-user-text);
        }

        .badge-role i {
            font-size: 0.72rem;
        }

        /* ── Delete button ── */
        .btn-hapus {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 20px;
            border-radius: var(--radius-btn);
            border: none;
            background: var(--btn-danger-bg);
            color: var(--btn-danger-text);
            font-size: 0.82rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-hapus:hover {
            background: var(--btn-danger-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        .btn-hapus:active {
            transform: translateY(0);
        }

        .btn-hapus i {
            font-size: 0.88rem;
        }

        /* ── Disabled text ── */
        .text-disabled {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
            font-style: italic;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .text-disabled i {
            font-size: 0.85rem;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 70px 20px;
        }

        .empty-state .icon-wrap {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0f1fd, #f0f8ff);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(58, 155, 213, 0.12);
        }

        .empty-state .icon-wrap i {
            font-size: 2.2rem;
            color: var(--primary);
        }

        .empty-state h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: 0.88rem;
            color: var(--text-muted);
            font-weight: 500;
            margin: 0;
        }

        /* ── Toast ── */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 22px;
            border-radius: 16px;
            font-size: 0.88rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            animation: slideIn .35s ease both;
            min-width: 280px;
        }

        .toast-item.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .toast-item.danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .toast-item i {
            font-size: 1.15rem;
        }

        .toast-item.fade-out {
            animation: slideOut .3s ease both;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(40px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(40px);
            }
        }

        /* ── Modal ── */
        .modal-content {
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #fff1f2, #ffe4e6);
            border-bottom: none;
            padding: 28px 32px 20px;
        }

        .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header .modal-title i {
            font-size: 1.3rem;
        }

        .modal-header .btn-close {
            opacity: 0.5;
        }

        .modal-body {
            padding: 28px 32px;
            font-size: 0.92rem;
            color: var(--text-dark);
            font-weight: 500;
            line-height: 1.7;
        }

        .modal-body .user-name-highlight {
            display: inline-block;
            background: #fef2f2;
            color: #dc2626;
            padding: 2px 12px;
            border-radius: 8px;
            font-weight: 700;
        }

        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 18px 32px 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-modal-cancel {
            padding: 10px 24px;
            border-radius: var(--radius-btn);
            border: 1px solid var(--border-soft);
            background: #fff;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-modal-cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-modal-delete {
            padding: 10px 24px;
            border-radius: var(--radius-btn);
            border: none;
            background: #dc2626;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-modal-delete:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
        }

        /* ── Responsive table ── */
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 575px) {
            .card-main {
                padding: 28px 18px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .table-custom thead th,
            .table-custom tbody td {
                padding: 12px 14px;
                font-size: 0.83rem;
            }

            .info-bar {
                font-size: 0.8rem;
                padding: 10px 14px;
            }
        }

        /* ── Back link ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.2s ease;
            padding: 6px 0;
        }

        .back-link:hover {
            color: #2563eb;
            gap: 10px;
        }

        .back-link i {
            font-size: 1rem;
            transition: transform 0.2s ease;
        }

        .back-link:hover i {
            transform: translateX(-3px);
        }
    </style>
</head>

<body>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer">
        <?php if ($showToast): ?>
            <div class="toast-item <?= $toastType ?>" id="autoToast">
                <i class="bi <?= $toastType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                <span><?= htmlspecialchars($toastMsg) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus pengguna
                    <span class="user-name-highlight" id="deleteUserName">—</span>?
                    <br>Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="" id="deleteForm">
                        <input type="hidden" name="hapus_id" id="deleteUserId" value="">
                        <button type="submit" class="btn-modal-delete">
                            <i class="bi bi-trash3-fill"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card-main">
        <!-- Back link -->
        <a href="../index.php" class="back-link">
            <i class="bi bi-arrow-left"></i>
            Kembali ke Dashboard
        </a>

        <!-- Header -->
        <div class="page-header">
            <h1>👥 Kelola User</h1>
            <p>Daftar seluruh pengguna AquaGas</p>
        </div>

        <!-- Info bar -->
        <div class="info-bar">
            <i class="bi bi-info-circle-fill"></i>
            Pengguna baru mendaftar secara mandiri melalui halaman registrasi. Admin tidak dapat menghapus akun admin lain.
        </div>

        <?php if (empty($users)): ?>
            <!-- Empty State -->
            <div class="table-wrapper">
                <div class="empty-state">
                    <div class="icon-wrap">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h3>Belum ada pengguna</h3>
                    <p>Pengguna yang mendaftar akan muncul di sini</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Table -->
            <div class="table-wrapper">
                <div class="table-responsive-custom">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#e0f1fd,#c7e3f9);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <i class="bi bi-person-fill" style="font-size:0.95rem;color:var(--primary);"></i>
                                            </div>
                                            <span><?= htmlspecialchars($u['name']) ?></span>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php

                                        if ($u['role'] == 'admin') {

                                            echo '
                                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                                    <i class="bi bi-shield-check"></i>
                                                    Admin
                                                </span>';
                                        } elseif ($u['role'] == 'seller') {

                                            echo '
                                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                    <i class="bi bi-shop"></i>
                                                    Seller
                                                </span>';
                                       } else {

                                            echo '
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <i class="bi bi-person"></i>
                                                    Buyer
                                                </span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="text-disabled">
                                                <i class="bi bi-slash-circle"></i> Tidak bisa dihapus
                                            </span>
                                        <?php else: ?>
                                            <button type="button" class="btn-hapus"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-id="<?= (int) $u['id'] ?>"
                                                data-name="<?= htmlspecialchars($u['name']) ?>">
                                                <i class="bi bi-trash3"></i> Hapus
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate modal with user data
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            const userName = button.getAttribute('data-name');
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
        });

        // Auto-dismiss toast
        const autoToast = document.getElementById('autoToast');
        if (autoToast) {
            setTimeout(function() {
                autoToast.classList.add('fade-out');
                setTimeout(function() {
                    autoToast.remove();
                }, 300);
            }, 3500);
        }
    </script>
</body>

</html>
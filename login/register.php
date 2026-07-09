<?php
// Menggunakan file koneksi.php yang sudah Anda miliki
require '../koneksi.php';

// Inisialisasi variabel dan error
 $name = $email = "";
 $errors = [];
 $success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 1. Validasi semua field wajib diisi
    if (empty($name)) {
        $errors[] = "Nama Lengkap wajib diisi.";
    }
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    }
    if (empty($confirm_password)) {
        $errors[] = "Konfirmasi Password wajib diisi.";
    }

    // 2. Validasi Email harus valid
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // 3. Validasi Password minimal 8 karakter
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password minimal harus 8 karakter.";
    }

    // 4. Validasi Password dan Konfirmasi Password harus sama
    if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
        $errors[] = "Password dan Konfirmasi Password tidak sama.";
    }

    // 5. Validasi Email tidak boleh duplikat
    if (empty($errors)) {
        // Gunakan mysqli_real_escape_string dengan variabel $mysqli
        $email_esc = mysqli_real_escape_string($mysqli, $email);
        $query_check = "SELECT id FROM users WHERE email = '$email_esc'";
        
        // Gunakan mysqli_query dengan variabel $mysqli
        $result_check = mysqli_query($mysqli, $query_check);
        
        // Gunakan mysqli_num_rows
        if (mysqli_num_rows($result_check) > 0) {
            $errors[] = "Email sudah terdaftar. Silakan gunakan email lain.";
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        // Gunakan mysqli_real_escape_string dengan variabel $mysqli
        $name_esc = mysqli_real_escape_string($mysqli, $name);
        
        // Wajib menggunakan password_hash() untuk keamanan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'buyer';

        $query_insert = "INSERT INTO users (name, email, password, role) VALUES ('$name_esc', '$email_esc', '$hashed_password', '$role')";
        
        // Eksekusi query insert menggunakan $mysqli
        if (mysqli_query($mysqli, $query_insert)) {
            $success = true;
            // Kosongkan variabel input agar form bersih setelah sukses
            $name = $email = "";
        } else {
            $errors[] = "Terjadi kesalahan sistem. Coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register </title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .card-custom {
            width: 100%;
            max-width: 450px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .card-header-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            border-radius: 15px 15px 0 0 !important;
            padding: 30px 20px 20px;
            text-align: center;
        }
        .btn-primary-custom {
            background-color: #2575fc;
            border-color: #2575fc;
            font-weight: 500;
        }
        .btn-primary-custom:hover {
            background-color: #1a5fcc;
            border-color: #1a5fcc;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.2rem rgba(106, 17, 203, 0.25);
        }
    </style>
</head>
<body>

<div class="card card-custom">
    <div class="card-header card-header-custom">
        <h3 class="mb-1 fw-bold text-dark">Buat Akun Baru</h3>
        <p class="text-muted mb-0">Silakan daftar untuk melanjutkan</p>
    </div>
    <div class="card-body p-4 p-md-5">
        
        <!-- Alert Notifikasi Sukses -->
        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    Registrasi Berhasil! Anda akan diarahkan ke halaman login dalam 2 detik...
                </div>
            </div>
            <!-- Script Redirect otomatis ke login.php -->
            <script>
                setTimeout(function() {
                    window.location.href = "login.php";
                }, 2000);
            </script>
        <?php endif; ?>

        <!-- Alert Notifikasi Error -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Register -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama lengkap" value="<?php echo htmlspecialchars($name); ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" value="<?php echo htmlspecialchars($email); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 8 karakter">
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password">
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary-custom btn-lg text-white">Register</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted">Sudah punya akun? </span>
            <a href="login.php" class="text-decoration-none fw-semibold">Login di sini</a>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
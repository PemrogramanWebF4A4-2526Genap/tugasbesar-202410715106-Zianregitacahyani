<?php
session_start();
require "../koneksi.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
</head>

<style>
    .main {
        height: 100vh;
    }

    .Login-box {
        width: 500px;
        height: 300px;
        box-sizing: border-box;
        border-radius: 10px;
    }
</style>

<body>
    <div class="main d-flex flex-column align-items-center justify-content-center">
        <div class="Login-box p-5 shadow">
            <form action="login.php" method="post">
                <div>
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username">
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password">
                </div>
                <button type="submit" class="btn btn-success form-control mt-3" name="loginbtn">Login</button>
            </form>
        </div>
        <div class="mt-3" style="width: 500px">
            <a href="register.php" class="btn btn-primary form-control">Kembali</a>

            <?php

            if (isset($_POST['loginbtn'])) {

                $username = htmlspecialchars($_POST['username']);
                $password = htmlspecialchars($_POST['password']);

                $query = mysqli_query(
                    $mysqli,
                    "SELECT * FROM users WHERE name='$username'"
                );

                $countdata = mysqli_num_rows($query);
                $data = mysqli_fetch_assoc($query);

                if ($countdata > 0) {

                    if (password_verify($password, $data['password'])) {

                        $_SESSION['login'] = true;
                        $_SESSION['user_id'] = $data['id'];
                        $_SESSION['username'] = $data['name'];
                        $_SESSION['role'] = $data['role'];

                        // Redirect sesuai role
                        if ($data['role'] == 'seller') {
                            header("Location: ../Penjual/index.php");
                        } elseif ($data['role'] == 'admin') {
                            header("Location: ../admin/index.php");
                        } else {
                            header("Location: ../index.php");
                        }

                        exit();
                    } else {
            ?>
                        <div class="alert alert-danger mt-3">
                            Password Salah
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="alert alert-warning mt-3">
                        Username Tidak Ditemukan
                    </div>
            <?php
                }
            }
            ?>

        </div>
    </div>
</body>

</html>
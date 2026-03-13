<?php
session_start();
include "config/koneksi.php";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = ($_POST['password']);

    $query = mysqli_query($mysqli, "
        SELECT * FROM users 
        WHERE email='$email' AND password='$password'
    ");

    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        // SIMPAN SESSION
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];

        // REDIRECT BERDASARKAN ROLE
        if ($user['role'] === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;

    } else {
        echo "<script>alert('Email atau Password salah');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | UPB FOOD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4" style="width: 350px;">
        <h3 class="text-center mb-4">UPB FOOD</h3>
        <form method="post">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button name="login" class="btn btn-warning w-100">Login</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">
    Kembali ke Beranda
</a>
        </form>
    </div>
</div>

</body>
</html>

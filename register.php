<?php
include "config/koneksi.php";

if (isset($_POST['register'])) {
    $nama      = mysqli_real_escape_string($mysqli, $_POST['nama']);
    $email     = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password  = mysqli_real_escape_string($mysqli, $_POST['password']);
    $alamat    = mysqli_real_escape_string($mysqli, $_POST['alamat']);
    $nomer_hp  = mysqli_real_escape_string($mysqli, $_POST['nomer_hp']);

    // cek email sudah terdaftar atau belum
    $cek = mysqli_query($mysqli, "SELECT email FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah terdaftar');</script>";
    } else {
        // ROLE OTOMATIS CUSTOMER
        $query = mysqli_query($mysqli, "
            INSERT INTO users 
            (nama, email, password, Alamat, Nomer_Hp, role) 
            VALUES 
            ('$nama', '$email', '$password', '$alamat', '$nomer_hp', 'customer')
        ");

        if ($query) {
            echo "<script>
                alert('Registrasi berhasil, silakan login');
                window.location='login.php';
            </script>";
        } else {
            echo "<script>alert('Registrasi gagal');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register | UPB FOOD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4" style="width: 400px;">
        <h3 class="text-center mb-4">Register UPB FOOD</h3>

        <form method="post">
            <div class="mb-2">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="mb-2">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-2">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-2">
                <label>Alamat</label>
                <input type="text" name="alamat" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>No HP</label>
                <input type="text" name="nomer_hp" class="form-control" required>
            </div>

            <button name="register" class="btn btn-warning w-100">
                Daftar
            </button>

            <a href="login.php" class="btn btn-secondary w-100 mt-2">
                Sudah punya akun? Login
            </a>
        </form>
    </div>
</div>

</body>
</html>

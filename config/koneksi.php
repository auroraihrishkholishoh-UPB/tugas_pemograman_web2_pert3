<?php
// ============================
// KONFIGURASI DATABASE
// ============================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "upb_food";

// ============================
// KONEKSI MYSQLI
// ============================

$mysqli = mysqli_connect($host, $user, $pass, $db);

// ============================
// CEK KONEKSI
// ============================

if (!$mysqli) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>

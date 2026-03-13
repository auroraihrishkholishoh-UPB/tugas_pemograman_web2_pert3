<?php
session_start();
include "config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

$q = mysqli_query($mysqli,"
    SELECT 
        o.id_order,
        o.total,
        o.order_date,
        o.status,
        u.nama,
        u.email
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    WHERE o.id_order = $id
");

$d = mysqli_fetch_assoc($q);

if (!$d) {
    echo "<div class='alert alert-danger'>Data transaksi tidak ditemukan</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

<!-- CARD DETAIL -->
<div class="card shadow-sm mx-auto" style="max-width:600px">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-receipt me-2"></i> Detail Transaksi
        </h5>
    </div>

    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <td width="40%"><strong>ID Transaksi</strong></td>
                <td>: <?= $d['id_order']; ?></td>
            </tr>
            <tr>
                <td><strong>Customer</strong></td>
                <td>: <?= $d['nama']; ?></td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td>: <?= $d['email']; ?></td>
            </tr>
            <tr>
                <td><strong>Total Bayar</strong></td>
                <td>: <span class="text-success fw-bold">
                    Rp <?= number_format($d['total']); ?>
                </span></td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>: <?= date('d-m-Y H:i', strtotime($d['order_date'])); ?></td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>
                    <?php if($d['status']=='paid'){ ?>
                        <span class="badge bg-success">PAID</span>
                    <?php } else { ?>
                        <span class="badge bg-warning text-dark"><?= strtoupper($d['status']) ?></span>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include "config/koneksi.php";
require "class/Transaksi.php";

$transaksi = new Transaksi($mysqli);
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['update_status'])) {

    $transaksi->updateStatus($_POST['id_order'], $_POST['status']);

    header("Location: transaksi.php");
    exit;
}

$orders = $transaksi->getOrders();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Transaksi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
<div class="row min-vh-100">

<!-- SIDEBAR -->
<!-- SIDEBAR -->
<aside class="col-md-2 bg-dark text-white p-3">
    <h4 class="mb-4">ADMIN</h4>

    <a href="dashboard.php" class="d-block text-white mb-2">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>

    <a href="produk.php" class="d-block text-white mb-2">
        <i class="bi bi-box-seam me-2"></i> Produk
    </a>

    <a href="transaksi.php" class="d-block text-white mb-2 fw-bold">
        <i class="bi bi-credit-card me-2"></i> Transaksi
    </a>

    <hr class="border-secondary">

    <form action="logout.php" method="post">
        <button class="btn btn-danger w-100">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
    </form>
</aside>

<!-- MAIN -->
<main class="col-md-10 p-4">

<!-- HEADER -->
<div class="card shadow-sm mb-4">
<div class="card-body d-flex justify-content-between align-items-center">
<div>
<h4><i class="bi bi-credit-card"></i> Data Transaksi</h4>
<small class="text-muted">Admin hanya dapat melihat & mengubah status</small>
</div>
    <span class="badge bg-primary fs-6"><?= $_SESSION['nama']; ?></span>
  </div>
</div>

<div class="card shadow-sm">
<div class="card-body">

<table class="table table-bordered table-hover align-middle">
<thead class="table-light">
<tr>
<th>No</th>
<th>Tanggal</th>
<th>Customer</th>
<th>Total</th>
<th>Status</th>
<th>Detail</th>
</tr>
</thead>

<tbody>
<?php
$no = 1;
$modals = '';

while ($o = mysqli_fetch_assoc($orders)) {
?>
<tr>
<td><?= $no++ ?></td>
<td><?= $o['order_date'] ?></td>
<td><?= $o['nama'] ?></td>
<td>Rp <?= number_format($o['total']) ?></td>

<td>
<form method="post" class="d-flex gap-1">
<input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
<select name="status" class="form-select form-select-sm">
<option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
<option value="paid" <?= $o['status']=='paid'?'selected':'' ?>>Paid</option>
<option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
</select>
<button name="update_status" class="btn btn-sm btn-success">
<i class="bi bi-check"></i>
</button>
</form>
</td>

<td class="text-center">
<button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#detail<?= $o['id_order'] ?>">
<i class="bi bi-eye"></i>
</button>
</td>
</tr>

<?php
// MODAL (DI SIMPAN)
ob_start();
?>
<div class="modal fade" id="detail<?= $o['id_order'] ?>" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Detail Transaksi #<?= $o['id_order'] ?></h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<table class="table table-bordered">
<thead>
<tr>
<th>Produk</th>
<th>Varian</th>
<th>Qty</th>
<th>Harga</th>
<th>Subtotal</th>
</tr>
</thead>
<tbody>

<?php
$item = $transaksi->getDetail($o['id_order']);

while ($i = mysqli_fetch_assoc($item)) {
?>
<tr>
<td><?= $i['nama_produk'] ?></td>
<td><?= $i['rasa'].' '.$i['ukuran'] ?></td>
<td><?= $i['qty'] ?></td>
<td>Rp <?= number_format($i['harga']) ?></td>
<td>Rp <?= number_format($i['subtotal']) ?></td>
</tr>
<?php } ?>

</tbody>
</table>

<div class="text-end fw-bold">
Total: Rp <?= number_format($o['total']) ?>
</div>
</div>

</div>
</div>
</div>
<?php
$modals .= ob_get_clean();
}
?>
</tbody>
</table>

</div>
</div>

<?= $modals ?>

</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
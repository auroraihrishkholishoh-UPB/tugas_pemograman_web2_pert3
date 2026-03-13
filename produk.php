<?php
session_start();
include "config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ================= ACTION ================= */

// TAMBAH PRODUK
if (isset($_POST['add_produk'])) {

    $gambar = "";
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . "_" . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "img/" . $gambar);
    }

    mysqli_query($mysqli,"
        INSERT INTO products (nama_produk, harga, gambar, deskripsi)
        VALUES (
            '$_POST[nama]',
            '$_POST[harga]',
            '$gambar',
            '$_POST[deskripsi]'
        )
    ");

    header("Location: produk.php");
    exit;
}

// UPDATE PRODUK
if (isset($_POST['update_produk'])) {

    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . "_" . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "img/" . $gambar);
        $updateGambar = ", gambar='$gambar'";
    } else {
        $updateGambar = "";
    }

    mysqli_query($mysqli,"
        UPDATE products SET
            nama_produk='$_POST[nama]',
            harga='$_POST[harga]',
            deskripsi='$_POST[deskripsi]'
            $updateGambar
        WHERE id_product='$_POST[id]'
    ");

    header("Location: produk.php");
    exit;
}

// HAPUS PRODUK
if (isset($_GET['hapus'])) {
    mysqli_query($mysqli,"DELETE FROM products WHERE id_product='$_GET[hapus]'");
    header("Location: produk.php");
    exit;
}

// TAMBAH VARIANT
if (isset($_POST['add_variant'])) {
    mysqli_query($mysqli,"
        INSERT INTO product_variants (id_product, rasa, ukuran, tambahan_harga)
        VALUES (
            '$_POST[id_product]',
            '$_POST[rasa]',
            '$_POST[ukuran]',
            '$_POST[tambahan]'
        )
    ");
    header("Location: produk.php");
    exit;
}

// HAPUS VARIANT
if (isset($_GET['hapus_variant'])) {
    mysqli_query($mysqli,"DELETE FROM product_variants WHERE id_variant='$_GET[hapus_variant]'");
    header("Location: produk.php");
    exit;
}

/* ================= DATA ================= */
$produk = mysqli_query($mysqli,"SELECT * FROM products ORDER BY id_product DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen Produk</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
<div class="row min-vh-100">

<!-- SIDEBAR -->
<aside class="col-md-2 bg-dark text-white p-3">
    <h4 class="mb-4">ADMIN</h4>

    <a href="dashboard.php" class="d-block text-white mb-2">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>

    <a href="produk.php" class="d-block text-white mb-2 fw-bold">
        <i class="bi bi-box-seam me-2"></i> Produk
    </a>

    <a href="transaksi.php" class="d-block text-white mb-2">
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
<h4><i class="bi bi-box-seam me-2"></i> Manajemen Produk</h4>
<small class="text-muted">Tambah, edit, hapus produk & variant</small>
</div>
    <span class="badge bg-primary fs-6"><?= $_SESSION['nama']; ?></span>
  </div>
</div>

<!-- TAMBAH PRODUK -->
<div class="card shadow-sm mb-4">
<div class="card-body">
<h5><i class="bi bi-plus-circle"></i> Tambah Produk</h5>
<form method="post" enctype="multipart/form-data" class="row g-2">
<div class="col-md-3">
<input type="text" name="nama" class="form-control" placeholder="Nama Produk" required>
</div>
<div class="col-md-2">
<input type="number" name="harga" class="form-control" placeholder="Harga" required>
</div>
<div class="col-md-3">
<input type="file" name="gambar" class="form-control">
</div>
<div class="col-md-3">
<input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi">
</div>
<div class="col-md-1">
<button name="add_produk" class="btn btn-success w-100">+</button>
</div>
</form>
</div>
</div>

<!-- TABEL PRODUK -->
<div class="card shadow-sm">
<div class="card-body">
<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>No</th>
<th>Gambar</th>
<th>Produk</th>
<th>Harga</th>
<th>Variant</th>
<th class="text-center">Aksi</th>
</tr>
</thead>
<tbody>

<?php $no=1; while($p=mysqli_fetch_assoc($produk)){ ?>
<tr>
<td><?= $no++ ?></td>

<td>
<?php if($p['gambar']){ ?>
<img src="img/<?= $p['gambar'] ?>" width="60" class="rounded">
<?php } ?>
</td>

<td><?= $p['nama_produk'] ?></td>
<td>Rp <?= number_format($p['harga']) ?></td>

<td>
<?php
$v = mysqli_query($mysqli,"SELECT * FROM product_variants WHERE id_product='$p[id_product]'");
while($vr=mysqli_fetch_assoc($v)){
?>
<span class="badge bg-secondary">
<?= $vr['rasa'] ?> <?= $vr['ukuran'] ?> (+<?= $vr['tambahan_harga'] ?>)
</span>
<?php } ?>
</td>

<td class="text-nowrap text-center">

<button class="btn btn-sm btn-warning"
data-bs-toggle="modal"
data-bs-target="#edit<?= $p['id_product'] ?>">
<i class="bi bi-pencil"></i>
</button>

<button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#variant<?= $p['id_product'] ?>">
<i class="bi bi-layers"></i>
</button>

<a href="?hapus=<?= $p['id_product'] ?>"
onclick="return confirm('Hapus produk?')"
class="btn btn-sm btn-danger">
<i class="bi bi-trash"></i>
</a>

</td>
</tr>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="edit<?= $p['id_product'] ?>">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="post" enctype="multipart/form-data">

<div class="modal-header">
<h5 class="modal-title">Edit Produk</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body row g-2">
<input type="hidden" name="id" value="<?= $p['id_product'] ?>">

<div class="col-md-6">
<label>Nama Produk</label>
<input type="text" name="nama" value="<?= $p['nama_produk'] ?>" class="form-control">
</div>

<div class="col-md-6">
<label>Harga</label>
<input type="number" name="harga" value="<?= $p['harga'] ?>" class="form-control">
</div>

<div class="col-md-6">
<label>Gambar</label>
<input type="file" name="gambar" class="form-control">
</div>

<div class="col-md-6">
<label>Deskripsi</label>
<input type="text" name="deskripsi" value="<?= $p['deskripsi'] ?>" class="form-control">
</div>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
<button name="update_produk" class="btn btn-warning">Update</button>
</div>

</form>
</div>
</div>
</div>

<!-- ================= MODAL VARIANT ================= -->
<div class="modal fade" id="variant<?= $p['id_product'] ?>">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Variant Produk</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<?php
$v2 = mysqli_query($mysqli,"SELECT * FROM product_variants WHERE id_product='$p[id_product]'");
while($vr=mysqli_fetch_assoc($v2)){
?>
<div class="d-flex justify-content-between border rounded p-2 mb-1">
<span><?= $vr['rasa'] ?> | <?= $vr['ukuran'] ?> (+<?= $vr['tambahan_harga'] ?>)</span>
<a href="?hapus_variant=<?= $vr['id_variant'] ?>" class="text-danger">x</a>
</div>
<?php } ?>

<hr>

<form method="post">
<input type="hidden" name="id_product" value="<?= $p['id_product'] ?>">
<input type="text" name="rasa" class="form-control mb-2" placeholder="Rasa" required>
<input type="text" name="ukuran" class="form-control mb-2" placeholder="Ukuran" required>
<input type="number" name="tambahan" class="form-control mb-2" placeholder="Tambah Harga" required>
<button name="add_variant" class="btn btn-primary w-100">Tambah Variant</button>
</form>
<a href="?hapus=<?= $p['id_product'] ?>" class="btn btn-danger btn-sm w-100"
onclick="return confirm('Hapus produk?')">Hapus</a>
</div>
</div>
</div>
</div>

<?php } ?>

</tbody>
</table>
</div>
</div>


</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

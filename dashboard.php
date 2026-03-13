<?php
session_start();
include "config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ================= FILTER TAHUN & BULAN ================= */
$tahunTerpilih = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');
$bulanTerpilih = isset($_GET['bulan']) ? intval($_GET['bulan']) : "";

$sqlFilter = "AND YEAR(o.order_date) = $tahunTerpilih";
$where = "";

if ($bulanTerpilih) {
    $where = "AND MONTH(o.order_date) = $bulanTerpilih";
}

/* ================= STATISTIK ================= */
$totalProduk = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) total FROM products")
)['total'];

$totalVariant = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) total FROM product_variants")
)['total'];

/* cek tabel transaksi */
$qTrans = mysqli_query($mysqli, "SHOW TABLES LIKE 'orders'");
$adaTrans = mysqli_num_rows($qTrans);

$totalTransaksi = 0;
$totalOmzet = 0;

if ($adaTrans) {
    $qTotal = mysqli_fetch_assoc(
        mysqli_query($mysqli,"
            SELECT COUNT(*) total 
            FROM orders o
            WHERE o.status='paid'
            $sqlFilter
            $where
        ")
    );
    $totalTransaksi = $qTotal['total'];

    $qOmzet = mysqli_fetch_assoc(
    mysqli_query($mysqli,"
        SELECT SUM(o.total) total
        FROM orders o
        WHERE o.status='paid'
        $sqlFilter
        $where
    ")
);

$totalOmzet = isset($qOmzet['total']) ? $qOmzet['total'] : 0;
}

/* ================= LIST TRANSAKSI ================= */
$dataTransaksi = [];

if ($adaTrans) {
    $qList = mysqli_query($mysqli,"
        SELECT o.id_order, u.nama, o.total, o.order_date
        FROM orders o
        JOIN users u ON o.id_user = u.id_user
        WHERE o.status='paid'
        $sqlFilter
        $where
        ORDER BY o.order_date DESC
        LIMIT 10
    ");

    while ($r = mysqli_fetch_assoc($qList)) {
        $dataTransaksi[] = $r;
    }
}

/* ================= CHART ================= */
$chartLabel = [];
$chartTrans = [];
$chartOmzet = [];

if ($adaTrans) {
    $qChart = mysqli_query($mysqli,"
        SELECT DATE(o.order_date) tgl, COUNT(*) jml, SUM(o.total) omzet
        FROM orders o
        WHERE o.status='paid'
        $sqlFilter
        $where
        GROUP BY DATE(o.order_date)
        ORDER BY tgl ASC
    ");

    while ($c = mysqli_fetch_assoc($qChart)) {
        $chartLabel[] = $c['tgl'];
        $chartTrans[] = $c['jml'];
        $chartOmzet[] = $c['omzet'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<a href="produk.php" class="d-block text-white mb-2">
  <i class="bi bi-box-seam me-2"></i> Produk
</a>

<a href="transaksi.php" class="d-block text-white mb-2">
  <i class="bi bi-credit-card me-2"></i> Transaksi
</a>
<form action="logout.php" method="post">
<button href="logout.php" class="btn btn-danger w-100 mt-3">
  <i class="bi bi-box-arrow-right me-2"></i> Logout
</button>
</form>

</aside>

<!-- MAIN CONTENT -->
<main class="col-md-10 p-4">

<!-- HEADER -->
<div class="card shadow-sm mb-4">
  <div class="card-body d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1">
  <i class="bi bi-person-circle me-2"></i> Selamat Datang
</h4>

      <small class="text-muted">Kelola produk, transaksi, dan omzet</small>
    </div>
    <span class="badge bg-primary fs-6"><?= $_SESSION['nama']; ?></span>
  </div>
</div>

<!-- STATISTIK -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card text-bg-primary shadow">
      <div class="card-body">
        <h3><i class="bi bi-box"></i> <?= $totalProduk ?></h3>
<small>Total Produk</small>

      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-secondary shadow">
      <div class="card-body">
        <h3><i class="bi bi-layers"></i> <?= $totalVariant ?></h3>
<small>Total Variant</small>

      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-success shadow">
      <div class="card-body">
        <h3><i class="bi bi-receipt"></i> <?= $totalTransaksi ?></h3>
<small>Total Transaksi</small>

      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-warning shadow">
      <div class="card-body">
        <h3><i class="bi bi-cash-stack"></i> Rp <?= number_format($totalOmzet) ?></h3>
<small>Total Omzet</small>

      </div>
    </div>
  </div>
</div>

<!-- FILTER -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-center">
    <div class="col-md-3">
  <select name="tahun" class="form-select">
    <option value="2025" <?= $tahunTerpilih==2025?'selected':'' ?>>2025</option>
    <option value="2026" <?= $tahunTerpilih==2026?'selected':'' ?>>2026</option>
  </select>
</div>
  
    <div class="col-md-4">
        <select name="bulan" class="form-select">
          <option value="">-- Semua Bulan --</option>
          <?php for($i=1;$i<=12;$i++){ ?>
          <option value="<?= $i ?>" <?= ($bulanTerpilih==$i?'selected':'') ?>>
            <?= date('F', mktime(0,0,0,$i,1)); ?>
          </option>
          <?php } ?>
        </select>
      </div>
      <div class="col-md-8">
        <button class="btn btn-primary">
  <i class="bi bi-funnel"></i> Filter
</button>

<a href="export_excel.php?bulan=<?= $bulanTerpilih ?>&tahun=<?= $tahunTerpilih ?>" class="btn btn-success">
  <i class="bi bi-file-earmark-excel"></i> Excel
</a>

<a href="export_pdf.php?bulan=<?= $bulanTerpilih ?>&tahun=<?= $tahunTerpilih ?>" class="btn btn-danger">
  <i class="bi bi-file-earmark-pdf"></i> PDF
</a>

      </div>
    </form>
  </div>
</div>

<!-- CHART -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <h5><i class="bi bi-graph-up"></i> Grafik Transaksi</h5>
    <canvas id="chartTransaksi"></canvas>
  </div>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <h5><i class="bi bi-currency-dollar"></i> Grafik Omzet</h5>
    <canvas id="chartOmzet"></canvas>
  </div>
</div>

<!-- TABEL -->
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3">
  <i class="bi bi-list-ul"></i> Transaksi Terbaru
</h5>
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <td>No</td>
          <td>Customer</td>
          <td>Total</td>
          <td>Tanggal</td>
          <td>Aksi</td>
        </tr>
      </thead>
      <tbody>
        <?php if(!$dataTransaksi){ ?>
          <tr><td colspan="5" class="text-center">Belum ada data</td></tr>
        <?php } ?>
        <?php $no=1; foreach($dataTransaksi as $t){ ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $t['nama'] ?></td>
          <td>Rp <?= number_format($t['total']) ?></td>
          <td><?= date('d-m-Y', strtotime($t['order_date'])) ?></td>
          <td>
            <a class="btn btn-sm btn-warning"
   href="transaksi_detail.php?id=<?= $t['id_order'] ?>">
   <i class="bi bi-eye"></i> Detail
</a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

</main>
</div>
</div>

<script>
new Chart(document.getElementById('chartTransaksi'),{
  type:'line',
  data:{
    labels:<?= json_encode($chartLabel) ?>,
    datasets:[{
      label:'Transaksi',
      data:<?= json_encode($chartTrans) ?>,
      fill:true,
      tension:.4
    }]
  }
});

new Chart(document.getElementById('chartOmzet'),{
  type:'bar',
  data:{
    labels:<?= json_encode($chartLabel) ?>,
    datasets:[{
      label:'Omzet',
      data:<?= json_encode($chartOmzet) ?>
    }]
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include "config/koneksi.php";
require('fpdf/fpdf.php');

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

$where = "WHERE 1=1";

if ($bulan != "") {
    $where .= " AND MONTH(orders.order_date) = '$bulan'";
}
if ($tahun != "") {
    $where .= " AND YEAR(orders.order_date) = '$tahun'";
}

$sql = "
SELECT
    orders.id_order,
    users.nama AS customer,
    orders.status,
    products.nama_produk,
    CONCAT(product_variants.rasa, ' ', product_variants.ukuran) AS varian,
    order_items.qty,
    order_items.harga,
    order_items.subtotal,
    orders.order_date
FROM order_items
JOIN orders ON order_items.id_order = orders.id_order
JOIN users ON orders.id_user = users.id_user
JOIN products ON order_items.id_product = products.id_product
LEFT JOIN product_variants ON order_items.id_variant = product_variants.id_variant
$where
ORDER BY orders.id_order DESC
";

$q = mysqli_query($mysqli, $sql);

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'LAPORAN INVOICE TRANSAKSI',0,1,'C');
$pdf->Ln(3);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(10,8,'No',1);
$pdf->Cell(20,8,'Order',1);
$pdf->Cell(45,8,'Customer',1);
$pdf->Cell(25,8,'Status',1);
$pdf->Cell(45,8,'Produk',1);
$pdf->Cell(40,8,'Varian',1);
$pdf->Cell(15,8,'Qty',1);
$pdf->Cell(30,8,'Harga',1);
$pdf->Cell(30,8,'Subtotal',1);
$pdf->Cell(35,8,'Tanggal',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);
$no = 1;

while ($r = mysqli_fetch_assoc($q)) {
    $pdf->Cell(10,8,$no,1);
    $pdf->Cell(20,8,$r['id_order'],1);
    $pdf->Cell(45,8,$r['customer'],1);
    $pdf->Cell(25,8,$r['status'],1);
    $pdf->Cell(45,8,$r['nama_produk'],1);
    $pdf->Cell(40,8,$r['varian'],1);
    $pdf->Cell(15,8,$r['qty'],1);
    $pdf->Cell(30,8,'Rp '.number_format($r['harga']),1);
    $pdf->Cell(30,8,'Rp '.number_format($r['subtotal']),1);
    $pdf->Cell(35,8,$r['order_date'],1);
    $pdf->Ln();
    $no++;
}

$pdf->Output('D','invoice_'.$bulan.'-'.$tahun.'.pdf');

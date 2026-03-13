<?php
include "config/koneksi.php";

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=invoice_$bulan-$tahun.xls");

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

echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>ID Order</th>
        <th>Customer</th>
        <th>Status</th>
        <th>Produk</th>
        <th>Varian</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
        <th>Tanggal</th>
      </tr>";

$no = 1;
while ($r = mysqli_fetch_assoc($q)) {
    echo "<tr>
        <td>$no</td>
        <td>{$r['id_order']}</td>
        <td>{$r['customer']}</td>
        <td>{$r['status']}</td>
        <td>{$r['nama_produk']}</td>
        <td>{$r['varian']}</td>
        <td>{$r['qty']}</td>
        <td>{$r['harga']}</td>
        <td>{$r['subtotal']}</td>
        <td>{$r['order_date']}</td>
    </tr>";
    $no++;
}

echo "</table>";

<?php
session_start();
include "config/koneksi.php";
// ===============================
// AMBIL VARIANT (AJAX)
// ===============================
if (isset($_GET['action']) && $_GET['action'] === 'get_variant') {

    $id = intval($_GET['id_product']);

    $q = mysqli_query($mysqli, "
        SELECT 
            id_variant,
            rasa,
            ukuran,
            tambahan_harga
        FROM product_variants
        WHERE id_product = '$id'
    ");

    $data = [];
    while ($r = mysqli_fetch_assoc($q)) {
        $data[] = $r;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/* =================================
   AJAX HANDLER (CART & CHECKOUT)
================================= */
if (isset($_GET['action'])) {

    // ===============================
    // TAMBAH KE KERANJANG
    // ===============================
   if ($_GET['action'] === 'add_cart') {
    if (!isset($_SESSION['id_user'])) {
    echo "login_required";
    exit;
}


    $id_variant = intval($_POST['id_variant']);
    $qty = max(1, intval($_POST['qty']));

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_variant'] == $id_variant) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = [
            'id_variant' => $id_variant,
            'qty' => $qty
        ];
    }

    echo count($_SESSION['cart']);
    exit;
}



    // ===============================
    // LIHAT KERANJANG
    // ===============================
    if ($_GET['action'] === 'view_cart') {
        if (!isset($_SESSION['id_user'])) {
    echo "login_required";
    exit;
}


    $items = [];
    $total = 0;

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $i => $c) {

            $id_variant = $c['id_variant'];
            $qty = $c['qty'];

            $q = mysqli_query($mysqli,"
                SELECT 
                    p.nama_produk,
                    p.harga AS harga_dasar,
                    v.rasa,
                    v.ukuran,
                    v.tambahan_harga
                FROM product_variants v
                JOIN products p ON p.id_product = v.id_product
                WHERE v.id_variant='$id_variant'
            ");
            $d = mysqli_fetch_assoc($q);

            if (!$d) continue;

            $harga = $d['harga_dasar'] + $d['tambahan_harga'];
            $subtotal = $harga * $qty;
            $total += $subtotal;

            $items[] = [
                'index' => $i,
                'nama' => $d['nama_produk'],
                'rasa' => $d['rasa'],
                'ukuran' => $d['ukuran'],
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $subtotal
            ];
        }
    }
    header('Content-Type: application/json');

    echo json_encode([
        'items' => $items,
        'total' => $total
    ]);
    exit;
}


    // ===============================
    // UPDATE QTY
    // ===============================
    if ($_GET['action'] === 'update_qty') {
    $i = intval($_POST['index']);
    $qty = max(1, intval($_POST['qty']));

    if (isset($_SESSION['cart'][$i])) {
        $_SESSION['cart'][$i]['qty'] = $qty;
    }

    echo "ok";
    exit;
}


    // ===============================
    // HAPUS ITEM
    // ===============================
    if ($_GET['action'] === 'remove_cart') {
    $i = intval($_POST['index']);

    if (isset($_SESSION['cart'][$i])) {
        unset($_SESSION['cart'][$i]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    echo "ok";
    exit;
}


    // ===============================
    // CHECKOUT
    // ===============================
    if ($_GET['action'] === 'checkout') {
        if (!isset($_SESSION['id_user'])) {
    echo "login_required";
    exit;
}


    if (empty($_SESSION['cart'])) {
        echo "cart_empty";
        exit;
    }

    mysqli_begin_transaction($mysqli);

    try {

        $total = 0;

        foreach ($_SESSION['cart'] as $c) {

            $q = mysqli_query($mysqli,"
                SELECT p.id_product, p.harga AS harga_dasar, v.tambahan_harga
                FROM product_variants v
                JOIN products p ON p.id_product=v.id_product
                WHERE v.id_variant='{$c['id_variant']}'
            ");
            $d = mysqli_fetch_assoc($q);
            if (!$d) throw new Exception("Variant not found");

            $harga = $d['harga_dasar'] + $d['tambahan_harga'];
            $total += $harga * $c['qty'];
        }

        $id_user = $_SESSION['id_user'];

mysqli_query($mysqli,"
    INSERT INTO orders (id_user, total, status)
    VALUES ('$id_user', '$total', 'pending')
");

$id_order = mysqli_insert_id($mysqli);

        $id_order = mysqli_insert_id($mysqli);

        foreach ($_SESSION['cart'] as $c) {

            $q = mysqli_query($mysqli,"
                SELECT p.id_product, p.harga AS harga_dasar, v.tambahan_harga
                FROM product_variants v
                JOIN products p ON p.id_product=v.id_product
                WHERE v.id_variant='{$c['id_variant']}'
            ");
            $d = mysqli_fetch_assoc($q);
            if (!$d) throw new Exception("Variant not found");

            $harga = $d['harga_dasar'] + $d['tambahan_harga'];
            $subtotal = $harga * $c['qty'];

            mysqli_query($mysqli,"
                INSERT INTO order_items
                (id_order,id_product,id_variant,qty,harga,subtotal)
                VALUES
                ('$id_order','{$d['id_product']}',
                 '{$c['id_variant']}','{$c['qty']}',
                 '$harga','$subtotal')
            ");
        }

        mysqli_commit($mysqli);
        unset($_SESSION['cart']);
        echo "ok";

    } catch (Exception $e) {
        mysqli_rollback($mysqli);
        echo "error";
    }

    exit;
}

    }

/* =========================
   AMBIL PRODUK
========================= */
$produk = mysqli_query($mysqli,"SELECT * FROM products");
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Sederhana - Makanan Lezat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gradient">
    <!-- Navbar Inovatif -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-orange shadow-lg">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-utensils me-2"></i>UPB FOOD</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

        <div class="ms-auto d-flex align-items-center gap-2">

            <?php if (!isset($_SESSION['id_user'])) { ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="register.php" class="btn btn-light btn-sm">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php } else { ?>
                <span class="text-white me-2">
                    Hi, <?= $_SESSION['nama'] ?>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    Logout
                </a>
            <?php } ?>

            <button class="btn btn-warning" id="cart-btn">
                <i class="fas fa-shopping-cart"></i> Keranjang
                (<span id="cart-count"><?= $cartCount ?></span>)
            </button>
        </div>
    </div>
</nav>


<!-- HERO -->
<!-- Hero Section dengan Carousel Inovatif -->
    <section class="hero">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="img/dimsum.jpg" class="d-block w-100" alt="Dimsum">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold">Nikmati Dimsum Segar!</h1>
                        <p class="lead">Pesan sekarang dan rasakan kelezatannya.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="img/pisang1.png" class="d-block w-100" alt="Pisang Coklat">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold">Pisang Coklat Manis!</h1>
                        <p class="lead">Camilan favorit yang tak pernah salah.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="img/corndog.jpg" class="d-block w-100" alt="Corndog">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold">Corndog Crispy!</h1>
                        <p class="lead">Renyah di luar, juicy di dalam.</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Previous slide">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Next slide">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </section>


<main class="container my-5">
<section>
<h2 class="text-center fw-bold text-orange mb-5">Produk Kami</h2>
<div class="row g-4">

<?php while ($p = mysqli_fetch_assoc($produk)) { ?>
<div class="col-md-4">
    <div class="card shadow product-card"
        data-id="<?= $p['id_product'] ?>"
        data-nama="<?= $p['nama_produk'] ?>"
        data-harga="<?= $p['harga'] ?>"
        data-gambar="<?= $p['gambar'] ?>">
        <img src="img/<?= $p['gambar'] ?>" class="card-img-top">
        <div class="card-body text-center">
            <h5><?= $p['nama_produk'] ?></h5>
            <p>Rp <?= number_format($p['harga']) ?></p>
            <button class="btn btn-orange view-detail-btn">
                Lihat Detail
            </button>
        </div>
    </div>
</div>
<?php } ?>

</div>
</section>
</main>

<!-- MODAL DETAIL -->
<div class="modal fade" id="product-modal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header bg-orange text-white">
    <h5 id="product-title"></h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <img id="product-image" class="img-fluid mb-3">
    <select id="variant-select" class="form-select mb-2">
    <option value="">-- Pilih Variant --</option>
</select>

    <p class="fw-bold">Harga: Rp <span id="product-price"></span></p>
    <input type="number" id="quantity-input" class="form-control" value="1" min="1">
    <p class="mt-3 fw-bold">
        Subtotal: Rp <span id="subtotal">0</span>
    </p>
</div>
<div class="modal-footer">
    <button class="btn btn-success" id="add-to-cart-btn">
        Tambah ke Keranjang
    </button>
</div>
</div>
</div>
</div>

<!-- MODAL CART -->
<div class="modal fade" id="cart-modal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header bg-orange text-white">
    <h5>Keranjang</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div id="cart-items"></div>
    <p class="fw-bold mt-3">
        Total: Rp <span id="cart-total">0</span>
    </p>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" id="checkout-btn">
        Checkout
    </button>
</div>
</div>
</div>
</div>

<!-- Footer Inovatif -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; 2025 | UPB FOOD. CREATED BY KELOMPOK 1.</p>
            <div class="social-icons">
                <a href="https:/facebook.com" class="text-white me-3" aria-label="Kunjungi Facebook Kami"><i class="fab fa-facebook fa-2x"></i></a>
                <a href="https:/instagram.com" class="text-white me-3" aria-label="Kunjungi Instagram Kami"><i class="fab fa-instagram fa-2x"></i></a>
                <a href="https:/twitter.com" class="text-white" aria-label="Kunjungi Twitter Kami"><i class="fab fa-twitter fa-2x"></i></a>
            </div>
        </div>
    </footer>
    <script>
    window.isLogin = <?= isset($_SESSION['id_user']) ? 'true' : 'false' ?>;
</script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>

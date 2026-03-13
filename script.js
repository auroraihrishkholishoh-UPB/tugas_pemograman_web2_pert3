let currentProduct = null;

// ===============================
// BUKA MODAL DETAIL + LOAD VARIANT
// ===============================
document.querySelectorAll('.view-detail-btn').forEach(btn => {
    btn.addEventListener('click', function () {

        const card = this.closest('.product-card');

        currentProduct = {
            id: card.dataset.id,
            name: card.dataset.nama,
            price: parseInt(card.dataset.harga),
            image: card.dataset.gambar
        };

        document.getElementById('product-title').innerText = currentProduct.name;
        document.getElementById('product-image').src = 'img/'+currentProduct.image;
        document.getElementById('quantity-input').value = 1;
        

        // LOAD VARIANT
        fetch(`?action=get_variant&id_product=${currentProduct.id}`)

        .then(res => res.json())
        .then(data => {
            let html = '<option value="">-- Pilih Variant --</option>';
            data.forEach(v => {
                html += `
                <option value="${v.id_variant}" data-extra="${v.tambahan_harga}">
                    ${v.rasa} - ${v.ukuran} (+Rp ${v.tambahan_harga})
                </option>`;
            });
            document.getElementById('variant-select').innerHTML = html;
        });

        document.getElementById('subtotal').innerText =
            currentProduct.price.toLocaleString('id-ID');

        new bootstrap.Modal(document.getElementById('product-modal')).show();
    });
});

// ===============================
// HITUNG SUBTOTAL REALTIME
// ===============================
function hitungSubtotal() {
    const qty = parseInt(document.getElementById('quantity-input').value) || 1;
    const variant = document.getElementById('variant-select');
    const extra = variant.selectedOptions[0]?.dataset.extra || 0;

    const subtotal = (currentProduct.price + parseInt(extra)) * qty;
    document.getElementById('subtotal').innerText =
        subtotal.toLocaleString('id-ID');
}

document.getElementById('quantity-input').addEventListener('input', hitungSubtotal);
document.getElementById('variant-select').addEventListener('change', hitungSubtotal);

// ===============================
// TAMBAH KE KERANJANG
// ===============================
document.getElementById('add-to-cart-btn').addEventListener('click', function () {
    if (!window.isLogin) {
    alert('Silakan login terlebih dahulu!');
    bootstrap.Modal.getInstance(
        document.getElementById('product-modal')
    )?.hide();
    window.location.href = 'login.php';
    return;
}


    const variant = document.getElementById('variant-select').value;
    const qty = parseInt(document.getElementById('quantity-input').value);
if (qty < 1) {
    alert('Jumlah minimal 1');
    return;
}


    if (!variant) {
        alert('Pilih variant terlebih dahulu!');
        return;
    }

    fetch('?action=add_cart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_variant=${variant}&qty=${qty}`
    })
    .then(res => res.text())
.then(count => {

    if (count === 'login_required') {
        alert('Silakan login terlebih dahulu!');
        window.location.href = 'login.php';
        return;
    }

    document.getElementById('cart-count').innerText = count;
    alert('Produk ditambahkan ke keranjang!');
});

});

// ===============================
// BUKA MODAL KERANJANG
// ===============================
document.getElementById('cart-btn').addEventListener('click', function () {

    fetch('?action=view_cart')
    .then(res => res.text())
    .then(res => {

        if (res === 'login_required') {
            alert('Silakan login terlebih dahulu!');
            window.location.href = 'login.php';
            return;
        }

        const data = JSON.parse(res);

        let html = '';
        if (data.items.length === 0) {
            html = '<p>Keranjang kosong</p>';
        } else {
            data.items.forEach(item => {
                html += `
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <strong>${item.nama}</strong><br>
                        ${item.rasa} - ${item.ukuran} (${item.qty})
                    </div>
                    <div>
                        Rp ${item.subtotal.toLocaleString('id-ID')}
                    </div>
                </div>`;
            });
        }

        document.getElementById('cart-items').innerHTML = html;
        document.getElementById('cart-total').innerText =
            data.total.toLocaleString('id-ID');

        new bootstrap.Modal(document.getElementById('cart-modal')).show();
    });
});


// ===============================
// CHECKOUT
// ===============================
document.getElementById('checkout-btn').addEventListener('click', function () {

    fetch('?action=checkout')
    .then(res => res.text())
    .then(res => {
        if (res === 'ok') {
            alert('Checkout berhasil!');
            location.reload();
        } else {
            alert('Checkout gagal!');
        }
    });
});

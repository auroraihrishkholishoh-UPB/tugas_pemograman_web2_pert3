<?php

class Transaksi {

    private $db;

    public function __construct($mysqli){
        $this->db = $mysqli;
    }

    // ambil semua transaksi
    public function getOrders(){

        $query = "
        SELECT o.*, u.nama 
        FROM orders o
        LEFT JOIN users u ON o.id_user = u.id_user
        ORDER BY o.id_order DESC
        ";

        return mysqli_query($this->db,$query);
    }

    // update status transaksi
    public function updateStatus($id_order,$status){

        $query = "
        UPDATE orders 
        SET status='$status'
        WHERE id_order='$id_order'
        ";

        return mysqli_query($this->db,$query);
    }

    // ambil detail transaksi
    public function getDetail($id_order){

        $query = "
        SELECT i.*, p.nama_produk, v.rasa, v.ukuran
        FROM order_items i
        JOIN products p ON i.id_product = p.id_product
        JOIN product_variants v ON i.id_variant = v.id_variant
        WHERE i.id_order='$id_order'
        ";

        return mysqli_query($this->db,$query);
    }

}
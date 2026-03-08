<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tanggal = date('Y-m-d');
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $metode = $_POST['metode_pembayaran'];

    // insert header dulu
    mysqli_query($conn,"
        INSERT INTO tb_penjualan
        (tanggal, nama_pelanggan, total, metode)
        VALUES
        ('$tanggal', '$nama_pelanggan', 0, '$metode')
    ");

    $id_penjualan = mysqli_insert_id($conn);

    // kosongkan keranjang
    $_SESSION['keranjang_penjualan'] = [];
    $_SESSION['id_penjualan'] = $id_penjualan;

    header("Location: penjualan_detail.php?id=$id_penjualan");
    exit;
}
?>
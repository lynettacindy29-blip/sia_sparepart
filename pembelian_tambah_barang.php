<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_barang     = intval($_POST['id_barang']);
    $jumlah        = intval($_POST['jumlah']);
    $id_pembelian  = intval($_POST['id_pembelian']);

    if ($jumlah <= 0) {
        header("Location: pembelian_detail.php?id=$id_pembelian");
        exit;
    }

    // ambil data barang
    $qBarang = mysqli_query($conn, "
        SELECT * FROM tb_barang WHERE id = $id_barang
    ");

    if (!$qBarang) {
        die("Query barang error: " . mysqli_error($conn));
    }

    $barang = mysqli_fetch_assoc($qBarang);

    if (!$barang) {
        die("Barang tidak ditemukan");
    }

    $harga_beli = $barang['harga_beli'];
    $subtotal   = $harga_beli * $jumlah;

    // buat keranjang kalau belum ada
    if (!isset($_SESSION['keranjang_pembelian'])) {
        $_SESSION['keranjang_pembelian'] = [];
    }

    // tambahkan ke keranjang
    $_SESSION['keranjang_pembelian'][] = [
        'id_barang'   => $id_barang,
        'nama_barang' => $barang['nama_barang'],
        'harga_beli'  => $harga_beli,
        'jumlah'      => $jumlah,
        'subtotal'    => $subtotal
    ];

    header("Location: pembelian_detail.php?id=$id_pembelian");
    exit;
}
?>
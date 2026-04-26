<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_barang     = intval($_POST['id_barang']);
    $jumlah        = intval($_POST['jumlah']);
    $id_pembelian  = intval($_POST['id_pembelian']);
    // TANGKAP HARGA BELI YANG DI-INPUT USER DI FORM
    $harga_beli_baru = floatval($_POST['harga_beli']);

    if ($jumlah <= 0 || $harga_beli_baru <= 0) {
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

    // GUNAKAN HARGA DARI FORM, BUKAN DARI DATABASE LAMA
    $subtotal   = $harga_beli_baru * $jumlah;

    // buat keranjang kalau belum ada
    if (!isset($_SESSION['keranjang_pembelian'])) {
        $_SESSION['keranjang_pembelian'] = [];
    }

    // tambahkan ke keranjang
    $_SESSION['keranjang_pembelian'][] = [
        'id_barang'   => $id_barang,
        'nama_barang' => $barang['nama_barang'],
        'harga_beli'  => $harga_beli_baru, // Pakai harga baru
        'jumlah'      => $jumlah,
        'subtotal'    => $subtotal
    ];

    header("Location: pembelian_detail.php?id=$id_pembelian");
    exit;
}
?>
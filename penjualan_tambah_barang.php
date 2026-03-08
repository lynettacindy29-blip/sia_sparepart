<?php
session_start();
include "config/db.php";

/* CEK METHOD */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penjualan.php");
    exit;
}

/* VALIDASI POST */
if (!isset($_POST['id_penjualan'], $_POST['id_barang'], $_POST['jumlah'])) {
    die("Data tidak lengkap");
}

$id_penjualan = intval($_POST['id_penjualan']);
$id_barang = intval($_POST['id_barang']);
$jumlah = intval($_POST['jumlah']);

if ($jumlah <= 0) {
    die("Jumlah tidak valid");
}

/* ambil barang */
$q = mysqli_query($conn,"
    SELECT nama_barang, harga_jual, stok 
    FROM tb_barang 
    WHERE id=$id_barang
");

if (!$q) {
    die("Query error: " . mysqli_error($conn));
}

$barang = mysqli_fetch_assoc($q);

if (!$barang) {
    die("Barang tidak ditemukan");
}

if ($barang['stok'] < $jumlah) {
    die("Stok tidak cukup");
}

$harga = $barang['harga_jual'];
$subtotal = $harga * $jumlah;

/* pastikan session ada */
if (!isset($_SESSION['keranjang_penjualan'])) {
    $_SESSION['keranjang_penjualan'] = [];
}

$_SESSION['keranjang_penjualan'][] = [
    'id_barang' => $id_barang,
    'nama_barang' => $barang['nama_barang'],
    'harga' => $harga,
    'jumlah' => $jumlah,
    'subtotal' => $subtotal
];

header("Location: penjualan_detail.php?id=$id_penjualan");
exit;
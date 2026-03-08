<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $id_kategori = $_POST['id_kategori'];
    $harga_beli  = $_POST['harga_pokok'];   // dari form
    $harga_jual  = $_POST['harga_jual'];
    $stok        = $_POST['stok'];

    // validasi sederhana
    if ($nama_barang == '' || $id_kategori == '' || $harga_beli == '' || $harga_jual == '') {
        die("Data tidak lengkap!");
    }

    // jika stok kosong atau minus, default 0
    if ($stok == '' || $stok < 0) {
        $stok = 0;
    }

    // buat kode barang otomatis (opsional tapi bagus)
    $kode_barang = 'BRG' . time();

    $insert = mysqli_query($conn, "
        INSERT INTO tb_barang
        (kode_barang, nama_barang, id_kategori, harga_beli, harga_jual, stok)
        VALUES
        ('$kode_barang', '$nama_barang', '$id_kategori', '$harga_beli', '$harga_jual', '$stok')
    ");

    if ($insert) {
        header("Location: data_barang.php?status=sukses");
        exit;
    } else {
        die("Gagal menyimpan data: " . mysqli_error($conn));
    }
}
?>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id            = intval($_POST['id']);
    $nama_barang   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $id_kategori   = $_POST['id_kategori'];
    $harga_beli    = $_POST['harga_beli'];
    $harga_jual    = $_POST['harga_jual'];
    $stok          = $_POST['stok'];

    // validasi
    if ($nama_barang == '' || $id_kategori == '' || $harga_beli == '' || $harga_jual == '') {
        die("Data tidak lengkap!");
    }

    // stok default 0 jika kosong atau minus
    if ($stok == '' || $stok < 0) {
        $stok = 0;
    }

    $update = mysqli_query($conn, "
        UPDATE tb_barang SET
            nama_barang  = '$nama_barang',
            id_kategori  = '$id_kategori',
            harga_beli   = '$harga_beli',
            harga_jual   = '$harga_jual',
            stok         = '$stok'
        WHERE id = $id
    ");

    if ($update) {
        header("Location: data_barang.php?status=update_sukses");
        exit;
    } else {
        die("Gagal update: " . mysqli_error($conn));
    }
}
?>
<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tanggal      = $_POST['tanggal'];
    $id_supplier  = intval($_POST['id_supplier']);
    $metode       = $_POST['metode'];

    // simpan header dulu, total sementara 0

mysqli_query($conn, "
    INSERT INTO tb_pembelian
    (tanggal, id_supplier, total, metode)
    VALUES
    ('$tanggal', '$id_supplier', 0, '$metode')
");

    $id_pembelian = mysqli_insert_id($conn);

    // buat keranjang kosong
    $_SESSION['keranjang_pembelian'] = [];

    // simpan id pembelian di session
    $_SESSION['id_pembelian'] = $id_pembelian;

    header("Location: pembelian_detail.php?id=$id_pembelian");
    exit;
}
?>
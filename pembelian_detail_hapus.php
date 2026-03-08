<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id_detail = $_GET['id'];
$id_pembelian = $_GET['pembelian_id'];

/* ambil data detail */
$d = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM tb_pembelian_detail WHERE id='$id_detail'
"));

/* kembalikan stok */
mysqli_query($conn, "
    UPDATE tb_barang
    SET stok = stok - {$d['qty']}
    WHERE id = '{$d['barang_id']}'
");

/* kurangi total pembelian */
mysqli_query($conn, "
    UPDATE tb_pembelian
    SET total = total - {$d['subtotal']}
    WHERE id = '$id_pembelian'
");

/* hapus detail */
mysqli_query($conn, "
    DELETE FROM tb_pembelian_detail WHERE id='$id_detail'
");

header("Location: pembelian_detail.php?id=$id_pembelian");
exit;

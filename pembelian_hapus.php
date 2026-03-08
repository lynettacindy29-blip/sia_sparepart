<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: pembelian.php");
    exit;
}

/* ============================
   1️⃣ AMBIL DETAIL PEMBELIAN
============================ */
$qDetail = mysqli_query($conn, "
    SELECT * FROM tb_detail_pembelian
    WHERE id_pembelian = '$id'
");

if (!$qDetail) {
    die("Query Detail Error: " . mysqli_error($conn));
}

/* ============================
   2️⃣ KURANGI STOK
============================ */
while ($d = mysqli_fetch_assoc($qDetail)) {

    mysqli_query($conn, "
        UPDATE tb_barang
        SET stok = stok - {$d['jumlah']}
        WHERE id = {$d['id_barang']}
    ");
}

/* ============================
   3️⃣ HAPUS DETAIL
============================ */
mysqli_query($conn, "
    DELETE FROM tb_detail_pembelian
    WHERE id_pembelian = '$id'
");

/* ============================
   4️⃣ HAPUS HEADER
============================ */
mysqli_query($conn, "
    DELETE FROM tb_pembelian
    WHERE id = '$id'
");

header("Location: pembelian.php");
exit;
?>
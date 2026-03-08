<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// ambil data penjualan
$q = mysqli_query($conn, "
    SELECT barang_id, jumlah 
    FROM tb_penjualan 
    WHERE id='$id'
");

if (!$q) {
    die("Query Error: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($q);

if ($data) {

    // kembalikan stok
    mysqli_query($conn, "
        UPDATE tb_barang 
        SET stok = stok + {$data['jumlah']}
        WHERE id = '{$data['barang_id']}'
    ");

    // hapus penjualan
    mysqli_query($conn, "DELETE FROM tb_penjualan WHERE id='$id'");
}

header("Location: penjualan.php");
exit;

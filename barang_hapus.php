<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

// cek stok
$cek = mysqli_query($conn, "
    SELECT stok FROM tb_barang WHERE id = $id
");

$data = mysqli_fetch_assoc($cek);

if ($data['stok'] > 0) {
    echo "<script>
        alert('Barang tidak bisa dihapus karena stok masih ada!');
        window.location='data_barang.php';
    </script>";
    exit;
}

// hapus barang
$hapus = mysqli_query($conn, "
    DELETE FROM tb_barang WHERE id = $id
");

if ($hapus) {
    header("Location: data_barang.php?status=hapus_sukses");
} else {
    die("Gagal hapus: " . mysqli_error($conn));
}
?>
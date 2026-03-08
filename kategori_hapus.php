<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: data_kategori.php");
    exit;
}

$id = intval($_GET['id']);

/* CEK APAKAH KATEGORI MASIH DIPAKAI BARANG */
$cek = mysqli_query($conn, "
    SELECT id FROM tb_barang WHERE id_kategori = $id
");

if (mysqli_num_rows($cek) > 0) {
    echo "<script>
        alert('Kategori tidak bisa dihapus karena masih dipakai barang!');
        window.location='data_kategori.php';
    </script>";
    exit;
}

/* JIKA TIDAK DIPAKAI → HAPUS */
$hapus = mysqli_query($conn, "
    DELETE FROM tb_kategori WHERE id = $id
");

if ($hapus) {
    header("Location: data_kategori.php?status=hapus_sukses");
} else {
    die("Gagal hapus: " . mysqli_error($conn));
}
?>
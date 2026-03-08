<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

// cek apakah supplier dipakai di pembelian
$cek = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM tb_pembelian 
    WHERE id_supplier = $id
");

if ($cek) {
    $data = mysqli_fetch_assoc($cek);

    if ($data['total'] > 0) {
        echo "<script>
            alert('Supplier tidak bisa dihapus karena sudah dipakai pembelian!');
            window.location='supplier.php';
        </script>";
        exit;
    }
}

// hapus supplier
$hapus = mysqli_query($conn, "
    DELETE FROM tb_supplier WHERE id = $id
");

if ($hapus) {
    header("Location: supplier.php?status=hapus_sukses");
} else {
    die("Gagal hapus: " . mysqli_error($conn));
}
?>
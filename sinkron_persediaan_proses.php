<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. HITUNG TOTAL NILAI PERSEDIAAN BERDASARKAN STOK AWAL & HPP AWAL
$qStok = mysqli_query($conn, "SELECT SUM(stok_awal * hpp_awal) AS total_aset FROM tb_barang");
$dStok = mysqli_fetch_assoc($qStok);

$total_nilai_persediaan = $dStok['total_aset'] ? $dStok['total_aset'] : 0; 

// 2. UPDATE KE TABEL AKUN (Mencari akun yang namanya mengandung 'Persediaan')
$update = mysqli_query($conn, "
    UPDATE tb_akun 
    SET saldo_awal = '$total_nilai_persediaan' 
    WHERE nama_akun LIKE '%Persediaan%' OR kode_akun = '104'
");

if ($update) {
    $rupiah = number_format($total_nilai_persediaan, 0, ',', '.');
    echo "<script>alert('Sinkronisasi Berhasil! Nilai Persediaan Rp $rupiah berhasil di-update ke Neraca Awal.'); window.location='data_barang.php';</script>";
} else {
    echo "<script>alert('Gagal melakukan sinkronisasi: " . mysqli_error($conn) . "'); window.location='data_barang.php';</script>";
}
?>
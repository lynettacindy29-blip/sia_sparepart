<?php
session_start();
include "config/db.php";

$id_pembelian = intval($_POST['id_pembelian']);
$jumlah_bayar = intval($_POST['jumlah_bayar']);
$tanggal = date('Y-m-d');

// ambil data pembelian
$q = mysqli_query($conn, "
    SELECT * FROM tb_pembelian WHERE id = $id_pembelian
");
$data = mysqli_fetch_assoc($q);

$sisa_lama = $data['sisa_hutang'];
$sisa_baru = $sisa_lama - $jumlah_bayar;

// tentukan status baru
if ($sisa_baru <= 0) {
    $status = 'lunas';
    $sisa_baru = 0;
} else {
    $status = 'belum_lunas';
}

// update pembelian
mysqli_query($conn, "
    UPDATE tb_pembelian
    SET 
        sisa_hutang = $sisa_baru,
        status = '$status'
    WHERE id = $id_pembelian
");

// simpan ke tabel pembayaran
mysqli_query($conn, "
    INSERT INTO tb_pembayaran
    (id_pembelian, tanggal_bayar, jumlah_bayar)
    VALUES
    ($id_pembelian, '$tanggal', $jumlah_bayar)
");

// ======================
// INSERT JURNAL BAYAR HUTANG
// ======================

$qSetting = mysqli_query($conn, "
    SELECT * FROM tb_setting_jurnal 
    WHERE jenis_transaksi = 'bayar_hutang'
");
$setting = mysqli_fetch_assoc($qSetting);

$akun_debit  = $setting['akun_debit'];   // Hutang
$akun_kredit = $setting['akun_kredit'];  // Kas

// debit hutang
mysqli_query($conn, "
    INSERT INTO tb_jurnal
    (tanggal, keterangan, id_akun, debit, kredit)
    VALUES
    ('$tanggal', 'Bayar Hutang', $akun_debit, $jumlah_bayar, 0)
");

// kredit kas
mysqli_query($conn, "
    INSERT INTO tb_jurnal
    (tanggal, keterangan, id_akun, debit, kredit)
    VALUES
    ('$tanggal', 'Bayar Hutang', $akun_kredit, 0, $jumlah_bayar)
");

header("Location: hutang_pembelian.php");
exit;
?>
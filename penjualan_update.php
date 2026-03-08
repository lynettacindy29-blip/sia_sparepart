<?php
session_start();
include "config/db.php";

$id             = $_POST['id'];
$nama_pelanggan = $_POST['nama_pelanggan'];
$harga_jual     = $_POST['harga_jual'];
$jumlah         = $_POST['jumlah'];
$status         = $_POST['status'];

$total = $harga_jual * $jumlah;

// ambil harga pokok lama
$q = mysqli_query($conn, "SELECT harga_pokok FROM tb_penjualan WHERE id='$id'");
$d = mysqli_fetch_assoc($q);
$laba = ($harga_jual - $d['harga_pokok']) * $jumlah;

mysqli_query($conn, "
    UPDATE tb_penjualan SET
        nama_pelanggan='$nama_pelanggan',
        harga_jual='$harga_jual',
        jumlah='$jumlah',
        total='$total',
        laba='$laba',
        status='$status'
    WHERE id='$id'
");

header("Location: penjualan.php");
exit;

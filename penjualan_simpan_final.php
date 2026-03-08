<?php
session_start();
include "config/db.php";

$id_penjualan = $_POST['id_penjualan'];

$qHeader = mysqli_query($conn,"
    SELECT * FROM tb_penjualan WHERE id=$id_penjualan
");
$header = mysqli_fetch_assoc($qHeader);

$metode = $header['metode'];
$tanggal = $header['tanggal'];

$total = 0;

/* loop keranjang */
foreach($_SESSION['keranjang_penjualan'] as $item){

    $id_barang = $item['id_barang'];
    $harga = $item['harga'];
    $qty = $item['jumlah'];
    $subtotal = $item['subtotal'];

    $total += $subtotal;

    // insert detail
    mysqli_query($conn,"
        INSERT INTO tb_detail_penjualan
        (id_penjualan,id_barang,qty,harga,subtotal)
        VALUES
        ($id_penjualan,$id_barang,$qty,$harga,$subtotal)
    ");

    // kurangi stok
    mysqli_query($conn,"
        UPDATE tb_barang
        SET stok = stok - $qty
        WHERE id=$id_barang
    ");
}

/* set status */
if($metode == 'cash'){
    $status = 'lunas';
    $sisa = 0;
}else{
    $status = 'belum_lunas';
    $sisa = $total;
}

/* update header */
mysqli_query($conn,"
    UPDATE tb_penjualan
    SET total=$total,
        status_bayar='$status',
        sisa_piutang=$sisa
    WHERE id=$id_penjualan
");

/* bersihkan session */
unset($_SESSION['keranjang_penjualan']);
unset($_SESSION['id_penjualan']);

header("Location: penjualan.php");
exit;
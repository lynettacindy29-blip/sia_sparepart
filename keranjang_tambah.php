<?php
include "../config/db.php";

$id_penjualan = $_POST['id_penjualan'];
$id_barang = $_POST['id_barang'];
$qty = $_POST['qty'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM tb_barang WHERE id='$id_barang'
"));

$harga = $data['harga_jual'];

mysqli_query($conn,"
INSERT INTO tb_detail_penjualan
(id_penjualan,id_barang,qty,harga)
VALUES
('$id_penjualan','$id_barang','$qty','$harga')
");

header("Location: penjualan_keranjang.php?id=$id_penjualan");
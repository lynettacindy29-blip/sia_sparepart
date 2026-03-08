<?php
include "../config/db.php";

$nama = $_POST['nama_pelanggan'];

mysqli_query($conn,"INSERT INTO tb_penjualan(nama_pelanggan,tanggal)
VALUES('$nama',NOW())");

$id_penjualan = mysqli_insert_id($conn);

header("Location: penjualan_keranjang.php?id=$id_penjualan");
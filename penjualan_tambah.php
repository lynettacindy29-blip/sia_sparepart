<?php 
include "../config/db.php";
include "../sidebar.php";
?>

<link rel="stylesheet" href="../inc/style.css">

<div class="content">

<h2>Tambah Penjualan</h2>

<div class="card">

<form method="POST" action="penjualan_simpan.php">

<label>Nama Pelanggan</label>
<input type="text" name="nama_pelanggan" required>

<br><br>

<button type="submit">Buat Transaksi</button>

</form>

</div>

</div>
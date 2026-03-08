<?php
include "../config/db.php";
include "../sidebar.php";

$id_penjualan = $_GET['id'];

$barang = mysqli_query($conn,"SELECT * FROM tb_barang");

$detail = mysqli_query($conn,"
SELECT d.*, b.nama_barang 
FROM tb_detail_penjualan d
JOIN tb_barang b ON d.id_barang=b.id
WHERE id_penjualan='$id_penjualan'
");
?>

<h2>Keranjang Penjualan</h2>

<form method="POST" action="keranjang_tambah.php">

<input type="hidden" name="id_penjualan" value="<?= $id_penjualan ?>">

<label>Barang</label>

<select name="id_barang" required>

<option value="">Pilih Barang</option>

<?php while($b=mysqli_fetch_assoc($barang)){ ?>

<option value="<?= $b['id'] ?>">
<?= $b['nama_barang'] ?> | Stok <?= $b['stok'] ?>
</option>

<?php } ?>

</select>

<label>Jumlah</label>
<input type="number" name="qty" required>

<button type="submit">Tambah</button>

</form>

<hr>

<h3>Detail Keranjang</h3>

<table border="1" cellpadding="8">

<tr>
<th>Barang</th>
<th>Qty</th>
<th>Harga</th>
<th>Subtotal</th>
<th>Aksi</th>
</tr>

<?php
$total = 0;

while($d=mysqli_fetch_assoc($detail)){

$subtotal = $d['qty'] * $d['harga'];
$total += $subtotal;
?>

<tr>

<td><?= $d['nama_barang'] ?></td>
<td><?= $d['qty'] ?></td>
<td><?= number_format($d['harga']) ?></td>
<td><?= number_format($subtotal) ?></td>

<td>
<a href="keranjang_hapus.php?id=<?= $d['id'] ?>&id_penjualan=<?= $id_penjualan ?>">
Hapus
</a>
</td>

</tr>

<?php } ?>

<tr>
<td colspan="3"><b>Total</b></td>
<td colspan="2"><?= number_format($total) ?></td>
</tr>

</table>
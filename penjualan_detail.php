<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID Penjualan tidak ditemukan");
}

$id_penjualan = intval($_GET['id']);

$qHeader = mysqli_query($conn,"
    SELECT * FROM tb_penjualan WHERE id=$id_penjualan
");
$dataHeader = mysqli_fetch_assoc($qHeader);

if(!$dataHeader){
    die("Data tidak ditemukan");
}

$qBarang = mysqli_query($conn,"
    SELECT * FROM tb_barang ORDER BY nama_barang ASC
");
?>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">

<h3>Detail Penjualan</h3>

<p>
Tanggal: <?= $dataHeader['tanggal'] ?><br>
Pelanggan: <?= $dataHeader['nama_pelanggan'] ?><br>
Metode: <?= $dataHeader['metode'] ?>
</p>

<hr>

<h4>Tambah Barang</h4>

<form method="POST" action="penjualan_tambah_barang.php">
    <input type="hidden" name="id_penjualan" value="<?= $id_penjualan ?>">

    <label>Barang</label>
    <select name="id_barang" required>
        <option value="">-- Pilih Barang --</option>
        <?php while($b=mysqli_fetch_assoc($qBarang)) { ?>
            <option value="<?= $b['id'] ?>">
                <?= $b['nama_barang'] ?> (Stok: <?= $b['stok'] ?>)
            </option>
        <?php } ?>
    </select>

    <label>Jumlah</label>
    <input type="number" name="jumlah" required min="1">

    <br><br>
    <button type="submit">Tambah</button>
</form>

<hr>

<h4>Keranjang</h4>

<table border="1" cellpadding="8">
<tr>
    <th>Barang</th>
    <th>Harga</th>
    <th>Qty</th>
    <th>Subtotal</th>
</tr>

<?php
$total = 0;

if(!empty($_SESSION['keranjang_penjualan'])){
    foreach($_SESSION['keranjang_penjualan'] as $item){
        $total += $item['subtotal'];
        echo "<tr>
            <td>{$item['nama_barang']}</td>
            <td>{$item['harga']}</td>
            <td>{$item['jumlah']}</td>
            <td>{$item['subtotal']}</td>
        </tr>";
    }
}
?>

<tr>
    <td colspan="3"><b>Total</b></td>
    <td><b><?= $total ?></b></td>
</tr>
</table>

<br>

<form method="POST" action="penjualan_simpan_final.php">
    <input type="hidden" name="id_penjualan" value="<?= $id_penjualan ?>">
    <button type="submit">Simpan Transaksi</button>
</form>

</div>
</div>
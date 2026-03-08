<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID Pembelian tidak ditemukan");
}

$id_pembelian = intval($_GET['id']);

/* ambil data header + supplier */
$qHeader = mysqli_query($conn, "
    SELECT p.*, s.nama_supplier
    FROM tb_pembelian p
    JOIN tb_supplier s ON p.id_supplier = s.id
    WHERE p.id = $id_pembelian
");

if (!$qHeader) {
    die("Query header error: " . mysqli_error($conn));
}

$dataHeader = mysqli_fetch_assoc($qHeader);

if (!$dataHeader) {
    die("Data pembelian tidak ditemukan");
}

/* ambil daftar barang */
$qBarang = mysqli_query($conn, "
    SELECT * FROM tb_barang ORDER BY nama_barang ASC
");

if (!$qBarang) {
    die("Query barang error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">

<h3>Detail Pembelian</h3>

<p>
Tanggal: <?= $dataHeader['tanggal'] ?><br>
Supplier: <?= $dataHeader['nama_supplier'] ?><br>
Metode: <?= $dataHeader['metode'] ?>
</p>

<hr>

<h4>Tambah Barang</h4>

<form method="POST" action="pembelian_tambah_barang.php">
    <input type="hidden" name="id_pembelian" value="<?= $id_pembelian ?>">

    <label>Barang</label>
    <select name="id_barang" required>
        <option value="">-- Pilih Barang --</option>
        <?php while($b = mysqli_fetch_assoc($qBarang)) { ?>
            <option value="<?= $b['id'] ?>">
                <?= $b['nama_barang'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Jumlah</label>
    <input type="number" name="jumlah" required min="1">

    <button type="submit">Tambah</button>
</form>

<hr>

<h4>Keranjang</h4>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Nama Barang</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Subtotal</th>
    </tr>

<?php
$total = 0;

if (!empty($_SESSION['keranjang_pembelian'])) {
    foreach ($_SESSION['keranjang_pembelian'] as $item) {
        $total += $item['subtotal'];
        echo "<tr>
            <td>{$item['nama_barang']}</td>
            <td>{$item['harga_beli']}</td>
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

<form method="POST" action="pembelian_simpan.php">
    <input type="hidden" name="id_pembelian" value="<?= $id_pembelian ?>">
    <button type="submit">Simpan Transaksi</button>
</form>

</div>
</body>
</html>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$qSupplier = mysqli_query($conn, "SELECT * FROM tb_supplier ORDER BY nama_supplier ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card" style="width:500px;">

<h3>Buat Transaksi Pembelian</h3>

<form method="POST" action="pembelian_proses.php">

    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

    <label>Supplier</label>
    <select name="id_supplier" required>
        <option value="">-- Pilih Supplier --</option>
        <?php while($s = mysqli_fetch_assoc($qSupplier)) { ?>
            <option value="<?= $s['id'] ?>">
                <?= $s['nama_supplier'] ?>
            </option>
        <?php } ?>
    </select>

<label>Metode Pembayaran</label>
<select name="metode" required>
    <option value="tunai">Tunai</option>
    <option value="kredit">Kredit</option>
</select>

    <button type="submit">Buat Transaksi</button>

</form>

</div>
</div>

</body>
</html>
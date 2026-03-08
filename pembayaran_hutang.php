<?php
session_start();
include "config/db.php";

$id_pembelian = intval($_GET['id']);

$q = mysqli_query($conn, "
    SELECT p.*, s.nama_supplier
    FROM tb_pembelian p
    JOIN tb_supplier s ON p.id_supplier = s.id
    WHERE p.id = $id_pembelian
");

$data = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>
<head>
<title>Bayar Hutang</title>
<link rel="stylesheet" href="inc/style.css">
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">

<h3>Pembayaran Hutang</h3>

<p>
Supplier: <?= $data['nama_supplier'] ?><br>
Total: <?= number_format($data['total']) ?><br>
Sisa Hutang: <b><?= number_format($data['sisa_hutang']) ?></b>
</p>

<form method="POST" action="pembayaran_hutang_proses.php">
    <input type="hidden" name="id_pembelian" value="<?= $id_pembelian ?>">

    <label>Jumlah Bayar</label>
    <input type="number" name="jumlah_bayar" required>

    <button type="submit">Bayar</button>
</form>

</div>
</body>
</html>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$q = mysqli_query($conn, "
    SELECT p.*, s.nama_supplier
    FROM tb_pembelian p
    JOIN tb_supplier s ON p.id_supplier = s.id
    WHERE p.status = 'belum_lunas'
    ORDER BY p.tanggal DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Daftar Hutang Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">

<h3>Daftar Hutang Pembelian</h3>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
    <th>Tanggal</th>
    <th>Supplier</th>
    <th>Total</th>
    <th>Sisa Hutang</th>
    <th>Aksi</th>
</tr>

<?php while($d = mysqli_fetch_assoc($q)) { ?>
<tr>
    <td><?= $d['tanggal'] ?></td>
    <td><?= $d['nama_supplier'] ?></td>
    <td><?= number_format($d['total']) ?></td>
    <td><b><?= number_format($d['sisa_hutang']) ?></b></td>
    <td>
        <a href="pembayaran_hutang.php?id=<?= $d['id'] ?>">
            Bayar
        </a>
    </td>
</tr>
<?php } ?>

</table>

</div>
</body>
</html>
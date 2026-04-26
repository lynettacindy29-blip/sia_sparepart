<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM tb_supplier ORDER BY id DESC");

if (!$q) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Supplier</title>
<link rel="stylesheet" href="inc/style.css">

<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.header { background:#fff; padding:15px; border-radius:8px; font-size:22px; font-weight:bold; margin-bottom:20px; }
.card { background:#fff; padding:20px; border-radius:8px; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#f1f1f1; }
.btn { padding:6px 12px; border-radius:5px; text-decoration:none; font-size:13px; display:inline-block; margin: 2px; }
.btn-tambah { background:#007bff; color:#fff; }
.btn-edit { background:#ffc107; color:#000; }
.btn-delete { background:#dc3545; color:#fff; }
.btn-pay { background:#28a745; color:#fff; } /* Warna hijau untuk tombol bayar */
.btn-edit:hover { background:#e0a800; }
.btn-delete:hover { background:#c82333; }
.btn-pay:hover { background:#218838; }
.text-right { text-align: right !important; } 
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="header">DATA SUPPLIER</div>

<div class="card">

<a href="supplier_tambah.php" class="btn btn-tambah">+ Tambah Supplier</a>

<input 
    type="text" 
    id="searchInput" 
    placeholder="Cari nama supplier..."
    style="float:right; padding:8px; width:250px; margin-bottom:10px;"
>

<table id="tabelSupplier">
<thead>
<tr>
    <th>No</th>
    <th>Nama Supplier</th>
    <th>Alamat</th>
    <th>Telepon</th>
    <th>Saldo Awal Utang</th> 
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php 
$no = 1; 
$total_saldo_utang = 0; 
?>
<?php while ($row = mysqli_fetch_assoc($q)) { 
    $saldo_awal = isset($row['saldo_awal_utang']) ? $row['saldo_awal_utang'] : 0;
    $total_saldo_utang += $saldo_awal;
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nama_supplier']) ?></td>
    <td><?= htmlspecialchars($row['alamat']) ?></td>
    <td><?= htmlspecialchars($row['telp']) ?></td>
    <td class="text-right">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></td> 
    <td>
        <?php if ($saldo_awal > 0) { ?>
            <a href="pembayaran_hutang.php?id_supplier=<?= $row['id'] ?>" class="btn btn-pay">Bayar</a>
        <?php } ?>
        
        <a href="supplier_edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
        <a href="supplier_hapus.php?id=<?= $row['id'] ?>" 
           onclick="return confirm('Yakin ingin menghapus?')" 
           class="btn btn-delete">
           Hapus
        </a>
    </td>
</tr>
<?php } ?>

</tbody>
<tfoot>
    <tr style="background: #f8fafc; font-weight: bold;">
        <td colspan="4" class="text-right" style="padding-right: 15px;">TOTAL RINCIAN SALDO AWAL UTANG:</td>
        <td class="text-right" style="color: #dc3545;">Rp <?= number_format($total_saldo_utang, 0, ',', '.') ?></td>
        <td></td>
    </tr>
</tfoot>
</table>

</div>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tabelSupplier tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
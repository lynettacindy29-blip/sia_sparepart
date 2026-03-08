<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$q = mysqli_query($conn, "
    SELECT 
        pb.id,
        s.nama_supplier,
        br.nama_barang,
        br.harga_beli AS harga_barang,
        pd.harga_beli,
        pd.jumlah,
        pd.subtotal,
        pb.tanggal
    FROM tb_pembelian pb
    JOIN tb_supplier s ON pb.id_supplier = s.id
    JOIN tb_detail_pembelian pd ON pb.id = pd.id_pembelian
    JOIN tb_barang br ON pd.id_barang = br.id
    ORDER BY pb.id DESC
");

?>
<!DOCTYPE html>
<html>
<head>
<title>Data Pembelian</title>
<link rel="stylesheet" href="inc/style.css">

<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.header { background:#fff; padding:15px; border-radius:8px; font-size:22px; font-weight:bold; margin-bottom:20px; }
.card { background:#fff; padding:20px; border-radius:8px; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#f1f1f1; }
.btn { padding:6px 12px; border-radius:5px; text-decoration:none; font-size:13px; }
.btn-tambah { background:#007bff; color:#fff; }
.btn-detail { background:#17a2b8; color:#fff; }
.btn-edit {
    background:#ffc107;
    color:#000;
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:13px;
    margin-right:4px;
    display:inline-block;
}

.btn-delete {
    background:#dc3545;
    color:#fff;
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:13px;
    display:inline-block;
}

.btn-edit:hover {
    background:#e0a800;
}

.btn-delete:hover {
    background:#c82333;
}
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="header">DATA PEMBELIAN</div>

<div class="card">
<a href="pembelian_tambah.php" class="btn btn-tambah">+ Tambah Pembelian</a>
<input 
    type="text" 
    id="searchInput" 
    placeholder="Search"
    style="float:right; padding:8px; width:250px; margin-bottom:10px;"
>
<table id="tabelPembelian">
<thead>
<tr>
    <th>ID</th>
    <th>Nama Supplier</th>
    <th>Nama Barang</th>
    <th>Harga Barang</th>
    <th>Harga Beli</th>
    <th>Jumlah</th>
    <th>Total Harga</th>
    <th>Tanggal</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php $no = 1; ?>
<?php while ($row = mysqli_fetch_assoc($q)) { ?>
<tr>
    <td><?= $no++ ?></td>

    <td><?= $row['nama_supplier'] ?></td>
    <td><?= $row['nama_barang'] ?></td>

    <td>Rp <?= number_format($row['harga_barang'],0,',','.') ?></td>
    <td>Rp <?= number_format($row['harga_beli'],0,',','.') ?></td>

    <td><?= $row['jumlah'] ?></td>

    <td>Rp <?= number_format($row['subtotal'],0,',','.') ?></td>

    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>

    <!-- AKSI TIDAK HILANG -->
    <td>
        <a href="pembelian_edit.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
        <a href="pembelian_hapus.php?id=<?= $row['id'] ?>" 
           onclick="return confirm('Yakin ingin menghapus data ini?')" 
           class="btn-delete">
           Hapus
        </a>
    </td>
</tr>
<?php } ?>


</table>

</div>
</div>
<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tabelPembelian tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>

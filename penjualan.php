<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$keyword = '';
$where = '';

$keyword = '';
$where = '';

if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where = "WHERE 
        nama_pelanggan LIKE '%$keyword%' 
        OR nama_barang LIKE '%$keyword%'
    ";
}

$q = mysqli_query($conn, "
    SELECT *
    FROM tb_penjualan
    $where
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Data Penjualan</title>

<link rel="stylesheet" href="inc/style.css">

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}
.content {
    margin-left: 240px;
    padding: 30px;
}
.header {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 20px;
}
.card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
}
.btn {
    background: #007bff;
    color: #fff;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
}
.btn:hover {
    background: #0056b3;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
    font-size: 14px;
}
table th {
    background: #f1f1f1;
}
.status-lunas {
    color: green;
    font-weight: bold;
}
.status-kredit {
    color: orange;
    font-weight: bold;
}
.btn-edit {
    background:#ffc107;
    color:#000;
}
.btn-hapus {
    background:#dc3545;
    color:#fff;
}
.aksi-btn {
    display: flex;
    justify-content: center;
    gap: 6px;
}

</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">

<div class="header">DATA PENJUALAN</div>

<div class="card">

<a href="penjualan/penjualan_tambah.php" class="btn">+ Tambah Data</a>
<form method="GET" style="margin-top:15px; margin-bottom:15px;">
  <input
    type="text"
    id="search"
    placeholder="Search"
    style="padding:8px; width:300px; margin-bottom:15px;"
>
    <button type="submit" class="btn">Search</button>

    <?php if (isset($_GET['keyword']) && $_GET['keyword'] != '') { ?>
        <a href="penjualan.php" class="btn" style="background:#6c757d;">Reset</a>
    <?php } ?>
</form>

<table id="tabel-penjualan">            
<tr>
    <th>No</th>
    <th>Nama Pelanggan</th>
    <th>Nama Barang</th>
    <th>Harga Pokok</th>
    <th>Harga Jual</th>
    <th>Jumlah</th>
    <th>Total</th>
    <th>Laba</th>
    <th>Status</th>
    <th>Tanggal</th>
    <th>Tgl Batas Kredit</th>
    <th>Aksi</th>

</tr>

<?php
$no = 1;
while ($row = mysqli_fetch_assoc($q)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_pelanggan'] ?></td>
    <td><?= $row['nama_barang'] ?></td>
    <td>Rp <?= number_format($row['harga_pokok'],0,',','.') ?></td>
    <td>Rp <?= number_format($row['harga_jual'],0,',','.') ?></td>
    <td><?= $row['jumlah'] ?></td>
    <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
    <td>Rp <?= number_format($row['laba'],0,',','.') ?></td>
    <td class="<?= $row['status']=='Lunas'?'status-lunas':'status-kredit' ?>">
        <?= $row['status'] ?>
    </td>
    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
    <td>
    <?= $row['tanggal_batas_kredit'] 
        ? date('d-m-Y', strtotime($row['tanggal_batas_kredit'])) 
        : '-' ?>
    </td>
<td>
    <div class="aksi-btn">
    <a href="penjualan_edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
    <a href="penjualan_hapus.php?id=<?= $row['id'] ?>" 
       class="btn btn-hapus"
       onclick="return confirm('Yakin ingin menghapus data ini?')">
       Hapus
    </a>
</td>

</tr>
<?php } ?>

</table>

</div>
</div>
<script>
const searchInput = document.getElementById("search");
const table = document.getElementById("tabel-penjualan");

searchInput.addEventListener("keyup", function () {
    const keyword = this.value;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "penjualan_search.php?keyword=" + encodeURIComponent(keyword), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            table.innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});
</script>

</body>
</html>

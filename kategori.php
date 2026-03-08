<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM tb_kategori ORDER BY id DESC");

if (!$q) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Kategori</title>
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
.btn-edit { background:#ffc107; color:#000; }
.btn-delete { background:#dc3545; color:#fff; }
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="header">DATA KATEGORI</div>

<div class="card">

<a href="kategori_tambah.php" class="btn btn-tambah">+ Tambah Kategori</a>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Nama Kategori</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php $no=1; ?>
<?php while($row = mysqli_fetch_assoc($q)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_kategori'] ?></td>
    <td>
        <a href="kategori_edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
        <a href="kategori_hapus.php?id=<?= $row['id'] ?>" 
           onclick="return confirm('Yakin ingin menghapus?')" 
           class="btn btn-delete">
           Hapus
        </a>
    </td>
</tr>
<?php } ?>

</tbody>
</table>

</div>
</div>

</body>
</html>
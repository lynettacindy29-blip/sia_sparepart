<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$qKategori = mysqli_query($conn, "SELECT * FROM tb_kategori ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Kategori</title>

<link rel="stylesheet" href="inc/style.css">
<style>
.content {
    margin-left:260px;
    padding:20px;
    background:#eef1f5;
    min-height:100vh;
}

.card {
    background:#fff;
    border-radius:8px;
    padding:20px;
    width:600px;
}

.btn-tambah {
    background:#2563eb;
    color:white;
    padding:8px 15px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

th, td {
    padding:10px;
    border-bottom:1px solid #eee;
    font-size:13px;
}

th {
    background:#f1f5f9;
    text-align:left;
}

.aksi-btn {
    display:flex;
    gap:6px;
}

.btn-sm {
    padding:5px 10px;
    font-size:12px;
    border-radius:4px;
    text-decoration:none;
    color:white;
}

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

</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">

<div class="card">

<h3>Data Kategori</h3>

<a href="kategori_tambah.php" class="btn-tambah">Tambah Data</a>

<table>
    <thead>
        <tr>
            <th width="50">No</th>
            <th>Nama</th>
            <th width="150">Aksi</th>
        </tr>
    </thead>
    <tbody>

    <?php 
    $no = 1;
    while($row = mysqli_fetch_assoc($qKategori)) { 
    ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_kategori'] ?></td>
            <td>
                <div class="aksi-btn">
                    <a href="kategori_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Edit</a>
                    <a href="kategori_hapus.php?id=<?= $row['id'] ?>" 
                       class="btn-sm btn-delete"
                       onclick="return confirm('Hapus kategori ini?')">
                       Hapus
                    </a>
                </div>
            </td>
        </tr>
    <?php } ?>

    </tbody>
</table>

</div>
</div>

</body>
</html>

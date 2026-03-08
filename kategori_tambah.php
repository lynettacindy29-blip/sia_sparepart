<?php
session_start();
include "config/db.php";

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_kategori'];

    mysqli_query($conn, "INSERT INTO tb_kategori (nama_kategori)
                         VALUES ('$nama')");

    header("Location: kategori.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Kategori</title>
<link rel="stylesheet" href="inc/style.css">
<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.card { background:#fff; padding:25px; border-radius:8px; width:400px; }
input { width:100%; padding:8px; margin-bottom:15px; }
button { padding:8px 15px; background:#007bff; color:#fff; border:none; border-radius:5px; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">
<h3>Tambah Kategori</h3>

<form method="POST">
<label>Nama Kategori</label>
<input type="text" name="nama_kategori" required>
<button type="submit" name="simpan">Simpan</button>
</form>

</div>
</div>
</body>
</html>
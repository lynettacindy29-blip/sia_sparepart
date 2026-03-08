<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

// ambil data lama
$data = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM tb_kategori WHERE id=$id"));

if (!$data) {
    die("Data tidak ditemukan");
}

// proses update
if (isset($_POST['update'])) {

    $nama = $_POST['nama_kategori'];

    mysqli_query($conn,
        "UPDATE tb_kategori 
         SET nama_kategori='$nama' 
         WHERE id=$id");

    header("Location: kategori.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Kategori</title>
<link rel="stylesheet" href="inc/style.css">
<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.card { background:#fff; padding:25px; border-radius:8px; width:400px; }
input { width:100%; padding:8px; margin-bottom:15px; }
button { padding:8px 15px; background:#28a745; color:#fff; border:none; border-radius:5px; }
button:hover { background:#218838; }
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">

<h3>Edit Kategori</h3>

<form method="POST">

<label>Nama Kategori</label>
<input type="text" name="nama_kategori"
value="<?= $data['nama_kategori'] ?>" required>

<button type="submit" name="update">Update</button>

</form>

</div>
</div>

</body>
</html>
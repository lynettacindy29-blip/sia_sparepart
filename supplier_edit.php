<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM tb_supplier WHERE id=$id"));

if (isset($_POST['update'])) {

    $nama_supplier = $_POST['nama_supplier'];
    $alamat        = $_POST['alamat'];
    $telp          = $_POST['telp'];

    mysqli_query($conn, "UPDATE tb_supplier SET 
        nama_supplier='$nama_supplier',
        alamat='$alamat',
        telp='$telp'
        WHERE id=$id");

    header("Location: supplier.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Supplier</title>
<link rel="stylesheet" href="inc/style.css">
<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.card { background:#fff; padding:25px; border-radius:8px; width:500px; }
input, textarea {
    width:100%;
    padding:8px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:5px;
}
button {
    padding:8px 15px;
    background:#28a745;
    color:#fff;
    border:none;
    border-radius:5px;
}
button:hover { background:#218838; }
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">

<h3>Edit Supplier</h3>

<form method="POST">

<label>Nama Supplier</label>
<input type="text" name="nama_supplier" 
value="<?= $data['nama_supplier'] ?>" required>

<label>Alamat</label>
<textarea name="alamat" required><?= $data['alamat'] ?></textarea>

<label>No. Telepon</label>
<input type="text" name="telp" 
value="<?= $data['telp'] ?>" required>

<button type="submit" name="update">Update</button>

</form>

</div>
</div>

</body>
</html>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: data_barang.php");
    exit;
}

$id = intval($_GET['id']);

/* ambil data barang */
$qBarang = mysqli_query($conn, "
    SELECT * FROM tb_barang WHERE id = $id
");

if (mysqli_num_rows($qBarang) == 0) {
    die("Data tidak ditemukan");
}

$data = mysqli_fetch_assoc($qBarang);

/* ambil kategori */
$qKategori = mysqli_query($conn, "
    SELECT * FROM tb_kategori ORDER BY nama_kategori ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Barang</title>
<link rel="stylesheet" href="inc/style.css">

<style>
.content {
    margin-left:240px;
    padding:20px;
    background:#eef1f5;
    min-height:100vh;
}
.card {
    background:#fff;
    border-radius:8px;
    padding:20px;
    width:500px;
}
label {
    font-size:13px;
    margin-top:10px;
    display:block;
}
input, select {
    width:100%;
    padding:8px;
    margin-top:4px;
    border:1px solid #ccc;
    border-radius:5px;
}
button {
    margin-top:15px;
    width:100%;
    padding:10px;
    border:none;
    border-radius:6px;
    background:#2563eb;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
}
.btn-back {
    background:#6b7280;
    margin-top:10px;
}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">

<h3>Edit Barang</h3>

<form method="POST" action="barang_update.php">

    <input type="hidden" name="id" value="<?= $data['id'] ?>">

    <label>Nama Barang</label>
    <input type="text" name="nama_barang"
           value="<?= $data['nama_barang'] ?>" required>

    <label>Kategori Barang</label>
    <select name="id_kategori" required>
        <?php while($k = mysqli_fetch_assoc($qKategori)) { ?>
            <option value="<?= $k['id'] ?>"
                <?= $k['id'] == $data['id_kategori'] ? 'selected' : '' ?>>
                <?= $k['nama_kategori'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Harga Beli</label>
    <input type="number" name="harga_beli"
           value="<?= $data['harga_beli'] ?>" required>

    <label>Harga Jual</label>
    <input type="number" name="harga_jual"
           value="<?= $data['harga_jual'] ?>" required>

    <label>Stok Barang</label>
    <input type="number" name="stok"
           value="<?= $data['stok'] ?>">

    <button type="submit">Update Barang</button>

</form>

<a href="data_barang.php">
    <button class="btn-back">Kembali</button>
</a>

</div>
</div>

</body>
</html>
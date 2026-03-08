<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0; 

// ambil data penjualan
$q = mysqli_query($conn, "
    SELECT * FROM tb_penjualan
    WHERE id = '$id'
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "Data tidak ditemukan";
    exit;
}

// ambil list barang
$barang = mysqli_query($conn, "SELECT * FROM tb_barang ORDER BY nama_barang ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Penjualan</title>
<link rel="stylesheet" href="inc/style.css">

<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.card { background:#fff; padding:20px; border-radius:8px; }
input, select { width:100%; padding:8px; margin-top:5px; }
button { background:#007bff; color:#fff; padding:10px 20px; border:none; border-radius:5px; }
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">
<h2>Edit Penjualan</h2>

<form method="POST" action="penjualan_update.php">

<input type="hidden" name="id" value="<?= $data['id'] ?>">

<label>Nama Pelanggan</label>
<input type="text" name="nama_pelanggan" value="<?= $data['nama_pelanggan'] ?>" required>

<label>Barang</label>
<select name="nama_barang" required>
    <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
        <option value="<?= $b['nama_barang'] ?>"
            <?= ($b['nama_barang'] == $data['nama_barang']) ? 'selected' : '' ?>>
            <?= $b['nama_barang'] ?>
        </option>  
    <?php } ?>
</select>

<label>Harga Pokok</label>
<input type="number" name="harga_pokok" value="<?= $data['harga_pokok'] ?>" readonly>

<label>Harga Jual</label>
<input type="number" name="harga_jual" value="<?= $data['harga_jual'] ?>" required>

<label>Jumlah</label>
<input type="number" name="jumlah" value="<?= $data['jumlah'] ?>" required>

<label>Metode Pembayaran</label>
<select name="status" id="status" onchange="toggleKredit()">
    <option value="Lunas" <?= $data['status']=='Lunas'?'selected':'' ?>>Lunas</option>
    <option value="Kredit" <?= $data['status']=='Kredit'?'selected':'' ?>>Kredit</option>
</select>

<div id="kredit_box" style="margin-top:10px; display:none;">
    <label>Tanggal Batas Kredit</label>
    <input type="date" name="tanggal_batas_kredit"
           value="<?= $data['tanggal_batas_kredit'] ?>">
</div>

<br>
<button type="submit">Update</button>
<a href="penjualan.php" style="margin-left:10px;">Batal</a>

</form>
</div>
</div>

<script>
function toggleKredit() {
    var status = document.getElementById("status").value;
    document.getElementById("kredit_box").style.display =
        (status === "Kredit") ? "block" : "none";
}

// jalankan saat page load
toggleKredit();
</script>

</body>
</html>

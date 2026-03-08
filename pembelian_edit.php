<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: pembelian.php");
    exit;
}

/* ===========================
   AMBIL DATA PEMBELIAN + SUPPLIER
=========================== */
$qPembelian = mysqli_query($conn, "
    SELECT p.*, s.nama_supplier
    FROM tb_pembelian p
    JOIN tb_supplier s ON p.id_supplier = s.id
    WHERE p.id = '$id'
");

if (!$qPembelian) {
    die("Query Pembelian Error: " . mysqli_error($conn));
}

$pembelian = mysqli_fetch_assoc($qPembelian);

if (!$pembelian) {
    echo "Data pembelian tidak ditemukan";
    exit;
}

/* ===========================
   AMBIL DETAIL PEMBELIAN
=========================== */
$qDetail = mysqli_query($conn, "
    SELECT d.*, b.nama_barang
    FROM tb_detail_pembelian d
    JOIN tb_barang b ON d.id_barang = b.id
    WHERE d.id_pembelian = '$id'
");

if (!$qDetail) {
    die("Query Detail Error: " . mysqli_error($conn));
}

/* ===========================
   AMBIL LIST BARANG
=========================== */
$qBarang = mysqli_query($conn, "SELECT * FROM tb_barang");

if (!$qBarang) {
    die("Query Barang Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Pembelian</title>
<link rel="stylesheet" href="inc/style.css">

<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f9;
}
.content {
    margin-left:240px;
    padding:30px;
}
.card {
    background:#fff;
    padding:20px;
    border-radius:8px;
}
table {
    width:100%;
    border-collapse: collapse;
    margin-top:15px;
}
th, td {
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}
th {
    background:#f1f1f1;
}
input, select {
    width:96%;
    padding:6px;
}
.btn {
    padding:6px 10px;
    border-radius:4px;
    text-decoration:none;
    font-size:13px;
}
.btn-primary { background:#007bff; color:#fff; }
.btn-danger { background:#dc3545; color:#fff; }
.btn-success { background:#28a745; color:#fff; }
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">

<h2>Edit Pembelian</h2>

<form method="POST" action="pembelian_update.php">

<input type="hidden" name="id" value="<?= $pembelian['id'] ?>">

<label>Supplier</label>
<input type="text" value="<?= $pembelian['nama_supplier'] ?>" readonly>

<label>Tanggal</label>
<input type="date" name="tanggal" 
value="<?= date('Y-m-d', strtotime($pembelian['tanggal'])) ?>" required>

<label>Metode</label>
<select name="metode" required>
    <option value="tunai" <?= $pembelian['metode']=='tunai'?'selected':'' ?>>Tunai</option>
    <option value="kredit" <?= $pembelian['metode']=='kredit'?'selected':'' ?>>Kredit</option>
</select>

<h3>Detail Barang</h3>

<table>
<tr>
    <th>Barang</th>
    <th>Harga Beli</th>
    <th>Jumlah</th>
    <th>Subtotal</th>
</tr>

<?php while ($d = mysqli_fetch_assoc($qDetail)) { ?>
<tr>
    <td>
        <select name="id_barang[]" required>
            <?php
            mysqli_data_seek($qBarang, 0);
            while ($b = mysqli_fetch_assoc($qBarang)) {
                $selected = ($b['id'] == $d['id_barang']) ? 'selected' : '';
                echo "<option value='{$b['id']}' $selected>{$b['nama_barang']}</option>";
            }
            ?>
        </select>
    </td>

    <td>
        <input type="number" name="harga_beli[]" 
        value="<?= $d['harga_beli'] ?>" required>
    </td>

    <td>
        <input type="number" name="jumlah[]" 
        value="<?= $d['jumlah'] ?>" required>
    </td>

    <td>
        Rp <?= number_format($d['subtotal'],0,',','.') ?>
    </td>
</tr>
<?php } ?>
</table>

<br>
<button type="submit" class="btn btn-success">Update Pembelian</button>
<a href="pembelian.php" class="btn btn-danger">Batal</a>

</form>

</div>
</div>

</body>
</html>
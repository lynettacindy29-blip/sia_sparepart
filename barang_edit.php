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

/* CEK APAKAH BARANG INI SUDAH ADA TRANSAKSI DI KARTU STOK?
   Jika sudah ada transaksi (selain Setup Saldo Awal), 
   maka HPP dan Stok TIDAK BOLEH diedit lagi dari form ini.
*/
$qCekTransaksi = mysqli_query($conn, "SELECT COUNT(id) as jml_transaksi FROM tb_kartu_stok WHERE id_barang = $id");
$jml_transaksi = mysqli_fetch_assoc($qCekTransaksi)['jml_transaksi'];

// Anggap 1 baris pertama di kartu stok adalah "Setup Saldo Awal". 
// Jika baris > 1, berarti sudah ada jual/beli.
$sudah_transaksi = ($jml_transaksi > 1) ? true : false; 
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Barang</title>
<link rel="stylesheet" href="inc/style.css">

<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:30px; max-width:600px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);}
h3 { margin-top: 0; color: #1e293b; margin-bottom: 20px;}
label { font-size:13px; margin-top:15px; display:block; font-weight: bold; color: #334155;}
input, select { width:100%; padding:10px; margin-top:5px; border:1px solid #cbd5e1; border-radius:5px; box-sizing: border-box; font-size: 14px;}
input[readonly] { background: #f1f5f9; cursor: not-allowed; color: #64748b; border-color: #e2e8f0; }

.btn-update { margin-top:25px; width:100%; padding:12px; border:none; border-radius:6px; background:#2563eb; color:#fff; font-weight:bold; cursor:pointer; font-size: 15px;}
.btn-update:hover { background:#1d4ed8; }
.btn-back { background:#64748b; color: white; display:block; text-align:center; padding: 12px; border-radius: 6px; text-decoration:none; margin-top: 10px; font-weight: bold; }
.btn-back:hover { background:#475569; }

.alert-warning { background: #fffbeb; color: #b45309; padding: 12px; border-radius: 6px; border-left: 4px solid #f59e0b; font-size: 13px; margin-bottom: 20px; line-height: 1.5;}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">

<h3>Edit Master Barang: <?= htmlspecialchars($data['kode_barang']) ?></h3>

<?php if($sudah_transaksi): ?>
    <div class="alert-warning">
        <strong>⚠️ Peringatan Akuntansi:</strong> Barang ini sudah memiliki riwayat transaksi (Pembelian/Penjualan). 
        Untuk menjaga keakuratan Laporan Stok, nilai <strong>Harga Beli (HPP)</strong> dan <strong>Stok</strong> telah dikunci dan tidak bisa diubah secara manual.
    </div>
<?php endif; ?>

<form method="POST" action="barang_update.php">

    <input type="hidden" name="id" value="<?= $data['id'] ?>">

    <label>Nama Barang</label>
    <input type="text" name="nama_barang" value="<?= htmlspecialchars($data['nama_barang']) ?>" required>

    <label>Kategori Barang</label>
    <select name="id_kategori" required>
        <?php while($k = mysqli_fetch_assoc($qKategori)) { ?>
            <option value="<?= $k['id'] ?>" <?= $k['id'] == $data['id_kategori'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama_kategori']) ?>
            </option>
        <?php } ?>
    </select>

    <label>Harga Jual</label>
    <input type="number" name="harga_jual" value="<?= $data['harga_jual'] ?>" required>

    <label>Batas Stok Minimal (Warning)</label>
    <input type="number" name="stok_minimal" value="<?= isset($data['stok_minimal']) ? $data['stok_minimal'] : 5 ?>" required>

    <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 25px 0;">

    <label>Harga Beli Dasar / HPP <?= $sudah_transaksi ? '🔒' : '' ?></label>
    <input type="number" name="harga_beli" value="<?= $data['harga_beli'] ?>" required <?= $sudah_transaksi ? 'readonly title="Terkunci karena sudah ada transaksi"' : '' ?>>

    <label>Stok Fisik Saat Ini <?= $sudah_transaksi ? '🔒' : '' ?></label>
    <input type="number" name="stok" value="<?= $data['stok'] ?>" <?= $sudah_transaksi ? 'readonly title="Terkunci karena sudah ada transaksi"' : '' ?>>

    <button type="submit" class="btn-update">💾 Update Data Barang</button>

</form>

<a href="data_barang.php" class="btn-back">⬅️ Kembali ke Data Barang</a>

</div>
</div>

</body>
</html>
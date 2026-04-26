<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: piutang_penjualan.php");
    exit;
}

$id_penjualan = intval($_GET['id']);

$q = mysqli_query($conn, "SELECT * FROM tb_penjualan WHERE id = $id_penjualan");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Data piutang tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Penerimaan Piutang</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:25px; width:500px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; margin-bottom: 20px;}

.info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;}
.info-box span { display: block; margin-bottom: 5px; color: #475569;}
.info-box strong { color: #1d4ed8; font-size: 16px;}

label { font-size:13px; margin-top:15px; display:block; font-weight: bold; color: #475569; }
input { width:100%; padding:10px; margin-top:6px; border:1px solid #cbd5e1; border-radius:5px; font-size: 14px; box-sizing: border-box; }
input:focus { border-color: #2563eb; outline: none; }

button[type="submit"] { margin-top:25px; width:100%; padding:12px; border:none; border-radius:6px; background:#2563eb; color:#fff; font-weight:bold; cursor:pointer; font-size: 14px; }
button[type="submit"]:hover { background:#1d4ed8; }

.btn-back { background:#64748b; color: white; display:block; text-align:center; padding:12px; border-radius:6px; text-decoration:none; margin-top:10px; font-weight: bold; font-size: 14px; }
.btn-back:hover { background:#475569; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Proses Penerimaan Piutang</h3>

        <div class="info-box">
            <span>Pelanggan: <b><?= htmlspecialchars($data['nama_pelanggan']) ?></b></span>
            <span>No. Nota: <b><?= isset($data['no_nota']) ? $data['no_nota'] : '-' ?></b></span>
            <span>Total Transaksi: Rp <?= number_format($data['total'],0,',','.') ?></span>
            <hr style="border: 0; border-top: 1px solid #bfdbfe; margin: 10px 0;">
            <span>Sisa Piutang (Tagihan):</span>
            <strong>Rp <?= number_format($data['sisa_piutang'],0,',','.') ?></strong>
        </div>

        <form method="POST" action="pembayaran_piutang_proses.php" enctype="multipart/form-data">
            <input type="hidden" name="id_penjualan" value="<?= $id_penjualan ?>">

            <label>Jumlah Pembayaran yang Diterima (Rp)</label>
            <input type="number" name="jumlah_bayar" required min="1" max="<?= $data['sisa_piutang'] ?>" placeholder="Masukkan nominal uang...">
            
            <label>Keterangan Tambahan</label>
            <input type="text" name="keterangan" placeholder="Contoh: Pembayaran cicilan 1 tunai" required>

            <label>Upload Bukti Transfer/Bayar (Opsional)</label>
            <input type="file" name="bukti_bayar" accept="image/jpeg,image/png,image/jpg,application/pdf" style="padding: 6px; cursor: pointer;">
            <small style="color: #64748b; font-size: 11px; display:block; margin-top:2px;">Format yang diizinkan: JPG, PNG, PDF</small>

            <button type="submit" onclick="return confirm('Proses penerimaan piutang ini?')">Simpan Penerimaan Piutang</button>
            <a href="piutang_penjualan.php" class="btn-back">Kembali</a>
        </form>

    </div>
</div>

</body>
</html>
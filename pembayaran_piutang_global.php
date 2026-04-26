<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil daftar pelanggan yang masih punya sisa piutang > 0
$qPelanggan = mysqli_query($conn, "
    SELECT nama_pelanggan, SUM(sisa_piutang) as total_hutang 
    FROM tb_penjualan 
    WHERE sisa_piutang > 0 
    GROUP BY nama_pelanggan
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Piutang Global</title>
    <link rel="stylesheet" href="inc/style.css">
    <style>
        .content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
        .card { background:#fff; border-radius:8px; padding:25px; width:500px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        h3 { margin-top:0; color:#333; margin-bottom: 20px;}
        
        .info-box { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; color: #78350f;}

        label { font-size:13px; margin-top:15px; display:block; font-weight: bold; color: #475569; }
        select, input { width:100%; padding:10px; margin-top:6px; border:1px solid #cbd5e1; border-radius:5px; font-size: 14px; box-sizing: border-box; }
        
        button[type="submit"] { margin-top:25px; width:100%; padding:12px; border:none; border-radius:6px; background:#10b981; color:#fff; font-weight:bold; cursor:pointer; font-size: 14px; }
        button[type="submit"]:hover { background:#059669; }
        .btn-back { background:#64748b; color: white; display:block; text-align:center; padding:12px; border-radius:6px; text-decoration:none; margin-top:10px; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Pembayaran Piutang Global</h3>
        <div class="info-box">
            Fitur ini menggunakan metode FIFO (First-In, First-Out). Uang yang diinput akan otomatis memotong nota tagihan tertua milik pelanggan tersebut.
        </div>

        <form method="POST" action="pembayaran_piutang_global_proses.php" enctype="multipart/form-data">
            
            <label>Pilih Pelanggan</label>
            <select name="nama_pelanggan" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php while($p = mysqli_fetch_assoc($qPelanggan)) { ?>
                    <option value="<?= htmlspecialchars($p['nama_pelanggan']) ?>">
                        <?= htmlspecialchars($p['nama_pelanggan']) ?> (Total Hutang: Rp <?= number_format($p['total_hutang'],0,',','.') ?>)
                    </option>
                <?php } ?>
            </select>

            <label>Nominal Pembayaran Diterima (Rp)</label>
            <input type="number" name="jumlah_bayar" required min="1" placeholder="Masukkan total uang...">

            <label>Keterangan / Catatan</label>
            <input type="text" name="keterangan" required placeholder="Contoh: Pembayaran bulk via transfer BCA">

            <label>Upload Bukti Bayar (Opsional)</label>
            <input type="file" name="bukti_bayar" accept="image/jpeg,image/png,image/jpg,application/pdf" style="padding: 6px; cursor: pointer;">

            <button type="submit" onclick="return confirm('Yakin ingin memproses pembayaran global ini?')">Proses Pembayaran</button>
            <a href="piutang_penjualan.php" class="btn-back">Kembali</a>
        </form>
    </div>
</div>

</body>
</html>
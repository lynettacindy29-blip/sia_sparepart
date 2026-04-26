<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Mengambil data pelanggan dan total piutangnya (hanya yang sisa piutangnya lebih dari 0)
$query = "
    SELECT 
        nama_pelanggan, 
        COUNT(id) as jumlah_faktur, 
        SUM(total) as total_belanja,
        SUM(sisa_piutang) as total_piutang 
    FROM tb_penjualan 
    WHERE sisa_piutang > 0 
    GROUP BY nama_pelanggan 
    ORDER BY total_piutang DESC
";
$qPiutang = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Rekap Piutang</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.btn-print { background:#3b82f6; color:#fff; padding:8px 15px; border:none; border-radius:5px; cursor:pointer; font-weight:bold; float:right; margin-top:-40px;}
.btn-print:hover { background:#2563eb; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Laporan</button>
        <h3>Laporan Rekap Piutang per Pelanggan</h3>
        <p class="desc">Menampilkan total tagihan (piutang) yang belum dilunasi oleh masing-masing pelanggan.</p>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="center">No</th>
                    <th style="width: 35%;">Nama Pelanggan</th>
                    <th class="center" style="width: 20%;">Jumlah Transaksi Belum Lunas</th>
                    <th class="right" style="width: 40%;">Total Sisa Piutang (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $grand_total_piutang = 0;
                
                if($qPiutang && mysqli_num_rows($qPiutang) > 0) {
                    while($row = mysqli_fetch_assoc($qPiutang)) { 
                        $grand_total_piutang += $row['total_piutang'];
                ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                    <td class="center"><span style="background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:bold;"><?= $row['jumlah_faktur'] ?> Nota</span></td>
                    <td class="right" style="font-weight: bold; color: #dc2626;">Rp <?= number_format($row['total_piutang'],0,',','.') ?></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='4' class='center' style='padding: 20px; color: #94a3b8;'>Hore! Tidak ada piutang pelanggan yang menunggak.</td></tr>";
                }
                ?>
                <tr style="background: #f8fafc; font-weight: bold; font-size: 16px;">
                    <td colspan="3" class="right">GRAND TOTAL PIUTANG TOKO:</td>
                    <td class="right" style="color: #dc2626;">Rp <?= number_format($grand_total_piutang,0,',','.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
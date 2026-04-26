<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Query untuk menggabungkan Saldo Awal Utang dari tb_supplier dengan Sisa Hutang dari tb_pembelian
$query = "
    SELECT 
        s.nama_supplier, 
        s.saldo_awal_utang, 
        COUNT(p.id) as jumlah_faktur,
        COALESCE(SUM(p.sisa_hutang), 0) as total_hutang_faktur,
        (s.saldo_awal_utang + COALESCE(SUM(p.sisa_hutang), 0)) as grand_total_hutang
    FROM tb_supplier s
    LEFT JOIN tb_pembelian p ON s.id = p.id_supplier AND p.sisa_hutang > 0
    GROUP BY s.id, s.nama_supplier, s.saldo_awal_utang
    HAVING grand_total_hutang > 0
    ORDER BY grand_total_hutang DESC
";
$qHutang = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Rekap Hutang</title>
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

.btn-print { background:#10b981; color:#fff; padding:8px 15px; border:none; border-radius:5px; cursor:pointer; font-weight:bold; float:right; margin-top:-40px;}
.btn-print:hover { background:#059669; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Laporan</button>
        <h3>Laporan Rekap Hutang per Supplier</h3>
        <p class="desc">Menampilkan total kewajiban (hutang) toko yang harus dibayarkan kepada masing-masing supplier.</p>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="center">No</th>
                    <th style="width: 35%;">Nama Supplier</th>
                    <th class="right" style="width: 20%;">Saldo Awal Hutang</th>
                    <th class="right" style="width: 20%;">Hutang Faktur (Aktif)</th>
                    <th class="right" style="width: 20%;">Total Hutang (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $total_semua_hutang = 0;
                
                if($qHutang && mysqli_num_rows($qHutang) > 0) {
                    while($row = mysqli_fetch_assoc($qHutang)) { 
                        $total_semua_hutang += $row['grand_total_hutang'];
                ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_supplier']) ?></strong><br>
                        <small style="color: #64748b;">(<?= $row['jumlah_faktur'] ?> Faktur Belum Lunas)</small>
                    </td>
                    <td class="right">Rp <?= number_format($row['saldo_awal_utang'],0,',','.') ?></td>
                    <td class="right">Rp <?= number_format($row['total_hutang_faktur'],0,',','.') ?></td>
                    <td class="right" style="font-weight: bold; color: #dc2626;">Rp <?= number_format($row['grand_total_hutang'],0,',','.') ?></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='5' class='center' style='padding: 20px; color: #94a3b8;'>Toko tidak memiliki tanggungan hutang ke supplier manapun.</td></tr>";
                }
                ?>
                <tr style="background: #f8fafc; font-weight: bold; font-size: 16px;">
                    <td colspan="4" class="right">GRAND TOTAL HUTANG TOKO:</td>
                    <td class="right" style="color: #dc2626;">Rp <?= number_format($total_semua_hutang,0,',','.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
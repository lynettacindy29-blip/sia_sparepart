<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$query_utama = "
    SELECT ph.*, p.no_faktur, s.nama_supplier 
    FROM tb_pembayaran_hutang ph
    JOIN tb_pembelian p ON ph.id_pembelian = p.id
    LEFT JOIN tb_supplier s ON p.id_supplier = s.id
    ORDER BY ph.tanggal DESC, ph.id DESC
";

$qHistori = mysqli_query($conn, $query_utama);

if (!$qHistori) {
    $query_cadangan = "
        SELECT ph.*, p.no_faktur, p.id_supplier AS nama_supplier 
        FROM tb_pembayaran_hutang ph
        JOIN tb_pembelian p ON ph.id_pembelian = p.id
        ORDER BY ph.tanggal DESC, ph.id DESC
    ";
    $qHistori = mysqli_query($conn, $query_cadangan);
    
    if (!$qHistori) {
        die("Error Database: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Histori Pembayaran Hutang</title>
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

.badge-out { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.btn-bukti { background:#10b981; color:#fff; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:11px; font-weight:bold; }
.btn-bukti:hover { background:#059669; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Histori Pembayaran Hutang Usaha</h3>
        <p class="desc">Catatan riwayat cicilan atau pelunasan hutang kepada supplier (Uang Keluar / Kas Berkurang).</p>

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Tanggal Bayar</th>
                    <th style="width: 18%;">No. Faktur</th>
                    <th style="width: 20%;">Nama Supplier</th>
                    <th style="width: 20%;">Keterangan</th>
                    <th class="right" style="width: 15%;">Nominal (Rp)</th>
                    <th class="center" style="width: 15%;">Bukti Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_keluar = 0;
                if($qHistori && mysqli_num_rows($qHistori) > 0) {
                    while($row = mysqli_fetch_assoc($qHistori)) { 
                        $total_keluar += $row['jumlah_bayar'];
                ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['no_faktur'] ?? '-') ?></strong></td>
                    
                    <td><?= htmlspecialchars($row['nama_supplier'] ?? 'Supplier ID: ' . ($row['id_supplier'] ?? '-')) ?></td>
                    
                    <td><span class="badge-out">Keluar</span> <?= htmlspecialchars($row['keterangan'] ?? 'Pembayaran Hutang') ?></td>
                    <td class="right" style="font-weight: bold; color: #dc2626;">- Rp <?= number_format($row['jumlah_bayar'],0,',','.') ?></td>
                    
                    <td class="center">
                        <?php if(!empty($row['bukti_bayar'])): ?>
                            <a href="assets/bukti_bayar/<?= htmlspecialchars($row['bukti_bayar']) ?>" target="_blank" class="btn-bukti">
                                Lihat Bukti
                            </a>
                        <?php else: ?>
                            <span style="color:#94a3b8; font-size:11px; font-style:italic;">Tidak ada bukti</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='center' style='padding: 20px; color: #94a3b8;'>Belum ada riwayat pembayaran hutang ke supplier.</td></tr>";
                }
                ?>
                <tr style="background: #f8fafc; font-weight: bold; font-size: 15px;">
                    <td colspan="4" class="right">TOTAL HUTANG DIBAYAR:</td>
                    <td class="right" style="color: #dc2626;">Rp <?= number_format($total_keluar,0,',','.') ?></td>
                    <td></td> </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
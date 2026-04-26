<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$qHistori = mysqli_query($conn, "
    SELECT pp.*, p.no_nota, p.nama_pelanggan 
    FROM tb_pembayaran_piutang pp
    JOIN tb_penjualan p ON pp.id_penjualan = p.id
    ORDER BY pp.tanggal DESC, pp.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Histori Pembayaran Piutang</title>
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

.badge-in { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.btn-bukti { background:#3b82f6; color:#fff; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:11px; font-weight:bold; }
.btn-bukti:hover { background:#2563eb; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Histori Penerimaan Piutang</h3>
        <p class="desc">Catatan riwayat cicilan atau pelunasan hutang dari pelanggan (Uang Masuk / Kas Bertambah).</p>

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Tanggal Bayar</th>
                    <th style="width: 18%;">No. Nota</th>
                    <th style="width: 20%;">Nama Pelanggan</th>
                    <th style="width: 20%;">Keterangan</th>
                    <th class="right" style="width: 15%;">Nominal (Rp)</th>
                    <th class="center" style="width: 15%;">Bukti Transfer</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_masuk = 0;
                if($qHistori && mysqli_num_rows($qHistori) > 0) {
                    while($row = mysqli_fetch_assoc($qHistori)) { 
                        $total_masuk += $row['jumlah_bayar'];
                ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><strong><?= isset($row['no_nota']) ? htmlspecialchars($row['no_nota']) : '-' ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td><span class="badge-in">Masuk</span> <?= htmlspecialchars($row['keterangan'] ?? 'Pembayaran Piutang') ?></td>
                    <td class="right" style="font-weight: bold; color: #059669;">+ Rp <?= number_format($row['jumlah_bayar'],0,',','.') ?></td>
                    
                    <td class="center">
                        <?php if(!empty($row['bukti_bayar'])): ?>
                            <a href="assets/bukti_bayar/<?= htmlspecialchars($row['bukti_bayar']) ?>" target="_blank" class="btn-bukti">
                                Buka Bukti
                            </a>
                        <?php else: ?>
                            <span style="color:#94a3b8; font-size:11px; font-style:italic;">Belum upload</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='center' style='padding: 20px; color: #94a3b8;'>Belum ada riwayat penerimaan piutang dari pelanggan.</td></tr>";
                }
                ?>
                <tr style="background: #f8fafc; font-weight: bold; font-size: 15px;">
                    <td colspan="4" class="right">TOTAL PIUTANG DITERIMA:</td>
                    <td class="right" style="color: #059669;">Rp <?= number_format($total_masuk,0,',','.') ?></td>
                    <td></td> </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
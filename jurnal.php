<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fitur Filter Bulan & Tahun (Nilai plus untuk skripsi)
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query Jurnal Umum diurutkan berdasarkan Tanggal, lalu No. Bukti, lalu ID (agar urutan Debit-Kredit rapi)
$qJurnal = mysqli_query($conn, "
    SELECT j.*, a.kode_akun, a.nama_akun 
    FROM tb_jurnal j
    LEFT JOIN tb_akun a ON j.id_akun = a.id
    WHERE MONTH(j.tanggal) = '$bulan_pilih' AND YEAR(j.tanggal) = '$tahun_pilih'
    ORDER BY j.tanggal DESC, j.no_bukti DESC, j.id ASC
");

// Array nama bulan untuk dropdown
$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
?>

<!DOCTYPE html>
<html>
<head>
<title>Jurnal Umum</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;}
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.filter-box { display: flex; gap: 10px; align-items: flex-end; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 20px;}
.filter-box label { font-size: 13px; font-weight: bold; color: #475569; display: block; margin-bottom: 5px;}
.filter-box select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; width: 150px; outline: none;}
.filter-box button { background: #2563eb; color: white; border: none; padding: 9px 15px; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: bold;}
.filter-box button:hover { background: #1d4ed8; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:10px 12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; vertical-align: top;}
th { background:#f8fafc; color: #475569; font-weight: 600; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;}
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.total-row { font-weight: bold; font-size: 15px; background: #f1f5f9; color: #0f172a; }
.balance-ok { color: #10b981; }
.balance-err { color: #ef4444; }

/* Standar Akuntansi: Akun Kredit menjorok ke dalam */
.akun-kredit { padding-left: 30px; font-style: italic; color: #475569;}
.keterangan-transaksi { font-size: 11px; color: #94a3b8; display: block; margin-top: 4px;}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Jurnal Umum (General Journal)</h3>
        <p class="desc">Buku harian untuk mencatat semua transaksi keuangan secara kronologis beserta akun yang di-Debit dan di-Kredit.</p>

        <form method="GET" class="filter-box">
            <div>
                <label>Pilih Bulan</label>
                <select name="bulan">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= str_pad($i, 2, "0", STR_PAD_LEFT) ?>" <?= ($bulan_pilih == str_pad($i, 2, "0", STR_PAD_LEFT)) ? 'selected' : '' ?>>
                            <?= $nama_bulan[$i] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label>Pilih Tahun</label>
                <select name="tahun">
                    <?php 
                    $tahun_sekarang = date('Y');
                    for($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 1; $i++): 
                    ?>
                        <option value="<?= $i ?>" <?= ($tahun_pilih == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit">Tampilkan Jurnal</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 15%;">No. Bukti</th>
                    <th style="width: 28%;">Nama Akun & Keterangan</th>
                    <th class="center" style="width: 10%;">Ref</th>
                    <th class="right" style="width: 17.5%;">Debit (Rp)</th>
                    <th class="right" style="width: 17.5%;">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_debit = 0;
                $total_kredit = 0;
                
                if($qJurnal && mysqli_num_rows($qJurnal) > 0) {
                    while($row = mysqli_fetch_assoc($qJurnal)) { 
                        $total_debit += $row['debit'];
                        $total_kredit += $row['kredit'];
                        
                        // Standar Akuntansi: Jika kredit > 0, nama akun menjorok ke kanan
                        $class_akun = ($row['kredit'] > 0) ? 'akun-kredit' : '';
                ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['no_bukti'] ?? '') ?></strong></td>
                    <td>
                        <div class="<?= $class_akun ?>"><?= htmlspecialchars($row['nama_akun'] ?? 'Akun Tidak Ditemukan') ?></div>
                        <span class="keterangan-transaksi"><?= htmlspecialchars($row['keterangan'] ?? '') ?></span>
                    </td>
             
                    <td class="center"><?= isset($row['kode_akun']) ? $row['kode_akun'] : '-' ?></td>
                    <td class="right"><?= ($row['debit'] > 0) ? number_format($row['debit'],0,',','.') : '-' ?></td>
                    <td class="right"><?= ($row['kredit'] > 0) ? number_format($row['kredit'],0,',','.') : '-' ?></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='center' style='padding: 30px; color: #94a3b8;'>Tidak ada transaksi pada bulan dan tahun terpilih.</td></tr>";
                }
                ?>
                
                <tr class="total-row">
                    <td colspan="4" class="right" style="padding: 15px;">TOTAL KESELURUHAN</td>
                    <td class="right" style="padding: 15px; border-top: 2px solid #cbd5e1;">Rp <?= number_format($total_debit,0,',','.') ?></td>
                    <td class="right" style="padding: 15px; border-top: 2px solid #cbd5e1;">Rp <?= number_format($total_kredit,0,',','.') ?></td>
                </tr>
                <tr>
                    <td colspan="6" class="center" style="padding: 10px; font-size: 12px;">
                        Status: 
                        <?php if($total_debit == $total_kredit): ?>
                            <strong class="balance-ok">✔️ BALANCE (SEIMBANG)</strong>
                        <?php else: ?>
                            <strong class="balance-err">❌ TIDAK BALANCE (Selisih: Rp <?= number_format(abs($total_debit - $total_kredit),0,',','.') ?>)</strong>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
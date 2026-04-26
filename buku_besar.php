<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fitur Filter
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$akun_pilih  = isset($_GET['id_akun']) ? intval($_GET['id_akun']) : 0;

$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

// Ambil daftar akun untuk dropdown
$qAkun = mysqli_query($conn, "SELECT id, kode_akun, nama_akun FROM tb_akun ORDER BY kode_akun ASC");

$detail_akun = null;
$saldo_awal = 0;
$qJurnal = null;

// Jika user sudah memilih akun
if ($akun_pilih > 0) {
    $qDetail = mysqli_query($conn, "SELECT * FROM tb_akun WHERE id = $akun_pilih");
    $detail_akun = mysqli_fetch_assoc($qDetail);
    
    $saldo_normal = strtoupper($detail_akun['saldo_normal'] ?? 'DEBIT');

    // 1. Hitung Saldo Awal (Total transaksi SEBELUM bulan & tahun yang dipilih)
    $qSaldoAwal = mysqli_query($conn, "
        SELECT SUM(debit) as tot_debit, SUM(kredit) as tot_kredit 
        FROM tb_jurnal 
        WHERE id_akun = $akun_pilih 
        AND (YEAR(tanggal) < '$tahun_pilih' OR (YEAR(tanggal) = '$tahun_pilih' AND MONTH(tanggal) < '$bulan_pilih'))
    ");
    
    $rowAwal = mysqli_fetch_assoc($qSaldoAwal);
    $tot_debit_awal = $rowAwal['tot_debit'] ?? 0;
    $tot_kredit_awal = $rowAwal['tot_kredit'] ?? 0;

    // Logika perhitungan saldo awal berdasarkan Saldo Normal
    if ($saldo_normal == 'DEBIT') {
        $saldo_awal = $tot_debit_awal - $tot_kredit_awal;
    } else {
        $saldo_awal = $tot_kredit_awal - $tot_debit_awal;
    }

    // 2. Ambil mutasi jurnal PADA bulan & tahun yang dipilih
    $qJurnal = mysqli_query($conn, "
        SELECT * FROM tb_jurnal 
        WHERE id_akun = $akun_pilih 
        AND MONTH(tanggal) = '$bulan_pilih' 
        AND YEAR(tanggal) = '$tahun_pilih'
        ORDER BY tanggal ASC, id ASC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Buku Besar</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;}
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.filter-box { display: flex; gap: 10px; align-items: flex-end; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 20px;}
.filter-box label { font-size: 13px; font-weight: bold; color: #475569; display: block; margin-bottom: 5px;}
.filter-box select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; outline: none; background: white;}
.filter-box button { background: #2563eb; color: white; border: none; padding: 9px 15px; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: bold;}
.filter-box button:hover { background: #1d4ed8; }

.info-akun { display: flex; justify-content: space-between; background: #eff6ff; padding: 15px; border-radius: 6px; border-left: 4px solid #3b82f6; margin-bottom: 15px;}
.info-akun h4 { margin: 0; color: #1e3a8a; font-size: 18px;}
.info-akun p { margin: 5px 0 0 0; color: #3b82f6; font-size: 13px; font-weight: bold;}

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:10px 12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;}
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.row-awal { background: #f1f5f9; font-weight: bold; color: #475569;}
.keterangan-transaksi { color: #64748b; font-size: 12px;}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Buku Besar (General Ledger)</h3>
        <p class="desc">Laporan rincian mutasi per akun untuk melihat saldo akhir dari masing-class komponen keuangan.</p>

        <form method="GET" class="filter-box">
            <div>
                <label>Pilih Akun</label>
                <select name="id_akun" required style="width: 250px;">
                    <option value="">-- Pilih Akun --</option>
                    <?php while($a = mysqli_fetch_assoc($qAkun)): ?>
                        <option value="<?= $a['id'] ?>" <?= ($akun_pilih == $a['id']) ? 'selected' : '' ?>>
                            <?= $a['kode_akun'] ?> - <?= htmlspecialchars($a['nama_akun']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Bulan</label>
                <select name="bulan">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= str_pad($i, 2, "0", STR_PAD_LEFT) ?>" <?= ($bulan_pilih == str_pad($i, 2, "0", STR_PAD_LEFT)) ? 'selected' : '' ?>>
                            <?= $nama_bulan[$i] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label>Tahun</label>
                <select name="tahun">
                    <?php 
                    $tahun_sekarang = date('Y');
                    for($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 1; $i++): 
                    ?>
                        <option value="<?= $i ?>" <?= ($tahun_pilih == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit">Tampilkan Buku Besar</button>
        </form>

        <?php if ($akun_pilih > 0 && $detail_akun): ?>
            
            <div class="info-akun">
                <div>
                    <h4><?= isset($detail_akun['kode_akun']) ? $detail_akun['kode_akun'] : '' ?> - <?= htmlspecialchars($detail_akun['nama_akun']) ?></h4>
                    <p>Saldo Normal: <?= $saldo_normal ?></p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 15%;">No. Bukti</th>
                        <th style="width: 33%;">Keterangan</th>
                        <th class="right" style="width: 13%;">Debit (Rp)</th>
                        <th class="right" style="width: 13%;">Kredit (Rp)</th>
                        <th class="right" style="width: 14%;">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-awal">
                        <td colspan="3" class="right">SALDO AWAL (Per 1 <?= $nama_bulan[intval($bulan_pilih)] ?> <?= $tahun_pilih ?>)</td>
                        <td class="right">-</td>
                        <td class="right">-</td>
                        <td class="right"><?= number_format($saldo_awal,0,',','.') ?></td>
                    </tr>

                    <?php 
                    $saldo_berjalan = $saldo_awal;
                    $total_debit_mutasi = 0;
                    $total_kredit_mutasi = 0;

                    if($qJurnal && mysqli_num_rows($qJurnal) > 0) {
                        while($row = mysqli_fetch_assoc($qJurnal)) { 
                            $debit = $row['debit'] ?? 0;
                            $kredit = $row['kredit'] ?? 0;
                            
                            $total_debit_mutasi += $debit;
                            $total_kredit_mutasi += $kredit;

                            // Rumus Saldo Berjalan
                            if ($saldo_normal == 'DEBIT') {
                                $saldo_berjalan = $saldo_berjalan + $debit - $kredit;
                            } else {
                                $saldo_berjalan = $saldo_berjalan + $kredit - $debit;
                            }
                    ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td><strong><?= htmlspecialchars($row['no_bukti'] ?? '') ?></strong></td>
                        <td class="keterangan-transaksi"><?= htmlspecialchars($row['keterangan'] ?? '') ?></td>
                        <td class="right"><?= ($debit > 0) ? number_format($debit,0,',','.') : '-' ?></td>
                        <td class="right"><?= ($kredit > 0) ? number_format($kredit,0,',','.') : '-' ?></td>
                        <td class="right" style="font-weight: bold;"><?= number_format($saldo_berjalan,0,',','.') ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='6' class='center' style='padding: 20px; color: #94a3b8;'>Tidak ada mutasi transaksi pada bulan ini.</td></tr>";
                    }
                    ?>
                    
                    <tr class="row-awal" style="border-top: 2px solid #cbd5e1;">
                        <td colspan="3" class="right">TOTAL MUTASI & SALDO AKHIR</td>
                        <td class="right"><?= number_format($total_debit_mutasi,0,',','.') ?></td>
                        <td class="right"><?= number_format($total_kredit_mutasi,0,',','.') ?></td>
                        <td class="right" style="color: #2563eb; font-size: 15px;"><?= number_format($saldo_berjalan,0,',','.') ?></td>
                    </tr>
                </tbody>
            </table>

        <?php endif; ?>

    </div>
</div>

</body>
</html>
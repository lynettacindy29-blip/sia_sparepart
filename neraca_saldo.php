<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fitur Filter Bulan & Tahun
$bulan_pilih = isset($_GET['bulan']) ? str_pad($_GET['bulan'], 2, "0", STR_PAD_LEFT) : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

// Ambil semua daftar akun
$qAkun = mysqli_query($conn, "SELECT * FROM tb_akun ORDER BY kode_akun ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Neraca Saldo</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;}
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.filter-box { display: flex; gap: 10px; align-items: flex-end; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 20px;}
.filter-box label { font-size: 13px; font-weight: bold; color: #475569; display: block; margin-bottom: 5px;}
.filter-box select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; outline: none;}
.filter-box button { background: #2563eb; color: white; border: none; padding: 9px 15px; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: bold;}
.filter-box button:hover { background: #1d4ed8; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:10px 12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; vertical-align: middle;}
th { background:#f8fafc; color: #475569; font-weight: 600; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;}
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.total-row { font-weight: bold; font-size: 16px; background: #f1f5f9; color: #0f172a; border-top: 2px solid #94a3b8;}
.balance-ok { color: #10b981; background: #d1fae5; padding: 10px; border-radius: 5px; display: inline-block; margin-top: 15px;}
.balance-err { color: #ef4444; background: #fef2f2; padding: 10px; border-radius: 5px; display: inline-block; margin-top: 15px;}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Neraca Saldo (Trial Balance)</h3>
        <p class="desc">Daftar yang berisi seluruh jenis nama akun beserta saldo total dari setiap akun yang disusun secara sistematis sesuai kode akun.</p>

        <form method="GET" class="filter-box">
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
            <button type="submit">Tampilkan Neraca Saldo</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Kode Akun</th>
                    <th style="width: 45%;">Nama Akun</th>
                    <th class="right" style="width: 20%;">Debit (Rp)</th>
                    <th class="right" style="width: 20%;">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total_debit = 0;
                $grand_total_kredit = 0;

                if(mysqli_num_rows($qAkun) > 0) {
                    while($akun = mysqli_fetch_assoc($qAkun)) { 
                        $id_akun = $akun['id'];
                        $saldo_normal = strtoupper($akun['saldo_normal'] ?? 'DEBIT');

                        // Hitung total akumulasi jurnal untuk akun ini sampai bulan yang dipilih
                        $qJurnal = mysqli_query($conn, "
                            SELECT SUM(debit) as tot_debit, SUM(kredit) as tot_kredit 
                            FROM tb_jurnal 
                            WHERE id_akun = '$id_akun' 
                            AND (
                                YEAR(tanggal) < '$tahun_pilih' 
                                OR (YEAR(tanggal) = '$tahun_pilih' AND MONTH(tanggal) <= '$bulan_pilih')
                            )
                        ");
                        $rowJurnal = mysqli_fetch_assoc($qJurnal);
                        
                        $tot_debit = $rowJurnal['tot_debit'] ?? 0;
                        $tot_kredit = $rowJurnal['tot_kredit'] ?? 0;

                        // Tentukan saldo akhir berdasarkan Saldo Normal
                        $saldo_akhir_debit = 0;
                        $saldo_akhir_kredit = 0;

                        if ($saldo_normal == 'DEBIT') {
                            $saldo = $tot_debit - $tot_kredit;
                            if ($saldo > 0) {
                                $saldo_akhir_debit = $saldo;
                            } else if ($saldo < 0) {
                                $saldo_akhir_kredit = abs($saldo); // Jarang terjadi, tapi jaga-jaga jika minus
                            }
                        } else {
                            // Jika saldo normal Kredit (Liabilitas, Modal, Pendapatan)
                            $saldo = $tot_kredit - $tot_debit;
                            if ($saldo > 0) {
                                $saldo_akhir_kredit = $saldo;
                            } else if ($saldo < 0) {
                                $saldo_akhir_debit = abs($saldo);
                            }
                        }

                        // Tambahkan ke Grand Total
                        $grand_total_debit += $saldo_akhir_debit;
                        $grand_total_kredit += $saldo_akhir_kredit;

                        // Hanya tampilkan akun yang punya saldo (tidak nol)
                        if ($saldo_akhir_debit > 0 || $saldo_akhir_kredit > 0) {
                ?>
                <tr>
                    <td><strong><?= isset($akun['kode_akun']) ? $akun['kode_akun'] : '-' ?></strong></td>
                    <td><?= htmlspecialchars($akun['nama_akun']) ?></td>
                    <td class="right"><?= ($saldo_akhir_debit > 0) ? number_format($saldo_akhir_debit,0,',','.') : '-' ?></td>
                    <td class="right"><?= ($saldo_akhir_kredit > 0) ? number_format($saldo_akhir_kredit,0,',','.') : '-' ?></td>
                </tr>
                <?php 
                        } // end if saldo > 0
                    } // end while akun
                } else {
                    echo "<tr><td colspan='4' class='center' style='padding: 20px;'>Belum ada data akun.</td></tr>";
                }
                ?>
                
                <tr class="total-row">
                    <td colspan="2" class="right" style="padding: 15px 12px;">TOTAL NERACA SALDO</td>
                    <td class="right" style="padding: 15px 12px; color: #2563eb;">Rp <?= number_format($grand_total_debit,0,',','.') ?></td>
                    <td class="right" style="padding: 15px 12px; color: #2563eb;">Rp <?= number_format($grand_total_kredit,0,',','.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="center">
            <?php if(round($grand_total_debit) === round($grand_total_kredit) && $grand_total_debit > 0): ?>
                <div class="balance-ok">
                    <strong>✔️ NERACA SEIMBANG (BALANCE)</strong><br>
                    <span style="font-size: 13px;">Selamat! Total Debit dan Kredit Anda cocok. Jurnal Anda sudah benar.</span>
                </div>
            <?php elseif($grand_total_debit > 0 || $grand_total_kredit > 0): ?>
                <div class="balance-err">
                    <strong>❌ NERACA TIDAK SEIMBANG</strong><br>
                    <span style="font-size: 13px;">Ada selisih sebesar Rp <?= number_format(abs($grand_total_debit - $grand_total_kredit),0,',','.') ?>. Silakan cek ulang Jurnal Umum Anda.</span>
                </div>
            <?php else: ?>
                <div style="color: #64748b; padding: 10px; margin-top: 15px; font-style: italic;">Belum ada transaksi untuk dihitung.</div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
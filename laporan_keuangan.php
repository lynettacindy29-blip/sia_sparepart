<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$bulan_ini = date('m');
$tahun_ini = date('Y');

// 1. MENGHITUNG TOTAL KAS & BANK 
$qKas = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total_kas 
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id 
    WHERE a.nama_akun LIKE '%Kas%' OR a.nama_akun LIKE '%Bank%'
");
$kas = mysqli_fetch_assoc($qKas)['total_kas'] ?? 0;

// 2. MENGHITUNG TOTAL PIUTANG
$qPiutang = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total_piutang 
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id 
    WHERE a.nama_akun LIKE '%Piutang%'
");
$piutang = mysqli_fetch_assoc($qPiutang)['total_piutang'] ?? 0;

// 3. MENGHITUNG TOTAL HUTANG USAHA (LIABILITAS)
$qHutang = mysqli_query($conn, "
    SELECT SUM(j.kredit - j.debit) as total_hutang 
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id 
    WHERE a.kategori LIKE '%Liabilitas%' OR a.kategori LIKE '%Hutang%' OR a.kategori LIKE '%Kewajiban%'
");
$hutang = mysqli_fetch_assoc($qHutang)['total_hutang'] ?? 0;

// 4. MENGHITUNG LABA/RUGI BULAN INI (Termasuk HPP)
// Catatan: Jika ini Rp 0, artinya belum ada transaksi penjualan/pembelian di bulan ini.
$qPendapatanBulanIni = mysqli_query($conn, "
    SELECT SUM(j.kredit - j.debit) as total_pendapatan 
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id 
    WHERE (a.kategori = 'Pendapatan' OR a.nama_akun LIKE '%Pendapatan%') AND MONTH(j.tanggal) = '$bulan_ini' AND YEAR(j.tanggal) = '$tahun_ini'
");
$pendapatan_bulan_ini = mysqli_fetch_assoc($qPendapatanBulanIni)['total_pendapatan'] ?? 0;

$qBebanBulanIni = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total_beban 
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id 
    WHERE (a.kategori = 'Beban' OR a.id = 12 OR a.nama_akun LIKE '%Harga Pokok%') AND MONTH(j.tanggal) = '$bulan_ini' AND YEAR(j.tanggal) = '$tahun_ini'
");
$beban_bulan_ini = mysqli_fetch_assoc($qBebanBulanIni)['total_beban'] ?? 0;

$laba_rugi = $pendapatan_bulan_ini - $beban_bulan_ini;

// 5. PEMBUKTIAN NERACA (Aset = Liabilitas + Modal + Laba Berjalan)
$qAset = mysqli_query($conn, "SELECT SUM(j.debit - j.kredit) as total FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id WHERE a.kategori LIKE '%Aset%' OR a.kategori LIKE '%Harta%'");
$total_aset = mysqli_fetch_assoc($qAset)['total'] ?? 0;

$qModal = mysqli_query($conn, "SELECT SUM(j.kredit - j.debit) as total FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id WHERE a.kategori LIKE '%Modal%' OR a.kategori LIKE '%Ekuitas%'");
$total_modal = mysqli_fetch_assoc($qModal)['total'] ?? 0;

// PERBAIKAN: Seluruh Laba/Rugi dari awal berdiri 
$qPendapatanAll = mysqli_query($conn, "SELECT SUM(j.kredit - j.debit) as total FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id WHERE a.kategori = 'Pendapatan' OR a.nama_akun LIKE '%Pendapatan%'");
$pendapatan_all = mysqli_fetch_assoc($qPendapatanAll)['total'] ?? 0;

$qBebanAll = mysqli_query($conn, "SELECT SUM(j.debit - j.kredit) as total FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id WHERE a.kategori = 'Beban' OR a.id = 12 OR a.nama_akun LIKE '%Harga Pokok%'");
$beban_all = mysqli_fetch_assoc($qBebanAll)['total'] ?? 0;

// REVISI DOSEN: Laba Ditahan diubah menjadi Laba Berjalan
$laba_berjalan = $pendapatan_all - $beban_all;

$total_pasiva = $hutang + $total_modal + $laba_berjalan;
?>

<!DOCTYPE html>
<html>
<head>
<title>Ringkasan Laporan Keuangan</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.header-title { font-size: 22px; color: #1e293b; margin-bottom: 5px; font-weight: bold; }
.subtitle { font-size: 14px; color: #64748b; margin-bottom: 25px; }

/* Dashboard Cards */
.grid-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
.card-widget { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 5px solid #2563eb; }
.card-widget.green { border-left-color: #10b981; }
.card-widget.red { border-left-color: #ef4444; }
.card-widget.orange { border-left-color: #f59e0b; }

.widget-title { font-size: 13px; color: #64748b; font-weight: bold; text-transform: uppercase; margin-bottom: 8px;}
.widget-value { font-size: 24px; font-weight: bold; color: #0f172a; }

/* Neraca Mini */
.neraca-container { display: flex; gap: 20px; }
.neraca-box { flex: 1; background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.neraca-header { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; text-align: center; }
.neraca-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px; color: #475569; }
.neraca-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: #0f172a; margin-top: 15px; border-top: 2px solid #cbd5e1; padding-top: 15px; }

.balance-status { text-align: center; margin-top: 25px; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 16px; }
.status-ok { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
.status-err { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="header-title">Dashboard Keuangan</div>
    <div class="subtitle">Ringkasan performa akuntansi dan status keuangan toko Anda.</div>

    <div class="grid-container">
        <div class="card-widget">
            <div class="widget-title">Total Kas & Bank Aktif</div>
            <div class="widget-value">Rp <?= number_format($kas,0,',','.') ?></div>
        </div>
        <div class="card-widget orange">
            <div class="widget-title">Total Piutang (Belum Tertagih)</div>
            <div class="widget-value">Rp <?= number_format($piutang,0,',','.') ?></div>
        </div>
        <div class="card-widget red">
            <div class="widget-title">Total Hutang Usaha</div>
            <div class="widget-value">Rp <?= number_format($hutang,0,',','.') ?></div>
        </div>
        <div class="card-widget green">
            <div class="widget-title"><?= ($laba_rugi >= 0) ? 'Laba' : 'Rugi' ?> Bulan Ini</div>
            <div class="widget-value">Rp <?= number_format(abs($laba_rugi),0,',','.') ?></div>
        </div>
    </div>

    <div class="header-title" style="font-size: 18px; margin-top: 40px;">Pembuktian Persamaan Dasar Akuntansi</div>
    <div class="subtitle">Menurut Standar Akuntansi: Total Aset harus selalu sama dengan Total Kewajiban + Modal.</div>

    <div class="neraca-container">
        <div class="neraca-box">
            <div class="neraca-header">ASET (AKTIVA)</div>
            <div class="neraca-row">
                <span>Total Seluruh Harta & Aset Lancar</span>
                <span>Rp <?= number_format($total_aset,0,',','.') ?></span>
            </div>
            <div class="neraca-total" style="color: #2563eb;">
                <span>TOTAL ASET</span>
                <span>Rp <?= number_format($total_aset,0,',','.') ?></span>
            </div>
        </div>

        <div class="neraca-box">
            <div class="neraca-header">LIABILITAS & EKUITAS (PASIVA)</div>
            <div class="neraca-row">
                <span>Total Kewajiban (Hutang)</span>
                <span>Rp <?= number_format($hutang,0,',','.') ?></span>
            </div>
            <div class="neraca-row">
                <span>Modal Pemilik</span>
                <span>Rp <?= number_format($total_modal,0,',','.') ?></span>
            </div>
            <div class="neraca-row">
                <span>Laba Berjalan</span>
                <span>Rp <?= number_format($laba_berjalan,0,',','.') ?></span>
            </div>
            <div class="neraca-total" style="color: #2563eb;">
                <span>TOTAL PASIVA</span>
                <span>Rp <?= number_format($total_pasiva,0,',','.') ?></span>
            </div>
        </div>
    </div>

    <?php if(round($total_aset) == round($total_pasiva) && $total_aset > 0): ?>
        <div class="balance-status status-ok">
            ✔️ SISTEM BALANCE (SEIMBANG): ASET = LIABILITAS + EKUITAS
        </div>
    <?php elseif($total_aset > 0 || $total_pasiva > 0): ?>
        <div class="balance-status status-err">
            ❌ SISTEM TIDAK BALANCE! Ada selisih Rp <?= number_format(abs($total_aset - $total_pasiva),0,',','.') ?>. Silakan cek jurnal.
        </div>
    <?php endif; ?>

</div>

</body>
</html>
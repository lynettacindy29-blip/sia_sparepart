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

// FUNGSI UNTUK FORMAT ANGKA AKUNTANSI (Minus jadi Kurung)
function formatAkuntansi($angka) {
    if ($angka < 0) {
        return "(Rp " . number_format(abs($angka), 0, ',', '.') . ")";
    } else {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// FUNGSI BANTUAN UNTUK MENGAMBIL SALDO AKUN
function getSaldoNeraca($conn, $kategori_array, $bulan, $tahun, $saldo_normal = 'DEBIT') {
    $where_kategori = [];
    foreach($kategori_array as $kat) {
        $where_kategori[] = "a.kategori LIKE '%$kat%'";
    }
    $where_sql = implode(" OR ", $where_kategori);

    if (in_array('Aset', $kategori_array)) {
        $where_sql .= " OR a.nama_akun LIKE '%Kas%' OR a.nama_akun LIKE '%Bank%' OR a.nama_akun LIKE '%Piutang%' OR a.nama_akun LIKE '%Persediaan%' OR a.nama_akun LIKE '%Peralatan%' OR a.nama_akun LIKE '%Bangunan%' OR a.nama_akun LIKE '%Tanah%' OR a.nama_akun LIKE '%Kendaraan%'";
    } elseif (in_array('Liabilitas', $kategori_array)) {
        $where_sql .= " OR a.nama_akun LIKE '%Hutang%' OR a.nama_akun LIKE '%Kewajiban%'";
    } elseif (in_array('Modal', $kategori_array)) {
        $where_sql .= " OR a.nama_akun LIKE '%Modal%' OR a.nama_akun LIKE '%Ekuitas%'";
    }
    
    // PERBAIKAN: Menggunakan LEFT JOIN agar akun yang punya Saldo Awal tapi belum ada mutasi Jurnal tetap muncul
    $q = mysqli_query($conn, "
        SELECT a.nama_akun, a.saldo_awal, 
               COALESCE(SUM(j.debit), 0) as tot_debit, 
               COALESCE(SUM(j.kredit), 0) as tot_kredit
        FROM tb_akun a
        LEFT JOIN tb_jurnal j ON a.id = j.id_akun 
              AND (YEAR(j.tanggal) < '$tahun' OR (YEAR(j.tanggal) = '$tahun' AND MONTH(j.tanggal) <= '$bulan'))
        WHERE ($where_sql) 
        GROUP BY a.id, a.nama_akun, a.saldo_awal
    ");
    
    $data = [];
    $total = 0;
    if($q && mysqli_num_rows($q) > 0) {
        while($row = mysqli_fetch_assoc($q)) {
            $saldo_awal = $row['saldo_awal'] ?? 0;
            $debit = $row['tot_debit'];
            $kredit = $row['tot_kredit'];
            
            // PERBAIKAN: Menjumlahkan Saldo Awal sesuai sifat Saldo Normal Akun
            if ($saldo_normal == 'DEBIT') {
                $saldo = $saldo_awal + $debit - $kredit;
            } else {
                $saldo = $saldo_awal + $kredit - $debit;
            }
            
            if ($saldo != 0) {
                $data[] = [
                    'nama_akun' => $row['nama_akun'],
                    'saldo' => $saldo
                ];
                $total += $saldo;
            }
        }
    }
    return ['data' => $data, 'total' => $total];
}

$aset = getSaldoNeraca($conn, ['Aset', 'Harta'], $bulan_pilih, $tahun_pilih, 'DEBIT');
$liabilitas = getSaldoNeraca($conn, ['Liabilitas', 'Kewajiban'], $bulan_pilih, $tahun_pilih, 'KREDIT');
$ekuitas = getSaldoNeraca($conn, ['Modal', 'Ekuitas'], $bulan_pilih, $tahun_pilih, 'KREDIT');

// PERBAIKAN: MENGHITUNG PENDAPATAN BERSIH
$qPendapatan = mysqli_query($conn, "
    SELECT SUM(j.kredit - j.debit) as total
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id
    WHERE (a.kategori = 'Pendapatan' OR a.nama_akun LIKE '%Pendapatan%') 
    AND (YEAR(j.tanggal) < '$tahun_pilih' OR (YEAR(j.tanggal) = '$tahun_pilih' AND MONTH(j.tanggal) <= '$bulan_pilih'))
");
$tot_pendapatan = mysqli_fetch_assoc($qPendapatan)['total'] ?? 0;

// PERBAIKAN: MENGHITUNG BEBAN (TERMASUK HPP ID 12)
$qBeban = mysqli_query($conn, "
    SELECT SUM(j.debit - j.kredit) as total
    FROM tb_jurnal j JOIN tb_akun a ON j.id_akun = a.id
    WHERE (a.kategori = 'Beban' OR a.id = 12 OR a.nama_akun LIKE '%Harga Pokok%') 
    AND (YEAR(j.tanggal) < '$tahun_pilih' OR (YEAR(j.tanggal) = '$tahun_pilih' AND MONTH(j.tanggal) <= '$bulan_pilih'))
");
$tot_beban = mysqli_fetch_assoc($qBeban)['total'] ?? 0;

$laba_berjalan = $tot_pendapatan - $tot_beban;
$total_pasiva = $liabilitas['total'] + $ekuitas['total'] + $laba_berjalan;
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Neraca</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;}

h3 { margin-top:0; color:#000; text-align: center; font-size: 20px; margin-bottom: 5px; text-transform: uppercase;}
.subtitle { text-align: center; color: #000; font-size: 16px; margin-bottom: 5px; font-weight: bold;}
.periode { text-align: center; color: #000; font-size: 14px; margin-bottom: 40px; font-style: italic;}

.filter-box { display: flex; gap: 10px; justify-content: center; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 30px;}
.filter-box select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; outline: none;}
.filter-box button { background: #2563eb; color: white; border: none; padding: 9px 15px; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: bold;}
.filter-box button:hover { background: #1d4ed8; }

.neraca-container { display: flex; width: 100%; border: 2px solid #000; font-family: 'Times New Roman', Times, serif; font-size: 15px; color: #000; }
.neraca-col { flex: 1; padding: 20px; }
.col-left { border-right: 2px solid #000; }

.section-title { font-weight: bold; text-align: center; margin-bottom: 15px; text-decoration: underline; font-size: 16px;}
.account-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
.account-name { padding-left: 15px; }
.account-val { text-align: right; }

.subtotal-row { display: flex; justify-content: space-between; font-weight: bold; margin-top: 15px; padding-top: 10px; border-top: 1px solid #000; }
.grand-total { display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 30px; padding-top: 15px; border-top: 2px solid #000; border-bottom: 4px double #000; }

.spacer { flex-grow: 1; } 
.col-content { display: flex; flex-direction: column; height: 100%; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Toko Sparepart</h3>
        <div class="subtitle">Laporan Posisi Keuangan (Neraca)</div>
        <div class="periode">Per <?= date('t', strtotime("$tahun_pilih-$bulan_pilih-01")) ?> <?= $nama_bulan[(int)$bulan_pilih] ?> <?= $tahun_pilih ?></div>

        <form method="GET" class="filter-box" style="font-family: Arial, sans-serif;">
            <select name="bulan">
                <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?= str_pad($i, 2, "0", STR_PAD_LEFT) ?>" <?= ($bulan_pilih == str_pad($i, 2, "0", STR_PAD_LEFT)) ? 'selected' : '' ?>>
                        <?= $nama_bulan[$i] ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="tahun">
                <?php 
                $tahun_sekarang = date('Y');
                for($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 1; $i++): 
                ?>
                    <option value="<?= $i ?>" <?= ($tahun_pilih == $i) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Tampilkan</button>
            <button type="button" onclick="window.print()" style="background: #10b981;">Cetak</button>
        </form>

        <div class="neraca-container">
            <div class="neraca-col col-left">
                <div class="col-content">
                    <div class="section-title">ASET</div>
                    
                    <?php 
                    if(count($aset['data']) > 0) {
                        foreach($aset['data'] as $a) { 
                    ?>
                        <div class="account-row">
                            <span class="account-name"><?= htmlspecialchars($a['nama_akun']) ?></span>
                            <span class="account-val"><?= formatAkuntansi($a['saldo']) ?></span>
                        </div>
                    <?php 
                        } 
                    } else {
                        echo "<div style='text-align:center; color:#999; font-style:italic;'>Belum ada data aset.</div>";
                    }
                    ?>

                    <div class="spacer"></div>

                    <div class="grand-total">
                        <span>TOTAL ASET</span>
                        <span><?= formatAkuntansi($aset['total']) ?></span>
                    </div>
                </div>
            </div>

            <div class="neraca-col">
                <div class="col-content">
                    
                    <div class="section-title">LIABILITAS</div>
                    <?php 
                    if(count($liabilitas['data']) > 0) {
                        foreach($liabilitas['data'] as $l) { 
                    ?>
                        <div class="account-row">
                            <span class="account-name"><?= htmlspecialchars($l['nama_akun']) ?></span>
                            <span class="account-val"><?= formatAkuntansi($l['saldo']) ?></span>
                        </div>
                    <?php 
                        } 
                    } else {
                        echo "<div style='text-align:center; color:#999; font-style:italic;'>Belum ada data liabilitas.</div>";
                    }
                    ?>
                    <div class="subtotal-row" style="margin-bottom: 25px;">
                        <span>Jumlah Liabilitas</span>
                        <span><?= formatAkuntansi($liabilitas['total']) ?></span>
                    </div>

                    <div class="section-title">EKUITAS</div>
                    <?php 
                    if(count($ekuitas['data']) > 0) {
                        foreach($ekuitas['data'] as $e) { 
                    ?>
                        <div class="account-row">
                            <span class="account-name"><?= htmlspecialchars($e['nama_akun']) ?></span>
                            <span class="account-val"><?= formatAkuntansi($e['saldo']) ?></span>
                        </div>
                    <?php 
                        } 
                    }
                    ?>
                    
                    <div class="account-row">
                        <span class="account-name">Laba Berjalan</span>
                        <span class="account-val"><?= formatAkuntansi($laba_berjalan) ?></span>
                    </div>

                    <div class="subtotal-row">
                        <span>Jumlah Ekuitas</span>
                        <span><?= formatAkuntansi($ekuitas['total'] + $laba_berjalan) ?></span>
                    </div>

                    <div class="spacer"></div>

                    <div class="grand-total">
                        <span>TOTAL LIABILITAS & EKUITAS</span>
                        <span><?= formatAkuntansi($total_pasiva) ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
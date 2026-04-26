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

$tgl_awal = "$tahun_pilih-$bulan_pilih-01";
$tgl_akhir = date('Y-m-t', strtotime($tgl_awal));

?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Laba Rugi</title>
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

table { width:100%; border-collapse:collapse; font-family: 'Times New Roman', Times, serif; font-size: 15px; color: #000;}
td { padding:8px 10px; }
td.right { text-align: right; width: 160px;}
td.indent { padding-left: 30px; }
td.double-indent { padding-left: 60px; }

.header-row td { font-weight: bold; padding-top: 15px; }
.subtotal-row td { font-weight: bold; padding-top: 15px; padding-bottom: 15px; }
.border-top { border-top: 1px solid #000; }
.border-bottom { border-bottom: 1px solid #000; }
.border-double { border-bottom: 3px double #000; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Toko Sparepart</h3>
        <div class="subtitle">Laporan Laba Rugi</div>
        <div class="periode"> <?= date('t', strtotime($tgl_awal)) ?> <?= $nama_bulan[(int)$bulan_pilih] ?> <?= $tahun_pilih ?></div>

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

        <?php
// =========================================================================
        // PENGHITUNGAN LABA RUGI (RINGKAS UNTUK METODE PERPETUAL)
        // =========================================================================

        // 1. PENDAPATAN PENJUALAN
        $query_penjualan = "
            SELECT SUM(COALESCE(j.kredit, 0) - COALESCE(j.debit, 0)) as total 
            FROM tb_jurnal j 
            JOIN tb_akun a ON j.id_akun = a.id 
            WHERE a.kode_akun LIKE '401%' 
            AND MONTH(j.tanggal) = '$bulan_pilih' 
            AND YEAR(j.tanggal) = '$tahun_pilih'
        ";
        $qPenjualan = mysqli_query($conn, $query_penjualan);
        $rowPenjualan = mysqli_fetch_assoc($qPenjualan);
        $penjualan = $rowPenjualan['total'] ?? 0;
        
        $retur_jual = 0; 
        $potongan_jual = 0; 
        $penjualan_bersih = $penjualan - $retur_jual - $potongan_jual;

        // 2. HARGA POKOK PENJUALAN (HPP)
        $query_hpp = "
            SELECT SUM(COALESCE(j.debit, 0) - COALESCE(j.kredit, 0)) as total 
            FROM tb_jurnal j 
            JOIN tb_akun a ON j.id_akun = a.id 
            WHERE a.kode_akun LIKE '601%' 
            AND MONTH(j.tanggal) = '$bulan_pilih' 
            AND YEAR(j.tanggal) = '$tahun_pilih'
        ";
        $qHPP = mysqli_query($conn, $query_hpp);
        $rowHPP = mysqli_fetch_assoc($qHPP);
        $hpp = $rowHPP['total'] ?? 0;

        // 3. LABA KOTOR
        $laba_kotor = $penjualan_bersih - $hpp;

        // 4. BEBAN OPERASIONAL
        $query_beban = "
            SELECT a.nama_akun, SUM(COALESCE(j.debit, 0) - COALESCE(j.kredit, 0)) as total
            FROM tb_akun a 
            JOIN tb_jurnal j ON a.id = j.id_akun
            WHERE a.kategori = 'Beban' 
            AND a.kode_akun NOT LIKE '601%' 
            AND MONTH(j.tanggal) = '$bulan_pilih' 
            AND YEAR(j.tanggal) = '$tahun_pilih'
            GROUP BY a.id
        ";
        $qBeban = mysqli_query($conn, $query_beban);
        $total_beban = 0;
        ?>

        <table>
            <tr class="header-row">
                <td colspan="3">Pendapatan:</td>
            </tr>
            <tr>
                <td class="indent">Penjualan</td>
                <td class="right"></td>
                <td class="right">Rp <?= number_format($penjualan,0,',','.') ?></td>
            </tr>
            
            <?php if($retur_jual > 0 || $potongan_jual > 0): ?>
            <tr>
                <td class="indent">Dikurangi: Retur dan Potongan Penjualan</td>
                <td class="right border-bottom">Rp <?= number_format($retur_jual + $potongan_jual,0,',','.') ?></td>
                <td></td>
            </tr>
            <tr>
                <td class="indent">Penjualan Bersih</td>
                <td></td>
                <td class="right">Rp <?= number_format($penjualan_bersih,0,',','.') ?></td>
            </tr>
            <?php endif; ?>

            <tr class="header-row">
                <td colspan="3">Harga Pokok Penjualan:</td>
            </tr>
            <tr>
                <td class="indent">Harga Pokok Penjualan</td>
                <td></td>
                <td class="right border-bottom">(Rp <?= number_format($hpp,0,',','.') ?>)</td>
            </tr>
            
            <tr class="subtotal-row">
                <td>Laba Kotor</td>
                <td></td>
                <td class="right">Rp <?= number_format($laba_kotor,0,',','.') ?></td>
            </tr>

            <tr class="header-row">
                <td colspan="3">Beban Operasional:</td>
            </tr>
            <?php 
            if(mysqli_num_rows($qBeban) > 0) {
                while($row = mysqli_fetch_assoc($qBeban)) {
                    $saldo = $row['total'] ?? 0;
                    if ($saldo > 0) {
                        $total_beban += $saldo;
            ?>
            <tr>
                <td class="indent"><?= htmlspecialchars($row['nama_akun']) ?></td>
                <td class="right">Rp <?= number_format($saldo,0,',','.') ?></td>
                <td></td>
            </tr>
            <?php 
                    }
                }
            } else {
                echo "<tr><td class='indent' style='color:#999;'>Tidak ada data beban bulan ini.</td><td></td><td></td></tr>";
            }
            ?>
            <tr class="subtotal-row">
                <td class="indent">Total Beban Operasional</td>
                <td></td>
                <td class="right border-top border-bottom">(Rp <?= number_format($total_beban,0,',','.') ?>)</td>
            </tr>

            <?php 
            $laba_bersih = $laba_kotor - $total_beban;
            $teks_laba = ($laba_bersih >= 0) ? "Laba Bersih" : "Rugi Bersih";
            ?>
            <tr class="subtotal-row">
                <td style="font-size: 16px;"><?= $teks_laba ?></td>
                <td></td>
                <td class="right border-double" style="font-size: 16px;">Rp <?= number_format(abs($laba_bersih),0,',','.') ?></td>
            </tr>

        </table>

    </div>
</div>

</body>
</html>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Mengambil semua data barang dari master barang
$qBarang = mysqli_query($conn, "SELECT * FROM tb_barang ORDER BY nama_barang ASC");

$grand_total_stok = 0;
$grand_total_nilai = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Nilai Persediaan Stok</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
.card { background:#fff; border-radius:8px; padding:40px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;}

.header-laporan { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e293b; padding-bottom: 20px;}
.header-laporan h2 { margin: 0; color: #1e293b; font-size: 24px; text-transform: uppercase;}
.header-laporan h3 { margin: 5px 0 0 0; color: #475569; font-size: 18px; font-weight: normal;}
.header-laporan p { margin: 10px 0 0 0; color: #64748b; font-style: italic; font-size: 14px;}

.action-bar { display: flex; justify-content: flex-end; margin-bottom: 15px; }
.btn-cetak { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; text-decoration: none;}
.btn-cetak:hover { background: #059669; }

table.tb-stok { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px;}
table.tb-stok th { background: #f8fafc; color: #1e293b; padding: 12px; border: 1px solid #cbd5e1; text-align: center; }
table.tb-stok td { padding: 10px 12px; border: 1px solid #cbd5e1; color: #334155;}
.angka { text-align: right; }
.tengah { text-align: center; }

.row-total { background: #f1f5f9; font-weight: bold; font-size: 15px; color: #0f172a;}
.row-total td { padding: 15px 12px; border-top: 2px solid #1e293b; }

.info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin-top: 25px; font-size: 13px; color: #1e3a8a; line-height: 1.5;}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        
        <div class="action-bar">
            <button onclick="window.print()" class="btn-cetak">🖨️ Cetak Laporan</button>
        </div>

        <div class="header-laporan">
            <h2>Toko Sparepart</h2>
            <h3>Laporan Rincian Nilai Persediaan Barang (Stok)</h3>
            <p>Kondisi Terkini Per Tanggal: <?= date('d F Y') ?></p>
        </div>

        <table class="tb-stok">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Kode Barang</th>
                    <th width="35%">Nama Barang</th>
                    <th width="10%">Stok Fisik</th>
                    <th width="15%">Harga Pokok (HPP/Rata-rata)</th>
                    <th width="20%">Total Nilai Persediaan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if(mysqli_num_rows($qBarang) > 0) {
                    while($b = mysqli_fetch_assoc($qBarang)) {
                        $stok = $b['stok'];
                        // Di sistem Perpetual Moving Average, harga_beli di master barang berfungsi sebagai HPP Terkini
                        $hpp = $b['harga_beli']; 
                        
                        $total_nilai = $stok * $hpp;

                        // Akumulasi Grand Total
                        $grand_total_stok += $stok;
                        $grand_total_nilai += $total_nilai;
                ?>
                <tr>
                    <td class="tengah"><?= $no++ ?></td>
                    <td class="tengah"><?= htmlspecialchars($b['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                    <td class="angka"><?= number_format($stok, 0, ',', '.') ?> pcs</td>
                    <td class="angka">Rp <?= number_format($hpp, 0, ',', '.') ?></td>
                    <td class="angka" style="font-weight: 500;">Rp <?= number_format($total_nilai, 0, ',', '.') ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='tengah' style='font-style:italic; color:#94a3b8;'>Belum ada data barang di database.</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="row-total">
                    <td colspan="3" class="angka" style="padding-right: 20px;">GRAND TOTAL:</td>
                    <td class="angka"><?= number_format($grand_total_stok, 0, ',', '.') ?> pcs</td>
                    <td></td>
                    <td class="angka" style="color: #2563eb; font-size: 16px;">Rp <?= number_format($grand_total_nilai, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="info-box">
            <strong>💡 Catatan Audit:</strong><br>
            Nilai <strong>Grand Total Persediaan (Rp <?= number_format($grand_total_nilai, 0, ',', '.') ?>)</strong> di atas merupakan rincian wujud fisik dari saldo akun <strong>Persediaan Barang Dagangan</strong> yang tercatat pada Laporan Neraca saat ini. Sistem ini menggunakan metode Perpetual Inventory - Moving Average.
        </div>

    </div>
</div>

</body>
</html>
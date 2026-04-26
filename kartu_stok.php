<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Menangkap ID barang yang dipilih dari dropdown
$id_barang = isset($_GET['id_barang']) ? intval($_GET['id_barang']) : 0;

$qDaftarBarang = mysqli_query($conn, "SELECT id, kode_barang, nama_barang FROM tb_barang ORDER BY nama_barang ASC");

$qKartu = null;
$nama_barang_terpilih = "";
$stok_sekarang = 0;
$hpp_sekarang = 0; // Ini HPP Average Terkini

if ($id_barang > 0) {
    // Ambil detail nama barang dan HPP Average terkininya dari Master Barang
    $qNama = mysqli_query($conn, "SELECT nama_barang, stok, harga_beli FROM tb_barang WHERE id = $id_barang");
    if ($rowNama = mysqli_fetch_assoc($qNama)) {
        $nama_barang_terpilih = $rowNama['nama_barang'];
        $stok_sekarang = $rowNama['stok'];
        $hpp_sekarang = $rowNama['harga_beli']; // Harga Rata-rata Perpetual
    }

    // Ambil riwayat stok
    $qKartu = mysqli_query($conn, "
        SELECT * FROM tb_kartu_stok 
        WHERE id_barang = $id_barang 
        ORDER BY tanggal ASC, id ASC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Kartu Stok</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.form-group { display:flex; gap:10px; align-items:flex-end; margin-bottom: 20px;}
label { font-size:13px; display:block; margin-bottom: 5px; font-weight: bold; color: #333;}
select { width:300px; padding:8px; border:1px solid #ccc; border-radius:5px; }
button { padding:9px 15px; border:none; border-radius:5px; background:#2563eb; color:#fff; font-weight:bold; cursor:pointer; }

table { width:100%; border-collapse:collapse; margin-top:10px; background:#fff; font-size: 13px; }
th, td { padding:12px 10px; border-bottom:1px solid #eee; text-align: left;}
th { background:#f8fafc; font-weight: 600; color: #475569; text-align: center;}
td.center { text-align: center; }
td.right { text-align: right; }

.badge-masuk { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;}
.badge-keluar { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;}
.badge-sesuai { background-color: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;}

.summary-box { display: flex; justify-content: space-between; margin-top: 25px; padding-top: 20px; border-top: 2px dashed #cbd5e1; background: #f8fafc; padding: 15px; border-radius: 6px;}
.summary-item { text-align: right; }
.summary-label { font-size: 13px; color: #64748b; margin-bottom: 5px; text-transform: uppercase; font-weight: bold;}
.summary-value { font-size: 20px; font-weight: bold; color: #0f172a; }
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">

    <div class="card">
        <h3>Laporan Kartu Stok & Jejak Audit</h3>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Memantau pergerakan masuk/keluar barang beserta harga beli aktualnya.</p>
        
        <form method="GET" action="kartu_stok.php" class="form-group">
            <div>
                <label>Pilih Barang:</label>
                <select name="id_barang" required>
                    <option value="">-- Silakan Pilih Barang --</option>
                    <?php while($b = mysqli_fetch_assoc($qDaftarBarang)) { ?>
                        <option value="<?= $b['id'] ?>" <?= ($id_barang == $b['id']) ? 'selected' : '' ?>>
                            <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit">Tampilkan Riwayat</button>
        </form>
    </div>

    <?php if ($id_barang > 0): ?>
    <div class="card">
        <h4 style="margin-bottom: 15px;">Kartu Stok: <span style="color: #2563eb; text-transform: uppercase;"><?= $nama_barang_terpilih ?></span></h4>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 15%;">No. Referensi</th>
                    <th style="width: 15%;">Jenis Transaksi</th>
                    <th style="width: 15%;">Keterangan</th>
                    <th style="width: 8%;">Masuk</th>
                    <th style="width: 8%;">Keluar</th>
                    <th style="width: 10%;">Saldo Qty</th>
                    <th style="width: 14%;">Harga Transaksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($qKartu) > 0) {
                    while($row = mysqli_fetch_assoc($qKartu)) { 
                        $badge_class = "badge-sesuai";
                        if($row['jenis_transaksi'] == 'pembelian') $badge_class = "badge-masuk";
                        if($row['jenis_transaksi'] == 'penjualan') $badge_class = "badge-keluar";

                        // Tampilkan harga aktual yang tercatat di tabel tb_kartu_stok
                        $harga_tampil = (isset($row['harga_pokok']) && $row['harga_pokok'] > 0) ? "Rp " . number_format($row['harga_pokok'], 0, ',', '.') : "-";
                ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <td class="center"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td class="center"><strong><?= $row['no_referensi'] ?></strong></td>
                    <td class="center"><span class="<?= $badge_class ?>"><?= strtoupper($row['jenis_transaksi']) ?></span></td>
                    <td><?= $row['keterangan'] ?></td>
                    <td class="center" style="color: #059669; font-weight:bold;">
                        <?= $row['qty_masuk'] > 0 ? '+'.$row['qty_masuk'] : '-' ?>
                    </td>
                    <td class="center" style="color: #dc2626; font-weight:bold;">
                        <?= $row['qty_keluar'] > 0 ? '-'.$row['qty_keluar'] : '-' ?>
                    </td>
                    <td class="center" style="font-weight: bold; background: #f8fafc; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                        <?= $row['saldo_stok'] ?>
                    </td>
                    <td class="right" style="color: #475569;">
                        <?= $harga_tampil ?>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='9' class='center' style='padding:20px; color:#94a3b8;'>Belum ada riwayat transaksi.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <div class="summary-box">
            <div class="summary-item" style="text-align: left;">
                <div class="summary-label">Metode Penilaian Persediaan</div>
                <div class="summary-value" style="font-size: 16px; color: #059669; margin-top: 5px;">Perpetual Moving Average</div>
            </div>
            <div style="display: flex; gap: 40px;">
                <div class="summary-item" style="border-right: 2px solid #cbd5e1; padding-right: 40px;">
                    <div class="summary-label">Sisa Stok Akhir</div>
                    <div class="summary-value" style="color: #2563eb;"><?= isset($stok_sekarang) ? $stok_sekarang : 0 ?> Unit</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">HPP (Average) Terkini</div>
                    <div class="summary-value" style="color: #1e293b; text-decoration: underline;">
                        Rp <?= number_format($hpp_sekarang, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php endif; ?>

</div>

</body>
</html>
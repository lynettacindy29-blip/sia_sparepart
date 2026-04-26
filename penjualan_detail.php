<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID Penjualan tidak ditemukan");
}

$id_penjualan = intval($_GET['id']);

// Ambil data Nota (Header)
$qHeader = mysqli_query($conn,"SELECT * FROM tb_penjualan WHERE id=$id_penjualan");
$dataHeader = mysqli_fetch_assoc($qHeader);

if(!$dataHeader){
    die("Data tidak ditemukan");
}

// Ambil data barang yang STOK-nya LEBIH DARI 0
$qBarang = mysqli_query($conn,"SELECT * FROM tb_barang WHERE stok > 0 ORDER BY nama_barang ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Penjualan (Kasir)</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam Kasir */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.row { display:flex; gap:20px; align-items: flex-start; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.col-4 { width: 35%; }
.col-8 { width: 65%; }

h3 { margin-top:0; color:#333; }
.info-box { background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #e2e8f0; font-size: 14px;}
.info-box span { font-weight: bold; color: #2563eb; }

label { font-size:13px; margin-top:10px; display:block; font-weight: bold; color: #475569; }
input, select { width:100%; padding:9px; margin-top:5px; border:1px solid #cbd5e1; border-radius:5px; box-sizing: border-box;}
input:focus, select:focus { outline:none; border-color: #2563eb; }

button { margin-top:15px; width:100%; padding:10px; border:none; border-radius:6px; background:#2563eb; color:#fff; font-weight:bold; cursor:pointer; font-size: 14px;}
button:hover { background: #1d4ed8; }
.btn-success { background: #10b981; font-size: 15px; padding: 12px;}
.btn-success:hover { background: #059669; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.right, th.right { text-align: right; }
.total-row { font-weight: bold; font-size: 16px; background: #f8fafc; color: #1e293b; }
.empty-cart { text-align: center; color: #94a3b8; padding: 20px; font-style: italic; }
.stok-badge { font-size: 11px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; color: #475569;}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    
    <div class="info-box">
        Nota Penjualan: 
        Tanggal <span><?= date('d-m-Y', strtotime($dataHeader['tanggal'])) ?></span> | 
        Pelanggan: <span><?= htmlspecialchars($dataHeader['nama_pelanggan']) ?></span> | 
        Metode Pembayaran: <span><strong style="text-transform: uppercase;"><?= $dataHeader['metode'] ?></strong></span>
    </div>

    <div class="row">
        <div class="col-4">
            <div class="card">
                <h3>Pilih Barang</h3>
                <p style="font-size:12px; color:#64748b; margin-top:-5px;">Masukkan barang yang ingin dibeli pelanggan.</p>
                
                <form method="POST" action="penjualan_tambah_barang.php">
                    <input type="hidden" name="id_penjualan" value="<?= $id_penjualan ?>">

                    <label>Nama Barang (Hanya yang ada stok)</label>
                    <select name="id_barang" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php while($b = mysqli_fetch_assoc($qBarang)) { ?>
                            <option value="<?= $b['id'] ?>">
                                <?= $b['kode_barang'] ?> - <?= htmlspecialchars($b['nama_barang']) ?> 
                                (Sisa: <?= $b['stok'] ?>)
                            </option>
                        <?php } ?>
                    </select>

                    <label>Jumlah (Qty)</label>
                    <input type="number" name="jumlah" required min="1" value="1">

                    <button type="submit">Masukkan ke Struk</button>
                </form>
            </div>
        </div>

        <div class="col-8">
            <div class="card">
                <h3>Keranjang / Struk Belanja</h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th class="right">Harga Jual</th>
                            <th class="right">Qty</th>
                            <th class="right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        if (!empty($_SESSION['keranjang_penjualan'])) {
                            foreach ($_SESSION['keranjang_penjualan'] as $item) {
                                $total += $item['subtotal'];
                                echo "<tr>
                                    <td>{$item['nama_barang']}</td>
                                    <td class='right'>Rp " . number_format($item['harga'],0,',','.') . "</td>
                                    <td class='right'>{$item['jumlah']}</td>
                                    <td class='right'>Rp " . number_format($item['subtotal'],0,',','.') . "</td>
                                </tr>";
                            }
                        ?>
                            <tr class="total-row">
                                <td colspan="3" class="right">Total yang harus dibayar</td>
                                <td class="right">Rp <?= number_format($total,0,',','.') ?></td>
                            </tr>
                        <?php
                        } else {
                            echo "<tr><td colspan='4' class='empty-cart'>Struk masih kosong. Silakan pilih barang di sebelah kiri.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <?php if (!empty($_SESSION['keranjang_penjualan'])): ?>
                <div style="margin-top: 25px; border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                    <form method="POST" action="penjualan_simpan_final.php">
                        <input type="hidden" name="id_penjualan" value="<?= $id_penjualan ?>">
                        <button type="submit" class="btn-success">
                            Selesaikan Transaksi (Simpan)
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

</body>
</html>
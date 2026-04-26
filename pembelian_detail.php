<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID Pembelian tidak ditemukan");
}

$id_pembelian = intval($_GET['id']);

/* ambil data header + supplier */
$qHeader = mysqli_query($conn, "
    SELECT p.*, s.nama_supplier
    FROM tb_pembelian p
    JOIN tb_supplier s ON p.id_supplier = s.id
    WHERE p.id = $id_pembelian
");

if (!$qHeader) {
    die("Query header error: " . mysqli_error($conn));
}

$dataHeader = mysqli_fetch_assoc($qHeader);

if (!$dataHeader) {
    die("Data pembelian tidak ditemukan");
}

/* ambil daftar barang */
$qBarang = mysqli_query($conn, "
    SELECT * FROM tb_barang ORDER BY nama_barang ASC
");

if (!$qBarang) {
    die("Query barang error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam */
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

button { margin-top:15px; width:100%; padding:10px; border:none; border-radius:6px; background:#2563eb; color:#fff; font-weight:bold; cursor:pointer; }
button:hover { background: #1d4ed8; }
.btn-success { background: #10b981; font-size: 15px; padding: 12px;}
.btn-success:hover { background: #059669; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.right, th.right { text-align: right; }
.total-row { font-weight: bold; font-size: 16px; background: #f8fafc; color: #1e293b; }
.empty-cart { text-align: center; color: #94a3b8; padding: 20px; font-style: italic; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="info-box">
        Detail Transaksi: 
        Tanggal <span><?= date('d-m-Y', strtotime($dataHeader['tanggal'])) ?></span> | 
        Supplier: <span><?= htmlspecialchars($dataHeader['nama_supplier']) ?></span> | 
        Metode: <span><strong style="text-transform: uppercase;"><?= $dataHeader['metode'] ?></strong></span>
    </div>

    <div class="row">
        <div class="col-4">
            <div class="card">
                <h3>Pilih Barang</h3>
                <p style="font-size:12px; color:#64748b; margin-top:-5px;">Masukkan barang yang dibeli ke keranjang.</p>
                
                <form method="POST" action="pembelian_tambah_barang.php">
                    <input type="hidden" name="id_pembelian" value="<?= $id_pembelian ?>">

                    <label>Nama Barang</label>
                    <select name="id_barang" id="selectBarang" required onchange="setHargaLama()">
                        <option value="" data-harga="0">-- Pilih Barang --</option>
                        <?php while($b = mysqli_fetch_assoc($qBarang)) { ?>
                            <option value="<?= $b['id'] ?>" data-harga="<?= $b['harga_beli'] ?>">
                                <?= $b['kode_barang'] ?> - <?= htmlspecialchars($b['nama_barang']) ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label>Jumlah (Qty)</label>
                    <input type="number" name="jumlah" required min="1" value="1">

                    <label>Harga Beli (Satuan)</label>
                    <input type="number" name="harga_beli" id="inputHarga" required min="1" placeholder="Masukkan harga beli terbaru...">
                    <small style="font-size: 11px; color: #64748b;">HPP (Average) akan ter-update otomatis jika harga ini berbeda dari modal lama.</small>

                    <button type="submit">Masukkan Keranjang</button>
                </form>
            </div>
        </div>

        <div class="col-8">
            <div class="card">
                <h3>Keranjang Pembelian</h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th class="right">Harga Beli</th>
                            <th class="right">Qty</th>
                            <th class="right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        if (!empty($_SESSION['keranjang_pembelian'])) {
                            foreach ($_SESSION['keranjang_pembelian'] as $index => $item) {
                                $total += $item['subtotal'];
                                echo "<tr>
                                    <td>{$item['nama_barang']}</td>
                                    <td class='right'>Rp " . number_format($item['harga_beli'],0,',','.') . "</td>
                                    <td class='right'>{$item['jumlah']}</td>
                                    <td class='right'>Rp " . number_format($item['subtotal'],0,',','.') . "</td>
                                </tr>";
                            }
                        ?>
                            <tr class="total-row">
                                <td colspan="3" class="right">Total Keseluruhan</td>
                                <td class="right">Rp <?= number_format($total,0,',','.') ?></td>
                            </tr>
                        <?php
                        } else {
                            echo "<tr><td colspan='4' class='empty-cart'>Keranjang masih kosong. Sila masukkan barang di sebelah.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <?php if (!empty($_SESSION['keranjang_pembelian'])): ?>
                <div style="margin-top: 25px; border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                    <form method="POST" action="pembelian_simpan.php">
                        <input type="hidden" name="id_pembelian" value="<?= $id_pembelian ?>">
                        <button type="submit" class="btn-success">
                            Simpan Transaksi (Rp <?= number_format($total,0,',','.') ?>)
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
// Fungsi kecil untuk mengisi otomatis input harga dengan HPP lama saat barang dipilih
function setHargaLama() {
    var select = document.getElementById("selectBarang");
    var harga = select.options[select.selectedIndex].getAttribute("data-harga");
    document.getElementById("inputHarga").value = harga;
}
</script>

</body>
</html>
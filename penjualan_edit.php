<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0); 
if (!$id) {
    header("Location: penjualan.php");
    exit;
}

/* ===========================
   AMBIL DATA HEADER PENJUALAN
=========================== */
$qPenjualan = mysqli_query($conn, "SELECT * FROM tb_penjualan WHERE id = '$id'");
$penjualan = mysqli_fetch_assoc($qPenjualan);

if (!$penjualan) {
    die("Data penjualan tidak ditemukan!");
}

/* ===========================
   AMBIL DETAIL BARANG
=========================== */
$qDetail = mysqli_query($conn, "
    SELECT d.*, b.nama_barang
    FROM tb_detail_penjualan d
    JOIN tb_barang b ON d.id_barang = b.id
    WHERE d.id_penjualan = '$id'
");

/* ===========================
   AMBIL LIST BARANG (Termasuk Harga Jual)
=========================== */
// KITA TAMBAHKAN kolom "harga_jual" ke dalam query ini
$qBarang = mysqli_query($conn, "SELECT id, kode_barang, nama_barang, harga_jual FROM tb_barang WHERE stok > 0 OR id IN (SELECT id_barang FROM tb_detail_penjualan WHERE id_penjualan = '$id') ORDER BY nama_barang ASC");

// Simpan teks HTML pilihan barang ke dalam variabel + simpan harga_jual di atribut "data-harga"
$options_barang = "";
while ($b = mysqli_fetch_assoc($qBarang)) {
    $options_barang .= "<option value='" . $b['id'] . "' data-harga='" . $b['harga_jual'] . "'>" . $b['kode_barang'] . " - " . htmlspecialchars($b['nama_barang'], ENT_QUOTES) . "</option>";
}
// Kembalikan pointer data agar bisa di-loop lagi di tabel PHP
mysqli_data_seek($qBarang, 0); 

/* ===========================
   AMBIL LIST PELANGGAN LAMA
=========================== */
$qPelanggan = mysqli_query($conn, "SELECT DISTINCT nama_pelanggan FROM tb_penjualan WHERE nama_pelanggan != '' ORDER BY nama_pelanggan ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Penjualan</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

label { font-size:13px; margin-top:15px; display:block; font-weight: bold; color: #475569; }
input, select { width:100%; padding:9px; margin-top:6px; border:1px solid #cbd5e1; border-radius:5px; font-size: 14px; box-sizing: border-box; }
input:focus, select:focus { border-color: #2563eb; outline: none; }

table { width:100%; border-collapse:collapse; margin-top:10px; margin-bottom: 20px;}
th, td { padding:10px; border:1px solid #e2e8f0; font-size:13px; text-align: left; vertical-align: middle;}
th { background:#f8fafc; color: #475569; font-weight: 600; text-align: center;}

.btn-update { background:#10b981; color:#fff; padding:12px 20px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; font-size: 14px; }
.btn-update:hover { background:#059669; }
.btn-batal { background:#64748b; color: white; padding:12px 20px; border-radius:6px; text-decoration:none; font-weight: bold; font-size: 14px; margin-left: 10px;}
.btn-batal:hover { background:#475569; }

/* Tombol Aksi Tambah/Hapus Baris */
.btn-tambah-baris { background: #2563eb; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: bold;}
.btn-hapus-baris { background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; margin-top: 5px;}

/* Input Readonly / Terkunci */
.input-readonly { background-color: #f1f5f9; cursor: not-allowed; font-weight: bold; color: #334155;}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Edit Transaksi Penjualan</h3>
        <p class="desc">Nota: <strong><?= isset($penjualan['no_nota']) ? $penjualan['no_nota'] : '-' ?></strong></p>

        <form method="POST" action="penjualan_update.php" id="formEdit">
            <input type="hidden" name="id" value="<?= $penjualan['id'] ?>">

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d', strtotime($penjualan['tanggal'])) ?>" required>
                </div>
                <div style="flex: 1;">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" list="data_pelanggan" value="<?= htmlspecialchars($penjualan['nama_pelanggan']) ?>" autocomplete="off" required>
                    <datalist id="data_pelanggan">
                        <?php while($p = mysqli_fetch_assoc($qPelanggan)) { ?>
                            <option value="<?= htmlspecialchars($p['nama_pelanggan']) ?>"></option>
                        <?php } ?>
                    </datalist>
                </div>
                <div style="flex: 1;">
                    <label>Metode Pembayaran</label>
                    <select name="metode" required>
                        <option value="tunai" <?= $penjualan['metode']=='tunai'?'selected':'' ?>>Tunai (Cash)</option>
                        <option value="kredit" <?= $penjualan['metode']=='kredit'?'selected':'' ?>>Kredit (Piutang)</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0;">Rincian Barang</h4>
                <button type="button" class="btn-tambah-baris" onclick="tambahBaris()">+ Tambah Barang Lain</button>
            </div>
            
            <table id="tabelBarang">
                <tr>
                    <th style="width: 40%;">Pilih Barang</th>
                    <th style="width: 25%;">Harga Jual (Rp)</th>
                    <th style="width: 15%;">Jumlah (Qty)</th>
                    <th style="width: 20%;">Aksi</th>
                </tr>

                <?php while ($d = mysqli_fetch_assoc($qDetail)) { ?>
                <tr>
                    <td>
                        <select name="id_barang[]" required onchange="setHarga(this)">
                            <?php
                            mysqli_data_seek($qBarang, 0); // Reset pointer
                            while ($b = mysqli_fetch_assoc($qBarang)) {
                                $selected = ($b['id'] == $d['id_barang']) ? 'selected' : '';
                                echo "<option value='{$b['id']}' data-harga='{$b['harga_jual']}' $selected>{$b['kode_barang']} - {$b['nama_barang']}</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="harga[]" value="<?= $d['harga'] ?>" required min="0" class="input-readonly" readonly>
                    </td>
                    <td>
                        <input type="number" name="qty[]" value="<?= $d['qty'] ?>" required min="1">
                    </td>
                    <td style="text-align: center;">
                        <span style="font-size: 11px; color: #64748b;">Subtotal Lama: Rp <?= number_format($d['subtotal'],0,',','.') ?></span><br>
                        <button type="button" class="btn-hapus-baris" onclick="hapusBaris(this)">Hapus Baris</button>
                    </td>
                </tr>
                <?php } ?>
            </table>

            <button type="submit" class="btn-update">Simpan Perubahan (Update)</button>
            <a href="penjualan.php" class="btn-batal">Batal</a>

        </form>
    </div>
</div>

<script>
// Fungsi otomatisasi Harga Jual berdasarkan pilihan Dropdown
function setHarga(selectElement) {
    // Ambil opsi yang sedang dipilih oleh user
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    
    // Ambil nilai dari atribut data-harga
    var harga = selectedOption.getAttribute('data-harga');
    
    // Cari elemen input harga yang berada dalam baris tabel yang sama
    var row = selectElement.parentNode.parentNode;
    var inputHarga = row.querySelector('input[name="harga[]"]');
    
    // Set nilainya
    inputHarga.value = harga ? harga : 0;
}

// Fungsi JavaScript untuk menambah baris barang baru
function tambahBaris() {
    var table = document.getElementById("tabelBarang");
    var row = table.insertRow(-1); // Tambah di paling bawah
    
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    var cell4 = row.insertCell(3);
    
    // Tambahkan onchange="setHarga(this)" di select
    cell1.innerHTML = `<select name="id_barang[]" required onchange="setHarga(this)"><option value="" data-harga="0">-- Pilih Barang --</option><?= $options_barang ?></select>`;
    // Set input harga sebagai readonly
    cell2.innerHTML = `<input type="number" name="harga[]" required min="0" class="input-readonly" readonly placeholder="Otomatis">`;
    cell3.innerHTML = `<input type="number" name="qty[]" value="1" required min="1">`;
    cell4.innerHTML = `<button type="button" class="btn-hapus-baris" onclick="hapusBaris(this)">Hapus Baris</button>`;
    
    cell4.style.textAlign = "center";
}

// Fungsi JavaScript untuk menghapus baris barang
function hapusBaris(btn) {
    var row = btn.parentNode.parentNode;
    var table = document.getElementById("tabelBarang");
    
    if (table.rows.length <= 2) {
        alert("Peringatan: Nota harus memiliki minimal 1 barang. Jika ingin membatalkan transaksi, gunakan tombol 'Hapus' di halaman depan.");
        return;
    }
    row.parentNode.removeChild(row);
}
</script>

</body>
</html>
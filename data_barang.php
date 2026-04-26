<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$qKategori = mysqli_query($conn, "SELECT * FROM tb_kategori");

// ==============================================================================
// QUERY SUPER RINGAN: LANGSUNG AMBIL DARI KOLOM STOK AWAL & HPP AWAL
// ==============================================================================
$qBarang = mysqli_query($conn, "
    SELECT 
        b.id, 
        b.kode_barang, 
        b.nama_barang, 
        b.harga_jual,
        b.stok_awal,
        b.hpp_awal,
        k.nama_kategori
    FROM tb_barang b
    LEFT JOIN tb_kategori k ON b.id_kategori = k.id
    ORDER BY b.id DESC
");

$grand_total_saldo = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Setup Master & Saldo Awal Barang</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.row { display:flex; gap:20px; }
.card { background:#fff; border-radius:8px; padding:15px; }
.col-3 { width:25%; }
.col-9 { width:75%; }
label { font-size:13px; margin-top:10px; display:block; font-weight: bold; color: #334155;}
input, select { width:100%; padding:8px; margin-top:4px; border:1px solid #ccc; border-radius:5px; box-sizing: border-box; }
button { margin-top:15px; width:100%; padding:10px; border:none; border-radius:6px; background:#10b981; color:#fff; font-weight:bold; cursor:pointer; }
button:hover { background: #059669; }

table { width:100%; border-collapse:collapse; margin-top:10px; background:#fff; }
th, td { padding:10px; border-bottom:1px solid #e2e8f0; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #1e293b;}

/* Styling Area Aksi Tabel */
.table-header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.search-box input { width:250px; padding:8px; border:1px solid #ccc; border-radius:5px; outline:none; }
.btn-sync { background:#3b82f6; color:#fff; padding:8px 15px; border-radius:5px; text-decoration:none; font-weight:bold; font-size: 13px; display:inline-flex; align-items:center; gap:6px; }
.btn-sync:hover { background:#2563eb; }

.aksi-btn { display:flex; gap:6px; }
.btn-sm { padding:5px 10px; font-size:12px; border-radius:4px; text-decoration:none; color:#fff; }
.btn-edit { background:#ffc107; color:#000; }
.btn-delete { background:#dc3545; color:#fff; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.grand-total-row { background: #f1f5f9; font-weight: bold; font-size: 15px; }
.grand-total-row td { border-top: 2px solid #94a3b8; padding: 15px 10px; }

.info-alert { background: #e0f2fe; color: #0369a1; padding: 10px; border-radius: 5px; font-size: 13px; margin-bottom: 15px; border-left: 4px solid #0284c7;}
</style>
</head>

<body>

<?php include "sidebar.php"; ?>
<div class="content">

<div class="row">
    <div class="col-3">
        <div class="card">
            <h3>Tambah Master Barang</h3>
            <p style="font-size: 12px; color: #64748b; margin-top:-3px;">Gunakan form ini untuk mendata barang baru dan menentukan stok awal fisiknya.</p>
            <form method="POST" action="barang_simpan.php">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" required>

                <label>Kategori Barang</label>
                <select name="id_kategori" required>
                    <option value="">-- Pilih --</option>
                    <?php while($k = mysqli_fetch_assoc($qKategori)) { ?>
                        <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                    <?php } ?>
                </select>

                <label>Harga Pokok Dasar (HPP)</label>
                <input type="number" name="harga_beli" required title="Harga modal dasar per satuan">

                <label>Harga Jual</label>
                <input type="number" name="harga_jual" required>

                <label>Setup Stok Awal Fisik</label>
                <input type="number" name="stok" value="0">

                <label>Batas Stok Minimal</label>
                <input type="number" name="stok_minimal" value="5" required>

                <button type="submit">+ Simpan Data Barang</button>
            </form>
        </div>
    </div>

    <div class="col-9">
        <div class="card">
            <h3>Setup Saldo Awal Fisik Persediaan</h3>
            
            <div class="info-alert">
                <strong>💡 Catatan Audit:</strong> Tabel ini murni menampilkan <strong>Setup Saldo Awal</strong>. Nilai ini tidak akan berubah meskipun terjadi pembelian atau penjualan di bulan berjalan.
            </div>

            <div class="table-header-actions">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Cari barang...">
                </div>
                
                <a href="sinkron_persediaan_proses.php" class="btn-sync" onclick="return confirm('Proses ini akan menimpa Saldo Awal akun Persediaan di Neraca dengan nilai Grand Total yang ada di tabel ini. Lanjutkan?')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    Sinkronisasi ke Neraca Awal
                </a>
            </div>

            <table id="tabelBarang">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nama Barang</th>
                        <th class="text-right" style="width: 15%;">HPP (Saldo Awal)</th>
                        <th class="text-right" style="width: 15%;">Harga Jual</th>
                        <th class="text-center" style="width: 15%;">Stok (Saldo Awal)</th>
                        <th class="text-right" style="width: 15%;">Total Nilai Awal</th>
                        <th style="width: 15%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if(mysqli_num_rows($qBarang) > 0) {
                    while($row = mysqli_fetch_assoc($qBarang)) { 
                        
                        // Menghitung total dari kolom baru
                        $total_nilai = $row['stok_awal'] * $row['hpp_awal'];
                        $grand_total_saldo += $total_nilai;
                ?>
                    <tr>
                        <td>
                            <span style="color:#64748b; font-size:11px;"><?= $row['kode_barang'] ?></span><br>
                            <strong><?= htmlspecialchars($row['nama_barang']) ?></strong>
                        </td>
                        <td class="text-right" style="color: #059669; font-weight: bold;">Rp <?= number_format($row['hpp_awal'],0,',','.') ?></td>
                        <td class="text-right" style="color: #2563eb;">Rp <?= number_format($row['harga_jual'],0,',','.') ?></td>
                        <td class="text-center" style="font-size: 15px; font-weight: bold;"><?= $row['stok_awal'] ?></td>
                        <td class="text-right" style="font-weight: bold; color: #0f172a;">Rp <?= number_format($total_nilai,0,',','.') ?></td>
                        <td>
                            <div class="aksi-btn" style="justify-content: center;">
                                <a href="barang_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <a href="barang_hapus.php?id=<?= $row['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus barang ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='text-center' style='padding:20px; color:#94a3b8;'>Belum ada data barang.</td></tr>";
                }
                ?>
                </tbody>
                <tfoot>
                    <tr class="grand-total-row">
                        <td colspan="4" class="text-right" style="color: #334155;">GRAND TOTAL SALDO AWAL FISIK:</td>
                        <td class="text-right" style="color: #10b981; font-size: 18px;">Rp <?= number_format($grand_total_saldo,0,',','.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll("#tabelBarang tbody tr").forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
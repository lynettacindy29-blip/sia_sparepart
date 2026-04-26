<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$query = "
    SELECT p.*, a.nama_akun 
    FROM tb_pengeluaran p
    LEFT JOIN tb_akun a ON p.id_akun = a.id
    ORDER BY p.tanggal DESC, p.id DESC
";
$q = mysqli_query($conn, $query);

if (!$q) {
    die("Error Database: " . mysqli_error($conn) . "<br><br>Coba cek nama tabel pengeluaranmu di phpMyAdmin.");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Pengeluaran Kas</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam untuk Master & Transaksi */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.btn-tambah { background: #2563eb; color: #fff; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; }
.btn-tambah:hover { background: #1d4ed8; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1; }
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.aksi-btn { display: flex; gap: 8px; justify-content: center;}
.btn-sm { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; color: white;}
.btn-edit { background: #f59e0b; }
.btn-edit:hover { background: #d97706; }
.btn-hapus { background: #ef4444; }
.btn-hapus:hover { background: #dc2626; }

.badge-beban { background-color: #fef2f2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.total-row { background: #f8fafc; font-weight: bold; font-size: 15px; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Data Pengeluaran Operasional</h3>
        <p class="desc">Catatan biaya dan beban operasional toko (Uang Keluar) selain untuk pembelian stok barang.</p>

        <div class="toolbar">
            <a href="pengeluaran_tambah.php" class="btn-tambah">+ Tambah Pengeluaran Kas</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 25%;">Keterangan Pengeluaran</th>
                    <th style="width: 25%;">Kategori / Akun Biaya</th>
                    <th class="right" style="width: 20%;">Nominal (Rp)</th>
                    <th class="center" style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_pengeluaran = 0;
                if(mysqli_num_rows($q) > 0) {
                    while($row = mysqli_fetch_assoc($q)) { 
                        // Sesuai dengan tabel database-mu (kolom 'jumlah')
                        $nominal = isset($row['jumlah']) ? $row['jumlah'] : (isset($row['nominal']) ? $row['nominal'] : 0);
                        $total_pengeluaran += $nominal;
                ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                    <td><span class="badge-beban"><?= htmlspecialchars($row['nama_akun'] ?? 'Beban Operasional') ?></span></td>
                    <td class="right" style="color: #dc2626; font-weight: bold;">- Rp <?= number_format($nominal,0,',','.') ?></td>
                    <td class="center aksi-btn">
                        <a href="pengeluaran_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Edit</a>
                        
                        <a href="pengeluaran_hapus.php?id=<?= $row['id'] ?>" class="btn-sm btn-hapus" onclick="return confirm('Yakin ingin menghapus data pengeluaran ini? Jurnal terkait juga harus disesuaikan.')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='5' class='center' style='padding: 20px; color: #94a3b8;'>Belum ada data pengeluaran kas.</td></tr>";
                }
                ?>
                <tr class="total-row">
                    <td colspan="3" class="right">TOTAL PENGELUARAN KAS:</td>
                    <td class="right" style="color: #dc2626;">Rp <?= number_format($total_pengeluaran,0,',','.') ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fitur Pencarian Nota/Pelanggan
$keyword = '';
$where = '';

if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where = "WHERE nama_pelanggan LIKE '%$keyword%' OR no_nota LIKE '%$keyword%'";
}

// Ambil data header penjualan
$q = mysqli_query($conn, "
    SELECT *
    FROM tb_penjualan
    $where
    ORDER BY tanggal DESC, id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Penjualan</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.btn-tambah { background: #2563eb; color: #fff; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; }
.btn-tambah:hover { background: #1d4ed8; }

.search-box { display: flex; gap: 5px; }
.search-box input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; width: 250px;}
.search-box button { background: #64748b; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 13px;}

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.badge-lunas { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.badge-piutang { background-color: #fef08a; color: #854d0e; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }

.aksi-btn { display: flex; gap: 5px; justify-content: center;}
.btn-sm { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; color: white;}
.btn-edit { background: #f59e0b; }
.btn-hapus { background: #ef4444; }
.btn-detail { background: #0ea5e9; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Daftar Transaksi Penjualan</h3>
        <p class="desc">Semua nota / invoice penjualan barang kepada pelanggan.</p>

        <div class="toolbar">
            <a href="penjualan_tambah.php" class="btn-tambah">+ Buat Penjualan Baru</a>
            
            <form method="GET" class="search-box">
                <input type="text" name="keyword" placeholder="Cari Pelanggan atau No. Nota..." value="<?= isset($_GET['keyword']) ? $_GET['keyword'] : '' ?>">
                <button type="submit">Cari</button>
                <?php if(isset($_GET['keyword']) && $_GET['keyword'] != ''): ?>
                    <a href="penjualan.php" style="background: #e2e8f0; color: #333; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 13px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tarikh</th>
                    <th>No. Nota</th>
                    <th>Nama Pelanggan</th>
                    <th class="center">Metode</th>
                    <th class="right">Total (Rp)</th>
                    <th class="center">Status</th>
                    <th class="center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($q) > 0) {
                    while($row = mysqli_fetch_assoc($q)) { 
                ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><strong><?= isset($row['no_nota']) ? $row['no_nota'] : '-' ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td class="center" style="text-transform: uppercase; font-size: 11px;"><?= $row['metode'] ?></td>
                    <td class="right" style="font-weight: bold;">Rp <?= number_format($row['total'] ?? 0, 0, ',', '.') ?></td>
                    
                    <td class="center">
                        <?php if($row['status_bayar'] == 'lunas'): ?>
                            <span class="badge-lunas">LUNAS</span>
                        <?php else: ?>
                            <span class="badge-piutang">PIUTANG</span>
                        <?php endif; ?>
                    </td>
                    
                    <td class="center aksi-btn">
                        <a href="penjualan_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Edit</a>
                        <a href="penjualan_hapus.php?id=<?= $row['id'] ?>" class="btn-sm btn-hapus" onclick="return confirm('Adakah anda pasti memadam nota penjualan ini? Stok dan Jurnal akan dibatalkan.')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='7' class='center' style='padding: 20px; color: #94a3b8;'>Belum ada transaksi penjualan setakat ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
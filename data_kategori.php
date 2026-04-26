<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Asumsi tabel kamu bernama tb_kategori dan memiliki kolom 'nama_kategori'
// Sesuaikan nama tabel jika berbeda ya!
$q = mysqli_query($conn, "SELECT * FROM tb_kategori ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Kategori Sparepart</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam untuk Master Data */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.btn-tambah { background: #2563eb; color: #fff; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; }
.btn-tambah:hover { background: #1d4ed8; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:14px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.center, th.center { text-align: center; }

.aksi-btn { display: flex; gap: 8px; justify-content: center;}
.btn-sm { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; color: white;}
.btn-edit { background: #f59e0b; }
.btn-edit:hover { background: #d97706; }
.btn-hapus { background: #ef4444; }
.btn-hapus:hover { background: #dc2626; }

/* Badge Kategori */
.badge-kat { background-color: #e0e7ff; color: #3730a3; padding: 5px 10px; border-radius: 20px; font-size: 13px; font-weight: bold; display: inline-block; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Master Data Kategori</h3>
        <p class="desc">Kelola kelompok jenis sparepart (misal: Oli, Ban, Busi, Kampas Rem) untuk mempermudah pencarian barang.</p>

        <div class="toolbar">
            <a href="kategori_tambah.php" class="btn-tambah">+ Tambah Kategori Baru</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 10%;">No</th>
                    <th style="width: 65%;">Nama Kategori</th>
                    <th class="center" style="width: 25%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if($q && mysqli_num_rows($q) > 0) {
                    while($row = mysqli_fetch_assoc($q)) { 
                ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <td>
                        <span class="badge-kat"><?= htmlspecialchars($row['nama_kategori'] ?? 'Kategori Tidak Diketahui') ?></span>
                    </td>
                    <td class="center aksi-btn">
                        <a href="kategori_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Edit</a>
                        <a href="kategori_hapus.php?id=<?= $row['id'] ?>" class="btn-sm btn-hapus" onclick="return confirm('Yakin ingin menghapus kategori ini? Pastikan tidak ada sparepart yang masih menggunakan kategori ini.')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='3' class='center' style='padding: 20px; color: #94a3b8;'>Belum ada data kategori sparepart. Silakan tambah data terlebih dahulu.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
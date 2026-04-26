<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data akun dari database (Bagan Akun)
// Asumsi tabel kamu bernama tb_akun dan memiliki kolom: kode_akun, nama_akun, kategori, saldo_normal
$q = mysqli_query($conn, "SELECT * FROM tb_akun ORDER BY kode_akun ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Akun (COA)</title>
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
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.center, th.center { text-align: center; }

/* Badge Kategori Akun */
.badge-harta { background-color: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.badge-kewajiban { background-color: #fef08a; color: #854d0e; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.badge-modal { background-color: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.badge-pendapatan { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.badge-beban { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }

/* Badge Saldo Normal */
.badge-debit { background-color: #f1f5f9; color: #475569; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 11px; border: 1px solid #cbd5e1; }
.badge-kredit { background-color: #f3f4f6; color: #374151; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 11px; border: 1px solid #d1d5db; }

.aksi-btn { display: flex; gap: 5px; justify-content: center;}
.btn-sm { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; color: white;}
.btn-edit { background: #f59e0b; }
.btn-hapus { background: #ef4444; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Daftar Akun Perkiraan (Chart of Accounts)</h3>
        <p class="desc">Bagan akun yang digunakan untuk mencatat dan mengklasifikasikan transaksi di Jurnal dan Buku Besar.</p>

        <div class="toolbar">
            <a href="akun_tambah.php" class="btn-tambah">+ Tambah Akun Baru</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Kode Akun</th>
                    <th style="width: 30%;">Nama Akun</th>
                    <th class="center" style="width: 20%;">Kategori</th>
                    <th class="center" style="width: 20%;">Saldo Normal</th>
                    <th class="center" style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($q && mysqli_num_rows($q) > 0) {
                    while($row = mysqli_fetch_assoc($q)) { 
                        
                        // Mewarnai badge kategori secara otomatis (Support Aset & Liabilitas)
                        $kategori = strtolower(isset($row['kategori']) ? $row['kategori'] : '');
                        $badge_kategori = 'badge-harta'; // Default warna biru
                        
                        if (strpos($kategori, 'aset') !== false || strpos($kategori, 'harta') !== false) $badge_kategori = 'badge-harta';
                        if (strpos($kategori, 'liabilitas') !== false || strpos($kategori, 'kewajiban') !== false) $badge_kategori = 'badge-kewajiban';
                        if (strpos($kategori, 'modal') !== false || strpos($kategori, 'ekuitas') !== false) $badge_kategori = 'badge-modal';
                        if (strpos($kategori, 'pendapatan') !== false) $badge_kategori = 'badge-pendapatan';
                        if (strpos($kategori, 'beban') !== false || strpos($kategori, 'biaya') !== false) $badge_kategori = 'badge-beban';

                        // Menentukan badge saldo normal
                        $saldo_normal = strtoupper(isset($row['saldo_normal']) ? $row['saldo_normal'] : 'DEBIT');
                        $badge_saldo = ($saldo_normal == 'KREDIT') ? 'badge-kredit' : 'badge-debit';
                ?>
                <tr>
                    <td><strong><?= isset($row['kode_akun']) ? $row['kode_akun'] : '-' ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_akun']) ?></td>
                    <td class="center"><span class="<?= $badge_kategori ?>"><?= strtoupper(isset($row['kategori']) ? $row['kategori'] : 'HARTA') ?></span></td>
                    <td class="center"><span class="<?= $badge_saldo ?>"><?= $saldo_normal ?></span></td>
                    <td class="center aksi-btn">
                        <a href="akun_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Edit</a>
                        <a href="akun_hapus.php?id=<?= $row['id'] ?>" class="btn-sm btn-hapus" onclick="return confirm('Yakin ingin menghapus akun ini? Pastikan akun ini tidak terpakai di Jurnal Umum.')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='5' class='center' style='padding: 20px; color: #94a3b8;'>Belum ada data akun. Silakan tambah data terlebih dahulu.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
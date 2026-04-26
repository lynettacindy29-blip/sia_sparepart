<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// PERBAIKAN: Mengambil data akun yang kategorinya 'Beban'
// Menggunakan kolom 'kategori' sesuai yang sudah kita perbarui tadi
$qAkun = mysqli_query($conn, "SELECT id, kode_akun, nama_akun FROM tb_akun WHERE kategori = 'Beban' ORDER BY kode_akun ASC");
?> 

<!DOCTYPE html>
<html>
<head>
<title>Tambah Pengeluaran</title> 
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam untuk Form */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:25px; width:500px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top: 0; margin-bottom: 5px; color: #333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

label { font-size:13px; margin-top:15px; display:block; font-weight: bold; color: #475569; }
input, select { width:100%; padding:10px; margin-top:6px; border:1px solid #cbd5e1; border-radius:5px; font-size: 14px; box-sizing: border-box; }
input:focus, select:focus { border-color: #2563eb; outline: none; }

button[type="submit"] { margin-top:25px; width:100%; padding:12px; border:none; border-radius:6px; background:#2563eb; color:#fff; font-weight:bold; cursor:pointer; font-size: 14px; }
button[type="submit"]:hover { background:#1d4ed8; }

.btn-back { background:#64748b; color: white; display:block; text-align:center; padding:12px; border-radius:6px; text-decoration:none; margin-top:10px; font-weight: bold; font-size: 14px; }
.btn-back:hover { background:#475569; }
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">

        <h3>Input Pengeluaran Kas</h3>
        <p class="desc">Catat biaya operasional toko yang keluar hari ini.</p>

        <form method="POST" action="pengeluaran_simpan.php">

            <label>Tanggal Pengeluaran</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

            <label>Keterangan / Keperluan</label>
            <input type="text" name="keterangan" placeholder="Contoh: Bayar Listrik Bulan Maret, Beli Galon..." required autofocus>

            <label>Kategori Biaya (Akun Beban)</label>
            <select name="id_akun" required>
                <option value="">-- Pilih Akun Beban --</option>
                <?php 
                if ($qAkun && mysqli_num_rows($qAkun) > 0) {
                    while($a = mysqli_fetch_assoc($qAkun)) { 
                ?>
                    <option value="<?= $a['id'] ?>"><?= $a['kode_akun'] ?> - <?= htmlspecialchars($a['nama_akun']) ?></option>
                <?php 
                    }
                } else {
                    echo "<option value=''>Belum ada akun Beban di Data Akun</option>";
                }
                ?>
            </select>

            <label>Nominal Pengeluaran (Rp)</label>
            <input type="number" name="nominal" placeholder="Contoh: 150000" min="1" required>

            <button type="submit">Simpan Pengeluaran</button>
            <a href="pengeluaran.php" class="btn-back">Batal & Kembali</a>

        </form>

    </div>
</div>

</body>
</html>
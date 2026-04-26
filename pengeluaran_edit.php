<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: pengeluaran.php");
    exit;
}

$id = intval($_GET['id']);
$qData = mysqli_query($conn, "SELECT * FROM tb_pengeluaran WHERE id = '$id'");
$data = mysqli_fetch_assoc($qData);

if (!$data) {
    die("Data pengeluaran tidak ditemukan.");
}

// Ambil daftar akun Beban untuk dropdown
$qAkun = mysqli_query($conn, "SELECT id, kode_akun, nama_akun FROM tb_akun WHERE kategori = 'Beban' ORDER BY kode_akun ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Pengeluaran</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS Seragam untuk Form */
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:25px; width:500px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top: 0; margin-bottom: 5px; color: #333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

label { font-size:13px; margin-top:15px; display:block; font-weight: bold; color: #475569; }
input, select { width:100%; padding:10px; margin-top:6px; border:1px solid #cbd5e1; border-radius:5px; font-size: 14px; box-sizing: border-box; }
input:focus, select:focus { border-color: #f59e0b; outline: none; }

button[type="submit"] { margin-top:25px; width:100%; padding:12px; border:none; border-radius:6px; background:#f59e0b; color:#fff; font-weight:bold; cursor:pointer; font-size: 14px; }
button[type="submit"]:hover { background:#d97706; }

.btn-back { background:#64748b; color: white; display:block; text-align:center; padding:12px; border-radius:6px; text-decoration:none; margin-top:10px; font-weight: bold; font-size: 14px; }
.btn-back:hover { background:#475569; }
</style>
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">

        <h3>Edit Pengeluaran Kas</h3>
        <p class="desc">Koreksi data pengeluaran. Jurnal umum akan disesuaikan secara otomatis.</p>

        <form method="POST" action="pengeluaran_edit_proses.php">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            <input type="hidden" name="tanggal_lama" value="<?= $data['tanggal'] ?>">
            <input type="hidden" name="keterangan_lama" value="<?= htmlspecialchars($data['keterangan']) ?>">

            <label>Tanggal Pengeluaran</label>
            <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>" required>

            <label>Keterangan / Keperluan</label>
            <input type="text" name="keterangan" value="<?= htmlspecialchars($data['keterangan']) ?>" required>

            <label>Kategori Biaya (Akun Beban)</label>
            <select name="id_akun" required>
                <?php 
                if ($qAkun && mysqli_num_rows($qAkun) > 0) {
                    while($a = mysqli_fetch_assoc($qAkun)) { 
                        $selected = ($a['id'] == $data['id_akun']) ? 'selected' : '';
                ?>
                    <option value="<?= $a['id'] ?>" <?= $selected ?>><?= $a['kode_akun'] ?> - <?= htmlspecialchars($a['nama_akun']) ?></option>
                <?php 
                    }
                }
                ?>
            </select>

            <label>Nominal Pengeluaran (Rp)</label>
            <input type="number" name="jumlah" value="<?= isset($data['jumlah']) ? $data['jumlah'] : 0 ?>" min="1" required>

            <button type="submit">Update & Sesuaikan Jurnal</button>
            <a href="pengeluaran.php" class="btn-back">Batal & Kembali</a>

        </form>

    </div>
</div>

</body>
</html>
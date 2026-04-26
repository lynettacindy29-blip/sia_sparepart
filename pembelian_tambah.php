<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil senarai supplier dari database
$qSupplier = mysqli_query($conn, "SELECT * FROM tb_supplier ORDER BY nama_supplier ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
<style>
/* CSS seragam seperti halaman lain */
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

        <h3>Buat Transaksi Pembelian</h3>

        <form method="POST" action="pembelian_proses.php">

            <label>Tanggal Pembelian</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

            <label>Pilih Supplier</label>
            <select name="id_supplier" required>
                <option value="">-- Pilih Supplier --</option>
                <?php while($s = mysqli_fetch_assoc($qSupplier)) { ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['nama_supplier']) ?>
                    </option>
                <?php } ?>
            </select>

            <label>Metode Pembayaran</label>
            <select name="metode" required>
                <option value="tunai">Tunai (Cash)</option>
                <option value="kredit">Kredit (Hutang)</option>
            </select>

            <button type="submit">Seterusnya: Tambah Barang</button>
            <a href="pembelian.php" class="btn-back">Kembali</a>

        </form>

    </div>
</div>

</body>
</html>
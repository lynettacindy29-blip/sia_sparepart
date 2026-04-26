<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

// Mengambil data supplier berdasarkan ID
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_supplier WHERE id=$id"));

if (isset($_POST['update'])) {

    $nama_supplier    = $_POST['nama_supplier'];
    $alamat           = $_POST['alamat'];
    $telp             = $_POST['telp'];
    $saldo_awal_utang = $_POST['saldo_awal_utang']; // Menangkap data saldo awal utang

    // Memperbarui query UPDATE untuk memasukkan saldo_awal_utang
    mysqli_query($conn, "UPDATE tb_supplier SET 
        nama_supplier='$nama_supplier',
        alamat='$alamat',
        telp='$telp',
        saldo_awal_utang='$saldo_awal_utang'
        WHERE id=$id");

    header("Location: supplier.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Supplier</title>
<link rel="stylesheet" href="inc/style.css">
<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.card { background:#fff; padding:25px; border-radius:8px; width:500px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
input, textarea {
    width: 95%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
input:focus, textarea:focus {
    outline: none;
    border-color: #3b82f6;
}
label { font-weight: bold; color: #333; font-size: 14px; }
.help-text { font-size: 12px; color: #64748b; margin-top: -12px; margin-bottom: 15px; display: block; }
button {
    padding: 10px 20px;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
}
button:hover { background: #218838; }
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3 style="margin-top: 0; color: #333;">Edit Supplier</h3>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Silakan perbarui informasi pemasok dan rincian saldo awal utang di bawah ini.</p>

        <form method="POST">
            <label>Nama Supplier</label>
            <input type="text" name="nama_supplier" value="<?= htmlspecialchars($data['nama_supplier']) ?>" required>

            <label>Alamat</label>
            <textarea name="alamat" rows="3" required><?= htmlspecialchars($data['alamat']) ?></textarea>

            <label>No. Telepon / WhatsApp</label>
            <input type="text" name="telp" value="<?= htmlspecialchars($data['telp']) ?>" required>

            <label>Saldo Awal Utang (Rp)</label>
            <input type="number" name="saldo_awal_utang" value="<?= isset($data['saldo_awal_utang']) ? $data['saldo_awal_utang'] : '0' ?>" min="0" required>
            <span class="help-text">Masukkan rincian sisa utang dari bulan sebelumnya. Tulis angka saja tanpa titik/koma (Contoh: 10000000).</span>

            <button type="submit" name="update">Simpan Perubahan</button>
        </form>
    </div>
</div>

</body>
</html>
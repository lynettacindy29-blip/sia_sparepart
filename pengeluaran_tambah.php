<?php
session_start();
include "config/db.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {

    $tanggal     = $_POST['tanggal'];
    $keterangan  = $_POST['keterangan'];
    $id_akun     = $_POST['id_akun']; // akun beban
    $jumlah      = $_POST['jumlah'];

    // =============================
    // 1️⃣ Simpan ke tabel pengeluaran
    // =============================
    mysqli_query($conn, "INSERT INTO tb_pengeluaran 
        (tanggal, keterangan, jumlah) 
        VALUES ('$tanggal','$keterangan','$jumlah')");

    // =============================
    // 2️⃣ Ambil akun Kas
    // =============================
    $kas = mysqli_query($conn, "SELECT id FROM tb_akun WHERE nama_akun='Kas' LIMIT 1");
    $data_kas = mysqli_fetch_assoc($kas);
    $akun_kas = $data_kas['id'];

    // =============================
    // 3️⃣ Insert Jurnal (Debit Beban)
    // =============================
    mysqli_query($conn, "INSERT INTO tb_jurnal 
        (tanggal, keterangan, id_akun, debit, kredit) 
        VALUES 
        ('$tanggal','$keterangan',$id_akun,$jumlah,0)");

    // =============================
    // 4️⃣ Insert Jurnal (Kredit Kas)
    // =============================
    mysqli_query($conn, "INSERT INTO tb_jurnal 
        (tanggal, keterangan, id_akun, debit, kredit) 
        VALUES 
        ('$tanggal','$keterangan',$akun_kas,0,$jumlah)");

    header("Location: pengeluaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pengeluaran</title>
    <link rel="stylesheet" href="inc/style.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 30px;
        }
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            width: 500px;
        }
        .card h3 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn {
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Tambah Pengeluaran</h3>

        <form method="POST">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan" required>
            </div>

            <div class="form-group">
                <label>Pilih Akun Beban</label>
                <select name="id_akun" required>
                    <option value="">-- Pilih Akun Beban --</option>
                    <?php
                    $akun = mysqli_query($conn, "SELECT * FROM tb_akun WHERE tipe='beban'");
                    while ($row = mysqli_fetch_assoc($akun)) {
                        echo "<option value='".$row['id']."'>".$row['kode_akun']." - ".$row['nama_akun']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah</label>
                <input type="number" name="jumlah" required>
            </div>

            <button type="submit" name="simpan" class="btn">Simpan</button>
        </form>
    </div>
</div>

</body>
</html>
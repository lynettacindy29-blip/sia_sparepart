<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- LOGIKA PENANGKAPAN DATA ---
$id_pembelian = isset($_GET['id']) ? $_GET['id'] : '';
$id_supplier_awal = isset($_GET['id_supplier']) ? $_GET['id_supplier'] : '';

$nama_tujuan = "";
$sisa_hutang = 0;
$mode = "";

if ($id_pembelian != '') {
    $q = mysqli_query($conn, "SELECT p.*, s.nama_supplier FROM tb_pembelian p JOIN tb_supplier s ON p.id_supplier = s.id WHERE p.id = '$id_pembelian'");
    $d = mysqli_fetch_assoc($q);
    $nama_tujuan = $d['nama_supplier'] . " (Faktur: " . $d['no_faktur'] . ")";
    $sisa_hutang = $d['sisa_hutang'];
    $mode = "faktur";
} elseif ($id_supplier_awal != '') {
    $q = mysqli_query($conn, "SELECT * FROM tb_supplier WHERE id = '$id_supplier_awal'");
    $d = mysqli_fetch_assoc($q);
    $nama_tujuan = $d['nama_supplier'] . " (Saldo Awal)";
    $sisa_hutang = $d['saldo_awal_utang'];
    $mode = "saldo_awal";
} else {
    header("Location: hutang_pembelian.php");
    exit;
}

// --- PROSES SIMPAN PEMBAYARAN ---
if (isset($_POST['proses_bayar'])) {
    $tgl = $_POST['tgl_bayar'];
    $nominal = $_POST['nominal'];
    $keterangan_pembayaran = "Pelunasan Utang ke " . $nama_tujuan;
    $no_bukti = "PH-" . date('Ymd') . "-" . rand(10, 99);

    // 1. PROSES UPLOAD BUKTI
    $nama_file_bukti = NULL;
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $file_name = $_FILES['bukti_bayar']['name'];
        $file_tmp = $_FILES['bukti_bayar']['tmp_name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $nama_file_baru = "hutang_" . time() . "_" . rand(10,99) . "." . $ext;
        $folder_tujuan = "assets/bukti_bayar/" . $nama_file_baru;
        
        if (move_uploaded_file($file_tmp, $folder_tujuan)) {
            $nama_file_bukti = $nama_file_baru;
        }
    }

    // 2. UPDATE DATA HUTANG
    if ($mode == "faktur") {
        mysqli_query($conn, "UPDATE tb_pembelian SET sisa_hutang = sisa_hutang - $nominal, status = IF(sisa_hutang <= 0, 'lunas', 'belum_lunas') WHERE id = '$id_pembelian'");
        $val_id_beli = $id_pembelian;
    } else {
        mysqli_query($conn, "UPDATE tb_supplier SET saldo_awal_utang = saldo_awal_utang - $nominal WHERE id = '$id_supplier_awal'");
        $val_id_beli = 0; // Atau sesuaikan jika ada ID khusus untuk saldo awal
    }

    // 3. INSERT KE tb_pembayaran_hutang (Sesuai Kolom Kamu)
    mysqli_query($conn, "INSERT INTO tb_pembayaran_hutang (id_pembelian, tanggal, jumlah_bayar, keterangan, bukti_bayar) 
                        VALUES ('$val_id_beli', '$tgl', '$nominal', '$keterangan_pembayaran', '$nama_file_bukti')");

    // 4. JURNAL
    mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, id_akun, keterangan, debit, kredit) VALUES ('$no_bukti', '$tgl', '5', '$keterangan_pembayaran', '$nominal', '0')");
    mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, id_akun, keterangan, debit, kredit) VALUES ('$no_bukti', '$tgl', '1', '$keterangan_pembayaran', '0', '$nominal')");

    echo "<script>alert('Pembayaran Berhasil!'); window.location='hutang_pembelian.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Proses Pembayaran Hutang</title>
    <link rel="stylesheet" href="inc/style.css">
    <style>
        .content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
        .card { background:#fff; border-radius:8px; padding:20px; max-width: 500px; margin: auto; }
        .info { background: #f1f5f9; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #10b981; color: white; border: none; padding: 10px; width: 100%; border-radius: 4px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="content">
        <div class="card">
            <h3>Konfirmasi Pembayaran</h3>
            <div class="info">
                Bayar Kepada: <strong><?= $nama_tujuan ?></strong><br>
                Sisa Hutang: <strong style="color:red;">Rp <?= number_format($sisa_hutang,0,',','.') ?></strong>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Nominal Pembayaran (Rp)</label>
                    <input type="number" name="nominal" max="<?= $sisa_hutang ?>" placeholder="Masukkan angka..." required>
                </div>
                <div class="form-group">
                    <label>Upload Bukti Bayar (Opsional)</label>
                    <input type="file" name="bukti_bayar" accept="image/*,application/pdf">
                </div>
                <button type="submit" name="proses_bayar" class="btn-submit">Simpan Pembayaran</button>
            </form>
        </div>
    </div>
</body>
</html>
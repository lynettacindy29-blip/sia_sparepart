<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tangkap data baru
    $id         = intval($_POST['id']);
    $tanggal    = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $id_akun    = intval($_POST['id_akun']); 
    $jumlah     = floatval($_POST['jumlah']);

    // Tangkap data lama untuk mencari jejak di Jurnal Umum
    $tanggal_lama    = mysqli_real_escape_string($conn, $_POST['tanggal_lama']);
    $keterangan_lama = mysqli_real_escape_string($conn, $_POST['keterangan_lama']);
    $ket_jurnal_lama = "Biaya Operasional: " . $keterangan_lama;

    // 1. Update data di tabel tb_pengeluaran
    $query_update = "UPDATE tb_pengeluaran SET 
                        tanggal = '$tanggal', 
                        keterangan = '$keterangan', 
                        id_akun = '$id_akun', 
                        jumlah = '$jumlah' 
                     WHERE id = '$id'";
    
    if (mysqli_query($conn, $query_update)) {
        
        // 2. HAPUS Jurnal Lama yang berkaitan dengan pengeluaran ini
        mysqli_query($conn, "DELETE FROM tb_jurnal WHERE tanggal = '$tanggal_lama' AND keterangan = '$ket_jurnal_lama'");

        // 3. BUAT Jurnal Baru dengan data yang sudah di-update
        $qKas = mysqli_query($conn, "SELECT id FROM tb_akun WHERE nama_akun LIKE '%Kas%' LIMIT 1");
        $dataKas = mysqli_fetch_assoc($qKas);
        
        if ($dataKas) {
            $id_akun_kas = $dataKas['id'];
            $no_bukti = 'BKK-' . date('Ymd', strtotime($tanggal)) . '-' . rand(10, 99);
            $ket_jurnal_baru = "Biaya Operasional: " . $keterangan;

            // Jurnal DEBIT: Beban bertambah (dengan nominal baru)
            mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                                 VALUES ('$no_bukti', '$tanggal', '$ket_jurnal_baru', '$id_akun', '$jumlah', 0)");

            // Jurnal KREDIT: Kas berkurang (dengan nominal baru)
            mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                                 VALUES ('$no_bukti', '$tanggal', '$ket_jurnal_baru', '$id_akun_kas', 0, '$jumlah')");
        }

        // Kembali ke halaman data pengeluaran
        header("Location: pengeluaran.php?status=edit_sukses");
        exit;

    } else {
        die("Error Database saat mengupdate: " . mysqli_error($conn));
    }
} else {
    header("Location: pengeluaran.php");
    exit;
}
?>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tangkap data dari form pengeluaran_tambah.php
    $tanggal    = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $id_akun    = intval($_POST['id_akun']); 
    
    // Tangkap input nominal dari form, simpan ke variabel $jumlah
    $jumlah     = floatval($_POST['nominal']);

    /* ==========================================================
       PERBAIKAN: Mengganti kolom 'nominal' menjadi 'jumlah' 
       agar sesuai dengan struktur tabel database-mu
    ========================================================== */
    $query_simpan = "INSERT INTO tb_pengeluaran (tanggal, keterangan, id_akun, jumlah) 
                     VALUES ('$tanggal', '$keterangan', '$id_akun', '$jumlah')";
    
    if (mysqli_query($conn, $query_simpan)) {
        
        /* ==========================================================
           OTOMATISASI JURNAL UMUM (Beban di Debit, Kas di Kredit)
        ========================================================== */
        // Cari ID Akun Kas
        $qKas = mysqli_query($conn, "SELECT id FROM tb_akun WHERE nama_akun LIKE '%Kas%' LIMIT 1");
        $dataKas = mysqli_fetch_assoc($qKas);
        
        if ($dataKas) {
            $id_akun_kas = $dataKas['id'];
            
            // Buat Nomor Bukti Kas Keluar (BKK)
            $no_bukti = 'BKK-' . date('Ymd', strtotime($tanggal)) . '-' . rand(10, 99);
            $ket_jurnal = "Biaya Operasional: " . $keterangan;

            // Jurnal DEBIT: Beban bertambah
            mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                                 VALUES ('$no_bukti', '$tanggal', '$ket_jurnal', '$id_akun', '$jumlah', 0)");

            // Jurnal KREDIT: Kas berkurang
            mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                                 VALUES ('$no_bukti', '$tanggal', '$ket_jurnal', '$id_akun_kas', 0, '$jumlah')");
        }

        // Sukses, kembali ke halaman data pengeluaran
        header("Location: pengeluaran.php?status=sukses");
        exit;

    } else {
        // Jika masih error, sistem akan memberi tahu kita masalahnya
        die("Error Database: " . mysqli_error($conn) . "<br><br><b>Info:</b> Jika masih muncul error 'Unknown column', tolong fotokan atau ketik daftar kolom dari tabel <b>tb_pengeluaran</b> di phpMyAdmin kamu ya!");
    }
} else {
    header("Location: pengeluaran.php");
    exit;
}
?>
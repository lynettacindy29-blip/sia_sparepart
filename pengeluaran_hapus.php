<?php
session_start();
include "config/db.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_pengeluaran = intval($_GET['id']);

    // 1. Ambil data pengeluaran SEBELUM dihapus (untuk mencari jejaknya di Jurnal Umum)
    $qData = mysqli_query($conn, "SELECT * FROM tb_pengeluaran WHERE id = '$id_pengeluaran'");
    $data = mysqli_fetch_assoc($qData);

    if ($data) {
        $tanggal = $data['tanggal'];
        $keterangan = $data['keterangan'];
        $jumlah = isset($data['jumlah']) ? $data['jumlah'] : 0;

        // 2. Hapus data dari tabel tb_pengeluaran
        $query_hapus = "DELETE FROM tb_pengeluaran WHERE id = '$id_pengeluaran'";
        
        if (mysqli_query($conn, $query_hapus)) {
            
            /* ==========================================================
               3. PEMBERSIHAN JURNAL UMUM (SANGAT PENTING!)
               Kita harus menghapus jurnal Kas dan Beban yang terlanjur 
               dibuat agar Buku Besar dan Neraca Saldo tetap akurat.
            ========================================================== */
            
            // Format keterangan jurnal yang kita buat di file simpan sebelumnya
            $ket_jurnal = "Biaya Operasional: " . $keterangan;
            
            // Hapus semua baris di jurnal yang cocok dengan pengeluaran ini
            mysqli_query($conn, "DELETE FROM tb_jurnal WHERE tanggal = '$tanggal' AND keterangan = '$ket_jurnal'");

            // Kembali ke halaman pengeluaran dengan status sukses
            header("Location: pengeluaran.php?status=hapus_sukses");
            exit;
        } else {
            die("Error Database: Gagal menghapus pengeluaran. " . mysqli_error($conn));
        }
    }
}

// Jika tidak ada ID atau data tidak ditemukan, tendang balik ke halaman pengeluaran
header("Location: pengeluaran.php");
exit;
?>
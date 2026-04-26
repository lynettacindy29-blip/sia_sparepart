<?php
session_start();
include "config/db.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pastikan form dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form dan bersihkan dari karakter berbahaya (Anti SQL Injection)
    $kode_akun    = mysqli_real_escape_string($conn, $_POST['kode_akun']);
    $nama_akun    = mysqli_real_escape_string($conn, $_POST['nama_akun']);
    $kategori     = mysqli_real_escape_string($conn, $_POST['kategori']); // Aset, Liabilitas, dll
    $saldo_normal = mysqli_real_escape_string($conn, $_POST['saldo_normal']); // Debit atau Kredit

    // Validasi sederhana: pastikan tidak ada kolom yang kosong
    if (empty($kode_akun) || empty($nama_akun) || empty($kategori) || empty($saldo_normal)) {
        die("Error: Semua kolom wajib diisi! <a href='akun_tambah.php'>Kembali</a>");
    }

    // Query untuk menyimpan data akun baru ke dalam tabel tb_akun
    $query = "INSERT INTO tb_akun (kode_akun, nama_akun, kategori, saldo_normal) 
              VALUES ('$kode_akun', '$nama_akun', '$kategori', '$saldo_normal')";

    // Eksekusi query
    if (mysqli_query($conn, $query)) {
        // Jika berhasil, arahkan kembali ke halaman daftar akun dengan pesan sukses
        header("Location: akun.php?status=tambah_sukses");
        exit;
    } else {
        // Jika gagal (misal kode akun duplikat atau kolom tidak ditemukan), tampilkan pesan error
        die("Gagal menyimpan data akun. Error database: " . mysqli_error($conn) . " <br><br><a href='akun_tambah.php'>Kembali</a>");
    }
} else {
    // Jika ada yang mencoba akses file ini langsung dari URL tanpa isi form
    header("Location: akun.php");
    exit;
}
?>
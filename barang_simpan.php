<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang  = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $id_kategori  = $_POST['id_kategori'];
    $harga_beli   = $_POST['harga_beli'];   // Ini HPP dasar yang diinput
    $harga_jual   = $_POST['harga_jual'];
    $stok         = $_POST['stok'];         // Ini stok fisik awal
    $stok_minimal = $_POST['stok_minimal'];

    // Validasi ketat: HPP tidak boleh kosong, harus diisi angka
    if ($nama_barang == '' || $id_kategori == '' || $harga_beli == '' || $harga_jual == '') {
        die("Data tidak lengkap! Pastikan Nama, Kategori, HPP, dan Harga Jual terisi.");
    }

    // Pastikan nilai default jika kosong
    if ($stok == '' || $stok < 0) {
        $stok = 0;
    }
    
    // Perbaikan Penting: Memastikan HPP awal mengikuti harga beli dasar (Bahkan jika stoknya 0)
    if ($harga_beli == '' || $harga_beli < 0) {
        $harga_beli = 0; 
    }

    if ($stok_minimal == '' || $stok_minimal < 0) {
        $stok_minimal = 5;
    }

    $kode_barang = 'BRG' . time();

    // ====================================================================
    // KLONING NILAI SETUP KE DALAM "BRANKAS" (stok_awal & hpp_awal)
    // ====================================================================
    $stok_awal = $stok;
    
    // HPP awal sekarang murni mengambil dari Harga Pokok Dasar (Tidak peduli stoknya 0 atau tidak)
    $hpp_awal  = $harga_beli; 

    // Simpan ke tb_barang
    $insert = mysqli_query($conn, "
        INSERT INTO tb_barang
        (kode_barang, nama_barang, id_kategori, harga_beli, harga_jual, stok, stok_minimal, stok_awal, hpp_awal)
        VALUES
        ('$kode_barang', '$nama_barang', '$id_kategori', '$harga_beli', '$harga_jual', '$stok', '$stok_minimal', '$stok_awal', '$hpp_awal')
    ");

    if ($insert) {
        $id_barang_baru = mysqli_insert_id($conn);
        $tgl_sekarang = date('Y-m-d');

        // Jika ada stok awal, otomatis buatkan riwayat di Kartu Stok
        if ($stok > 0) {
            mysqli_query($conn, "
                INSERT INTO tb_kartu_stok 
                (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, harga_pokok, keterangan)
                VALUES 
                ('$id_barang_baru', '$tgl_sekarang', 'penyesuaian', 'SAWAL', '$stok', 0, '$stok', '$hpp_awal', 'Setup Saldo Awal Fisik')
            ");
        }

        header("Location: data_barang.php?status=sukses");
        exit;
    } else {
        die("Gagal menyimpan data: " . mysqli_error($conn));
    }
}
?>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id            = intval($_POST['id']);
    $nama_barang   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $id_kategori   = $_POST['id_kategori'];
    $harga_beli    = $_POST['harga_beli'];
    $harga_jual    = $_POST['harga_jual'];
    $stok_baru     = $_POST['stok'];
    $stok_minimal  = $_POST['stok_minimal'];

    // Validasi dasar
    if ($nama_barang == '' || $id_kategori == '' || $harga_beli == '' || $harga_jual == '') {
        die("Data tidak lengkap! Pastikan Nama, Kategori, Harga Beli, dan Harga Jual terisi.");
    }

    // Pastikan nilai tidak kosong atau minus
    if ($stok_baru == '' || $stok_baru < 0) {
        $stok_baru = 0;
    }
    if ($harga_beli == '' || $harga_beli < 0) {
        $harga_beli = 0;
    }
    if ($stok_minimal == '' || $stok_minimal < 0) {
        $stok_minimal = 5;
    }

    // Ambil data lama sebelum di-update untuk pengecekan
    $qDataLama = mysqli_query($conn, "SELECT stok, stok_awal, hpp_awal FROM tb_barang WHERE id = $id");
    $rowLama = mysqli_fetch_assoc($qDataLama);
    $stok_lama = $rowLama['stok'];

    // Cek apakah barang ini sudah pernah ditransaksikan?
    $qCekTransaksi = mysqli_query($conn, "SELECT COUNT(id) as jml_transaksi FROM tb_kartu_stok WHERE id_barang = $id");
    $jml_transaksi = mysqli_fetch_assoc($qCekTransaksi)['jml_transaksi'];
    $sudah_transaksi = ($jml_transaksi > 1) ? true : false; 

    // =========================================================================
    // PERBAIKAN UTAMA: LOGIKA UPDATE HPP AWAL & STOK AWAL
    // =========================================================================
    // Secara default, kita hanya update data master yang biasa (Nama, Kategori, Harga Jual, Stok Minimal)
    $query_update = "
        UPDATE tb_barang SET
            nama_barang  = '$nama_barang',
            id_kategori  = '$id_kategori',
            harga_jual   = '$harga_jual',
            stok_minimal = '$stok_minimal'
    ";

    // JIKA BARANG BELUM PERNAH DITRANSAKSIKAN, maka perbarui juga Stok, Harga Beli, STOK AWAL, dan HPP AWAL!
    if (!$sudah_transaksi) {
        $query_update .= ", 
            stok       = '$stok_baru',
            harga_beli = '$harga_beli',
            stok_awal  = '$stok_baru',
            hpp_awal   = '$harga_beli' 
        ";
    }

    // Tutup query
    $query_update .= " WHERE id = $id";

    // Eksekusi Update ke Database
    $update = mysqli_query($conn, $query_update);

    if ($update) {
        
        // =========================================================================
        // FITUR KONTROL PERSEDIAAN: UPDATE KARTU STOK SAAT SETUP AWAL DIUBAH
        // =========================================================================
        if (!$sudah_transaksi && ($stok_baru != $rowLama['stok_awal'] || $harga_beli != $rowLama['hpp_awal'])) {
            
            $tgl_sekarang = date('Y-m-d');

            // 1. Karena ini masih "Setup", kita hapus dulu riwayat "Setup Saldo Awal" yang lama (jika ada)
            mysqli_query($conn, "DELETE FROM tb_kartu_stok WHERE id_barang = $id AND no_referensi = 'SAWAL'");

            // 2. Jika stok barunya lebih dari 0, kita buat ulang riwayat Setup Saldo Awal-nya yang baru
            if ($stok_baru > 0) {
                mysqli_query($conn, "
                    INSERT INTO tb_kartu_stok 
                    (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, harga_pokok, keterangan)
                    VALUES 
                    ('$id', '$tgl_sekarang', 'penyesuaian', 'SAWAL', '$stok_baru', 0, '$stok_baru', '$harga_beli', 'Setup Saldo Awal Fisik (Revisi)')
                ");
            }
        }

        header("Location: data_barang.php?status=update_sukses");
        exit;
    } else {
        die("Gagal update: " . mysqli_error($conn));
    }
}
?>
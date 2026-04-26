<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: penjualan.php");
    exit;
}

$id = intval($_GET['id']);
$tanggal_sekarang = date('Y-m-d');

// 1. Ambil data Header untuk mendapatkan no_nota
$qHeader = mysqli_query($conn, "SELECT no_nota, tanggal FROM tb_penjualan WHERE id = $id");
$header = mysqli_fetch_assoc($qHeader);

if ($header) {
    // Ambil nomor nota untuk jejak audit (Jika kosong, pakai ID)
    $no_nota = isset($header['no_nota']) && $header['no_nota'] != '' ? $header['no_nota'] : 'PJ-'.$id;

    // 2. Ambil semua detail barang yang dijual di nota ini
    $qDetail = mysqli_query($conn, "SELECT id_barang, qty FROM tb_detail_penjualan WHERE id_penjualan = $id");

    while ($row = mysqli_fetch_assoc($qDetail)) {
        $id_barang = $row['id_barang'];
        $qty_keluar = $row['qty'];

        // Ambil stok sekarang
        $qStok = mysqli_query($conn, "SELECT stok FROM tb_barang WHERE id = $id_barang");
        if ($dataStok = mysqli_fetch_assoc($qStok)) {
            $stok_sekarang = $dataStok['stok'];

            // Kembalikan stok (karena transaksi dibatalkan)
            $stok_baru = $stok_sekarang + $qty_keluar;

            // Update tb_barang
            mysqli_query($conn, "UPDATE tb_barang SET stok = $stok_baru WHERE id = $id_barang");

            // Catat ke Kartu Stok (Jejak Audit pembatalan)
            mysqli_query($conn, "
                INSERT INTO tb_kartu_stok 
                (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, keterangan)
                VALUES 
                ('$id_barang', '$tanggal_sekarang', 'penyesuaian', 'HAPUS-$no_nota', '$qty_keluar', 0, '$stok_baru', 'Pembatalan Transaksi Penjualan')
            ");
        }
    }

    // 3. Hapus Data dari Tabel Terkait
    // Hapus Jurnal yang berkaitan dengan transaksi ini
    mysqli_query($conn, "DELETE FROM tb_jurnal WHERE keterangan LIKE '%$no_nota%'");
    
    // Hapus Data Pembayaran Piutang (Jika sebelumnya ada pelanggan yang mencicil)
    mysqli_query($conn, "DELETE FROM tb_pembayaran_piutang WHERE id_penjualan = $id");

    // Hapus Detail Keranjang Penjualan
    mysqli_query($conn, "DELETE FROM tb_detail_penjualan WHERE id_penjualan = $id");

    // Terakhir, Hapus Header Penjualan
    mysqli_query($conn, "DELETE FROM tb_penjualan WHERE id = $id");
}

// Redirect kembali ke halaman data penjualan
header("Location: penjualan.php?status=hapus_sukses");
exit;
?>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id             = intval($_POST['id']);
    $tanggal        = $_POST['tanggal'];
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $metode         = $_POST['metode'];

    $id_barang_arr  = isset($_POST['id_barang']) ? $_POST['id_barang'] : [];
    $harga_arr      = isset($_POST['harga']) ? $_POST['harga'] : [];
    $qty_arr        = isset($_POST['qty']) ? $_POST['qty'] : [];

    // Ambil data header lama untuk rujukan
    $qHeader = mysqli_query($conn, "SELECT * FROM tb_penjualan WHERE id = $id");
    $headerLama = mysqli_fetch_assoc($qHeader);
    
    // Pastikan ada no_nota
    $no_nota = isset($headerLama['no_nota']) && $headerLama['no_nota'] != '' ? $headerLama['no_nota'] : 'PJ-' . date('Ymd', strtotime($tanggal)) . '-' . sprintf("%04d", $id);

    /* ==========================================================
       1. KEMBALIKAN (REVERT) STOK LAMA & CATAT KE KARTU STOK
    ========================================================== */
    $qOld = mysqli_query($conn, "SELECT * FROM tb_detail_penjualan WHERE id_penjualan = '$id'");

    while ($old = mysqli_fetch_assoc($qOld)) {
        $id_brg_lama = $old['id_barang'];
        $qty_lama = $old['qty'];

        // Ambil stok semasa
        $qStok = mysqli_query($conn, "SELECT stok FROM tb_barang WHERE id = $id_brg_lama");
        $rowStok = mysqli_fetch_assoc($qStok);
        $stok_sekarang = $rowStok['stok'];
        
        // Tambah semula stok (kerana penjualan dibatalkan sementara)
        $stok_revert = $stok_sekarang + $qty_lama;

        // Update ke tb_barang
        mysqli_query($conn, "UPDATE tb_barang SET stok = $stok_revert WHERE id = $id_brg_lama");

        // Catat pembalikan dalam Kartu Stok
        mysqli_query($conn, "
            INSERT INTO tb_kartu_stok 
            (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, keterangan)
            VALUES 
            ('$id_brg_lama', '$tanggal', 'penyesuaian', 'REVERT-$no_nota', '$qty_lama', 0, '$stok_revert', 'Revisi Penjualan (Tarik Balik)')
        ");
    }

    /* ==========================================================
       2. HAPUS DETAIL LAMA
    ========================================================== */
    mysqli_query($conn, "DELETE FROM tb_detail_penjualan WHERE id_penjualan = '$id'");

    /* ==========================================================
       3. INSERT DETAIL BARU + TOLAK STOK BARU
    ========================================================== */
    $total_baru = 0;

    for ($i = 0; $i < count($id_barang_arr); $i++) {

        $barang = intval($id_barang_arr[$i]);
        $harga  = floatval($harga_arr[$i]);
        $qty    = intval($qty_arr[$i]);
        
        $subtotal = $harga * $qty;
        $total_baru += $subtotal;

        // Insert Detail Baru
        mysqli_query($conn, "
            INSERT INTO tb_detail_penjualan (id_penjualan, id_barang, harga, qty, subtotal)
            VALUES ('$id', '$barang', '$harga', '$qty', '$subtotal')
        ");

        // Ambil stok selepas direvert
        $qStok2 = mysqli_query($conn, "SELECT stok FROM tb_barang WHERE id = $barang");
        $rowStok2 = mysqli_fetch_assoc($qStok2);
        $stok_sekarang2 = $rowStok2['stok'];

        // Tolak stok baru
        $stok_akhir = $stok_sekarang2 - $qty;

        mysqli_query($conn, "UPDATE tb_barang SET stok = $stok_akhir WHERE id = $barang");

        // Catat penjualan baru ke Kartu Stok
        mysqli_query($conn, "
            INSERT INTO tb_kartu_stok 
            (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, keterangan)
            VALUES 
            ('$barang', '$tanggal', 'penjualan', '$no_nota', 0, '$qty', '$stok_akhir', 'Revisi Penjualan (Stok Keluar)')
        ");
    }

    /* ==========================================================
       4. KIRA SEMULA SISA PIUTANG & STATUS
    ========================================================== */
    if ($metode == 'tunai') {
        $status_bayar = 'lunas';
        $sisa_piutang = 0;
    } else {
        $status_bayar = 'belum';
        $sisa_piutang = $total_baru; 
    }

    // Update Header Penjualan
    mysqli_query($conn, "
        UPDATE tb_penjualan
        SET tanggal='$tanggal',
            nama_pelanggan='$nama_pelanggan',
            metode='$metode',
            no_nota='$no_nota',
            total='$total_baru',
            status_bayar='$status_bayar',
            sisa_piutang='$sisa_piutang'
        WHERE id='$id'
    ");

    /* ==========================================================
       5. KEMASKINI JURNAL (Hapus yang lama, buat yang baru)
    ========================================================== */
    // Cari no_bukti lama
    mysqli_query($conn, "DELETE FROM tb_jurnal WHERE keterangan LIKE '%$no_nota%'");

    // Buat Jurnal Baru
    $no_bukti_baru = 'JJ-' . date('Ymd') . '-' . sprintf("%04d", $id);
    $jenis_jurnal = ($metode == 'tunai') ? 'penjualan_cash' : 'penjualan_utang';

    $qSetting = mysqli_query($conn, "SELECT * FROM tb_setting_jurnal WHERE jenis_transaksi = '$jenis_jurnal'");
    $setting = mysqli_fetch_assoc($qSetting);

    if ($setting) {
        $id_akun_debit  = $setting['akun_debit'];
        $id_akun_kredit = $setting['akun_kredit'];

        // Debit (Kas atau Piutang)
        mysqli_query($conn, "
            INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES ('$no_bukti_baru', '$tanggal', 'Revisi Penjualan ($no_nota)', $id_akun_debit, $total_baru, 0)
        ");

        // Kredit (Pendapatan Penjualan)
        mysqli_query($conn, "
            INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES ('$no_bukti_baru', '$tanggal', 'Revisi Penjualan ($no_nota)', $id_akun_kredit, 0, $total_baru)
        ");
    }

    header("Location: penjualan.php?status=update_sukses");
    exit;
}
?>
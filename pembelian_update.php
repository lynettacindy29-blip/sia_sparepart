<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id       = intval($_POST['id']);
    $tanggal  = $_POST['tanggal'];
    $metode   = $_POST['metode'];

    $id_barang_arr  = isset($_POST['id_barang']) ? $_POST['id_barang'] : [];
    $harga_beli_arr = isset($_POST['harga_beli']) ? $_POST['harga_beli'] : [];
    $jumlah_arr     = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];

    // Ambil data header lama untuk rujukan
    $qHeader = mysqli_query($conn, "SELECT * FROM tb_pembelian WHERE id = $id");
    $headerLama = mysqli_fetch_assoc($qHeader);
    
    // Pastikan ada no_faktur, jika tiada (data lama), kita buatkan
    $no_faktur = isset($headerLama['no_faktur']) && $headerLama['no_faktur'] != '' ? $headerLama['no_faktur'] : 'PB-' . date('Ymd', strtotime($tanggal)) . '-' . sprintf("%04d", $id);

    /* ==========================================================
       1. KEMBALIKAN (REVERT) STOK LAMA & CATAT KE KARTU STOK
    ========================================================== */
    $qOld = mysqli_query($conn, "SELECT * FROM tb_detail_pembelian WHERE id_pembelian = '$id'");

    while ($old = mysqli_fetch_assoc($qOld)) {
        $id_brg_lama = $old['id_barang'];
        $qty_lama = $old['jumlah'];

        // Ambil stok semasa
        $qStok = mysqli_query($conn, "SELECT stok FROM tb_barang WHERE id = $id_brg_lama");
        $rowStok = mysqli_fetch_assoc($qStok);
        $stok_sekarang = $rowStok['stok'];
        
        // Tolak stok (kerana pembelian dibatalkan sementara)
        $stok_revert = $stok_sekarang - $qty_lama;

        // Update ke tb_barang
        mysqli_query($conn, "UPDATE tb_barang SET stok = $stok_revert WHERE id = $id_brg_lama");

        // Catat pembalikan (reversal) dalam Kartu Stok
        mysqli_query($conn, "
            INSERT INTO tb_kartu_stok 
            (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, keterangan)
            VALUES 
            ('$id_brg_lama', '$tanggal', 'penyesuaian', 'REVERT-$no_faktur', 0, '$qty_lama', '$stok_revert', 'Revisi Pembelian (Tarik Balik)')
        ");
    }

    /* ==========================================================
       2. HAPUS DETAIL LAMA
    ========================================================== */
    mysqli_query($conn, "DELETE FROM tb_detail_pembelian WHERE id_pembelian = '$id'");

    /* ==========================================================
       3. INSERT DETAIL BARU + UPDATE STOK BARU
    ========================================================== */
    $total = 0;

    for ($i = 0; $i < count($id_barang_arr); $i++) {

        $barang = intval($id_barang_arr[$i]);
        $harga  = floatval($harga_beli_arr[$i]);
        $qty    = intval($jumlah_arr[$i]);
        
        $subtotal = $harga * $qty;
        $total += $subtotal;

        // Insert Detail Baru
        mysqli_query($conn, "
            INSERT INTO tb_detail_pembelian (id_pembelian, id_barang, harga_beli, jumlah, subtotal)
            VALUES ('$id', '$barang', '$harga', '$qty', '$subtotal')
        ");

        // Ambil stok selepas direvert tadi
        $qStok2 = mysqli_query($conn, "SELECT stok FROM tb_barang WHERE id = $barang");
        $rowStok2 = mysqli_fetch_assoc($qStok2);
        $stok_sekarang2 = $rowStok2['stok'];

        // Tambah stok baru
        $stok_baru = $stok_sekarang2 + $qty;

        mysqli_query($conn, "UPDATE tb_barang SET stok = $stok_baru WHERE id = $barang");

        // Catat pembelian baru ke Kartu Stok
        mysqli_query($conn, "
            INSERT INTO tb_kartu_stok 
            (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, keterangan)
            VALUES 
            ('$barang', '$tanggal', 'pembelian', '$no_faktur', '$qty', 0, '$stok_baru', 'Revisi Pembelian (Stok Baru)')
        ");
    }

    /* ==========================================================
       4. KIRA SEMULA SISA HUTANG & STATUS
    ========================================================== */
    if ($metode == 'tunai') {
        $status = 'lunas';
        $sisa_hutang = 0;
    } else {
        $status = 'belum_lunas';
        // Anggap belum ada ansuran dibayar (kerana diedit)
        $sisa_hutang = $total; 
    }

    // Update Header Pembelian
    mysqli_query($conn, "
        UPDATE tb_pembelian
        SET tanggal='$tanggal',
            metode='$metode',
            no_faktur='$no_faktur',
            total='$total',
            status='$status',
            sisa_hutang='$sisa_hutang'
        WHERE id='$id'
    ");

    /* ==========================================================
       5. KEMASKINI JURNAL (Hapus yang lama, buat yang baru)
    ========================================================== */
    // Cari no_bukti lama untuk dihapuskan (biasanya format JB-... atau berdasarkan keterangan)
    mysqli_query($conn, "DELETE FROM tb_jurnal WHERE keterangan LIKE '%$no_faktur%'");

    // Buat Jurnal Baru
    $no_bukti_baru = 'JB-' . date('Ymd') . '-' . sprintf("%04d", $id);
    $jenis_jurnal = ($metode == 'tunai') ? 'pembelian_cash' : 'pembelian_utang';

    $qSetting = mysqli_query($conn, "SELECT * FROM tb_setting_jurnal WHERE jenis_transaksi = '$jenis_jurnal'");
    $setting = mysqli_fetch_assoc($qSetting);

    if ($setting) {
        $id_akun_debit  = $setting['akun_debit'];
        $id_akun_kredit = $setting['akun_kredit'];

        // Debit
        mysqli_query($conn, "
            INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES ('$no_bukti_baru', '$tanggal', 'Revisi Pembelian Barang ($no_faktur)', $id_akun_debit, $total, 0)
        ");

        // Kredit
        mysqli_query($conn, "
            INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES ('$no_bukti_baru', '$tanggal', 'Revisi Pembelian Barang ($no_faktur)', $id_akun_kredit, 0, $total)
        ");
    }

    header("Location: pembelian.php?status=update_sukses");
    exit;
}
?>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pembelian = intval($_POST['id_pembelian']);
    $jumlah_bayar = intval($_POST['jumlah_bayar']);
    $keterangan   = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal      = date('Y-m-d');

    // ambil data pembelian semasa
    $q = mysqli_query($conn, "SELECT * FROM tb_pembelian WHERE id = $id_pembelian");
    $data = mysqli_fetch_assoc($q);

    if (!$data) {
        die("Data pembelian tidak ditemui.");
    }

    $no_faktur = isset($data['no_faktur']) ? $data['no_faktur'] : 'ID-'.$id_pembelian;
    $sisa_lama = $data['sisa_hutang'];
    $sisa_baru = $sisa_lama - $jumlah_bayar;

    // Pastikan tak terlebih bayar
    if ($sisa_baru < 0) {
        $sisa_baru = 0;
    }

    // tentukan status baru
    if ($sisa_baru <= 0) {
        $status = 'lunas';
        $sisa_baru = 0;
    } else {
        $status = 'belum_lunas';
    }

    // 1. Update baki hutang di tb_pembelian
    mysqli_query($conn, "
        UPDATE tb_pembelian
        SET 
            sisa_hutang = $sisa_baru,
            status = '$status'
        WHERE id = $id_pembelian
    ");

    // 2. Simpan sejarah pembayaran ke jadual yang betul (tb_pembayaran_hutang)
    mysqli_query($conn, "
        INSERT INTO tb_pembayaran_hutang
        (id_pembelian, tanggal, jumlah_bayar, keterangan)
        VALUES
        ($id_pembelian, '$tanggal', $jumlah_bayar, '$keterangan')
    ");

    // ==============================================
    // 3. INSERT JURNAL BAYAR HUTANG (EFISIENSI KEUANGAN)
    // ==============================================

    $qSetting = mysqli_query($conn, "
        SELECT * FROM tb_setting_jurnal 
        WHERE jenis_transaksi = 'bayar_hutang'
    ");
    $setting = mysqli_fetch_assoc($qSetting);

    if ($setting) {
        $akun_debit  = $setting['akun_debit'];   // Hutang berkurang (Debit)
        $akun_kredit = $setting['akun_kredit'];  // Kas/Bank berkurang (Kredit)
        
        // Buat no_bukti Jurnal (Format: PH-TahunBulanTanggal-ID)
        $no_bukti = 'PH-' . date('Ymd') . '-' . rand(100,999);
        $ket_jurnal = "Pembayaran Hutang Fak: $no_faktur ($keterangan)";

        // debit hutang
        mysqli_query($conn, "
            INSERT INTO tb_jurnal
            (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES
            ('$no_bukti', '$tanggal', '$ket_jurnal', $akun_debit, $jumlah_bayar, 0)
        ");

        // kredit kas
        mysqli_query($conn, "
            INSERT INTO tb_jurnal
            (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES
            ('$no_bukti', '$tanggal', '$ket_jurnal', $akun_kredit, 0, $jumlah_bayar)
        ");
    }

    header("Location: hutang_pembelian.php?status=sukses");
    exit;
}
?>
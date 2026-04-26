<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['keranjang_pembelian']) || empty($_SESSION['keranjang_pembelian'])) {
    die("Keranjang kosong!");
}

$id_pembelian = intval($_POST['id_pembelian']);

/* Ambil data header */
$qHeader = mysqli_query($conn, "
    SELECT * FROM tb_pembelian WHERE id = $id_pembelian
");
$header = mysqli_fetch_assoc($qHeader);

$tanggal = $header['tanggal'];
$metode  = $header['metode'];

// 1. Buat Nomor Faktur Pembelian Otomatis (Format: PB-TahunBulanTanggal-ID)
$no_faktur = 'PB-' . date('Ymd') . '-' . sprintf("%04d", $id_pembelian);

$total = 0;

/* Loop keranjang */
foreach ($_SESSION['keranjang_pembelian'] as $item) {

    $id_barang  = $item['id_barang'];
    $harga      = $item['harga_beli'];
    $jumlah     = $item['jumlah'];
    $subtotal   = $item['subtotal'];

    $total += $subtotal;

    // insert detail
    mysqli_query($conn, "
        INSERT INTO tb_detail_pembelian
        (id_pembelian, id_barang, harga_beli, jumlah, subtotal)
        VALUES
        ($id_pembelian, $id_barang, $harga, $jumlah, $subtotal)
    ");

    /* ====================================================================
       TAMBAHAN LOGIKA: PERPETUAL MOVING AVERAGE (MENGHITUNG HPP BARU)
       ==================================================================== */
    // Ambil stok dan HPP saat ini dari tb_barang
    $qStok = mysqli_query($conn, "SELECT stok, harga_beli FROM tb_barang WHERE id = $id_barang");
    $rowStok = mysqli_fetch_assoc($qStok);
    
    $stok_sekarang = intval($rowStok['stok']);
    $hpp_lama = floatval($rowStok['harga_beli']); // HPP sebelumnya
    
    // Hitung Saldo Stok Baru
    $saldo_stok_baru = $stok_sekarang + $jumlah;

    // Hitung Harga Pokok (HPP) Rata-rata Baru
    if ($stok_sekarang == 0 || $hpp_lama == 0) {
        $hpp_baru = $harga; // Jika stok kosong, langsung pakai harga beli yang baru
    } else {
        $nilai_stok_lama = $stok_sekarang * $hpp_lama;
        $nilai_beli_baru = $jumlah * $harga;
        $hpp_baru = ($nilai_stok_lama + $nilai_beli_baru) / $saldo_stok_baru;
    }
    /* ==================================================================== */

    // update stok dan HPP (PERPETUAL)
    mysqli_query($conn, "
        UPDATE tb_barang 
        SET stok = $saldo_stok_baru,
            harga_beli = '$hpp_baru'
        WHERE id = $id_barang
    ");

    // 2. FITUR KONTROL PERSEDIAAN: Catat jejaknya ke Kartu Stok (Termasuk harga_pokok)
    mysqli_query($conn, "
        INSERT INTO tb_kartu_stok 
        (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, harga_pokok, keterangan)
        VALUES 
        ('$id_barang', '$tanggal', 'pembelian', '$no_faktur', '$jumlah', 0, '$saldo_stok_baru', '$harga', 'Pembelian dari Supplier')
    ");
}

/* ===============================
   UPDATE HEADER + STATUS
   =============================== */

// Tentukan status berdasarkan metode
if ($metode == 'tunai') {
    $status = 'lunas';
    $sisa_hutang = 0;
} else {
    $status = 'belum_lunas';
    $sisa_hutang = $total;
}

// Update tabel pembelian (simpan total, status, dan no_faktur)
mysqli_query($conn, "
    UPDATE tb_pembelian 
    SET 
        no_faktur = '$no_faktur',
        total = $total,
        status = '$status',
        sisa_hutang = $sisa_hutang
    WHERE id = $id_pembelian
");

/* ===============================
   INSERT JURNAL OTOMATIS
   =============================== */

// 3. Buat Nomor Bukti Jurnal (Format: JB-TahunBulanTanggal-ID)
$no_bukti = 'JB-' . date('Ymd') . '-' . sprintf("%04d", $id_pembelian);

// mapping metode ke jenis_transaksi
if ($metode == 'tunai') {
    $jenis = 'pembelian_cash';
} else {
    $jenis = 'pembelian_utang';
}

// ambil setting jurnal
$qSetting = mysqli_query($conn, "
    SELECT * FROM tb_setting_jurnal 
    WHERE jenis_transaksi = '$jenis'
");
$setting = mysqli_fetch_assoc($qSetting);

if ($setting) {
    $id_akun_debit  = $setting['akun_debit'];
    $id_akun_kredit = $setting['akun_kredit'];

    // insert debit
    mysqli_query($conn, "
        INSERT INTO tb_jurnal
        (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
        VALUES
        ('$no_bukti', '$tanggal', 'Pembelian Barang ($no_faktur)', $id_akun_debit, $total, 0)
    ");

    // insert kredit
    mysqli_query($conn, "
        INSERT INTO tb_jurnal
        (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
        VALUES
        ('$no_bukti', '$tanggal', 'Pembelian Barang ($no_faktur)', $id_akun_kredit, 0, $total)
    ");
}

/* Hapus keranjang */
unset($_SESSION['keranjang_pembelian']);
unset($_SESSION['id_pembelian']);

// Redirect kembali ke halaman data pembelian
header("Location: pembelian.php?status=sukses");
exit;
?>
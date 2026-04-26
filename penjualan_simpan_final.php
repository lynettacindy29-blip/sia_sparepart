<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['keranjang_penjualan']) || empty($_SESSION['keranjang_penjualan'])) {
    die("Keranjang masih kosong! Silakan kembali dan pilih barang terlebih dahulu.");
}

$id_penjualan = intval($_POST['id_penjualan']);

// 1. Ambil data Header (Nota)
$qHeader = mysqli_query($conn, "SELECT * FROM tb_penjualan WHERE id = $id_penjualan");
$header = mysqli_fetch_assoc($qHeader);

if (!$header) {
    die("Nota penjualan tidak valid.");
}

$metode = $header['metode'];
$tanggal = $header['tanggal'];
$nama_pelanggan = mysqli_real_escape_string($conn, $header['nama_pelanggan']);

// Buat Nomor Nota Otomatis (Format: PJ-TahunBulanTanggal-ID)
$no_nota = 'PJ-' . date('Ymd', strtotime($tanggal)) . '-' . sprintf("%04d", $id_penjualan);

$total_pendapatan = 0;
$total_hpp_nota = 0; // Penampung total HPP (Modal) untuk seluruh barang di nota ini

/* ==========================================================
   2. LOOP KERANJANG, POTONG STOK, & CATAT KE KARTU STOK
========================================================== */
foreach ($_SESSION['keranjang_penjualan'] as $item) {

    $id_barang = $item['id_barang'];
    $harga_jual = $item['harga']; // Harga yang dibayar customer
    $qty = $item['jumlah'];
    $subtotal = $item['subtotal'];

    $total_pendapatan += $subtotal;

    // Insert rincian barang yang dibeli pelanggan
    mysqli_query($conn, "
        INSERT INTO tb_detail_penjualan (id_penjualan, id_barang, qty, harga, subtotal)
        VALUES ($id_penjualan, $id_barang, $qty, $harga_jual, $subtotal)
    ");

    // Ambil stok saat ini DAN HPP Rata-rata Terkini dari master barang
    $qStok = mysqli_query($conn, "SELECT stok, harga_beli FROM tb_barang WHERE id = $id_barang");
    $rowStok = mysqli_fetch_assoc($qStok);
    
    $stok_sekarang = $rowStok['stok'];
    $hpp_average = $rowStok['harga_beli']; // Harga modal rata-rata terkini
    
    // Kurangi stok fisik
    $saldo_stok_baru = $stok_sekarang - $qty;

    // Hitung total nilai HPP untuk barang ini (Modal Satuan x Qty Keluar)
    $subtotal_hpp = $hpp_average * $qty;
    $total_hpp_nota += $subtotal_hpp; // Akumulasikan ke total HPP satu nota

    // Update stok terbaru ke master barang
    mysqli_query($conn, "UPDATE tb_barang SET stok = $saldo_stok_baru WHERE id = $id_barang");

    // FITUR KONTROL PERSEDIAAN: Catat jejak keluarnya barang ke Kartu Stok beserta HPP-nya
    mysqli_query($conn, "
        INSERT INTO tb_kartu_stok 
        (id_barang, tanggal, jenis_transaksi, no_referensi, qty_masuk, qty_keluar, saldo_stok, harga_pokok, keterangan)
        VALUES 
        ('$id_barang', '$tanggal', 'penjualan', '$no_nota', 0, '$qty', '$saldo_stok_baru', '$hpp_average', 'Penjualan ke $nama_pelanggan')
    ");
}

/* ==========================================================
   3. SET STATUS (LUNAS / PIUTANG)
========================================================== */
if ($metode == 'tunai') {
    $status_bayar = 'lunas';
    $sisa_piutang = 0;
} else {
    $status_bayar = 'belum'; // Belum Lunas (Kredit)
    $sisa_piutang = $total_pendapatan;
}

// Update Header (Selesaikan Nota)
mysqli_query($conn, "
    UPDATE tb_penjualan
    SET no_nota = '$no_nota',
        total = $total_pendapatan,
        status_bayar = '$status_bayar',
        sisa_piutang = $sisa_piutang
    WHERE id = $id_penjualan
");

/* ==========================================================
   4. INSERT JURNAL OTOMATIS (PERPETUAL INVENTORY SYSTEM)
========================================================== */
// PERBAIKAN: Nomor bukti disinkronkan dengan $tanggal transaksi, bukan date('Ymd') hari ini
$no_bukti = 'JJ-' . date('Ymd', strtotime($tanggal)) . '-' . sprintf("%04d", $id_penjualan);
$jenis_jurnal = ($metode == 'tunai') ? 'penjualan_cash' : 'penjualan_utang';

// --- JURNAL 1: MENCATAT PENDAPATAN ---
$qSetting = mysqli_query($conn, "SELECT * FROM tb_setting_jurnal WHERE jenis_transaksi = '$jenis_jurnal'");
$setting = mysqli_fetch_assoc($qSetting);

if ($setting) {
    $id_akun_debit  = $setting['akun_debit'];   // Kas atau Piutang
    $id_akun_kredit = $setting['akun_kredit'];  // Pendapatan Penjualan

    $ket_jurnal = "Penjualan ($no_nota) - $nama_pelanggan";

    // Insert Debit dengan alarm error
    mysqli_query($conn, "
        INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
        VALUES ('$no_bukti', '$tanggal', '$ket_jurnal', $id_akun_debit, $total_pendapatan, 0)
    ") or die("Gagal Jurnal Debit Penjualan: " . mysqli_error($conn));

    // Insert Kredit dengan alarm error
    mysqli_query($conn, "
        INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
        VALUES ('$no_bukti', '$tanggal', '$ket_jurnal', $id_akun_kredit, 0, $total_pendapatan)
    ") or die("Gagal Jurnal Kredit Penjualan: " . mysqli_error($conn));
} else {
    // ALARM: Jika setting belum ada, hentikan program dan beri tahu kasir!
    die("Peringatan: Master Setting Jurnal untuk '$jenis_jurnal' belum diatur! Jurnal Kas/Piutang gagal dibuat.");
}

// --- JURNAL 2: MENCATAT HPP & MENGURANGI PERSEDIAAN ---
$id_akun_hpp = 12;         // ID Akun Harga Pokok Penjualan
$id_akun_persediaan = 4;   // ID Akun Persediaan Barang

if ($total_hpp_nota > 0) {
    $ket_hpp = "Harga Pokok Penjualan ($no_nota)";

    // Insert Debit HPP
    mysqli_query($conn, "
        INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
        VALUES ('$no_bukti', '$tanggal', '$ket_hpp', $id_akun_hpp, $total_hpp_nota, 0)
    ") or die("Gagal Jurnal Debit HPP: " . mysqli_error($conn));

    // Insert Kredit Persediaan
    mysqli_query($conn, "
        INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
        VALUES ('$no_bukti', '$tanggal', '$ket_hpp', $id_akun_persediaan, 0, $total_hpp_nota)
    ") or die("Gagal Jurnal Kredit Persediaan: " . mysqli_error($conn));
}

/* ==========================================================
   5. BERSIHKAN KERANJANG & SELESAI
========================================================== */
unset($_SESSION['keranjang_penjualan']);
unset($_SESSION['id_penjualan']);

header("Location: penjualan.php?status=sukses");
exit;
?>
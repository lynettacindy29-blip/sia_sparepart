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

    // update stok (PERPETUAL)
    mysqli_query($conn, "
        UPDATE tb_barang 
        SET stok = stok + $jumlah
        WHERE id = $id_barang
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

mysqli_query($conn, "
    UPDATE tb_pembelian 
    SET 
        total = $total,
        status = '$status',
        sisa_hutang = $sisa_hutang
    WHERE id = $id_pembelian
");

/* ===============================
   INSERT JURNAL OTOMATIS
   =============================== */

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

$id_akun_debit  = $setting['akun_debit'];
$id_akun_kredit = $setting['akun_kredit'];

// insert debit
mysqli_query($conn, "
    INSERT INTO tb_jurnal
    (tanggal, keterangan, id_akun, debit, kredit)
    VALUES
    ('$tanggal', 'Pembelian', $id_akun_debit, $total, 0)
");

// insert kredit
mysqli_query($conn, "
    INSERT INTO tb_jurnal
    (tanggal, keterangan, id_akun, debit, kredit)
    VALUES
    ('$tanggal', 'Pembelian', $id_akun_kredit, 0, $total)
");

/* Hapus keranjang */
unset($_SESSION['keranjang_pembelian']);
unset($_SESSION['id_pembelian']);

header("Location: pembelian_tambah.php?status=sukses");
exit;
?>
<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id       = $_POST['id'];
    $tanggal  = $_POST['tanggal'];
    $metode   = $_POST['metode'];

    $id_barang   = $_POST['id_barang'];
    $harga_beli  = $_POST['harga_beli'];
    $jumlah      = $_POST['jumlah'];

    /* ============================
       1️⃣ KEMBALIKAN STOK LAMA
    ============================ */
    $qOld = mysqli_query($conn, "
        SELECT * FROM tb_detail_pembelian
        WHERE id_pembelian = '$id'
    ");

    while ($old = mysqli_fetch_assoc($qOld)) {
        mysqli_query($conn, "
            UPDATE tb_barang 
            SET stok = stok - {$old['jumlah']}
            WHERE id = {$old['id_barang']}
        ");
    }

    /* ============================
       2️⃣ HAPUS DETAIL LAMA
    ============================ */
    mysqli_query($conn, "
        DELETE FROM tb_detail_pembelian
        WHERE id_pembelian = '$id'
    ");

    /* ============================
       3️⃣ UPDATE HEADER
    ============================ */
    mysqli_query($conn, "
        UPDATE tb_pembelian
        SET tanggal='$tanggal',
            metode='$metode'
        WHERE id='$id'
    ");

    /* ============================
       4️⃣ INSERT DETAIL BARU + UPDATE STOK
    ============================ */
    $total = 0;

    for ($i = 0; $i < count($id_barang); $i++) {

        $barang = $id_barang[$i];
        $harga  = $harga_beli[$i];
        $qty    = $jumlah[$i];
        $subtotal = $harga * $qty;
        $total += $subtotal;

        mysqli_query($conn, "
            INSERT INTO tb_detail_pembelian
            (id_pembelian, id_barang, harga_beli, jumlah, subtotal)
            VALUES
            ('$id', '$barang', '$harga', '$qty', '$subtotal')
        ");

        // tambah stok baru
        mysqli_query($conn, "
            UPDATE tb_barang
            SET stok = stok + $qty
            WHERE id = $barang
        ");
    }

    /* ============================
       5️⃣ UPDATE TOTAL
    ============================ */
    mysqli_query($conn, "
        UPDATE tb_pembelian
        SET total = '$total'
        WHERE id = '$id'
    ");

    header("Location: pembelian.php");
    exit;
}
?>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_supplier = intval($_POST['id_supplier']);
    $jumlah_uang_ditangan = floatval($_POST['jumlah_bayar']);
    $total_bayar_jurnal = $jumlah_uang_ditangan; 
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal_bayar = date('Y-m-d');
    $no_bukti_jurnal = 'PHG-' . date('Ymd') . '-' . rand(100,999);
    
    // --- 1. PROSES UPLOAD BUKTI ---
    $nama_file_bukti = NULL; 
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $file_name = $_FILES['bukti_bayar']['name'];
        $file_tmp = $_FILES['bukti_bayar']['tmp_name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $nama_file_baru = "global_hutang_" . time() . "_" . rand(10,99) . "." . $ext;
        
        if (move_uploaded_file($file_tmp, "assets/bukti_bayar/" . $nama_file_baru)) {
            $nama_file_bukti = $nama_file_baru;
        }
    }

    // Ambil nama supplier
    $qSup = mysqli_query($conn, "SELECT nama_supplier, saldo_awal_utang FROM tb_supplier WHERE id = $id_supplier");
    $dataSup = mysqli_fetch_assoc($qSup);
    $nama_supplier = $dataSup['nama_supplier'];
    $array_nota_dibayar = [];

    // --- 2A. LOGIKA FIFO: POTONG SALDO AWAL DULU ---
    $saldo_awal = floatval($dataSup['saldo_awal_utang']);
    if ($saldo_awal > 0 && $jumlah_uang_ditangan > 0) {
        if ($jumlah_uang_ditangan >= $saldo_awal) {
            $potong_saldo_awal = $saldo_awal;
            $sisa_saldo_awal = 0;
            $jumlah_uang_ditangan -= $saldo_awal;
        } else {
            $potong_saldo_awal = $jumlah_uang_ditangan;
            $sisa_saldo_awal = $saldo_awal - $jumlah_uang_ditangan;
            $jumlah_uang_ditangan = 0;
        }

        // Update saldo awal
        mysqli_query($conn, "UPDATE tb_supplier SET saldo_awal_utang = $sisa_saldo_awal WHERE id = $id_supplier");
        
        // Simpan ke histori (id_pembelian diisi id_supplier sebagai penanda saldo awal, sesuai logika sebelumnya)
        mysqli_query($conn, "INSERT INTO tb_pembayaran_hutang (id_pembelian, tanggal, jumlah_bayar, keterangan, bukti_bayar) 
                             VALUES ($id_supplier, '$tanggal_bayar', $potong_saldo_awal, '$keterangan (Potong Saldo Awal)', '$nama_file_bukti')");
        $array_nota_dibayar[] = "Saldo Awal";
    }

    // --- 2B. LOGIKA FIFO: POTONG FAKTUR PEMBELIAN ---
    if ($jumlah_uang_ditangan > 0) {
        $qFaktur = mysqli_query($conn, "
            SELECT id, no_faktur, sisa_hutang 
            FROM tb_pembelian 
            WHERE id_supplier = $id_supplier AND sisa_hutang > 0 
            ORDER BY tanggal ASC, id ASC
        ");

        while($faktur = mysqli_fetch_assoc($qFaktur)) {
            if ($jumlah_uang_ditangan <= 0) break; 

            $id_pembelian = $faktur['id'];
            $sisa_tagihan = $faktur['sisa_hutang'];
            $no_faktur = isset($faktur['no_faktur']) ? $faktur['no_faktur'] : "FB-".$id_pembelian;

            if ($jumlah_uang_ditangan >= $sisa_tagihan) {
                $potongan = $sisa_tagihan;
                $sisa_baru = 0;
                $status_baru = 'lunas';
                $jumlah_uang_ditangan -= $sisa_tagihan;
            } else {
                $potongan = $jumlah_uang_ditangan;
                $sisa_baru = $sisa_tagihan - $jumlah_uang_ditangan;
                $status_baru = 'belum_lunas';
                $jumlah_uang_ditangan = 0;
            }

            $array_nota_dibayar[] = $no_faktur;

            // Update Sisa Hutang
            mysqli_query($conn, "UPDATE tb_pembelian SET sisa_hutang = $sisa_baru, status = '$status_baru' WHERE id = $id_pembelian");

            // Insert Histori Pembayaran Hutang
            mysqli_query($conn, "INSERT INTO tb_pembayaran_hutang (id_pembelian, tanggal, jumlah_bayar, keterangan, bukti_bayar) 
                                 VALUES ($id_pembelian, '$tanggal_bayar', $potongan, '$keterangan (Global)', '$nama_file_bukti')");
        }
    }

    // --- 3. JURNAL AKUNTANSI GLOBAL ---
    $daftar_nota_str = implode(", ", $array_nota_dibayar);
    $ket_jurnal = "Global Payment ke $nama_supplier ($keterangan) - Ref: $daftar_nota_str";

    // (Debit) Utang Usaha - ID Akun 5
    mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, id_akun, keterangan, debit, kredit) VALUES ('$no_bukti_jurnal', '$tanggal_bayar', '5', '$ket_jurnal', '$total_bayar_jurnal', '0')");
    // (Kredit) Kas - ID Akun 1
    mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, id_akun, keterangan, debit, kredit) VALUES ('$no_bukti_jurnal', '$tanggal_bayar', '1', '$ket_jurnal', '0', '$total_bayar_jurnal')");

    echo "<script>alert('Pembayaran Hutang Global Berhasil!'); window.location='hutang_pembelian.php';</script>";
} else {
    header("Location: hutang_pembelian.php");
}
?>
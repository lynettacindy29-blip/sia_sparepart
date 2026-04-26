<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    
    $id_akun_arr = isset($_POST['id_akun']) ? $_POST['id_akun'] : [];
    $debit_arr   = isset($_POST['debit']) ? $_POST['debit'] : [];
    $kredit_arr  = isset($_POST['kredit']) ? $_POST['kredit'] : [];

    // Buat Nomor Bukti Khusus untuk Saldo Awal
    $no_bukti = 'SA-' . date('Ymd', strtotime($tanggal));

    // Lakukan looping untuk menyimpan tiap baris yang tidak bernilai 0
    for ($i = 0; $i < count($id_akun_arr); $i++) {
        $id_akun = intval($id_akun_arr[$i]);
        $debit   = floatval($debit_arr[$i]);
        $kredit  = floatval($kredit_arr[$i]);

        // Hanya simpan akun yang diisi angkanya (lebih dari 0)
        if ($debit > 0 || $kredit > 0) {
            mysqli_query($conn, "
                INSERT INTO tb_jurnal 
                (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                VALUES 
                ('$no_bukti', '$tanggal', 'Setoran Saldo Awal', '$id_akun', '$debit', '$kredit')
            ");
        }
    }

    // Arahkan kembali ke Buku Besar agar bisa langsung dilihat hasilnya
    header("Location: buku_besar.php?status=saldo_awal_sukses");
    exit;
}
?>
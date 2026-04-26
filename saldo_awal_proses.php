<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    
    // Bikin nomor bukti khusus Saldo Awal (SA)
    $no_bukti = 'SA-' . date('Ymd', strtotime($tanggal)) . '-' . rand(100,999); 
    $keterangan = "Saldo Awal Periode Terkini";

    $id_akun = $_POST['id_akun'];
    $debit = $_POST['debit'];
    $kredit = $_POST['kredit'];

    // 1. Validasi Total Balance
    $tot_debit = 0;
    $tot_kredit = 0;

    for ($i = 0; $i < count($id_akun); $i++) {
        $tot_debit += (float)$debit[$i];
        $tot_kredit += (float)$kredit[$i];
    }

    if ($tot_debit == 0 && $tot_kredit == 0) {
        die("<h3>GAGAL!</h3><p>Anda belum mengisi nominal apa pun.</p><a href='saldo_awal.php'>Kembali</a>");
    }

    if ($tot_debit != $tot_kredit) {
        die("<h3>GAGAL!</h3><p>Total Debit dan Kredit tidak Balance! (Selisih: Rp " . number_format(abs($tot_debit - $tot_kredit),0,',','.') . ")</p><a href='saldo_awal.php'>Kembali</a>");
    }

    // 2. Jika Balance, masukkan satu per satu ke tb_jurnal
    for ($i = 0; $i < count($id_akun); $i++) {
        $d = (float)$debit[$i];
        $k = (float)$kredit[$i];
        
        // Hanya insert akun yang diisi angkanya (tidak nol)
        if ($d > 0 || $k > 0) {
            $akun_id = $id_akun[$i];
            
            mysqli_query($conn, "
                INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
                VALUES ('$no_bukti', '$tanggal', '$keterangan', '$akun_id', '$d', '$k')
            ");
        }
    }

    // Redirect dengan pesan sukses
    echo "<script>
            alert('Saldo Awal berhasil disimpan! (No. Bukti: $no_bukti)');
            window.location.href='laporan_neraca.php';
          </script>";
}
?>
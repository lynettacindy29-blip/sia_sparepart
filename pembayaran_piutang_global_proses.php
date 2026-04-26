<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $jumlah_uang_ditangan = floatval($_POST['jumlah_bayar']);
    $total_bayar_jurnal = $jumlah_uang_ditangan; // Simpan untuk nilai jurnal
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal_bayar = date('Y-m-d');
    
    // --- 1. PROSES UPLOAD BUKTI ---
    $nama_file_bukti = NULL; 
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $file_name = $_FILES['bukti_bayar']['name'];
        $file_tmp = $_FILES['bukti_bayar']['tmp_name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $nama_file_baru = "global_piutang_" . time() . "_" . rand(10,99) . "." . $ext;
        
        if (move_uploaded_file($file_tmp, "assets/bukti_bayar/" . $nama_file_baru)) {
            $nama_file_bukti = $nama_file_baru;
        }
    }

    // --- 2. LOGIKA FIFO (PEMOTONGAN NOTA TERTUA) ---
    // Cari semua nota milik pelanggan ini yang belum lunas, urutkan dari yang paling TUA
    $qNota = mysqli_query($conn, "
        SELECT id, no_nota, sisa_piutang 
        FROM tb_penjualan 
        WHERE nama_pelanggan = '$nama_pelanggan' AND sisa_piutang > 0 
        ORDER BY tanggal ASC, id ASC
    ");

    $array_nota_dibayar = []; // Menyimpan log nota mana saja yang terbayar untuk keterangan jurnal

    while($nota = mysqli_fetch_assoc($qNota)) {
        if ($jumlah_uang_ditangan <= 0) {
            break; // Uang sudah habis terpotong, hentikan looping
        }

        $id_penjualan = $nota['id'];
        $sisa_tagihan_nota = $nota['sisa_piutang'];
        $no_nota = isset($nota['no_nota']) ? $nota['no_nota'] : "PJ-".$id_penjualan;

        // Tentukan berapa banyak dari nota ini yang bisa dibayar dengan uang di tangan
        if ($jumlah_uang_ditangan >= $sisa_tagihan_nota) {
            // Uang cukup untuk melunasi nota ini SEPENUHNYA
            $potongan = $sisa_tagihan_nota;
            $sisa_baru = 0;
            $status_baru = 'lunas';
            $jumlah_uang_ditangan -= $sisa_tagihan_nota; // Kurangi uang di tangan
        } else {
            // Uang HANYA CUKUP UNTUK SEBAGIAN nota ini
            $potongan = $jumlah_uang_ditangan;
            $sisa_baru = $sisa_tagihan_nota - $jumlah_uang_ditangan;
            $status_baru = 'belum';
            $jumlah_uang_ditangan = 0; // Uang habis
        }

        // Catat bahwa nota ini ikut dibayar
        $array_nota_dibayar[] = $no_nota;

        // A. UPDATE SISA PIUTANG NOTA INI
        mysqli_query($conn, "UPDATE tb_penjualan SET sisa_piutang = $sisa_baru, status_bayar = '$status_baru' WHERE id = $id_penjualan");

        // B. INSERT KE TABEL HISTORI
        mysqli_query($conn, "INSERT INTO tb_pembayaran_piutang (id_penjualan, tanggal, jumlah_bayar, keterangan, bukti_bayar) 
                             VALUES ($id_penjualan, '$tanggal_bayar', $potongan, '$keterangan (Global Payment)', '$nama_file_bukti')");
    }

    // --- 3. JURNAL AKUNTANSI (HANYA DIBUAT 1 KALI UNTUK TOTAL UANG) ---
    $qSetting = mysqli_query($conn, "SELECT * FROM tb_setting_jurnal WHERE jenis_transaksi = 'terima_piutang'");
    $setting = mysqli_fetch_assoc($qSetting);

    if ($setting) {
        $akun_debit  = $setting['akun_debit'];
        $akun_kredit = $setting['akun_kredit'];
        $no_bukti = 'TPG-' . date('Ymd') . '-' . rand(100,999);
        
        $daftar_nota_str = implode(", ", $array_nota_dibayar);
        $ket_jurnal = "Global Payment $nama_pelanggan ($keterangan) - Nota: $daftar_nota_str";

        // Debit Kas
        mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) VALUES ('$no_bukti', '$tanggal_bayar', '$ket_jurnal', $akun_debit, $total_bayar_jurnal, 0)");

        // Kredit Piutang Usaha
        mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) VALUES ('$no_bukti', '$tanggal_bayar', '$ket_jurnal', $akun_kredit, 0, $total_bayar_jurnal)");
    }

    echo "<script>alert('Pembayaran Global Berhasil Diproses!'); window.location='piutang_penjualan.php';</script>";
} else {
    header("Location: piutang_penjualan.php");
}
?>
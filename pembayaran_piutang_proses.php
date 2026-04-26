<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_penjualan = intval($_POST['id_penjualan']);
    $jumlah_bayar = floatval($_POST['jumlah_bayar']);
    $keterangan   = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal      = date('Y-m-d');

    // --- PROSES UPLOAD BUKTI ---
    $nama_file_bukti = ""; // Default string kosong jika tidak ada upload
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $file_name = $_FILES['bukti_bayar']['name'];
        $file_tmp = $_FILES['bukti_bayar']['tmp_name'];
        
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $nama_file_baru = "piutang_" . time() . "_" . rand(10,99) . "." . $ext;
        
        $folder_tujuan = "assets/bukti_bayar/" . $nama_file_baru;
        
        if (move_uploaded_file($file_tmp, $folder_tujuan)) {
            $nama_file_bukti = $nama_file_baru;
        }
    }

    // Ambil data penjualan saat ini
    $q = mysqli_query($conn, "SELECT * FROM tb_penjualan WHERE id = $id_penjualan");
    $data = mysqli_fetch_assoc($q);

    if (!$data) {
        die("Data penjualan tidak ditemukan.");
    }

    $no_nota = isset($data['no_nota']) ? $data['no_nota'] : 'PJ-'.$id_penjualan;
    $sisa_lama = $data['sisa_piutang'];
    $sisa_baru = $sisa_lama - $jumlah_bayar;

    // Pastikan tak jadi minus
    if ($sisa_baru < 0) {
        $sisa_baru = 0;
    }

    // Tentukan status baru
    if ($sisa_baru <= 0) {
        $status_bayar = 'lunas';
        $sisa_baru = 0;
    } else {
        $status_bayar = 'belum';
    }

    // 1. Update sisa piutang di tb_penjualan
    mysqli_query($conn, "
        UPDATE tb_penjualan
        SET 
            sisa_piutang = $sisa_baru,
            status_bayar = '$status_bayar'
        WHERE id = $id_penjualan
    ");

    // 2. Simpan sejarah penerimaan uang (DITAMBAH BUKTI BAYAR)
    // Pastikan nama tabelnya tb_pembayaran_piutang seperti kodemu sebelumnya
    mysqli_query($conn, "
        INSERT INTO tb_pembayaran_piutang
        (id_penjualan, tanggal, jumlah_bayar, keterangan, bukti_bayar)
        VALUES
        ($id_penjualan, '$tanggal', $jumlah_bayar, '$keterangan', '$nama_file_bukti')
    ");

    /* ==========================================================
       3. INSERT JURNAL PENERIMAAN PIUTANG (EFISIENSI KEUANGAN)
    ========================================================== */
    $qSetting = mysqli_query($conn, "
        SELECT * FROM tb_setting_jurnal 
        WHERE jenis_transaksi = 'terima_piutang'
    ");
    $setting = mysqli_fetch_assoc($qSetting);

    if ($setting) {
        $akun_debit  = $setting['akun_debit'];   // Kas bertambah (Debit)
        $akun_kredit = $setting['akun_kredit'];  // Piutang berkurang (Kredit)
        
        // Buat no_bukti Jurnal (Format: TP-TahunBulanTanggal-Random)
        $no_bukti = 'TP-' . date('Ymd') . '-' . rand(100,999);
        $ket_jurnal = "Penerimaan Piutang Nota: $no_nota ($keterangan)";

        // Debit Kas
        mysqli_query($conn, "
            INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES ('$no_bukti', '$tanggal', '$ket_jurnal', $akun_debit, $jumlah_bayar, 0)
        ");

        // Kredit Piutang Usaha
        mysqli_query($conn, "
            INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
            VALUES ('$no_bukti', '$tanggal', '$ket_jurnal', $akun_kredit, 0, $jumlah_bayar)
        ");
    }

    header("Location: piutang_penjualan.php?status=sukses");
    exit;
}
?>
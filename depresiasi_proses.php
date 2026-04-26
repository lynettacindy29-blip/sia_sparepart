<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $periode = $_POST['periode']; // Format: YYYY-MM
    $tanggal_jurnal = date("Y-m-t", strtotime($periode . "-01")); 
    $no_bukti = "DEP-" . date("ym", strtotime($periode)) . rand(100,999);

    // --- FITUR ANTI-DOUBLE POSTING ---
    // Cek apakah di tb_jurnal sudah ada kata 'Penyusutan' untuk periode tersebut
    $cek_double = mysqli_query($conn, "SELECT id FROM tb_jurnal WHERE keterangan LIKE '%Penyusutan%' AND keterangan LIKE '%$periode%'");
    
    if (mysqli_num_rows($cek_double) > 0) {
        echo "<script>
                alert('Gagal! Jurnal Depresiasi untuk periode $periode sudah pernah diposting sebelumnya. Silakan hapus jurnal lama jika ingin generate ulang.');
                window.location='jurnal_umum.php'; 
              </script>";
        exit; // Hentikan proses agar tidak lanjut ke bawah
    }
    // ---------------------------------

    $aset_tetap = [
        [
            'nama_aset' => 'Bangunan Toko', 
            'nama_beban' => 'Beban Penyusutan Bangunan', 
            'nama_akum' => 'Akumulasi Penyusutan Bangunan', 
            'persen' => 0.05
        ],
        [
            'nama_aset' => 'Kendaraan Operasional', 
            'nama_beban' => 'Beban Penyusutan Kendaraan', 
            'nama_akum' => 'Akumulasi Penyusutan Kendaraan', 
            'persen' => 0.10
        ],
        [
            'nama_aset' => 'Peralatan Toko & Komputer', 
            'nama_beban' => 'Beban Penyusutan Peralatan', 
            'nama_akum' => 'Akumulasi Penyusutan Peralatan', 
            'persen' => 0.20
        ]
    ];

    $berhasil_count = 0;

    foreach ($aset_tetap as $aset) {
        $qAset = mysqli_query($conn, "SELECT id, saldo_awal FROM tb_akun WHERE nama_akun = '{$aset['nama_aset']}'");
        $rowAset = mysqli_fetch_assoc($qAset);
        
        $qBeban = mysqli_query($conn, "SELECT id FROM tb_akun WHERE nama_akun = '{$aset['nama_beban']}'");
        $rowBeban = mysqli_fetch_assoc($qBeban);
        
        $qAkum = mysqli_query($conn, "SELECT id FROM tb_akun WHERE nama_akun = '{$aset['nama_akum']}'");
        $rowAkum = mysqli_fetch_assoc($qAkum);

        if ($rowAset && $rowBeban && $rowAkum) {
            $harga_perolehan = $rowAset['saldo_awal'];
            
            if ($harga_perolehan > 0) {
                $nominal = round(($harga_perolehan * $aset['persen']) / 12);
                $ket = "Penyusutan " . $aset['nama_aset'] . " " . $periode;

                $id_beban = $rowBeban['id'];
                $id_akum = $rowAkum['id'];

                mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                                     VALUES ('$no_bukti', '$tanggal_jurnal', '$ket', $id_beban, $nominal, 0)");
                
                mysqli_query($conn, "INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit) 
                                     VALUES ('$no_bukti', '$tanggal_jurnal', '$ket', $id_akum, 0, $nominal)");

                $berhasil_count++;
            }
        }
    }

    if ($berhasil_count > 0) {
        echo "<script>alert('Berhasil! $berhasil_count Jurnal Depresiasi telah terbit.'); window.location='laporan_neraca.php?bulan=".date('m', strtotime($tanggal_jurnal))."&tahun=".date('Y', strtotime($tanggal_jurnal))."';</script>";
    } else {
        echo "<script>alert('Gagal! Akun tidak ditemukan atau Saldo Awal 0.'); window.history.back();</script>";
    }
}
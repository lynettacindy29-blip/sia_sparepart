<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Data simulasi aset tetap yang akan disusutkan (sesuai database kamu)
$aset_tetap = [
    ['nama' => 'Bangunan Toko', 'id_akun_aset' => 122, 'id_akun_beban' => 511, 'id_akun_akum' => 1221, 'persen' => 0.05], // 5% per tahun
    ['nama' => 'Kendaraan Operasional', 'id_akun_aset' => 123, 'id_akun_beban' => 512, 'id_akun_akum' => 1231, 'persen' => 0.10], // 10% per tahun
    ['nama' => 'Peralatan Toko & Komputer', 'id_akun_aset' => 124, 'id_akun_beban' => 513, 'id_akun_akum' => 1241, 'persen' => 0.20] // 20% per tahun
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Proses Depresiasi Aset</title>
    <link rel="stylesheet" href="inc/style.css">
    <style>
        .content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
        .card { background:#fff; border-radius:8px; padding:25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-proses { background:#ef4444; color:white; padding:12px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
        table { width:100%; border-collapse:collapse; margin: 20px 0; }
        th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
        .info { background:#fef3c7; color:#92400e; padding:15px; border-radius:6px; margin-bottom:20px; font-size:14px; border-left:5px solid #f59e0b; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="content">
        <div class="card">
            <h3>Otomatisasi Jurnal Depresiasi Bulanan</h3>
            <div class="info">
                <strong>Cara Kerja:</strong> Sistem akan mengambil Saldo Awal aset tetap, menghitung beban penyusutan untuk 1 bulan berdasarkan tarif tahunan, dan membuat jurnal penyesuaian otomatis.
            </div>

            <form action="depresiasi_proses.php" method="POST">
                <label>Pilih Periode Bulan:</label>
                <input type="month" name="periode" value="<?= date('Y-m') ?>" required>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama Aset</th>
                            <th>Tarif (Per Tahun)</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($aset_tetap as $at) : ?>
                        <tr>
                            <td><?= $at['nama'] ?></td>
                            <td><?= $at['persen'] * 100 ?>%</td>
                            <td>Garis Lurus (Straight Line)</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn-proses" onclick="return confirm('Sistem akan membuat jurnal otomatis. Lanjutkan?')">
                    ⚡ Posting Jurnal Depresiasi
                </button>
            </form>
        </div>
    </div>
</body>
</html>
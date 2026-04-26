<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Menangkap nilai filter tanggal dari URL (jika ada)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Menyusun logika kondisi WHERE dasar
$kondisi = "p.status = 'belum_lunas'";

// Jika filter tanggal diisi, tambahkan logika BETWEEN
if ($start_date != '' && $end_date != '') {
    $kondisi .= " AND p.tanggal BETWEEN '$start_date' AND '$end_date'";
}

// Ambil daftar pembelian yang statusnya belum lunas beserta filternya
$q = mysqli_query($conn, "
    SELECT p.*, s.nama_supplier
    FROM tb_pembelian p
    JOIN tb_supplier s ON p.id_supplier = s.id
    WHERE $kondisi
    ORDER BY p.tanggal DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Daftar Hutang Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

/* Styling untuk kotak filter */
.filter-box { background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;}
.filter-box form { display: flex; align-items: center; gap: 10px; width: 100%; }
.filter-box label { font-size: 13px; font-weight: bold; color: #475569; }
.filter-box input[type="date"] { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; }
.btn-filter { background: #3b82f6; color: white; border: none; padding: 7px 15px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; }
.btn-filter:hover { background: #2563eb; }
.btn-reset { background: #94a3b8; color: white; padding: 7px 15px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; }
.btn-reset:hover { background: #64748b; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:12px; border-bottom:1px solid #eee; font-size:13px; text-align: left; }
th { background:#f8fafc; color: #475569; font-weight: 600; }
td.right, th.right { text-align: right; }
td.center, th.center { text-align: center; }

.badge-hutang { background-color: #fef08a; color: #854d0e; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
.btn-bayar { background:#10b981; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:12px; font-weight:bold; }
.btn-bayar:hover { background:#059669; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Daftar Hutang Pembelian (A/P)</h3>
        <p class="desc">Daftar transaksi pembelian secara kredit yang belum dilunaskan kepada supplier.</p>

        <div class="filter-box">
            <form method="GET" action="hutang_pembelian.php">
                <label>Dari Tanggal:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
                
                <label>Sampai Tanggal:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
                
                <button type="submit" class="btn-filter">Cari Data</button>
                <a href="hutang_pembelian.php" class="btn-reset">Reset</a>
            </form>
        </div>
        <div style="margin-bottom: 15px; text-align: right;">
             <a href="pembayaran_hutang_global.php" style="background: #10b981; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 13px;">
            + Pembayaran Global (Per Supplier)
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal Transaksi</th>
                    <th>No. Faktur</th>
                    <th>Nama Supplier</th>
                    <th class="right">Total Pembelian</th>
                    <th class="right">Sisa Hutang</th>
                    <th class="center">Status</th>
                    <th class="center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($q) > 0) {
                    while($d = mysqli_fetch_assoc($q)) { 
                ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($d['tanggal'])) ?></td>
                    <td><strong><?= isset($d['no_faktur']) ? $d['no_faktur'] : '-' ?></strong></td>
                    <td><?= htmlspecialchars($d['nama_supplier']) ?></td>
                    <td class="right">Rp <?= number_format($d['total'],0,',','.') ?></td>
                    <td class="right" style="color: red; font-weight: bold;">Rp <?= number_format($d['sisa_hutang'],0,',','.') ?></td>
                    <td class="center"><span class="badge-hutang">Belum Lunas</span></td>
                    <td class="center">
                        <a href="pembayaran_hutang.php?id=<?= $d['id'] ?>" class="btn-bayar">Bayar Hutang</a>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='7' class='center' style='padding: 20px; color: #64748b;'>Wah, bagus! Toko anda tidak mempunyai hutang pembelian pada rentang waktu ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
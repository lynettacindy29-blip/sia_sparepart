<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data HEADER Pembelian, digabung dengan jumlah item yang dibeli
$q = mysqli_query($conn, "
    SELECT 
        pb.id,
        pb.no_faktur,
        pb.tanggal,
        s.nama_supplier,
        pb.metode,
        pb.status,
        pb.total,
        pb.sisa_hutang,
        COUNT(pd.id) as jumlah_item
    FROM tb_pembelian pb
    LEFT JOIN tb_supplier s ON pb.id_supplier = s.id
    LEFT JOIN tb_detail_pembelian pd ON pb.id = pd.id_pembelian
    GROUP BY pb.id
    ORDER BY pb.id DESC
");

?>
<!DOCTYPE html>
<html>
<head>
<title>Data Pembelian</title>
<link rel="stylesheet" href="inc/style.css">

<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.header-title { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 20px;}

table { width:100%; border-collapse:collapse; margin-top:15px; background:#fff; }
th, td { border-bottom:1px solid #eee; padding:12px 10px; text-align: left; font-size:13px; }
th { background:#f8fafc; font-weight: 600; color: #475569; }
td.center { text-align: center; }
td.right { text-align: right; }

.btn { padding:8px 15px; border-radius:5px; text-decoration:none; font-size:13px; font-weight: bold; display: inline-block;}
.btn-tambah { background:#2563eb; color:#fff; margin-bottom: 15px; }

.btn-sm { padding:5px 10px; border-radius:4px; text-decoration:none; font-size:12px; color:#fff; display: inline-block;}
.btn-edit { background:#f59e0b; color:#fff; margin-right:4px;}
.btn-delete { background:#ef4444; color:#fff; }

.badge-lunas { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
.badge-hutang { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
.badge-metode { background-color: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 11px; border: 1px solid #cbd5e1; }

.search-box { float:right; }
.search-box input { padding:8px 12px; width:250px; border: 1px solid #ccc; border-radius: 5px; font-size: 13px;}
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">

<div class="card">
    <div class="header-title">Riwayat Transaksi Pembelian</div>
    
    <div>
        <a href="pembelian_tambah.php" class="btn btn-tambah">+ Buat Pembelian Baru</a>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari Faktur atau Supplier...">
        </div>
    </div>

    <table id="tabelPembelian">
        <thead>
            <tr>
                <th>No. Faktur</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th class="center">Item</th>
                <th>Metode</th>
                <th class="right">Total Bayar</th>
                <th class="center">Status</th>
                <th class="center">Aksi</th>
            </tr>
        </thead>
        <tbody>

        <?php 
        if(mysqli_num_rows($q) > 0) {
            while ($row = mysqli_fetch_assoc($q)) { 
                
                // Jika faktur kosong (transaksi lama), beri strip '-'
                $faktur = !empty($row['no_faktur']) ? $row['no_faktur'] : '-';
        ?>
        <tr>
            <td><strong><?= $faktur ?></strong></td>
            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
            <td><?= $row['nama_supplier'] ?? '<i style="color:#999">Tanpa Supplier</i>' ?></td>
            
            <td class="center"><?= $row['jumlah_item'] ?> jns</td>
            
            <td><span class="badge-metode"><?= strtoupper($row['metode']) ?></span></td>
            
            <td class="right"><strong>Rp <?= number_format($row['total'],0,',','.') ?></strong></td>
            
            <td class="center">
                <?php if($row['status'] == 'lunas'): ?>
                    <span class="badge-lunas">LUNAS</span>
                <?php else: ?>
                    <span class="badge-hutang">HUTANG (Sisa Rp<?= number_format($row['sisa_hutang'],0,',','.') ?>)</span>
                <?php endif; ?>
            </td>

            <td class="center">
                <a href="pembelian_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">Lihat/Edit</a>
                <a href="pembelian_hapus.php?id=<?= $row['id'] ?>" 
                   onclick="return confirm('Hapus transaksi beserta detail barang ini? Stok barang juga akan ditarik kembali.')" 
                   class="btn-sm btn-delete">
                   Hapus
                </a>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='8' class='center'>Belum ada transaksi pembelian.</td></tr>";
        }
        ?>

        </tbody>
    </table>

</div>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tabelPembelian tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
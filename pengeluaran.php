<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$data = mysqli_query($conn, "
    SELECT p.*, a.nama_akun 
    FROM tb_pengeluaran p
    JOIN tb_akun a ON p.id_akun = a.id
    ORDER BY p.tanggal DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pengeluaran</title>
    <link rel="stylesheet" href="inc/style.css">

    <style>
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #e5e7eb;
            padding: 10px;
            text-align: center;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <h2>Daftar Pengeluaran</h2>

    <div class="card">

        <a href="pengeluaran_tambah.php" class="btn-primary">
            + Tambah Pengeluaran
        </a>

        <table>
            <tr>
                <th>ID</th>
                <th>Deskripsi</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['keterangan'] ?></td>
                <td><?= $row['nama_akun'] ?></td>
                <td class="text-right">Rp <?= number_format($row['jumlah'],0,',','.') ?></td>
                <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
            </tr>
            <?php } ?>

        </table>

    </div>
</div>

</body>
</html>
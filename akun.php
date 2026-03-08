<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {

    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $tipe = $_POST['tipe'];

    mysqli_query($conn, "
        INSERT INTO tb_akun (kode_akun, nama_akun, tipe)
        VALUES ('$kode','$nama','$tipe')
    ");

    echo "<script>alert('Akun berhasil ditambahkan');</script>";
}

$data = mysqli_query($conn, "SELECT * FROM tb_akun ORDER BY kode_akun ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Akun</title>
    <link rel="stylesheet" href="inc/style.css">
    <style>
        .card {
            background:#fff;
            padding:25px;
            border-radius:10px;
            margin-bottom:20px;
        }

        input, select {
            width:100%;
            padding:8px;
            margin-bottom:10px;
        }

        button {
            background:#2563eb;
            color:#fff;
            padding:8px 15px;
            border:none;
            border-radius:5px;
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th, td {
            padding:8px;
            border-bottom:1px solid #eee;
        }

        th {
            background:#e5e7eb;
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <h2>Master Data Akun</h2>

    <div class="card">
        <form method="POST">
            <input type="text" name="kode" placeholder="Kode Akun (contoh: 501)" required>
            <input type="text" name="nama" placeholder="Nama Akun (contoh: Beban Listrik)" required>

            <select name="tipe" required>
                <option value="">Pilih Tipe</option>
                <option value="aset">Aset</option>
                <option value="kewajiban">Kewajiban</option>
                <option value="modal">Modal</option>
                <option value="pendapatan">Pendapatan</option>
                <option value="beban">Beban</option>
            </select>

            <button type="submit" name="simpan">Tambah Akun</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Akun</h3>
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama Akun</th>
                <th>Tipe</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= $row['kode_akun'] ?></td>
                <td><?= $row['nama_akun'] ?></td>
                <td><?= ucfirst($row['tipe']) ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div>

</body>
</html>
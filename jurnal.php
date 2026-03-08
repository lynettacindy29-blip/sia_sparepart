<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query = mysqli_query($conn, "
    SELECT j.tanggal, a.nama_akun, j.debit, j.kredit
    FROM tb_jurnal j
    JOIN tb_akun a ON j.akun_id = a.id
    WHERE MONTH(j.tanggal) = '$bulan'
    AND YEAR(j.tanggal) = '$tahun'
    ORDER BY j.tanggal ASC
");

$total_debit = 0;
$total_kredit = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jurnal Umum</title>
    <link rel="stylesheet" href="inc/style.css">

    <style>
        .filter-box {
            margin-bottom: 20px;
        }

        .filter-box select, .filter-box button {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
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

        .debit {
            color: green;
            text-align: right;
        }

        .kredit {
            color: red;
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background: #f3f4f6;
        }
    </style>
</head>
<body>

<?php include "inc/sidebar.php"; ?>

<div class="content">
    <h2>Jurnal Umum</h2>

    <div class="filter-box">
        <form method="GET">
            <select name="bulan">
                <?php
                for ($i=1; $i<=12; $i++) {
                    $selected = ($bulan == $i) ? "selected" : "";
                    echo "<option value='$i' $selected>".date("F", mktime(0,0,0,$i,10))."</option>";
                }
                ?>
            </select>

            <select name="tahun">
                <?php
                for ($y=2023; $y<=date('Y'); $y++) {
                    $selected = ($tahun == $y) ? "selected" : "";
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>

            <button type="submit">Tampilkan</button>
        </form>
    </div>

    <table>
        <tr>
            <th width="15%">Tanggal</th>
            <th>Nama Akun</th>
            <th width="20%">Debit</th>
            <th width="20%">Kredit</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($query)) { 
            $total_debit += $row['debit'];
            $total_kredit += $row['kredit'];
        ?>
        <tr>
            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
            <td><?= $row['nama_akun'] ?></td>
            <td class="debit">
                <?= $row['debit'] > 0 ? "Rp ".number_format($row['debit'],0,',','.') : '-' ?>
            </td>
            <td class="kredit">
                <?= $row['kredit'] > 0 ? "Rp ".number_format($row['kredit'],0,',','.') : '-' ?>
            </td>
        </tr>
        <?php } ?>

        <tr class="total-row">
            <td colspan="2" class="text-center">TOTAL</td>
            <td class="debit">Rp <?= number_format($total_debit,0,',','.') ?></td>
            <td class="kredit">Rp <?= number_format($total_kredit,0,',','.') ?></td>
        </tr>
    </table>

</div>

</body>
</html>
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id_detail = $_GET['id'];

/* detail lama */
$d = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT d.*, b.nama_barang 
    FROM tb_pembelian_detail d
    JOIN tb_barang b ON d.barang_id = b.id
    WHERE d.id='$id_detail'
"));

/* simpan edit */
if (isset($_POST['simpan'])) {
    $qty_baru = $_POST['qty'];
    $harga_baru = $_POST['harga_beli'];

    $subtotal_baru = $qty_baru * $harga_baru;

    /* selisih */
    $selisih_qty = $qty_baru - $d['qty'];
    $selisih_total = $subtotal_baru - $d['subtotal'];

    /* update detail */
    mysqli_query($conn, "
        UPDATE tb_pembelian_detail SET
        qty='$qty_baru',
        harga_beli='$harga_baru',
        subtotal='$subtotal_baru'
        WHERE id='$id_detail'
    ");

    /* update stok */
    mysqli_query($conn, "
        UPDATE tb_barang
        SET stok = stok + $selisih_qty
        WHERE id = '{$d['barang_id']}'
    ");

    /* update total pembelian */
    mysqli_query($conn, "
        UPDATE tb_pembelian
        SET total = total + $selisih_total
        WHERE id = '{$d['pembelian_id']}'
    ");

    header("Location: pembelian_detail.php?id={$d['pembelian_id']}");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Detail Pembelian</title>
<link rel="stylesheet" href="inc/style.css">
<style>
body { margin:0; font-family:Arial; background:#f4f6f9; }
.content { margin-left:240px; padding:30px; }
.card { background:#fff; padding:20px; border-radius:8px; }
input { width:100%; padding:8px; margin-top:5px; }
button { background:#ffc107; padding:8px 15px; border:none; border-radius:5px; }
</style>
</head>

<body>
<?php include "sidebar.php"; ?>

<div class="content">
<div class="card">
<h2>Edit Detail Pembelian</h2>

<form method="POST">
    <label>Barang</label>
    <input type="text" value="<?= $d['nama_barang'] ?>" readonly>

    <label>Qty</label>
    <input type="number" name="qty" value="<?= $d['qty'] ?>" required>

    <label>Harga Beli</label>
    <input type="number" name="harga_beli" value="<?= $d['harga_beli'] ?>" required>

    <br><br>
    <button type="submit" name="simpan">Simpan Perubahan</button>
</form>

</div>
</div>
</body>
</html>

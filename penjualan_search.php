<?php
include "config/db.php";

$keyword = '';
$where = '';

if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where = "WHERE 
        nama_pelanggan LIKE '%$keyword%' OR
        nama_barang LIKE '%$keyword%' OR
        status LIKE '%$keyword%' OR
        tanggal LIKE '%$keyword%' OR
        total LIKE '%$keyword%' OR
        laba LIKE '%$keyword%'
    ";
}

$q = mysqli_query($conn, "
    SELECT *
    FROM tb_penjualan
    $where
    ORDER BY id DESC
");
?>

<tr>
    <th>No</th>
    <th>Nama Pelanggan</th>
    <th>Nama Barang</th>
    <th>Harga Pokok</th>
    <th>Harga Jual</th>
    <th>Jumlah</th>
    <th>Total</th>
    <th>Laba</th>
    <th>Status</th>
    <th>Tanggal</th>
    <th>Tgl Batas Kredit</th>
</tr>

<?php
$no = 1;
while ($row = mysqli_fetch_assoc($q)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_pelanggan'] ?></td>
    <td><?= $row['nama_barang'] ?></td>
    <td>Rp <?= number_format($row['harga_pokok'],0,',','.') ?></td>
    <td>Rp <?= number_format($row['harga_jual'],0,',','.') ?></td>
    <td><?= $row['jumlah'] ?></td>
    <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
    <td>Rp <?= number_format($row['laba'],0,',','.') ?></td>
    <td class="<?= $row['status']=='Lunas'?'status-lunas':'status-kredit' ?>">
        <?= $row['status'] ?>
    </td>
    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
    <td>
        <?= $row['tanggal_batas_kredit'] 
            ? date('d-m-Y', strtotime($row['tanggal_batas_kredit'])) 
            : '-' ?>
    </td>
</tr>
<?php } ?>

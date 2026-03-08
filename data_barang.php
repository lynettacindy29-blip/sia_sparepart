<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* ambil kategori */
$qKategori = mysqli_query($conn, "SELECT * FROM tb_kategori");

/* ambil data barang */
$qBarang = mysqli_query($conn, "
    SELECT b.*, k.nama_kategori
    FROM tb_barang b
    LEFT JOIN tb_kategori k ON b.id_kategori = k.id
    ORDER BY b.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Data Barang</title>

<link rel="stylesheet" href="inc/style.css">
<style>

.content {
    margin-left:250px;
    padding:20px;
    background:#eef1f5;
    min-height:100vh;
}
.row {
    display:flex;
    gap:20px;
}
.card {
    background:#fff;
    border-radius:8px;
    padding:15px;
}
.col-3 { width:30%; }
.col-9 { width:75%; }

label {
    font-size:13px;
    margin-top:10px;
    display:block;
}
input, select {
    width:90%;
    padding:8px;
    margin-top:4px;
    border:1px solid #ccc;
    border-radius:5px;
}

button {
    margin-top:15px;
    width:100%;
    padding:10px;
    border:none;
    border-radius:6px;
    background:#2563eb;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    background:#fff;
}
th, td {
    padding:10px;
    border-bottom:1px solid #eee;
    font-size:13px;
}
th {
    background:#f8fafc;
    text-align:left;
}
td {
    vertical-align:middle;
}

.search-box {
    float:right;
    margin-bottom:10px;
}
.search-box input {
    width:200px;
    padding:6px;
}

.aksi-btn {
    display:flex;
    gap:6px;
}
.btn-sm {
    padding:5px 10px;
    font-size:12px;
    border-radius:4px;
    text-decoration:none;
    color:#fff;
}
.btn-edit {
    background:#ffc107;
    color:#000;
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:13px;
    margin-right:4px;
    display:inline-block;
}
.btn-delete {
    background:#dc3545;
    color:#fff;
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:13px;
    display:inline-block;
}

</style>
</head>

<body>


<?php include "sidebar.php"; ?>
<div class="content">

<div class="row">

    <!-- FORM TAMBAH BARANG -->
    <div class="col-3">
        <div class="card">
            <h3>Tambah Barang</h3>

            <form method="POST" action="barang_simpan.php">

                <label>Nama Barang</label>
                <input type="text" name="nama_barang" required>

                <label>Kategori Barang</label>
                <select name="id_kategori" required>
                    <option value="">-- Pilih --</option>
                    <?php while($k = mysqli_fetch_assoc($qKategori)) { ?>
                        <option value="<?= $k['id'] ?>">
                            <?= $k['nama_kategori'] ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Harga Pokok</label>
                <input type="number" name="harga_pokok" required>

                <label>Harga Jual</label>
                <input type="number" name="harga_jual" required>

                <label>Stok Barang</label>
                <input type="number" name="stok" value="0">

                <button type="submit">Tambah Barang</button>

            </form>
        </div>
    </div>

    <!-- TABEL DATA BARANG -->
    <div class="col-9">
        <div class="card">

            <h3>Data Barang</h3>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Cari...">
            </div>

            <table id="tabelBarang">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Category</th>
                        <th>Harga</th>
                        <th>Stok Barang</th>
                     
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php while($row = mysqli_fetch_assoc($qBarang)) { ?>
                    <tr>
                        <td><?= $row['nama_barang'] ?></td>
                        <td><?= $row['nama_kategori'] ?? '-' ?></td>
                        <td>Rp <?= number_format($row['harga_jual'],0,',','.') ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td>
                            <div class="aksi-btn">
                                <a href="barang_edit.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">
                                    Edit
                                </a>
                                <a href="barang_hapus.php?id=<?= $row['id'] ?>"
                                   class="btn-sm btn-delete"
                                   onclick="return confirm('Hapus barang ini?')">
                                   Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>

        </div>
    </div>

</div>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll("#tabelBarang tbody tr").forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>

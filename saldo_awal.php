<?php
include 'config/db.php';

/* ================= SIMPAN DATA ================= */
if (isset($_POST['simpan'])) {

    $kas   = $_POST['kas'];
    $bank  = $_POST['bank'];
    $modal = $_POST['modal'];

    $nama_barang = $_POST['nama_barang'];
    $qty         = $_POST['qty'];
    $harga       = $_POST['harga_pokok'];

    $total_persediaan = 0;

    for ($i = 0; $i < count($nama_barang); $i++) {
        if ($nama_barang[$i] != '') {

            $subtotal = $qty[$i] * $harga[$i];
            $total_persediaan += $subtotal;

            mysqli_query($conn, "
                INSERT INTO tb_barang (nama_barang, stok, harga_pokok)
                VALUES (
                    '{$nama_barang[$i]}',
                    '{$qty[$i]}',
                    '{$harga[$i]}'
                )
            ");
        }
    }

    mysqli_query($conn, "
        INSERT INTO tb_saldo_awal (kas, bank, modal, persediaan)
        VALUES ('$kas', '$bank', '$modal', '$total_persediaan')
    ");

    header("Location: saldo_awal.php?success=1");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saldo Awal</title>
    <link rel="stylesheet" href="inc/style.css">
    <style>
        body { background:#eef2f5; font-family:Arial; }
        .container { margin-left:260px; padding:20px; }
        .card { background:white; padding:20px; border-radius:8px; margin-bottom:20px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #ddd; padding:8px; text-align:center; }
        input { width:95%; padding:6px; }
        button { padding:8px 15px; border:none; border-radius:5px; cursor:pointer; }
        .btn-green { background:#28a745; color:white; }
        .btn-blue { background:#007bff; color:white; }
    </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="container">
<form method="post">

    <!-- FORM SALDO AWAL -->
    <div class="card">
        <h2>Form Saldo Awal</h2>
        <table>
            <tr>
                <th>Kas</th>
                <th>Bank</th>
                <th>Modal</th>
                <th>Persediaan</th>
            </tr>
            <tr>
                <td><input type="number" name="kas"></td>
                <td><input type="number" name="bank"></td>
                <td><input type="number" name="modal"></td>
                <td><input type="number" id="persediaan" readonly></td>
            </tr>
        </table>
    </div>

    <!-- FORM SALDO AWAL PERSEDIAAN -->
    <div class="card">
        <h2>Input Saldo Awal Persediaan</h2>

        <table id="tabel">
            <tr>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga Pokok</th>
                <th>Saldo Akhir</th>
                <th>Aksi</th>
            </tr>

            <tr>
                <td><input type="text" name="nama_barang[]"></td>
                <td><input type="number" name="qty[]" onkeyup="hitung(this)"></td>
                <td><input type="number" name="harga_pokok[]" onkeyup="hitung(this)"></td>
                <td><input type="number" class="saldo" readonly></td>
                <td>-</td>
            </tr>

            <tr>
                <td colspan="3"><b>Total Saldo Akhir</b></td>
                <td colspan="2">Rp <span id="total">0</span></td>
            </tr>
        </table>

        <br>
        <button type="button" class="btn-green" onclick="tambahBaris()">Tambah Baris</button>
        <button type="submit" name="simpan" class="btn-blue">Simpan</button>
    </div>

</form>
</div>

<script>
function tambahBaris() {
    let tabel = document.getElementById("tabel");
    let row = tabel.insertRow(tabel.rows.length - 1);

    row.innerHTML = `
        <td><input type="text" name="nama_barang[]"></td>
        <td><input type="number" name="qty[]" onkeyup="hitung(this)"></td>
        <td><input type="number" name="harga_pokok[]" onkeyup="hitung(this)"></td>
        <td><input type="number" class="saldo" readonly></td>
        <td>-</td>
    `;
}

function hitung(el) {
    let row = el.closest("tr");
    let qty = row.querySelector('[name="qty[]"]').value;
    let harga = row.querySelector('[name="harga_pokok[]"]').value;

    let saldo = (qty * harga) || 0;
    row.querySelector('.saldo').value = saldo;

    let total = 0;
    document.querySelectorAll('.saldo').forEach(e => {
        total += Number(e.value || 0);
    });

    document.getElementById("total").innerText = total.toLocaleString('id-ID');
    document.getElementById("persediaan").value = total;
}
</script>

</body>
</html>

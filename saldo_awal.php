<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil HANYA Akun Riil (Aset, Liabilitas, Ekuitas) untuk Saldo Awal
$qAkun = mysqli_query($conn, "
    SELECT * FROM tb_akun 
    WHERE kategori IN ('Aset', 'Harta', 'Liabilitas', 'Hutang', 'Kewajiban', 'Ekuitas', 'Modal')
    ORDER BY kode_akun ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Input Saldo Awal</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; }
.card { background:#fff; border-radius:8px; padding:25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
h3 { margin-top:0; color:#333; }
p.desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }

label { font-size:13px; font-weight: bold; color: #475569; display: block; margin-bottom: 5px;}
input[type="date"] { padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 14px; margin-bottom: 20px; width: 250px; }

table { width:100%; border-collapse:collapse; margin-top:10px; margin-bottom: 20px; }
th, td { padding:10px; border-bottom:1px solid #eee; font-size:13px; text-align: left; vertical-align: middle;}
th { background:#f8fafc; color: #475569; font-weight: 600; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;}
td.center, th.center { text-align: center; }

input[type="number"] { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; text-align: right; }
input[type="number"]:focus { outline: none; border-color: #2563eb; }

.btn-simpan { background:#2563eb; color:#fff; padding:12px 25px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; font-size: 14px; width: 100%; }
.btn-simpan:hover { background:#1d4ed8; }

.total-row { font-weight: bold; background: #f1f5f9; }
.total-val { font-size: 16px; color: #0f172a; text-align: right; padding: 15px 10px;}
#status-balance { text-align: center; padding: 10px; font-weight: bold; margin-top: 10px; border-radius: 5px; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Setor Saldo Awal Akun (Beginning Balance)</h3>
        <p class="desc">Masukkan saldo awal untuk masing-masing akun. Pastikan Total Debit dan Total Kredit bernilai sama (Seimbang / Balance) sebelum disimpan.</p>

        <form method="POST" action="saldo_awal_proses.php">
            
            <label>Tanggal Saldo Awal</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-01') ?>" required>

            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Kode Akun</th>
                        <th style="width: 35%;">Nama Akun</th>
                        <th class="center" style="width: 15%;">Saldo Normal</th>
                        <th style="width: 17.5%; text-align: right;">Debit (Rp)</th>
                        <th style="width: 17.5%; text-align: right;">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($a = mysqli_fetch_assoc($qAkun)): ?>
                    <tr>
                        <td><strong><?= isset($a['kode_akun']) ? $a['kode_akun'] : '-' ?></strong></td>
                        <td>
                            <?= htmlspecialchars($a['nama_akun']) ?>
                            <input type="hidden" name="id_akun[]" value="<?= $a['id'] ?>">
                        </td>
                        <td class="center">
                            <span style="font-size: 11px; padding: 2px 6px; background: #e2e8f0; border-radius: 4px;">
                                <?= isset($a['saldo_normal']) ? strtoupper($a['saldo_normal']) : 'DEBIT' ?>
                            </span>
                        </td>
                        <td>
                            <input type="number" name="debit[]" class="input-debit" value="0" min="0" onkeyup="hitungTotal()" onchange="hitungTotal()">
                        </td>
                        <td>
                            <input type="number" name="kredit[]" class="input-kredit" value="0" min="0" onkeyup="hitungTotal()" onchange="hitungTotal()">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right; padding: 15px 10px;">TOTAL KESELURUHAN</td>
                        <td class="total-val" id="total_debit">0</td>
                        <td class="total-val" id="total_kredit">0</td>
                    </tr>
                </tbody>
            </table>

            <div id="status-balance" style="background: #fef2f2; color: #ef4444;">❌ Belum Balance</div>
            <br>
            <button type="submit" class="btn-simpan" id="btnSimpan">Simpan Saldo Awal ke Jurnal</button>

        </form>
    </div>
</div>

<script>
// Logika JavaScript untuk menghitung otomatis saat user mengetik angka
function hitungTotal() {
    let debits = document.querySelectorAll('.input-debit');
    let kredits = document.querySelectorAll('.input-kredit');
    
    let total_d = 0;
    let total_k = 0;

    debits.forEach(function(item) {
        total_d += parseFloat(item.value) || 0;
    });

    kredits.forEach(function(item) {
        total_k += parseFloat(item.value) || 0;
    });

    // Format angka ke Rupiah
    document.getElementById('total_debit').innerText = total_d.toLocaleString('id-ID');
    document.getElementById('total_kredit').innerText = total_k.toLocaleString('id-ID');

    let statusBox = document.getElementById('status-balance');
    let btnSimpan = document.getElementById('btnSimpan');

    // Cek Balance
    if (total_d === total_k && total_d > 0) {
        statusBox.style.background = '#d1fae5';
        statusBox.style.color = '#065f46';
        statusBox.innerText = '✔️ BALANCE (Seimbang)';
    } else if (total_d === 0 && total_k === 0) {
        statusBox.style.background = '#f1f5f9';
        statusBox.style.color = '#475569';
        statusBox.innerText = '➖ Masukkan Angka Saldo';
    } else {
        statusBox.style.background = '#fef2f2';
        statusBox.style.color = '#ef4444';
        statusBox.innerText = '❌ TIDAK BALANCE (Ada Selisih: ' + Math.abs(total_d - total_k).toLocaleString('id-ID') + ')';
    }
}

// Panggil sekali saat halaman diload
hitungTotal();
</script>

</body>
</html>
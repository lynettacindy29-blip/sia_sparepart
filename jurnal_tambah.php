<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil daftar akun untuk dropdown
$qAkun = mysqli_query($conn, "SELECT * FROM tb_akun ORDER BY kode_akun ASC");
$opsi_akun = "";
while ($a = mysqli_fetch_assoc($qAkun)) {
    $opsi_akun .= "<option value='{$a['id']}'>{$a['kode_akun']} - {$a['nama_akun']}</option>";
}

// Proses jika tombol simpan ditekan
if (isset($_POST['btn_simpan'])) {
    $tanggal = $_POST['tanggal'];
    $keterangan_utama = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Bikin nomor bukti otomatis
    $no_bukti = 'JU-' . date('Ymd', strtotime($tanggal)) . '-' . rand(100,999); 

    $id_akun = $_POST['id_akun'];
    $posisi = $_POST['posisi'];
    $nominal = $_POST['nominal'];

    // Validasi Balance (Debit harus sama dengan Kredit)
    $tot_debit = 0;
    $tot_kredit = 0;

    for ($i = 0; $i < count($id_akun); $i++) {
        if (!empty($id_akun[$i]) && !empty($nominal[$i])) {
            if ($posisi[$i] == 'Debit') {
                $tot_debit += $nominal[$i];
            } else {
                $tot_kredit += $nominal[$i];
            }
        }
    }

    if ($tot_debit != $tot_kredit) {
        $error = "Gagal Simpan! Total Debit (Rp ".number_format($tot_debit).") tidak sama dengan Total Kredit (Rp ".number_format($tot_kredit)."). Jurnal harus BALANCE!";
    } elseif ($tot_debit == 0) {
        $error = "Nominal tidak boleh kosong!";
    } else {
        // Jika Balance, masukkan ke database
        for ($i = 0; $i < count($id_akun); $i++) {
            if (!empty($id_akun[$i]) && !empty($nominal[$i])) {
                $akun_id = $id_akun[$i];
                $nilai = $nominal[$i];
                $debit = ($posisi[$i] == 'Debit') ? $nilai : 0;
                $kredit = ($posisi[$i] == 'Kredit') ? $nilai : 0;

                mysqli_query($conn, "
                    INSERT INTO tb_jurnal (no_bukti, tanggal, keterangan, id_akun, debit, kredit)
                    VALUES ('$no_bukti', '$tanggal', '$keterangan_utama', '$akun_id', '$debit', '$kredit')
                ");
            }
        }
        $sukses = "Jurnal berhasil disimpan dengan No Bukti: $no_bukti";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Jurnal Umum</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left:250px; padding:20px; background:#eef1f5; min-height:100vh; font-family: sans-serif;}
.card { background:#fff; border-radius:8px; padding:30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto;}

/* Header Flexbox untuk Judul dan Tombol Depresiasi */
.header-jurnal { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
h3 { margin: 0; color:#1e293b; }

.btn-auto { background: #f43f5e; color: white; padding: 10px 15px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 12px; transition: 0.3s; }
.btn-auto:hover { background: #e11d48; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; color: #475569; }
.form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box; }

table.tb-input { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px;}
table.tb-input th { background: #f8fafc; padding: 10px; text-align: left; font-size: 13px; border-bottom: 2px solid #cbd5e1;}
table.tb-input td { padding: 8px 5px; }
table.tb-input select, table.tb-input input { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; }

.btn-simpan { background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; font-size: 14px;}
.btn-simpan:hover { background: #1d4ed8; }
.alert-err { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f87171;}
.alert-ok { background: #d1fae5; color: #059669; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #34d399;}
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <div class="header-jurnal">
            <h3>Catat Jurnal Umum Manual</h3>
            <a href="depresiasi_aset.php" class="btn-auto">
                ⚡ Generate Depresiasi Otomatis
            </a>
        </div>
        
        <p style="font-size: 13px; color: #64748b; margin-top: 0; margin-bottom: 20px;">
            Gunakan form ini untuk transaksi manual atau klik tombol merah untuk penyusutan aset.
        </p>

        <?php if(isset($error)) echo "<div class='alert-err'>$error</div>"; ?>
        <?php if(isset($sukses)) echo "<div class='alert-ok'>$sukses</div>"; ?>

        <form method="POST" action="">
            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>Tanggal Transaksi</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group" style="flex:2;">
                    <label>Keterangan / Uraian</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Setoran Modal Awal / Bayar Listrik" required>
                </div>
            </div>

            <table class="tb-input">
                <thead>
                    <tr>
                        <th width="45%">Pilih Akun</th>
                        <th width="20%">Posisi</th>
                        <th width="35%">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=1; $i<=4; $i++) { ?>
                    <tr>
                        <td>
                            <select name="id_akun[]">
                                <option value="">-- Kosongkan jika tidak dipakai --</option>
                                <?= $opsi_akun ?>
                            </select>
                        </td>
                        <td>
                            <select name="posisi[]">
                                <option value="Debit">Debit</option>
                                <option value="Kredit">Kredit</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="nominal[]" placeholder="0" min="0">
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <button type="submit" name="btn_simpan" class="btn-simpan">💾 Simpan Jurnal Manual</button>
        </form>
    </div>
</div>

</body>
</html>
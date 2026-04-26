<?php
session_start();
include "config/db.php";

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// =========================================================
// LOGIKA UNTUK MENYIMPAN PERUBAHAN (KETIKA TOMBOL SIMPAN DIKLIK)
// =========================================================
if (isset($_POST['btn_simpan'])) { 
    $id_akun = intval($_POST['id']);
    $kode_akun = mysqli_real_escape_string($conn, $_POST['kode_akun']);
    $nama_akun = mysqli_real_escape_string($conn, $_POST['nama_akun']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);

    $query_update = "UPDATE tb_akun SET 
                        kode_akun = '$kode_akun', 
                        nama_akun = '$nama_akun', 
                        kategori = '$kategori' 
                     WHERE id = $id_akun";

    if (mysqli_query($conn, $query_update)) {
        // Jika sukses, kembalikan ke halaman daftar akun dengan pesan sukses
        header("Location: akun.php?pesan=edit_sukses");
        exit;
    } else {
        $error_msg = "Gagal mengupdate data: " . mysqli_error($conn);
    }
}

// =========================================================
// LOGIKA UNTUK MENAMPILKAN DATA LAMA DI FORM
// =========================================================
if (!isset($_GET['id'])) {
    // Jika tidak ada ID di URL, lempar kembali ke halaman akun
    header("Location: akun.php");
    exit;
}

$id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM tb_akun WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Error: Data akun tidak ditemukan di database.");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Akun</title>
<link rel="stylesheet" href="inc/style.css">
<style>
.content { margin-left: 250px; padding: 20px; background: #eef1f5; min-height: 100vh; }
.card { background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 500px; margin: 0 auto; }
h3 { margin-top: 0; color: #1e293b; text-align: center; margin-bottom: 25px; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #475569; }
.form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #2563eb; }

.btn-group { display: flex; gap: 10px; margin-top: 25px; }
.btn-simpan { flex: 1; background: #2563eb; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; text-align: center; }
.btn-simpan:hover { background: #1d4ed8; }
.btn-batal { flex: 1; background: #64748b; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; text-align: center; text-decoration: none; }
.btn-batal:hover { background: #475569; }

.alert { padding: 10px; background: #fee2e2; color: #dc2626; border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #f87171; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <div class="card">
        <h3>Edit Data Akun</h3>

        <?php if(isset($error_msg)): ?>
            <div class="alert"><?= $error_msg ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <div class="form-group">
                <label>Kode Akun</label>
                <input type="text" name="kode_akun" value="<?= htmlspecialchars($data['kode_akun']) ?>" required>
            </div>

            <div class="form-group">
                <label>Nama Akun</label>
                <input type="text" name="nama_akun" value="<?= htmlspecialchars($data['nama_akun']) ?>" required>
            </div>

            <div class="form-group">
                <label>Kategori (Klasifikasi Neraca/Laba Rugi)</label>
                <select name="kategori" required>
                    <option value="Aset" <?= ($data['kategori'] == 'Aset' || $data['kategori'] == 'Harta') ? 'selected' : '' ?>>Aset / Harta</option>
                    <option value="Liabilitas" <?= ($data['kategori'] == 'Liabilitas' || $data['kategori'] == 'Hutang') ? 'selected' : '' ?>>Liabilitas / Kewajiban</option>
                    <option value="Ekuitas" <?= ($data['kategori'] == 'Ekuitas' || $data['kategori'] == 'Modal') ? 'selected' : '' ?>>Ekuitas / Modal</option>
                    <option value="Pendapatan" <?= ($data['kategori'] == 'Pendapatan') ? 'selected' : '' ?>>Pendapatan</option>
                    <option value="Beban" <?= ($data['kategori'] == 'Beban') ? 'selected' : '' ?>>Beban / Biaya</option>
                </select>
            </div>

            <div class="btn-group">
                <a href="akun.php" class="btn-batal">Batal</a>
                <button type="submit" name="btn_simpan" class="btn-simpan">Simpan Perubahan</button>
            </div>
        </form>

    </div>
</div>

</body>
</html>
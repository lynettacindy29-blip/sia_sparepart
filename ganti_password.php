<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$message = "";

if (isset($_POST['submit'])) {

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    $query = mysqli_query($conn, "SELECT password FROM tb_users WHERE id='$id_user'");
    $data = mysqli_fetch_assoc($query);

    if (!$data) {
        $message = "<div class='alert error'>User tidak ditemukan!</div>";
    }
    elseif (!password_verify($password_lama, $data['password'])) {
        $message = "<div class='alert error'>Password lama salah!</div>";
    }
    elseif ($password_baru != $konfirmasi) {
        $message = "<div class='alert error'>Konfirmasi password tidak cocok!</div>";
    }
    elseif (strlen($password_baru) < 6) {
        $message = "<div class='alert error'>Password minimal 6 karakter!</div>";
    }
    else {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

        mysqli_query($conn, "
            UPDATE tb_users 
            SET password='$password_hash' 
            WHERE id='$id_user'
        ");

        $message = "<div class='alert success'>Password berhasil diganti!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ganti Password</title>

    <!-- CSS UTAMA -->
    <link rel="stylesheet" href="inc/style.css">

    <!-- CSS TAMBAHAN KHUSUS HALAMAN INI -->
    <style>

    .content h2 {
        margin-bottom: 25px;
    }

    .card {
        background: #ffffff;
        border-radius: 14px;
        padding: 35px;
        width: 450px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        margin-top: 10px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #374151;
    }

    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 14px;
        transition: 0.2s;
    }

    .form-group input:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
    }

    .btn-primary {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(37, 99, 235, 0.3);
    }

    .btn-back {
        width: 100%;
        padding: 12px;
        margin-top: 12px;
        border-radius: 10px;
        border: none;
        background: #6b7280;
        color: white;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-back:hover {
        background: #4b5563;
    }

    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert.success {
        background: #dcfce7;
        color: #166534;
    }

    .alert.error {
        background: #fee2e2;
        color: #991b1b;
    }

    </style>

</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <h2 class="form-title">Ganti Password</h2>

    <?= $message ?>

    <div class="card">
        <form method="POST">

    <div class="form-group">
        <label>Password Lama</label>
        <input type="password" name="password_lama" required>
    </div>

    <div class="form-group">
        <label>Password Baru</label>
        <input type="password" name="password_baru" required>
    </div>

    <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="konfirmasi" required>
    </div>

    <button type="submit" name="submit" class="btn-primary">
        Ganti Password
    </button>

    <a href="dashboard.php">
        <button type="button" class="btn-back">
            Kembali
        </button>
    </a>

</form>
    </div>
</div>

</body>
</html>

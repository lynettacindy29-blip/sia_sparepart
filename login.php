<?php
session_start();
include "config/db.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM tb_users WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Akuntansi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-wrapper { display: flex; width: 900px; max-width: 95%; background: #fff; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden; }
        .login-visual { flex: 1; background: linear-gradient(135deg, #4F46E5 0%, #312E81 100%); color: #fff; padding: 40px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; }
        .login-visual h2 { font-size: 28px; font-weight: 700; margin-bottom: 16px; }
        .login-visual p { font-size: 15px; opacity: 0.8; line-height: 1.6; }
        .login-form-container { flex: 1; padding: 60px 50px; display: flex; flex-direction: column; justify-content: center; }
        .login-form-container h3 { font-size: 24px; font-weight: 700; color: #1F2937; margin-bottom: 8px; }
        .login-form-container p.subtitle { color: #6B7280; margin-bottom: 32px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; transition: 0.2s; outline: none; }
        .form-control:focus { border-color: #4F46E5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .login-btn { width: 100%; padding: 14px; background: #4F46E5; color: white; font-weight: 600; font-size: 15px; border: none; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .login-btn:hover { background: #4338CA; }
        .login-btn:active { transform: scale(0.98); }
        .alert-error { background: #FEE2E2; color: #B91C1C; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; border-left: 4px solid #DC2626; }
        @media (max-width: 768px) { .login-wrapper { flex-direction: column; } .login-visual { padding: 40px 20px; } .login-form-container { padding: 40px 20px; } }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-visual">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:20px;">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <h2>SIA Sparepart</h2>
            <p>Sistem Informasi Akuntansi & Manajemen Inventory yang modern, cepat, dan profesional.</p>
        </div>
        <div class="login-form-container">
            <h3>Selamat Datang</h3>
            <p class="subtitle">Silakan login ke akun Anda untuk melanjutkan.</p>

            <?php if (isset($error)) { ?>
                <div class="alert-error"><?= $error ?></div>
            <?php } ?>

            <form method="post">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Contoh: admin@sia.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
                </div>
                <button type="submit" name="login" class="login-btn">Masuk Sistem</button>
            </form>
        </div>
    </div>
</body>
</html>

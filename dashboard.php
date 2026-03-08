<?php
session_start();
include "config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$q = mysqli_query($conn, "SELECT SUM(total) AS total_penjualan FROM tb_penjualan");
if ($q) {
    $data = mysqli_fetch_assoc($q);
    $total_penjualan = $data['total_penjualan'] ?? 0;
} else {
    $total_penjualan = 0;
}

$admin_name = $_SESSION['nama'] ?? "Administrator";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Informasi Akuntansi</title>
    <link rel="stylesheet" href="inc/style.css">
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-wrapper">
    <div class="topbar">
        <div class="topbar-title">Overview Dashboard</div>
        <div class="user-profile" style="font-weight:500; font-size:14px; color:#4B5563; display:flex; align-items:center; gap:12px;">
            <div style="text-align: right;">
                <div style="color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($admin_name) ?></div>
                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($_SESSION['role'] ?? 'Admin') ?></div>
            </div>
            <div style="width:40px; height:40px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">
                <?= strtoupper(substr($admin_name, 0, 1)) ?>
            </div>
        </div>
    </div>

    <div class="content">
        <h2 style="font-size:24px; font-weight:700; margin-bottom:24px;">Ringkasan Data</h2>

        <div class="dashboard-grid">
            <div class="card stat-card">
                <div class="stat-info">
                    <p>Total Penjualan</p>
                    <h2>Rp <?= number_format($total_penjualan, 0, ',', '.') ?></h2>
                </div>
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>

            <div class="card stat-card">
                <div class="stat-info">
                    <p>Status Sistem</p>
                    <h2 style="font-size:24px; color: #4F46E5;">Online & Aktif</h2>
                </div>
                <div class="stat-icon" style="background: rgba(79, 70, 229, 0.1); color: #4F46E5;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>
            
            <div class="card" style="display:flex; flex-direction:column; justify-content:center; align-items:flex-start;">
                <h3 style="font-size:16px; margin-bottom:12px; font-weight:600; color:var(--text-main);">Akses Cepat</h3>
                <a href="laporan_keuangan.php" class="btn btn-primary" style="width:100%;">
                    Lihat Laporan Lengkap
                    <svg style="margin-left:8px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>

        <!-- Optional placeholder table section for a fuller dashboard feel -->
        <h2 style="font-size:20px; font-weight:700; margin-bottom:16px; margin-top:32px;">Aktivitas Terbaru</h2>
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:24px; color:var(--text-muted); text-align:center; font-size:14px; background:#f9fafb;">
                Belum ada aktivitas terbaru hari ini.
            </div>
        </div>
    </div>
</div>

</body>
</html>

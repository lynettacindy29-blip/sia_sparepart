<?php
// Mendapatkan nama file yang sedang diakses (misal: "data_barang.php")
$current_page = basename($_SERVER['PHP_SELF']);

// Mendefinisikan kelompok menu
$menu_master = ['data_barang.php', 'data_kategori.php', 'akun.php', 'supplier.php', 'barang_edit.php'];
$menu_transaksi = ['penjualan.php', 'pembelian.php', 'hutang_pembelian.php', 'pengeluaran.php', 'piutang_penjualan.php', 'histori_hutang.php', 'histori_piutang.php'];
$menu_laporan = ['kartu_stok.php', 'saldo_awal.php', 'jurnal.php', 'jurnal_tambah.php', 'buku_besar.php', 'laporan_laba_rugi.php', 'laporan_neraca.php', 'laporan_stok.php'];
?>

<div class="sidebar">
    <div class="sidebar-brand">
        <svg style="margin-right: 12px;" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
        SIA <span>MotoSP</span> 
    </div>

    <div style="padding: 18px 24px 8px; font-size: 11px; text-transform: uppercase; font-weight: 600; color: #9CA3AF; letter-spacing: 0.05em;">Menu Utama</div>
    
    <a href="dashboard.php" class="sidebar-item <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" style="justify-content: flex-start;">
        <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        Dashboard 
    </a>

    <?php $is_master = in_array($current_page, $menu_master); ?>
    <div class="sidebar-item" onclick="toggleMenu('master', this)">
        <div style="display: flex; align-items: center;">
            <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            Data Master
        </div>
        <svg class="chevron" style="<?= $is_master ? 'transform: rotate(180deg);' : '' ?>" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </div>
    <div class="submenu" id="master" style="<?= $is_master ? 'display: block;' : '' ?>">
        <a href="data_barang.php" class="<?= ($current_page == 'data_barang.php' || $current_page == 'barang_edit.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> Data Barang</a>
        <a href="data_kategori.php" class="<?= ($current_page == 'data_kategori.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg> Data Kategori</a>
        <a href="akun.php" class="<?= ($current_page == 'akun.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg> Data Akun</a>
        <a href="supplier.php" class="<?= ($current_page == 'supplier.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg> Data Supplier</a>
    </div>

    <?php $is_transaksi = in_array($current_page, $menu_transaksi); ?>
    <div class="sidebar-item" onclick="toggleMenu('transaksi', this)">
        <div style="display: flex; align-items: center;">
            <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Transaksi
        </div>
        <svg class="chevron" style="<?= $is_transaksi ? 'transform: rotate(180deg);' : '' ?>" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </div>
    <div class="submenu" id="transaksi" style="<?= $is_transaksi ? 'display: block;' : '' ?>">
        <a href="penjualan.php" class="<?= ($current_page == 'penjualan.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg> Penjualan</a>
        <a href="pembelian.php" class="<?= ($current_page == 'pembelian.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg> Pembelian</a>
        <a href="hutang_pembelian.php" class="<?= ($current_page == 'hutang_pembelian.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg> Pembayaran Utang</a>
        <a href="pengeluaran.php" class="<?= ($current_page == 'pengeluaran.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg> Pengeluaran Lain</a>
        <a href="piutang_penjualan.php" class="<?= ($current_page == 'piutang_penjualan.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg> Penerimaan Piutang</a>
        <a href="histori_hutang.php" class="<?= ($current_page == 'histori_hutang.php') ? 'active' : '' ?>" style="padding-left: 40px; font-size: 13px;">↳ Histori Bayar Hutang</a>
        <a href="histori_piutang.php" class="<?= ($current_page == 'histori_piutang.php') ? 'active' : '' ?>" style="padding-left: 40px; font-size: 13px;">↳ Histori Terima Piutang</a>
    </div>

    <?php $is_laporan = in_array($current_page, $menu_laporan); ?>
    <div class="sidebar-item" onclick="toggleMenu('laporan', this)">
        <div style="display: flex; align-items: center;">
            <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Laporan & Akuntansi
        </div>
        <svg class="chevron" style="<?= $is_laporan ? 'transform: rotate(180deg);' : '' ?>" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
    </div>
    <div class="submenu" id="laporan" style="<?= $is_laporan ? 'display: block;' : '' ?>">
        <a href="kartu_stok.php" class="<?= ($current_page == 'kartu_stok.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg> Kartu Stok</a>
        <a href="saldo_awal.php" class="<?= ($current_page == 'saldo_awal.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg> Setor Saldo Awal</a>
        <a href="jurnal.php" class="<?= ($current_page == 'jurnal.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Jurnal Umum</a>
        <a href="jurnal_tambah.php" class="<?= ($current_page == 'jurnal_tambah.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Tambah Jurnal Manual</a>
        <a href="buku_besar.php" class="<?= ($current_page == 'buku_besar.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg> Buku Besar</a>
        <a href="laporan_laba_rugi.php" class="<?= ($current_page == 'laporan_laba_rugi.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Laba Rugi</a>
        <a href="laporan_neraca.php" class="<?= ($current_page == 'laporan_neraca.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"></line><line x1="22" y1="12" x2="2" y2="12"></line><circle cx="12" cy="12" r="10"></circle></svg> Neraca</a>
        <a href="laporan_piutang.php" class="<?= ($current_page == 'laporan_piutang.php') ? 'active' : '' ?>">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
        <polyline points="14 2 14 8 20 8"></polyline>
        <line x1="16" y1="13" x2="8" y2="13"></line>
        <line x1="16" y1="17" x2="8" y2="17"></line>
        <polyline points="10 9 9 9 8 9"></polyline>
    </svg> Laporan Piutang
</a>

<a href="laporan_hutang.php" class="<?= ($current_page == 'laporan_hutang.php') ? 'active' : '' ?>">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
        <line x1="1" y1="10" x2="23" y2="10"></line>
    </svg> Laporan Hutang
</a>
        <a href="laporan_stok.php" class="<?= ($current_page == 'laporan_stok.php') ? 'active' : '' ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"></line><line x1="22" y1="12" x2="2" y2="12"></line><circle cx="12" cy="12" r="10"></circle></svg> Laporan Nilai Stok</a>
    </div>

    <div style="flex:1;"></div>

    <a href="logout.php" class="logout-btn">
        <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        Logout Akun
    </a>
</div>

<script>
function toggleMenu(id, element) {
    var allMenus = document.querySelectorAll('.submenu');
    var allChevrons = document.querySelectorAll('.sidebar-item .chevron');
    
    allMenus.forEach(function(m) {
        if (m.id !== id) {
            m.style.display = "none";
        }
    });
    
    allChevrons.forEach(function(c) {
        if (c.parentElement !== element) {
            c.style.transform = "rotate(0deg)";
        }
    });

    var menu = document.getElementById(id);
    var chevron = element.querySelector('.chevron');
    
    if (menu.style.display === "block") {
        menu.style.display = "none";
        if (chevron) chevron.style.transform = "rotate(0deg)";
    } else {
        menu.style.display = "block";
        if (chevron) chevron.style.transform = "rotate(180deg)";
    }
}
</script>
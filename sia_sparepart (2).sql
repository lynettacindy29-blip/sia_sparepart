-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 26, 2026 at 03:42 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sia_sparepart`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_akun`
--

CREATE TABLE `tb_akun` (
  `id` int NOT NULL,
  `kode_akun` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_akun` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` enum('aset','liabilitas','modal','pendapatan','beban','hpp') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `saldo_normal` enum('debit','kredit') COLLATE utf8mb4_general_ci NOT NULL,
  `saldo_awal` decimal(15,2) DEFAULT '0.00',
  `tanggal_saldo_awal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_akun`
--

INSERT INTO `tb_akun` (`id`, `kode_akun`, `nama_akun`, `kategori`, `saldo_normal`, `saldo_awal`, `tanggal_saldo_awal`) VALUES
(1, '101', 'Kas', 'aset', 'debit', 0.00, NULL),
(2, '102', 'Bank', 'aset', 'debit', 0.00, NULL),
(3, '103', 'Piutang Usaha', 'aset', 'debit', 0.00, NULL),
(4, '104', 'Persediaan', 'aset', 'debit', 5000000.00, NULL),
(5, '201', 'Utang Usaha', 'liabilitas', 'kredit', 0.00, NULL),
(6, '301', 'Modal', 'modal', 'kredit', 1405000000.00, NULL),
(7, '401', 'Penjualan', 'pendapatan', 'kredit', 0.00, NULL),
(8, '501', 'Beban Listrik', 'beban', 'debit', 0.00, NULL),
(9, '502', 'Beban Gaji', 'beban', 'debit', 0.00, NULL),
(10, '503', 'Beban Operasional', 'beban', 'debit', 0.00, NULL),
(12, '601', 'HPP', 'hpp', 'debit', 0.00, NULL),
(13, '504', 'Beban Transportasi', 'beban', 'debit', 0.00, NULL),
(14, '121', 'Tanah', 'aset', 'debit', 800000000.00, NULL),
(15, '122', 'Bangunan Toko', 'aset', 'debit', 500000000.00, NULL),
(16, '123', 'Kendaraan Operasional', 'aset', 'debit', 35000000.00, NULL),
(17, '124', 'Peralatan Toko & Komputer', 'aset', 'debit', 25000000.00, NULL),
(18, '122.1', 'Akumulasi Penyusutan Bangunan', 'aset', 'debit', 0.00, NULL),
(19, '123.1', 'Akumulasi Penyusutan Kendaraan', 'aset', 'debit', 0.00, NULL),
(20, '124.1', 'Akumulasi Penyusutan Peralatan', 'aset', 'debit', 0.00, NULL),
(21, '511', 'Beban Penyusutan Bangunan', 'aset', 'debit', 0.00, NULL),
(22, '512', 'Beban Penyusutan Kendaraan', 'aset', 'debit', 0.00, NULL),
(23, '513', 'Beban Penyusutan Peralatan', 'aset', 'debit', 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_barang`
--

CREATE TABLE `tb_barang` (
  `id` int NOT NULL,
  `kode_barang` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  `harga_beli` decimal(15,2) DEFAULT NULL,
  `harga_jual` decimal(15,2) DEFAULT NULL,
  `stok` int DEFAULT '0',
  `harga_pokok` double NOT NULL DEFAULT '0',
  `stok_minimal` int DEFAULT '5',
  `stok_awal` int NOT NULL DEFAULT '0',
  `hpp_awal` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_barang`
--

INSERT INTO `tb_barang` (`id`, `kode_barang`, `nama_barang`, `id_kategori`, `harga_beli`, `harga_jual`, `stok`, `harga_pokok`, `stok_minimal`, `stok_awal`, `hpp_awal`) VALUES
(1, 'BRG1772441629', 'Bearing 608', 4, 50277.73, 70000.00, 160, 0, 5, 100, 50000),
(2, 'BRG1772526554', 'mur besi', 2, 4000.00, 8000.00, 0, 0, 5, 0, 4000),
(3, 'BRG1773072345', 'Oli Motul', 4, 60000.00, 100000.00, 0, 87500, 5, 0, 60000),
(4, 'BRG1773852301', 'Baut Roofing Screw', 3, 420.00, 850.00, 0, 0, 5, 0, 420),
(5, 'BRG1776865547', 'Bearing Blok', 4, 40000.00, 60000.00, 40, 0, 5, 0, 40000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail_pembelian`
--

CREATE TABLE `tb_detail_pembelian` (
  `id` int NOT NULL,
  `id_pembelian` int NOT NULL,
  `id_barang` int NOT NULL,
  `harga_beli` decimal(15,2) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_detail_pembelian`
--

INSERT INTO `tb_detail_pembelian` (`id`, `id_pembelian`, `id_barang`, `harga_beli`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 50500.00, 100, 5050000.00),
(6, 8, 5, 40000.00, 50, 2000000.00),
(7, 10, 1, 50277.00, 10, 502770.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail_penjualan`
--

CREATE TABLE `tb_detail_penjualan` (
  `id` int NOT NULL,
  `id_penjualan` int DEFAULT NULL,
  `id_barang` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_detail_penjualan`
--

INSERT INTO `tb_detail_penjualan` (`id`, `id_penjualan`, `id_barang`, `qty`, `harga`, `subtotal`) VALUES
(2, 5, 1, 20, 70000.00, 1400000.00),
(3, 6, 1, 20, 70000.00, 1400000.00),
(4, 8, 5, 10, 60000.00, 600000.00),
(5, 9, 1, 10, 70000.00, 700000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_jurnal`
--

CREATE TABLE `tb_jurnal` (
  `id` int NOT NULL,
  `no_bukti` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `id_akun` int DEFAULT NULL,
  `debit` decimal(15,2) DEFAULT '0.00',
  `kredit` decimal(15,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jurnal`
--

INSERT INTO `tb_jurnal` (`id`, `no_bukti`, `tanggal`, `keterangan`, `id_akun`, `debit`, `kredit`) VALUES
(1, 'SA-20260228-617', '2026-02-28', 'Saldo Awal Periode Terkini', 1, 50000000.00, 0.00),
(3, 'SA-20260228-617', '2026-02-28', 'Saldo Awal Periode Terkini', 5, 0.00, 10000000.00),
(4, 'SA-20260228-617', '2026-02-28', 'Saldo Awal Periode Terkini', 6, 0.00, 0.00),
(9, 'JB-20260326-0001', '2026-03-26', 'Pembelian Barang (PB-20260326-0001)', 4, 5050000.00, 0.00),
(10, 'JB-20260326-0001', '2026-03-26', 'Pembelian Barang (PB-20260326-0001)', 5, 0.00, 5050000.00),
(11, 'PH-20260326-539', '2026-03-26', 'Pembayaran Hutang Fak: PB-20260326-0001 (Bayar 1)', 5, 3500000.00, 0.00),
(12, 'PH-20260326-539', '2026-03-26', 'Pembayaran Hutang Fak: PB-20260326-0001 (Bayar 1)', 1, 0.00, 3500000.00),
(21, 'BKK-20260304-53', '2026-03-04', 'Biaya Operasional: Biaya Listrik Februari', 8, 800000.00, 0.00),
(22, 'BKK-20260304-53', '2026-03-04', 'Biaya Operasional: Biaya Listrik Februari', 1, 0.00, 800000.00),
(23, 'JJ-20260407-0005', '2026-04-07', 'Penjualan (PJ-20260407-0005) - PT LAJ', 3, 1400000.00, 0.00),
(24, 'JJ-20260407-0005', '2026-04-07', 'Penjualan (PJ-20260407-0005) - PT LAJ', 7, 0.00, 1400000.00),
(25, 'JJ-20260407-0005', '2026-04-07', 'Harga Pokok Penjualan (PJ-20260407-0005)', 12, 1005555.60, 0.00),
(26, 'JJ-20260407-0005', '2026-04-07', 'Harga Pokok Penjualan (PJ-20260407-0005)', 4, 0.00, 1005555.60),
(27, 'TP-20260407-725', '2026-04-07', 'Penerimaan Piutang Nota: PJ-20260407-0005 (Cicilan 1)', 1, 700000.00, 0.00),
(28, 'TP-20260407-725', '2026-04-07', 'Penerimaan Piutang Nota: PJ-20260407-0005 (Cicilan 1)', 3, 0.00, 700000.00),
(29, 'JJ-20260326-0006', '2026-03-26', 'Penjualan (PJ-20260326-0006) - PT LAJ', 1, 1400000.00, 0.00),
(30, 'JJ-20260326-0006', '2026-03-26', 'Penjualan (PJ-20260326-0006) - PT LAJ', 7, 0.00, 1400000.00),
(31, 'JJ-20260326-0006', '2026-03-26', 'Harga Pokok Penjualan (PJ-20260326-0006)', 12, 1005555.60, 0.00),
(32, 'JJ-20260326-0006', '2026-03-26', 'Harga Pokok Penjualan (PJ-20260326-0006)', 4, 0.00, 1005555.60),
(33, 'JB-20260422-0008', '2026-04-22', 'Pembelian Barang (PB-20260422-0008)', 4, 2000000.00, 0.00),
(34, 'JB-20260422-0008', '2026-04-22', 'Pembelian Barang (PB-20260422-0008)', 1, 0.00, 2000000.00),
(35, 'JJ-20260422-0008', '2026-04-22', 'Penjualan (PJ-20260422-0008) - PT FEB', 3, 600000.00, 0.00),
(36, 'JJ-20260422-0008', '2026-04-22', 'Penjualan (PJ-20260422-0008) - PT FEB', 7, 0.00, 600000.00),
(37, 'JJ-20260422-0008', '2026-04-22', 'Harga Pokok Penjualan (PJ-20260422-0008)', 12, 400000.00, 0.00),
(38, 'JJ-20260422-0008', '2026-04-22', 'Harga Pokok Penjualan (PJ-20260422-0008)', 4, 0.00, 400000.00),
(41, 'PH-20260422-26', '2026-04-22', 'Pelunasan Utang ke PT Yaho (Saldo Awal)', 5, 6000000.00, 0.00),
(42, 'PH-20260422-26', '2026-04-22', 'Pelunasan Utang ke PT Yaho (Saldo Awal)', 1, 0.00, 6000000.00),
(43, 'PH-20260422-57', '2026-04-22', 'Pelunasan Utang ke PT AKJ (Saldo Awal)', 5, 1000000.00, 0.00),
(44, 'PH-20260422-57', '2026-04-22', 'Pelunasan Utang ke PT AKJ (Saldo Awal)', 1, 0.00, 1000000.00),
(45, 'JJ-20260423-0009', '2026-04-23', 'Penjualan (PJ-20260423-0009) - PT FEB', 3, 700000.00, 0.00),
(46, 'JJ-20260423-0009', '2026-04-23', 'Penjualan (PJ-20260423-0009) - PT FEB', 7, 0.00, 700000.00),
(47, 'JJ-20260423-0009', '2026-04-23', 'Harga Pokok Penjualan (PJ-20260423-0009)', 12, 502777.80, 0.00),
(48, 'JJ-20260423-0009', '2026-04-23', 'Harga Pokok Penjualan (PJ-20260423-0009)', 4, 0.00, 502777.80),
(49, 'JB-20260423-0010', '2026-04-23', 'Pembelian Barang (PB-20260423-0010)', 4, 502770.00, 0.00),
(50, 'JB-20260423-0010', '2026-04-23', 'Pembelian Barang (PB-20260423-0010)', 5, 0.00, 502770.00),
(51, 'TP-20260423-146', '2026-04-23', 'Penerimaan Piutang Nota: PJ-20260423-0009 (Cicilan 2)', 1, 350000.00, 0.00),
(52, 'TP-20260423-146', '2026-04-23', 'Penerimaan Piutang Nota: PJ-20260423-0009 (Cicilan 2)', 3, 0.00, 350000.00),
(53, 'PH-20260424-48', '2026-04-24', 'Pelunasan Utang ke PT AKJ (Faktur: PB-20260326-0001)', 5, 550000.00, 0.00),
(54, 'PH-20260424-48', '2026-04-24', 'Pelunasan Utang ke PT AKJ (Faktur: PB-20260326-0001)', 1, 0.00, 550000.00),
(55, 'TP-20260424-470', '2026-04-24', 'Penerimaan Piutang Nota: PJ-20260422-0008 (Cicilan 2)', 1, 300000.00, 0.00),
(56, 'TP-20260424-470', '2026-04-24', 'Penerimaan Piutang Nota: PJ-20260422-0008 (Cicilan 2)', 3, 0.00, 300000.00),
(61, 'DEP-2604313', '2026-04-30', 'Penyusutan Bangunan Toko 2026-04', 21, 2083333.00, 0.00),
(62, 'DEP-2604313', '2026-04-30', 'Penyusutan Bangunan Toko 2026-04', 18, 0.00, 2083333.00),
(63, 'DEP-2604313', '2026-04-30', 'Penyusutan Kendaraan Operasional 2026-04', 22, 291667.00, 0.00),
(64, 'DEP-2604313', '2026-04-30', 'Penyusutan Kendaraan Operasional 2026-04', 19, 0.00, 291667.00),
(65, 'DEP-2604313', '2026-04-30', 'Penyusutan Peralatan Toko & Komputer 2026-04', 23, 416667.00, 0.00),
(66, 'DEP-2604313', '2026-04-30', 'Penyusutan Peralatan Toko & Komputer 2026-04', 20, 0.00, 416667.00),
(73, 'DEP-2603374', '2026-03-31', 'Penyusutan Bangunan Toko 2026-03', 21, 2083333.00, 0.00),
(74, 'DEP-2603374', '2026-03-31', 'Penyusutan Bangunan Toko 2026-03', 18, 0.00, 2083333.00),
(75, 'DEP-2603374', '2026-03-31', 'Penyusutan Kendaraan Operasional 2026-03', 22, 291667.00, 0.00),
(76, 'DEP-2603374', '2026-03-31', 'Penyusutan Kendaraan Operasional 2026-03', 19, 0.00, 291667.00),
(77, 'DEP-2603374', '2026-03-31', 'Penyusutan Peralatan Toko & Komputer 2026-03', 23, 416667.00, 0.00),
(78, 'DEP-2603374', '2026-03-31', 'Penyusutan Peralatan Toko & Komputer 2026-03', 20, 0.00, 416667.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_kartu_stok`
--

CREATE TABLE `tb_kartu_stok` (
  `id` int NOT NULL,
  `id_barang` int NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_transaksi` enum('pembelian','penjualan','retur','penyesuaian') COLLATE utf8mb4_general_ci NOT NULL,
  `no_referensi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `qty_masuk` int DEFAULT '0',
  `qty_keluar` int DEFAULT '0',
  `saldo_stok` int NOT NULL,
  `harga_pokok` double NOT NULL DEFAULT '0',
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kartu_stok`
--

INSERT INTO `tb_kartu_stok` (`id`, `id_barang`, `tanggal`, `jenis_transaksi`, `no_referensi`, `qty_masuk`, `qty_keluar`, `saldo_stok`, `harga_pokok`, `keterangan`) VALUES
(1, 1, '2026-03-26', 'penyesuaian', 'EDIT-MANUAL', 100, 0, 100, 0, 'Penyesuaian Stok Manual'),
(2, 1, '2026-03-26', 'penjualan', 'PJ-20260326-0001', 0, 20, 80, 50000, 'Penjualan ke PT LAJ'),
(3, 1, '2026-03-26', 'pembelian', 'PB-20260326-0001', 100, 0, 180, 50500, 'Pembelian dari Supplier'),
(4, 4, '2026-02-20', 'pembelian', 'PB-20260331-0002', 14285, 0, 14285, 420, 'Pembelian dari Supplier'),
(5, 4, '2026-02-20', 'penyesuaian', 'REVERT-PB-20260331-0002', 0, 14285, 0, 0, 'Revisi Pembelian (Tarik Balik)'),
(6, 4, '2026-02-20', 'pembelian', 'PB-20260331-0002', 14286, 0, 14286, 0, 'Revisi Pembelian (Stok Baru)'),
(7, 4, '2026-02-20', 'penyesuaian', 'REVERT-PB-20260331-0002', 0, 14286, 0, 0, 'Revisi Pembelian (Tarik Balik)'),
(8, 4, '2026-02-20', 'pembelian', 'PB-20260331-0002', 100, 0, 100, 0, 'Revisi Pembelian (Stok Baru)'),
(9, 2, '2026-02-23', 'pembelian', 'PB-20260331-0004', 1000, 0, 1000, 4000, 'Pembelian dari Supplier'),
(10, 1, '2026-04-07', 'penjualan', 'PJ-20260407-0005', 0, 20, 160, 50277.78, 'Penjualan ke PT LAJ'),
(11, 1, '2026-03-26', 'penjualan', 'PJ-20260326-0006', 0, 20, 140, 50277.78, 'Penjualan ke PT LAJ'),
(12, 1, '2026-04-07', 'penyesuaian', 'HAPUS-PJ-20260326-0001', 20, 0, 160, 0, 'Pembatalan Transaksi Penjualan'),
(13, 5, '2026-04-22', 'pembelian', 'PB-20260422-0008', 50, 0, 50, 40000, 'Pembelian dari Supplier'),
(14, 5, '2026-04-22', 'penjualan', 'PJ-20260422-0008', 0, 10, 40, 40000, 'Penjualan ke PT FEB'),
(15, 1, '2026-04-23', 'penjualan', 'PJ-20260423-0009', 0, 10, 150, 50277.78, 'Penjualan ke PT FEB'),
(16, 1, '2026-04-23', 'pembelian', 'PB-20260423-0010', 10, 0, 160, 50277, 'Pembelian dari Supplier');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`id`, `nama_kategori`) VALUES
(1, 'Material Bangunan'),
(2, 'Besi Baja'),
(3, 'Baut Mur'),
(4, 'Bearing Mesin'),
(5, 'Elektrikal'),
(6, 'Tools');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembayaran_hutang`
--

CREATE TABLE `tb_pembayaran_hutang` (
  `id` int NOT NULL,
  `id_pembelian` int NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bukti_bayar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembayaran_hutang`
--

INSERT INTO `tb_pembayaran_hutang` (`id`, `id_pembelian`, `tanggal`, `jumlah_bayar`, `keterangan`, `created_at`, `bukti_bayar`) VALUES
(1, 1, '2026-03-26', 3500000.00, 'Bayar 1', '2026-03-26 14:39:37', NULL),
(2, 1, '2026-04-24', 550000.00, 'Pelunasan Utang ke PT AKJ (Faktur: PB-20260326-0001)', '2026-04-24 14:37:34', 'hutang_1777041454_32.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembayaran_piutang`
--

CREATE TABLE `tb_pembayaran_piutang` (
  `id` int NOT NULL,
  `id_penjualan` int NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bukti_bayar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembayaran_piutang`
--

INSERT INTO `tb_pembayaran_piutang` (`id`, `id_penjualan`, `tanggal`, `jumlah_bayar`, `keterangan`, `created_at`, `bukti_bayar`) VALUES
(1, 6, '2026-03-13', 500000.00, 'DP 1', '2026-03-13 16:49:23', NULL),
(2, 6, '2026-03-14', 3000000.00, 'pelunasan', '2026-03-14 01:52:59', NULL),
(3, 5, '2026-04-07', 700000.00, 'Cicilan 1', '2026-04-07 16:24:44', NULL),
(4, 9, '2026-04-23', 350000.00, 'Cicilan 2', '2026-04-23 05:52:05', NULL),
(5, 8, '2026-04-24', 300000.00, 'Cicilan 2', '2026-04-24 14:41:44', 'piutang_1777041704_42.png');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembelian`
--

CREATE TABLE `tb_pembelian` (
  `id` int NOT NULL,
  `no_faktur` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `id_supplier` int DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `metode` enum('tunai','kredit') COLLATE utf8mb4_general_ci DEFAULT 'tunai',
  `status` enum('lunas','belum_lunas') COLLATE utf8mb4_general_ci DEFAULT 'lunas',
  `sisa_hutang` decimal(15,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembelian`
--

INSERT INTO `tb_pembelian` (`id`, `no_faktur`, `tanggal`, `id_supplier`, `total`, `metode`, `status`, `sisa_hutang`) VALUES
(1, 'PB-20260326-0001', '2026-03-26', 2, 5050000.00, 'kredit', 'belum_lunas', 1000000.00),
(8, 'PB-20260422-0008', '2026-04-22', 2, 2000000.00, 'tunai', 'lunas', 0.00),
(10, 'PB-20260423-0010', '2026-04-23', 2, 502770.00, 'kredit', 'belum_lunas', 502770.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_pengeluaran`
--

CREATE TABLE `tb_pengeluaran` (
  `id` int NOT NULL,
  `tanggal` date DEFAULT NULL,
  `id_akun` int DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `jumlah` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pengeluaran`
--

INSERT INTO `tb_pengeluaran` (`id`, `tanggal`, `id_akun`, `keterangan`, `jumlah`) VALUES
(1, '2026-03-04', 8, 'Biaya Listrik Februari', 800000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_penjualan`
--

CREATE TABLE `tb_penjualan` (
  `id` int NOT NULL,
  `no_nota` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `nama_pelanggan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `metode` enum('tunai','kredit') COLLATE utf8mb4_general_ci DEFAULT 'tunai',
  `status_bayar` enum('lunas','belum') COLLATE utf8mb4_general_ci DEFAULT 'lunas',
  `sisa_piutang` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_penjualan`
--

INSERT INTO `tb_penjualan` (`id`, `no_nota`, `tanggal`, `nama_pelanggan`, `total`, `metode`, `status_bayar`, `sisa_piutang`) VALUES
(5, 'PJ-20260407-0005', '2026-04-07', 'PT LAJ', 1400000.00, 'kredit', 'belum', 700000),
(6, 'PJ-20260326-0006', '2026-03-26', 'PT LAJ', 1400000.00, 'tunai', 'lunas', 0),
(8, 'PJ-20260422-0008', '2026-04-22', 'PT FEB', 600000.00, 'kredit', 'belum', 300000),
(9, 'PJ-20260423-0009', '2026-04-23', 'PT FEB', 700000.00, 'kredit', 'belum', 350000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_setting_jurnal`
--

CREATE TABLE `tb_setting_jurnal` (
  `id` int NOT NULL,
  `jenis_transaksi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `akun_debit` int DEFAULT NULL,
  `akun_kredit` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_setting_jurnal`
--

INSERT INTO `tb_setting_jurnal` (`id`, `jenis_transaksi`, `akun_debit`, `akun_kredit`) VALUES
(1, 'pembelian_cash', 4, 1),
(2, 'pembelian_utang', 4, 5),
(3, 'bayar_hutang', 5, 1),
(4, 'penjualan_cash', 1, 7),
(5, 'penjualan_utang', 3, 7),
(6, 'terima_piutang', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tb_supplier`
--

CREATE TABLE `tb_supplier` (
  `id` int NOT NULL,
  `nama_supplier` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `telp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `saldo_awal_utang` decimal(15,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_supplier`
--

INSERT INTO `tb_supplier` (`id`, `nama_supplier`, `alamat`, `telp`, `saldo_awal_utang`) VALUES
(2, 'PT AKJ', 'Solo', '08127569382', 3000000.00),
(3, 'PT Yaho', 'Solo', '081928397493', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_users`
--

CREATE TABLE `tb_users` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','kasir') COLLATE utf8mb4_general_ci DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_users`
--

INSERT INTO `tb_users` (`id`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Administrator', 'admin@gmail.com', '$2y$10$4DDd0d0cn.x9TwAschCHlOb8zRurHnD3XrqFLCmxHsonLO8Zd5fki', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_akun`
--
ALTER TABLE `tb_akun`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_barang`
--
ALTER TABLE `tb_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `tb_detail_pembelian`
--
ALTER TABLE `tb_detail_pembelian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pembelian` (`id_pembelian`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `tb_detail_penjualan`
--
ALTER TABLE `tb_detail_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_penjualan` (`id_penjualan`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `tb_jurnal`
--
ALTER TABLE `tb_jurnal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indexes for table `tb_kartu_stok`
--
ALTER TABLE `tb_kartu_stok`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_pembayaran_hutang`
--
ALTER TABLE `tb_pembayaran_hutang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pembelian` (`id_pembelian`);

--
-- Indexes for table `tb_pembayaran_piutang`
--
ALTER TABLE `tb_pembayaran_piutang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_supplier` (`id_supplier`);

--
-- Indexes for table `tb_pengeluaran`
--
ALTER TABLE `tb_pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indexes for table `tb_penjualan`
--
ALTER TABLE `tb_penjualan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_setting_jurnal`
--
ALTER TABLE `tb_setting_jurnal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_supplier`
--
ALTER TABLE `tb_supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_akun`
--
ALTER TABLE `tb_akun`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tb_barang`
--
ALTER TABLE `tb_barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_detail_pembelian`
--
ALTER TABLE `tb_detail_pembelian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_detail_penjualan`
--
ALTER TABLE `tb_detail_penjualan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_jurnal`
--
ALTER TABLE `tb_jurnal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `tb_kartu_stok`
--
ALTER TABLE `tb_kartu_stok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_pembayaran_hutang`
--
ALTER TABLE `tb_pembayaran_hutang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_pembayaran_piutang`
--
ALTER TABLE `tb_pembayaran_piutang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_pengeluaran`
--
ALTER TABLE `tb_pengeluaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_penjualan`
--
ALTER TABLE `tb_penjualan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_setting_jurnal`
--
ALTER TABLE `tb_setting_jurnal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_supplier`
--
ALTER TABLE `tb_supplier`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_barang`
--
ALTER TABLE `tb_barang`
  ADD CONSTRAINT `tb_barang_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tb_detail_pembelian`
--
ALTER TABLE `tb_detail_pembelian`
  ADD CONSTRAINT `tb_detail_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `tb_pembelian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_detail_pembelian_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `tb_barang` (`id`);

--
-- Constraints for table `tb_detail_penjualan`
--
ALTER TABLE `tb_detail_penjualan`
  ADD CONSTRAINT `tb_detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `tb_penjualan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_detail_penjualan_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `tb_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_jurnal`
--
ALTER TABLE `tb_jurnal`
  ADD CONSTRAINT `tb_jurnal_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `tb_akun` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_pembayaran_hutang`
--
ALTER TABLE `tb_pembayaran_hutang`
  ADD CONSTRAINT `tb_pembayaran_hutang_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `tb_pembelian` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  ADD CONSTRAINT `tb_pembelian_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `tb_supplier` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tb_pengeluaran`
--
ALTER TABLE `tb_pengeluaran`
  ADD CONSTRAINT `tb_pengeluaran_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `tb_akun` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

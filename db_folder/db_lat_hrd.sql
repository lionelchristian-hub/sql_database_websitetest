-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 12:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_lat_hrd`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_catatan`
--

CREATE TABLE `tbl_catatan` (
  `id_catatan` int(11) NOT NULL,
  `id_user` varchar(15) NOT NULL,
  `isi_catatan` text NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_catatan`
--

INSERT INTO `tbl_catatan` (`id_catatan`, `id_user`, `isi_catatan`, `waktu`) VALUES
(1, 'ADM0002', 'SAYA MALAS', '2026-05-20 14:22:47'),
(2, 'ADM0002', 'Jangan lupa memuji King Nasir hari ini', '2026-05-21 07:10:16');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cuti`
--

CREATE TABLE `tbl_cuti` (
  `id_cuti` varchar(15) NOT NULL,
  `id_pegawai` varchar(15) DEFAULT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cuti`
--

INSERT INTO `tbl_cuti` (`id_cuti`, `id_pegawai`, `tgl_mulai`, `tgl_selesai`, `alasan`, `status`, `created_at`) VALUES
('CT202605001', 'PGW202605001', '2026-05-20', '2026-05-21', 'malas', 'disetujui', '2026-05-20 14:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_departemen`
--

CREATE TABLE `tbl_departemen` (
  `id_departemen` varchar(10) NOT NULL,
  `departemen` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_departemen`
--

INSERT INTO `tbl_departemen` (`id_departemen`, `departemen`, `created_at`) VALUES
('DEP001', 'Gabut', '2026-05-18 12:11:34'),
('DEP002', 'SIBUK', '2026-05-21 07:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_izin`
--

CREATE TABLE `tbl_izin` (
  `id_izin` varchar(15) NOT NULL,
  `id_pegawai` varchar(15) DEFAULT NULL,
  `tgl_izin` date DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_izin`
--

INSERT INTO `tbl_izin` (`id_izin`, `id_pegawai`, `tgl_izin`, `alasan`, `status`, `created_at`) VALUES
('CT202605001', 'PGW202605001', '2026-05-21', 'wqsdas', 'ditolak', '2026-05-21 07:23:33');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_jabatan`
--

CREATE TABLE `tbl_jabatan` (
  `id_jabatan` varchar(10) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_jabatan`
--

INSERT INTO `tbl_jabatan` (`id_jabatan`, `jabatan`, `created_at`) VALUES
('DEP001', 'King', '2026-05-18 12:11:39'),
('DEP002', 'KING PRO MAX', '2026-05-21 07:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_log_aktivitas`
--

CREATE TABLE `tbl_log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` varchar(15) NOT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_log_aktivitas`
--

INSERT INTO `tbl_log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `waktu`) VALUES
(1, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-20 12:01:46'),
(2, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-20 13:56:07'),
(3, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-20 13:57:46'),
(4, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-20 13:59:22'),
(5, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-21 07:01:53'),
(6, 'ADM0002', 'Logout dari sistem', '2026-05-21 08:18:16'),
(7, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-21 08:18:26'),
(8, 'ADM0002', 'Logout dari sistem', '2026-05-21 09:10:48'),
(9, 'ADM0002', 'Berhasil Login ke dalam sistem', '2026-05-21 09:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pegawai`
--

CREATE TABLE `tbl_pegawai` (
  `id_pegawai` varchar(15) NOT NULL,
  `id_departemen` varchar(10) DEFAULT NULL,
  `id_jabatan` varchar(10) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gaji` decimal(12,2) DEFAULT NULL,
  `status_pernikahan` enum('Menikah','Belum','Berpisah') DEFAULT NULL,
  `jenis_kelamin` enum('Laki-Laki','Perempuan') DEFAULT NULL,
  `status_kerja` enum('Tetap','Kontrak','Pensiun','Keluar') DEFAULT NULL,
  `jumlah_cuti` int(11) DEFAULT 0,
  `jenjang_pendidikan` enum('SD','SMP','SMA','SMK','D1','D2','D3','D4','S1','S2','S3') DEFAULT NULL,
  `tgl_mulai_kerja` date DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pegawai`
--

INSERT INTO `tbl_pegawai` (`id_pegawai`, `id_departemen`, `id_jabatan`, `nama`, `alamat`, `telepon`, `email`, `gaji`, `status_pernikahan`, `jenis_kelamin`, `status_kerja`, `jumlah_cuti`, `jenjang_pendidikan`, `tgl_mulai_kerja`, `foto`, `created_at`) VALUES
('PGW202605001', 'DEP001', 'DEP001', 'Pepelele', 'oiweqjdfio street', '293874659081', 'bababa@gmail.com', 0.49, 'Menikah', 'Laki-Laki', 'Tetap', 6, 'SMK', '2026-05-18', 'PGW202605001_1779106358.jpg', '2026-05-18 12:12:38'),
('PGW202605002', 'DEP002', 'DEP002', 'King Nasir', 'wqsfcasd', '235234655345', 'adksjfb@gmail.com', 0.46, 'Berpisah', 'Laki-Laki', 'Kontrak', 34, 'S1', '2025-05-21', 'PGW202605002_1779347317.jpg', '2026-05-21 07:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penghargaan`
--

CREATE TABLE `tbl_penghargaan` (
  `id_penghargaan` varchar(15) NOT NULL,
  `id_pegawai` varchar(15) DEFAULT NULL,
  `tgl_penghargaan` date DEFAULT NULL,
  `nama_penghargaan` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_penghargaan`
--

INSERT INTO `tbl_penghargaan` (`id_penghargaan`, `id_pegawai`, `tgl_penghargaan`, `nama_penghargaan`, `keterangan`, `created_at`) VALUES
('SP202605001', 'PGW202605001', '2026-05-20', 'PALING MALAS', 'HEBAD KAMU DEK', '2026-05-20 14:17:34'),
('SP202605002', 'PGW202605002', '2026-05-21', 'KING OF ALL KING', 'SANGAT INSPIRASIONAL', '2026-05-21 07:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengumuman`
--

CREATE TABLE `tbl_pengumuman` (
  `id_pengumuman` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pengumuman`
--

INSERT INTO `tbl_pengumuman` (`id_pengumuman`, `judul`, `isi`, `tanggal`) VALUES
(1, 'Pembaruan Data Profil Pegawai', 'Halo seluruh pegawai, mohon luangkan waktu sejenak untuk melengkapi nama dan memperbarui foto profil Anda melalui menu Pengaturan Profil demi penataan arsip HRD.', '2026-05-20 12:03:32'),
(2, 'Pengumuman Libur Nataru', 'Halo seluruh pegawai, pada tanggal 25 Desember sampai 5 Januari perusahaan ini libur. jadi kalok mau kerja lebih baik cari kerja yang lain ya (sama jangan ganggu bos).', '2026-05-20 12:12:44'),
(3, 'Waspadalah terhadap pesan penipuan!!!!', 'Halo seluruh pegawai, mohon berwaspada terhadap pesan-pesan penipuan dan juga link-link yang misterius dan berbahaya, jangan pakai komputer kantor untuk buka web aneh-aneh ya dek.', '2026-05-20 13:03:18'),
(4, 'Perhatian Penting sekali WAJIB DIBACA!!!', 'Halo seluruh pegawai, bos kalian ganteng banget lho.', '2026-05-20 13:04:01'),
(5, 'Pemberitahuan mengenai pemberian gaji', 'Halo seluruh pegawai, mohon luangkan waktu untuk menghitung gaji yang diberikan agar gaji yang diterima memiliki nominal yang sesuai, hanya menerima kelebihan gaji, bukan kekurangan nilai gaji!.', '2026-05-20 13:05:22');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_peringatan`
--

CREATE TABLE `tbl_peringatan` (
  `id_peringatan` varchar(15) NOT NULL,
  `id_pegawai` varchar(15) DEFAULT NULL,
  `tgl_peringatan` date DEFAULT NULL,
  `jenis` enum('SP1','SP2','SP3') DEFAULT 'SP1',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_peringatan`
--

INSERT INTO `tbl_peringatan` (`id_peringatan`, `id_pegawai`, `tgl_peringatan`, `jenis`, `keterangan`, `created_at`) VALUES
('SP202605001', 'PGW202605001', '2026-05-21', 'SP1', 'KAMU MALAS', '2026-05-21 07:11:07');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_usaha`
--

CREATE TABLE `tbl_usaha` (
  `id_usaha` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `npwp` varchar(30) DEFAULT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `noaccount` varchar(50) DEFAULT NULL,
  `atasnama` varchar(100) DEFAULT NULL,
  `pimpinan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_usaha`
--

INSERT INTO `tbl_usaha` (`id_usaha`, `nama`, `alamat`, `nomor_telepon`, `fax`, `email`, `npwp`, `bank`, `noaccount`, `atasnama`, `pimpinan`, `created_at`) VALUES
(0, 'Pepelele', 'ilakek dn street', '3214871235', 'fahh', 'bababa@gmail.com', '89032750978', 'Jago', '32145213465', 'Irwan', 'Iwan', '2026-05-18 12:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` varchar(15) NOT NULL,
  `nama_user` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','hrd','manager','staff') DEFAULT 'staff',
  `nama` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `nama_user`, `password`, `role`, `nama`, `foto`, `created_at`) VALUES
('ADM0001', 'Lele', '$2y$10$u7ky8NhYqjROb8uzVKp8tessBteq09mjKgkkQ/WLilqrE6NmtkPVu', 'admin', 'Pepelele', NULL, '2026-05-18 11:48:09'),
('ADM0002', 'Pepe', '$2y$10$w.H7arUvCdRkFmOaRmLo3uPcMC4vSnEoeWg/ybuyVrmIq7tNrM4du', 'admin', 'Araema', '1779354591_6a0ecbdf9014e.jpg', '2026-05-18 11:48:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_catatan`
--
ALTER TABLE `tbl_catatan`
  ADD PRIMARY KEY (`id_catatan`);

--
-- Indexes for table `tbl_cuti`
--
ALTER TABLE `tbl_cuti`
  ADD PRIMARY KEY (`id_cuti`),
  ADD UNIQUE KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tbl_departemen`
--
ALTER TABLE `tbl_departemen`
  ADD PRIMARY KEY (`id_departemen`);

--
-- Indexes for table `tbl_izin`
--
ALTER TABLE `tbl_izin`
  ADD PRIMARY KEY (`id_izin`),
  ADD UNIQUE KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tbl_jabatan`
--
ALTER TABLE `tbl_jabatan`
  ADD PRIMARY KEY (`id_jabatan`);

--
-- Indexes for table `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  ADD PRIMARY KEY (`id_log`);

--
-- Indexes for table `tbl_pegawai`
--
ALTER TABLE `tbl_pegawai`
  ADD PRIMARY KEY (`id_pegawai`),
  ADD UNIQUE KEY `id_departemen` (`id_departemen`),
  ADD UNIQUE KEY `id_jabatan` (`id_jabatan`);

--
-- Indexes for table `tbl_penghargaan`
--
ALTER TABLE `tbl_penghargaan`
  ADD PRIMARY KEY (`id_penghargaan`),
  ADD UNIQUE KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tbl_pengumuman`
--
ALTER TABLE `tbl_pengumuman`
  ADD PRIMARY KEY (`id_pengumuman`);

--
-- Indexes for table `tbl_peringatan`
--
ALTER TABLE `tbl_peringatan`
  ADD PRIMARY KEY (`id_peringatan`),
  ADD UNIQUE KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tbl_usaha`
--
ALTER TABLE `tbl_usaha`
  ADD PRIMARY KEY (`id_usaha`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nama_user` (`nama_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_catatan`
--
ALTER TABLE `tbl_catatan`
  MODIFY `id_catatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_pengumuman`
--
ALTER TABLE `tbl_pengumuman`
  MODIFY `id_pengumuman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

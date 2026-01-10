-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 10, 2026 at 10:02 PM
-- Server version: 11.4.9-MariaDB-cll-lve
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `didy3485_perpus`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`didy3485`@`localhost` PROCEDURE `tambah_peminjaman` (IN `p_id_anggota` INT, IN `p_id_buku` INT, IN `p_tgl_pinjam` DATE, IN `p_tgl_jatuh_tempo` DATE, IN `p_durasi_pinjam` INT)   BEGIN
    DECLARE v_stok INT;
    DECLARE v_jumlah_pinjam INT;
    DECLARE v_duplikat INT;
    DECLARE v_max_peminjaman INT DEFAULT 3;
    DECLARE v_durasi_default INT DEFAULT 7;
    DECLARE v_tgl_jatuh_tempo_calc DATE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    -- Ambil konfigurasi dari tabel pengaturan jika ada
    SELECT CAST(nilai AS SIGNED) INTO v_max_peminjaman
    FROM pengaturan WHERE nama_setting = 'maksimal_peminjaman'
    LIMIT 1;
    
    IF v_max_peminjaman IS NULL OR v_max_peminjaman <= 0 THEN
        SET v_max_peminjaman = 3;
    END IF;
    
    -- Jika durasi tidak diberikan, ambil dari pengaturan
    IF p_durasi_pinjam IS NULL OR p_durasi_pinjam <= 0 THEN
        SELECT CAST(nilai AS SIGNED) INTO v_durasi_default
        FROM pengaturan WHERE nama_setting = 'durasi_peminjaman'
        LIMIT 1;
        
        IF v_durasi_default IS NULL OR v_durasi_default <= 0 THEN
            SET v_durasi_default = 7;
        END IF;
        SET p_durasi_pinjam = v_durasi_default;
    END IF;
    
    -- Hitung tanggal jatuh tempo jika tidak diberikan
    IF p_tgl_jatuh_tempo IS NULL OR p_tgl_jatuh_tempo = '' THEN
        SET v_tgl_jatuh_tempo_calc = DATE_ADD(p_tgl_pinjam, INTERVAL p_durasi_pinjam DAY);
    ELSE
        SET v_tgl_jatuh_tempo_calc = p_tgl_jatuh_tempo;
    END IF;
    
    START TRANSACTION;
    
    -- Cek stok buku
    SELECT stok INTO v_stok FROM buku WHERE id_buku = p_id_buku FOR UPDATE;
    
    IF v_stok <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok buku tidak mencukupi';
    END IF;
    
    -- Cek jumlah peminjaman aktif anggota
    SELECT COUNT(*) INTO v_jumlah_pinjam
    FROM peminjaman
    WHERE id_anggota = p_id_anggota AND status = 'dipinjam';
    
    IF v_jumlah_pinjam >= v_max_peminjaman THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Maksimal peminjaman aktif telah tercapai';
    END IF;
    
    -- Cek apakah anggota sudah meminjam buku yang sama dan belum dikembalikan
    SELECT COUNT(*) INTO v_duplikat
    FROM peminjaman
    WHERE id_anggota = p_id_anggota
      AND id_buku = p_id_buku
      AND status = 'dipinjam';
    
    IF v_duplikat > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Anda masih meminjam buku yang sama dan belum dikembalikan';
    END IF;
    
    -- Tambah peminjaman dengan tanggal jatuh tempo
    INSERT INTO peminjaman (id_anggota, id_buku, tgl_pinjam, tgl_jatuh_tempo, durasi_pinjam, status) 
    VALUES (
        p_id_anggota, 
        p_id_buku, 
        p_tgl_pinjam, 
        v_tgl_jatuh_tempo_calc,
        p_durasi_pinjam,
        'dipinjam'
    );
    
    -- Catatan: stok buku akan dikurangi oleh trigger trigger_kurangi_stok
    
    COMMIT;
END$$

CREATE DEFINER=`didy3485`@`localhost` PROCEDURE `tambah_pengembalian` (IN `p_id_pinjam` INT, IN `p_tgl_kembali` DATE)   BEGIN
    DECLARE v_id_buku INT;
    DECLARE v_tgl_jatuh_tempo DATE;
    DECLARE v_denda_per_hari DECIMAL(10,2);
    DECLARE v_hari_telat INT;
    DECLARE v_jumlah_denda DECIMAL(10,2);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Ambil id_buku dan tanggal jatuh tempo dari peminjaman
    SELECT id_buku, tgl_jatuh_tempo INTO v_id_buku, v_tgl_jatuh_tempo 
    FROM peminjaman 
    WHERE id_pinjam = p_id_pinjam
    FOR UPDATE;
    
    -- Tambah pengembalian
    INSERT INTO pengembalian (id_pinjam, tgl_kembali) 
    VALUES (p_id_pinjam, p_tgl_kembali);
    
    -- Update status peminjaman
    UPDATE peminjaman 
    SET status = 'dikembalikan'
    WHERE id_pinjam = p_id_pinjam;
    
    -- Catatan: stok buku akan ditambah oleh trigger trigger_tambah_stok
    
    -- Hitung denda jika terlambat
    SET v_hari_telat = 0;
    IF v_tgl_jatuh_tempo IS NOT NULL AND p_tgl_kembali > v_tgl_jatuh_tempo THEN
        SET v_hari_telat = DATEDIFF(p_tgl_kembali, v_tgl_jatuh_tempo);
    END IF;
    
    IF v_hari_telat > 0 THEN
        -- Ambil nilai denda per hari dari pengaturan, default 20000 jika tidak ada
        SELECT CAST(nilai AS DECIMAL(10,2)) INTO v_denda_per_hari
        FROM pengaturan
        WHERE nama_setting = 'denda_per_hari'
        LIMIT 1;
        
        IF v_denda_per_hari IS NULL OR v_denda_per_hari <= 0 THEN
            SET v_denda_per_hari = 20000;  -- Rp. 20.000 per hari
        END IF;
        
        SET v_jumlah_denda = v_denda_per_hari * v_hari_telat;
        
        INSERT INTO denda (id_pinjam, jumlah_denda, status, keterangan)
        VALUES (
            p_id_pinjam,
            v_jumlah_denda,
            'belum_lunas',
            CONCAT('Terlambat ', v_hari_telat, ' hari')
        );
    END IF;
    
    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `nim` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `program_studi` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id_anggota`, `id_user`, `nama`, `alamat`, `no_hp`, `nim`, `password`, `kelas`, `program_studi`, `created_at`) VALUES
(1, NULL, 'Ahmad Rizki', 'Jl. Merdeka No. 123, Jakarta Pusat', '081234567890', 'NIM0001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(2, NULL, 'Siti Nurhaliza', 'Jl. Sudirman No. 45, Jakarta Selatan', '081234567891', 'NIM0002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(3, NULL, 'Budi Santoso', 'Jl. Thamrin No. 67, Jakarta Pusat', '081234567892', 'NIM0003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(4, NULL, 'Dewi Sartika', 'Jl. Gatot Subroto No. 89, Jakarta Selatan', '081234567893', 'NIM0004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(5, NULL, 'Rudi Hermawan', 'Jl. Asia Afrika No. 12, Bandung', '081234567894', 'NIM0005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(6, NULL, 'Nina Kartika', 'Jl. Ahmad Yani No. 34, Bandung', '081234567895', 'NIM0006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(7, NULL, 'Agus Setiawan', 'Jl. Diponegoro No. 56, Semarang', '081234567896', 'NIM0007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(8, NULL, 'Maya Indah', 'Jl. Hayam Wuruk No. 78, Yogyakarta', '081234567897', 'NIM0008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(9, NULL, 'Doni Prasetyo', 'Jl. Veteran No. 90, Surabaya', '081234567898', 'NIM0009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(10, NULL, 'Rina Safitri', 'Jl. Pahlawan No. 11, Malang', '081234567899', 'NIM0010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(11, NULL, 'Joko Widodo', 'Jl. Malioboro No. 22, Yogyakarta', '081234567800', 'NIM0011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(12, NULL, 'Sri Mulyani', 'Jl. Sudirman No. 33, Jakarta Pusat', '081234567801', 'NIM0012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(13, NULL, 'Bambang Brodjonegoro', 'Jl. Thamrin No. 44, Jakarta Pusat', '081234567802', 'NIM0013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(14, NULL, 'Erick Thohir', 'Jl. Gatot Subroto No. 55, Jakarta Selatan', '081234567803', 'NIM0014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(15, NULL, 'Luhut Binsar', 'Jl. Asia Afrika No. 66, Bandung', '081234567804', 'NIM0015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(16, NULL, 'Prabowo Subianto', 'Jl. Ahmad Yani No. 77, Bandung', '081234567805', 'NIM0016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(17, NULL, 'Ganjar Pranowo', 'Jl. Diponegoro No. 88, Semarang', '081234567806', 'NIM0017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(18, NULL, 'Anies Baswedan', 'Jl. Hayam Wuruk No. 99, Jakarta', '081234567807', 'NIM0018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(19, NULL, 'Sandiaga Uno', 'Jl. Veteran No. 100, Jakarta', '081234567808', 'NIM0019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(20, NULL, 'Ridwan Kamil', 'Jl. Pahlawan No. 111, Bandung', '081234567809', 'NIM0020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-01-08 21:00:29'),
(21, 1, 'Emison Wonda', '', '09696', '22101140198', '$2y$10$qUXgdhqo6G2qibzgH01jW.biAw7mZtSSu.Eing4/b6kG/9k1V9aOK', '07TPLP006', 'Teknik Informatika', '2026-01-08 21:07:29'),
(22, 4, 'Muhammad Razik ', '', '030193', '221011400299', '$2y$10$dulGTC/t/EYTqe/cjpFB7.A6tampi8qbn04ZZb9TD84AGICwQGPmq', '07TPLP006', 'Teknik Informatika', '2026-01-08 21:13:00'),
(23, 5, 'Aziz Arrasyid', '', '0190192', '221011400272', '$2y$10$WIQKhxbLkDEoU/FzybL2C.f6excw.ojDlEtN.bRyqx06WkztEJzVe', '07TPLP006', 'Teknik Informatika', '2026-01-08 21:15:04'),
(24, 6, 'Alatif Subekti', '', '909203', '221011400277', '$2y$10$sNVFLDeF3hqP0Q3xfWz7ReP3/.NdY0fGn/wJWaud8biGGjrDi/aTi', '07TPLP006', 'Teknik Informatika', '2026-01-08 21:17:07');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `tahun` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `program_studi` varchar(100) DEFAULT NULL,
  `jenis_buku` varchar(100) DEFAULT NULL,
  `rak` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `judul`, `penulis`, `tahun`, `stok`, `program_studi`, `jenis_buku`, `rak`, `created_at`) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 2005, 7, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(2, 'Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 5, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(3, 'Negeri 5 Menara', 'Ahmad Fuadi', 2009, 5, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(4, 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 2004, 10, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(5, 'Perahu Kertas', 'Dee Lestari', 2009, 6, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(6, 'Sang Pemimpi', 'Andrea Hirata', 2006, 4, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(7, 'Ronggeng Dukuh Paruk', 'Ahmad Tohari', 1982, 2, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(8, 'Jalan Raya Pos, Jalan Daendels', 'Pramoedya Ananta Toer', 1995, 2, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(9, 'Cantik Itu Luka', 'Eka Kurniawan', 2002, 4, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(10, 'Pulang', 'Tere Liye', 2015, 7, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(11, 'Hujan', 'Tere Liye', 2016, 5, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(12, 'Matahari', 'Tere Liye', 2016, 4, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(13, 'Bumi', 'Tere Liye', 2014, 6, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(14, 'Bulan', 'Tere Liye', 2015, 5, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(15, 'Bintang', 'Tere Liye', 2017, 6, 'Sastra', 'Fiksi', 'Rak A', '2026-01-08 21:00:29'),
(16, 'The Great Gatsby', 'F. Scott Fitzgerald', 1925, 2, 'Sastra', 'Fiksi', 'Rak B', '2026-01-08 21:00:29'),
(17, 'To Kill a Mockingbird', 'Harper Lee', 1960, 4, 'Sastra', 'Fiksi', 'Rak B', '2026-01-08 21:00:29'),
(18, '1984', 'George Orwell', 1949, 5, 'Sastra', 'Fiksi', 'Rak B', '2026-01-08 21:00:29'),
(19, 'Pride and Prejudice', 'Jane Austen', 1813, 2, 'Sastra', 'Fiksi', 'Rak B', '2026-01-08 21:00:29'),
(20, 'The Catcher in the Rye', 'J.D. Salinger', 1951, 2, 'Sastra', 'Fiksi', 'Rak B', '2026-01-08 21:00:29'),
(21, 'Clean Code', 'Robert C. Martin', 2008, 3, 'Teknik Informatika', 'Buku Teks', 'Rak C', '2026-01-08 21:00:29'),
(22, 'Design Patterns', 'Erich Gamma', 1994, 3, 'Teknik Informatika', 'Buku Teks', 'Rak C', '2026-01-08 21:00:29'),
(23, 'The Pragmatic Programmer', 'Andrew Hunt', 1999, 4, 'Teknik Informatika', 'Buku Teks', 'Rak C', '2026-01-08 21:00:29'),
(24, 'Refactoring', 'Martin Fowler', 1999, 3, 'Teknik Informatika', 'Buku Teks', 'Rak C', '2026-01-08 21:00:29'),
(25, 'Head First Design Patterns', 'Eric Freeman', 2004, 4, 'Teknik Informatika', 'Buku Teks', 'Rak C', '2026-01-08 21:00:29'),
(26, 'JavaScript: The Good Parts', 'Douglas Crockford', 2008, 5, 'Teknik Informatika', 'Buku Teks', 'Rak C', '2026-01-08 21:00:29'),
(27, 'Belajar', 'Guru', 2021, 2, 'Sastra', 'Edukasi', 'A', '2026-01-10 14:10:49');

-- --------------------------------------------------------

--
-- Table structure for table `denda`
--

CREATE TABLE `denda` (
  `id_denda` int(11) NOT NULL,
  `id_pinjam` int(11) NOT NULL,
  `jumlah_denda` decimal(10,2) DEFAULT 0.00,
  `status` enum('belum_lunas','lunas') DEFAULT 'belum_lunas',
  `tgl_bayar` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `denda`
--

INSERT INTO `denda` (`id_denda`, `id_pinjam`, `jumlah_denda`, `status`, `tgl_bayar`, `keterangan`, `created_at`) VALUES
(1, 35, 20000.00, 'belum_lunas', NULL, 'Terlambat 1 hari', '2026-01-08 21:44:15'),
(2, 36, 80000.00, 'belum_lunas', NULL, 'Terlambat 4 hari', '2026-01-08 21:45:08');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_pinjam` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_jatuh_tempo` date DEFAULT NULL,
  `durasi_pinjam` int(11) DEFAULT 7 COMMENT 'Durasi dalam hari',
  `perpanjangan` int(11) DEFAULT 0 COMMENT 'Jumlah perpanjangan',
  `tgl_perpanjangan` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_pinjam`, `id_anggota`, `id_buku`, `tgl_pinjam`, `tgl_jatuh_tempo`, `durasi_pinjam`, `perpanjangan`, `tgl_perpanjangan`, `status`, `created_at`) VALUES
(1, 1, 1, '2025-06-01', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(2, 2, 3, '2025-06-02', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(3, 3, 5, '2025-06-03', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(4, 4, 2, '2025-06-04', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(5, 5, 7, '2025-06-05', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(6, 6, 4, '2025-06-06', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(7, 7, 6, '2025-06-07', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(8, 8, 8, '2025-06-08', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(9, 9, 10, '2025-06-09', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(10, 10, 12, '2025-06-10', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(11, 11, 15, '2025-06-11', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(12, 12, 18, '2025-06-12', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(13, 13, 20, '2025-06-13', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(14, 14, 22, '2025-06-14', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(15, 15, 24, '2025-06-15', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(16, 1, 9, '2025-06-16', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(17, 2, 11, '2025-06-17', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(18, 3, 13, '2025-06-18', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(19, 4, 16, '2025-06-19', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(20, 5, 19, '2025-06-20', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(21, 6, 21, '2025-06-21', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(22, 7, 23, '2025-06-22', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(23, 8, 25, '2025-06-23', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(24, 9, 1, '2025-06-24', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(25, 10, 3, '2025-06-25', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(26, 11, 5, '2025-06-26', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(27, 12, 7, '2025-06-27', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(28, 13, 10, '2025-06-28', NULL, 7, 0, NULL, 'dipinjam', '2026-01-08 21:00:29'),
(29, 14, 12, '2025-06-29', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(30, 15, 15, '2025-06-30', NULL, 7, 0, NULL, 'dikembalikan', '2026-01-08 21:00:29'),
(31, 23, 4, '2026-01-02', '2026-01-09', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:41:56'),
(32, 23, 10, '2026-01-02', '2026-01-09', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:42:17'),
(33, 23, 11, '2026-01-02', '2026-01-09', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:42:31'),
(34, 23, 9, '2026-01-01', '2026-01-08', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:43:13'),
(35, 23, 7, '2026-01-01', '2026-01-07', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:44:10'),
(36, 21, 13, '2025-12-28', '2026-01-04', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:44:54'),
(37, 21, 21, '2026-01-01', '2026-01-08', 7, 0, NULL, 'dikembalikan', '2026-01-08 21:45:23'),
(38, 21, 26, '2026-01-08', '2026-01-15', 7, 0, NULL, 'dipinjam', '2026-01-10 14:35:52');

--
-- Triggers `peminjaman`
--
DELIMITER $$
CREATE TRIGGER `trigger_kurangi_stok` AFTER INSERT ON `peminjaman` FOR EACH ROW BEGIN
    UPDATE buku SET stok = stok - 1 WHERE id_buku = NEW.id_buku;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_setting` varchar(100) NOT NULL,
  `nilai` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_setting`, `nilai`, `keterangan`, `updated_at`) VALUES
(1, 'denda_per_hari', '20000', 'Denda per hari keterlambatan (Rp)', '2026-01-08 21:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id_kembali` int(11) NOT NULL,
  `id_pinjam` int(11) NOT NULL,
  `tgl_kembali` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengembalian`
--

INSERT INTO `pengembalian` (`id_kembali`, `id_pinjam`, `tgl_kembali`, `created_at`) VALUES
(1, 1, '2025-06-08', '2026-01-08 21:00:29'),
(2, 2, '2025-06-09', '2026-01-08 21:00:29'),
(3, 3, '2025-06-10', '2026-01-08 21:00:29'),
(4, 4, '2025-06-11', '2026-01-08 21:00:29'),
(5, 5, '2025-06-12', '2026-01-08 21:00:29'),
(6, 6, '2025-06-13', '2026-01-08 21:00:29'),
(7, 7, '2025-06-14', '2026-01-08 21:00:29'),
(8, 8, '2025-06-15', '2026-01-08 21:00:29'),
(9, 9, '2025-06-16', '2026-01-08 21:00:29'),
(10, 10, '2025-06-17', '2026-01-08 21:00:29'),
(11, 11, '2025-06-18', '2026-01-08 21:00:29'),
(12, 12, '2025-06-19', '2026-01-08 21:00:29'),
(13, 13, '2025-06-20', '2026-01-08 21:00:29'),
(14, 14, '2025-06-21', '2026-01-08 21:00:29'),
(15, 15, '2025-06-22', '2026-01-08 21:00:29'),
(16, 30, '2026-01-08', '2026-01-08 21:41:27'),
(17, 33, '2026-01-08', '2026-01-08 21:42:41'),
(18, 32, '2026-01-08', '2026-01-08 21:42:49'),
(19, 31, '2026-01-08', '2026-01-08 21:42:56'),
(20, 34, '2026-01-08', '2026-01-08 21:43:22'),
(21, 35, '2026-01-08', '2026-01-08 21:44:15'),
(22, 36, '2026-01-08', '2026-01-08 21:45:08'),
(23, 37, '2026-01-08', '2026-01-08 21:45:41'),
(24, 23, '2026-01-10', '2026-01-10 14:19:05'),
(25, 29, '2026-01-10', '2026-01-10 14:20:37');

--
-- Triggers `pengembalian`
--
DELIMITER $$
CREATE TRIGGER `trigger_tambah_stok` AFTER INSERT ON `pengembalian` FOR EACH ROW BEGIN
    DECLARE v_id_buku INT;
    SELECT id_buku INTO v_id_buku FROM peminjaman WHERE id_pinjam = NEW.id_pinjam;
    UPDATE buku SET stok = stok + 1 WHERE id_buku = v_id_buku;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','member') NOT NULL DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'emison', '$2y$10$qUXgdhqo6G2qibzgH01jW.biAw7mZtSSu.Eing4/b6kG/9k1V9aOK', 'member', '2026-01-08 21:07:29'),
(2, 'geraldo', '$2y$10$PN9GPfiIr5bBsk6YiBCHgeiHBSHa7towvYmTahHf7Wx5Ndt0ucqfq', 'staff', '2026-01-08 21:08:25'),
(3, 'mia', '$2y$10$bMDjLB1Zbr.cQjSKaxDB3uiz/r0NubqmQjyxtv1KjXHwxi9wjmCP.', 'admin', '2026-01-08 21:11:03'),
(4, 'razik', '$2y$10$dulGTC/t/EYTqe/cjpFB7.A6tampi8qbn04ZZb9TD84AGICwQGPmq', 'member', '2026-01-08 21:13:00'),
(5, 'aziz', '$2y$10$WIQKhxbLkDEoU/FzybL2C.f6excw.ojDlEtN.bRyqx06WkztEJzVe', 'member', '2026-01-08 21:15:04'),
(6, 'alatif', '$2y$10$sNVFLDeF3hqP0Q3xfWz7ReP3/.NdY0fGn/wJWaud8biGGjrDi/aTi', 'member', '2026-01-08 21:17:07'),
(7, 'geral', '$2y$10$9HFF3nhfShvbii4r29wxZe9OtHEPtjBmEh2gw6//3da1F.Ppr13eG', 'staff', '2026-01-08 21:24:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD KEY `idx_id_user` (`id_user`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`);

--
-- Indexes for table `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id_denda`),
  ADD KEY `id_pinjam` (`id_pinjam`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_pinjam`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_setting` (`nama_setting`);

--
-- Indexes for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id_kembali`),
  ADD KEY `id_pinjam` (`id_pinjam`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `denda`
--
ALTER TABLE `denda`
  MODIFY `id_denda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_pinjam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id_kembali` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_pinjam`) REFERENCES `peminjaman` (`id_pinjam`) ON DELETE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE;

--
-- Constraints for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`id_pinjam`) REFERENCES `peminjaman` (`id_pinjam`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

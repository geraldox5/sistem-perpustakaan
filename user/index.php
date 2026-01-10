<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: ../login.php');
    exit();
}

// Ambil data buku yang tersedia
$query_buku = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul LIMIT 6";
$result_buku = mysqli_query($koneksi, $query_buku);

// Ambil riwayat peminjaman user berdasarkan id_user
$query_riwayat = "SELECT p.*, b.judul as judul_buku, b.penulis 
                  FROM peminjaman p 
                  JOIN buku b ON p.id_buku = b.id_buku 
                  JOIN anggota a ON p.id_anggota = a.id_anggota
                  WHERE a.id_user = {$_SESSION['user_id']}
                  ORDER BY p.created_at DESC LIMIT 5";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);

// Statistik user
$query_total_peminjaman = "SELECT COUNT(*) as total FROM peminjaman";
$result_total_peminjaman = mysqli_query($koneksi, $query_total_peminjaman);
$total_peminjaman = mysqli_fetch_assoc($result_total_peminjaman)['total'];

$query_sedang_dipinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'";
$result_sedang_dipinjam = mysqli_query($koneksi, $query_sedang_dipinjam);
$sedang_dipinjam = mysqli_fetch_assoc($result_sedang_dipinjam)['total'];

// Tambahkan query buku terpopuler
$query_best = "SELECT b.*, COUNT(p.id_buku) as total_pinjam FROM peminjaman p JOIN buku b ON p.id_buku = b.id_buku GROUP BY p.id_buku ORDER BY total_pinjam DESC LIMIT 6";
$result_best = mysqli_query($koneksi, $query_best);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Sistem Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00b894;
            --secondary-color: #00a085;
            --accent-color: #667eea;
            --text-color: #2d3748;
            --light-bg: #f7fafc;
            --border-color: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-color);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        .sidebar-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: block;
            padding: 12px 20px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(0, 184, 148, 0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            margin-bottom: 15px;
        }

        .stat-icon.blue { background: var(--accent-color); }
        .stat-icon.green { background: var(--primary-color); }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #718096;
            font-size: 14px;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .book-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-cover {
            height: 150px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }

        .book-info {
            padding: 20px;
        }

        .book-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .book-author {
            color: #718096;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .book-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stock-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .stock-available {
            background: #d4edda;
            color: #155724;
        }

        .stock-unavailable {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .table {
            margin: 0;
        }

        .table th {
            border: none;
            background: var(--light-bg);
            font-weight: 600;
            color: var(--text-color);
            padding: 15px 20px;
        }

        .table td {
            border: none;
            padding: 15px 20px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(0, 184, 148, 0.05);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-dipinjam {
            background: #fff3cd;
            color: #856404;
        }

        .status-dikembalikan {
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .book-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-book-open"></i> Perpustakaan</h3>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="daftar_buku.php" class="menu-item">
                <i class="fas fa-book"></i> Daftar Buku
            </a>
            <a href="riwayat.php" class="menu-item">
                <i class="fas fa-history"></i> Riwayat Pinjam Buku
            </a>
            <a href="ubah_profil.php" class="menu-item">
                <i class="fas fa-user-edit"></i> Ubah Data Pribadi
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Sambutan -->
        <div class="alert alert-success mb-4">
            <h2>Selamat Datang di Perpustakaan!</h2>
            <p>Halo <b><?php echo $_SESSION['username']; ?></b>, selamat datang di sistem perpustakaan. Silakan cari dan pinjam buku favoritmu!</p>
        </div>
        <!-- Search Buku -->
        <form method="GET" action="daftar_buku.php" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari judul atau penulis buku...">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari Buku</button>
            </div>
        </form>
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>Dashboard User</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 600;"><?php echo $_SESSION['username']; ?></div>
                    <div style="font-size: 12px; color: #718096;">Member</div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stat-number"><?php echo $total_peminjaman; ?></div>
                <div class="stat-label">Total Peminjaman</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo $sedang_dipinjam; ?></div>
                <div class="stat-label">Sedang Dipinjam</div>
            </div>
        </div>

        <!-- Buku Tersedia -->
        <div class="content-card">
            <div class="card-header">
                <h5 class="card-title">Buku Tersedia</h5>
                <a href="daftar_buku.php" class="btn btn-primary btn-sm">
                    Lihat Semua
                </a>
            </div>
            <div class="book-grid">
                <?php while ($row = mysqli_fetch_assoc($result_buku)): ?>
                <div class="book-card">
                    <div class="book-cover">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="book-info">
                        <div class="book-title"><?php echo $row['judul']; ?></div>
                        <div class="book-author"><?php echo $row['penulis']; ?></div>
                        <div class="book-stock">
                            <span>Tahun: <?php echo $row['tahun']; ?></span>
                            <span class="stock-badge stock-available">
                                Stok: <?php echo $row['stok']; ?>
                            </span>
                        </div>
                        <div class="mb-1">
                            <span class="badge bg-info">Program Studi: <?php echo $row['program_studi']; ?></span>
                        </div>
                        <div class="mb-1">
                            <span class="badge bg-secondary">Jenis: <?php echo $row['jenis_buku']; ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">Rak: <?php echo $row['rak']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Riwayat Peminjaman -->
        <div class="content-card">
            <div class="card-header">
                <h5 class="card-title">Riwayat Peminjaman Terbaru</h5>
                <a href="riwayat.php" class="btn btn-primary btn-sm">
                    Lihat Semua
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Penulis</th>
                            <th>Tanggal Pinjam</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                        <tr>
                            <td><?php echo $row['judul_buku']; ?></td>
                            <td><?php echo $row['penulis']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $row['status'] == 'dipinjam' ? 'status-dipinjam' : 'status-dikembalikan'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Buku Terpopuler -->
        <div class="content-card mb-4">
            <div class="card-header">
                <h5 class="card-title">Buku Terpopuler</h5>
            </div>
            <div class="book-grid">
                <?php while ($row = mysqli_fetch_assoc($result_best)): ?>
                <div class="book-card">
                    <div class="book-cover">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="book-info">
                        <div class="book-title"><?php echo $row['judul']; ?></div>
                        <div class="book-author"><?php echo $row['penulis']; ?></div>
                        <div class="book-stock">
                            <span>Dipinjam: <?php echo $row['total_pinjam']; ?>x</span>
                            <span class="stock-badge stock-available">
                                Stok: <?php echo $row['stok']; ?>
                            </span>
                        </div>
                        <div class="mb-1">
                            <span class="badge bg-info">Program Studi: <?php echo $row['program_studi']; ?></span>
                        </div>
                        <div class="mb-1">
                            <span class="badge bg-secondary">Jenis: <?php echo $row['jenis_buku']; ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">Rak: <?php echo $row['rak']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
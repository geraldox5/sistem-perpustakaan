<?php
session_start();
include '../koneksi.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Ambil statistik
$query_total_anggota = "SELECT COUNT(*) as total FROM anggota";
$result_total_anggota = mysqli_query($koneksi, $query_total_anggota);
$total_anggota = mysqli_fetch_assoc($result_total_anggota)['total'];

$query_total_buku = "SELECT COUNT(*) as total FROM buku";
$result_total_buku = mysqli_query($koneksi, $query_total_buku);
$total_buku = mysqli_fetch_assoc($result_total_buku)['total'];

$query_total_peminjaman = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'";
$result_total_peminjaman = mysqli_query($koneksi, $query_total_peminjaman);
$total_peminjaman = mysqli_fetch_assoc($result_total_peminjaman)['total'];

$query_total_pengembalian = "SELECT COUNT(*) as total FROM pengembalian";
$result_total_pengembalian = mysqli_query($koneksi, $query_total_pengembalian);
$total_pengembalian = mysqli_fetch_assoc($result_total_pengembalian)['total'];

// Peminjaman terbaru
$query_peminjaman_terbaru = "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku 
                             FROM peminjaman p 
                             JOIN anggota a ON p.id_anggota = a.id_anggota 
                             JOIN buku b ON p.id_buku = b.id_buku 
                             ORDER BY p.created_at DESC LIMIT 5";
$result_peminjaman_terbaru = mysqli_query($koneksi, $query_peminjaman_terbaru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            transition: all 0.3s ease;
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
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        .stat-icon.orange { background: #f39c12; }
        .stat-icon.red { background: #e74c3c; }

        .stat-number {
            font-size: 28px;
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
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
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
            <a href="anggota.php" class="menu-item">
                <i class="fas fa-users"></i> Anggota
            </a>
            <a href="buku.php" class="menu-item">
                <i class="fas fa-book"></i> Buku
            </a>
            <a href="peminjaman.php" class="menu-item">
                <i class="fas fa-hand-holding"></i> Peminjaman
            </a>
            <a href="pengembalian.php" class="menu-item">
                <i class="fas fa-undo"></i> Pengembalian
            </a>
            <a href="laporan.php" class="menu-item">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
            <a href="staff.php" class="menu-item">
                <i class="fas fa-user-tie"></i> Staff
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>Dashboard Admin</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 600;"><?php echo $_SESSION['username']; ?></div>
                    <div style="font-size: 12px; color: #718096;">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $total_anggota; ?></div>
                <div class="stat-label">Total Anggota</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo $total_buku; ?></div>
                <div class="stat-label">Total Buku</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stat-number"><?php echo $total_peminjaman; ?></div>
                <div class="stat-label">Sedang Dipinjam</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="stat-number"><?php echo $total_pengembalian; ?></div>
                <div class="stat-label">Total Pengembalian</div>
            </div>
        </div>

        <!-- Recent Peminjaman -->
        <div class="content-card">
            <div class="card-header">
                <h5 class="card-title">Peminjaman Terbaru</h5>
                <a href="peminjaman.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Peminjaman
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_peminjaman_terbaru)): ?>
                        <tr>
                            <td><?php echo $row['nama_anggota']; ?></td>
                            <td><?php echo $row['judul_buku']; ?></td>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Mobile menu toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }
    </script>
</body>
</html> 
<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: ../login.php');
    exit();
}

// Get borrowing history for current user only berdasarkan id_user
$query_riwayat = "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku, b.penulis 
                  FROM peminjaman p 
                  JOIN anggota a ON p.id_anggota = a.id_anggota 
                  JOIN buku b ON p.id_buku = b.id_buku 
                  WHERE a.id_user = {$_SESSION['user_id']}
                  ORDER BY p.created_at DESC";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);

// Get return history untuk user yang login saja
$query_pengembalian = "SELECT pg.*, p.tgl_pinjam, a.nama as nama_anggota, b.judul as judul_buku 
                       FROM pengembalian pg 
                       JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam 
                       JOIN anggota a ON p.id_anggota = a.id_anggota 
                       JOIN buku b ON p.id_buku = b.id_buku 
                       WHERE a.id_user = {$_SESSION['user_id']}
                       ORDER BY pg.created_at DESC";
$result_pengembalian = mysqli_query($koneksi, $query_pengembalian);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Sistem Perpustakaan</title>
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

        .menu-item:hover,
        .menu-item.active {
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

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
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
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-book-open"></i> Perpustakaan</h3>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="daftar_buku.php" class="menu-item">
                <i class="fas fa-book"></i> Daftar Buku
            </a>
            <a href="riwayat.php" class="menu-item active">
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

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Riwayat Peminjaman</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
                <!-- Peminjaman Aktif User -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-hand-holding"></i> Peminjaman Aktif Saya</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Buku</th>
                                        <th>Penulis</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Jatuh Tempo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    mysqli_data_seek($result_riwayat, 0);
                                    $ada_data = false;
                                    while ($row = mysqli_fetch_assoc($result_riwayat)): 
                                        if ($row['status'] == 'dipinjam'):
                                            $ada_data = true;
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['judul_buku']; ?></td>
                                        <td><?php echo $row['penulis']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                        <td><?php echo $row['tgl_jatuh_tempo'] ? date('d/m/Y', strtotime($row['tgl_jatuh_tempo'])) : '-'; ?></td>
                                        <td>
                                            <span class="status-badge status-dipinjam">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    if (!$ada_data):
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada peminjaman aktif</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Riwayat Pengembalian Semua User -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-undo"></i> Riwayat Pengembalian Saya</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Anggota</th>
                                        <th>Buku</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Lama Pinjam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    mysqli_data_seek($result_pengembalian, 0);
                                    while ($row = mysqli_fetch_assoc($result_pengembalian)):
                                        $tgl_pinjam = new DateTime($row['tgl_pinjam']);
                                        $tgl_kembali = new DateTime($row['tgl_kembali']);
                                        $selisih = $tgl_pinjam->diff($tgl_kembali);
                                        $lama_pinjam = $selisih->days . ' hari';
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['nama_anggota']; ?></td>
                                        <td><?php echo $row['judul_buku']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tgl_kembali'])); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $lama_pinjam; ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
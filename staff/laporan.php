<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header('Location: ../login.php');
    exit();
}

// Laporan 1: Buku yang sedang dipinjam (belum kembali)
$query_buku_dipinjam = "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku, b.penulis 
                        FROM peminjaman p 
                        JOIN anggota a ON p.id_anggota = a.id_anggota 
                        JOIN buku b ON p.id_buku = b.id_buku 
                        WHERE p.status = 'dipinjam' 
                        ORDER BY p.tgl_pinjam DESC";
$result_buku_dipinjam = mysqli_query($koneksi, $query_buku_dipinjam);

// Laporan 2: Statistik jumlah peminjaman per bulan
$query_statistik_bulan = "SELECT 
                            MONTH(tgl_pinjam) as bulan,
                            MONTHNAME(tgl_pinjam) as nama_bulan,
                            COUNT(*) as total_peminjaman
                          FROM peminjaman 
                          WHERE YEAR(tgl_pinjam) = YEAR(CURDATE())
                          GROUP BY MONTH(tgl_pinjam)
                          ORDER BY bulan";
$result_statistik_bulan = mysqli_query($koneksi, $query_statistik_bulan);

// Laporan 3: Daftar peminjam paling aktif
$query_peminjam_aktif = "SELECT 
                           a.nama,
                           COUNT(p.id_pinjam) as total_peminjaman
                         FROM anggota a
                         LEFT JOIN peminjaman p ON a.id_anggota = p.id_anggota
                         GROUP BY a.id_anggota, a.nama
                         ORDER BY total_peminjaman DESC
                         LIMIT 10";
$result_peminjam_aktif = mysqli_query($koneksi, $query_peminjam_aktif);

// Laporan 4: Rata-rata lama peminjaman
$query_rata_lama = "SELECT 
                      AVG(DATEDIFF(pg.tgl_kembali, p.tgl_pinjam)) as rata_lama_hari
                    FROM pengembalian pg
                    JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam";
$result_rata_lama = mysqli_query($koneksi, $query_rata_lama);
$rata_lama = mysqli_fetch_assoc($result_rata_lama);

// Laporan 5: Buku terpopuler
$query_buku_populer = "SELECT 
                         b.judul,
                         b.penulis,
                         COUNT(p.id_pinjam) as total_dipinjam
                       FROM buku b
                       LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
                       GROUP BY b.id_buku, b.judul, b.penulis
                       ORDER BY total_dipinjam DESC
                       LIMIT 10";
$result_buku_populer = mysqli_query($koneksi, $query_buku_populer);

// Statistik umum
$query_total_buku = "SELECT COUNT(*) as total FROM buku";
$result_total_buku = mysqli_query($koneksi, $query_total_buku);
$total_buku = mysqli_fetch_assoc($result_total_buku)['total'];

$query_total_peminjaman = "SELECT COUNT(*) as total FROM peminjaman";
$result_total_peminjaman = mysqli_query($koneksi, $query_total_peminjaman);
$total_peminjaman = mysqli_fetch_assoc($result_total_peminjaman)['total'];

$query_total_pengembalian = "SELECT COUNT(*) as total FROM pengembalian";
$result_total_pengembalian = mysqli_query($koneksi, $query_total_pengembalian);
$total_pengembalian = mysqli_fetch_assoc($result_total_pengembalian)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #00b894;
            --secondary-color: #00a085;
            --accent-color: #667eea;
            --text-color: #2d3748;
            --light-bg: #f7fafc;
            --border-color: #e2e8f0;
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
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
            <a href="buku.php" class="menu-item">
                <i class="fas fa-book"></i> Buku
            </a>
            <a href="peminjaman.php" class="menu-item">
                <i class="fas fa-hand-holding"></i> Peminjaman
            </a>
            <a href="pengembalian.php" class="menu-item">
                <i class="fas fa-undo"></i> Pengembalian
            </a>
            <a href="laporan.php" class="menu-item active">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Laporan Perpustakaan</h1>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>

        <!-- Statistik Umum -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <i class="fas fa-book fa-2x text-success mb-2"></i>
                    <div class="stat-number"><?php echo $total_buku; ?></div>
                    <div class="text-muted">Total Buku</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <i class="fas fa-hand-holding fa-2x text-warning mb-2"></i>
                    <div class="stat-number"><?php echo $total_peminjaman; ?></div>
                    <div class="text-muted">Total Peminjaman</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <i class="fas fa-undo fa-2x text-info mb-2"></i>
                    <div class="stat-number"><?php echo $total_pengembalian; ?></div>
                    <div class="text-muted">Total Pengembalian</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Buku yang Sedang Dipinjam -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-list"></i> Buku yang Sedang Dipinjam</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Anggota</th>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_buku_dipinjam)): ?>
                                <tr>
                                    <td><?php echo $row['nama_anggota']; ?></td>
                                    <td><?php echo $row['judul_buku']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Peminjam Paling Aktif -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-trophy"></i> Peminjam Paling Aktif</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Total Peminjaman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_peminjam_aktif)): ?>
                                <tr>
                                    <td><?php echo $row['nama']; ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $row['total_peminjaman']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Buku Terpopuler -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-star"></i> Buku Terpopuler</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Total Dipinjam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_buku_populer)): ?>
                                <tr>
                                    <td><?php echo $row['judul']; ?></td>
                                    <td><?php echo $row['penulis']; ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $row['total_dipinjam']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Statistik Peminjaman per Bulan -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-bar"></i> Statistik Peminjaman per Bulan</h5>
                    <canvas id="chartBulan" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Rata-rata Lama Peminjaman -->
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container">
                    <h5><i class="fas fa-clock"></i> Rata-rata Lama Peminjaman</h5>
                    <div class="text-center">
                        <div class="stat-number"><?php echo round($rata_lama['rata_lama_hari'], 1); ?> hari</div>
                        <p class="text-muted">Rata-rata waktu peminjaman buku</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Data untuk chart
        const bulanData = {
            labels: [],
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: [],
                backgroundColor: 'rgba(0, 184, 148, 0.2)',
                borderColor: 'rgba(0, 184, 148, 1)',
                borderWidth: 2
            }]
        };

        // Ambil data dari PHP
        <?php 
        mysqli_data_seek($result_statistik_bulan, 0);
        while ($row = mysqli_fetch_assoc($result_statistik_bulan)): 
        ?>
        bulanData.labels.push('<?php echo $row['nama_bulan']; ?>');
        bulanData.datasets[0].data.push(<?php echo $row['total_peminjaman']; ?>);
        <?php endwhile; ?>

        // Buat chart
        const ctx = document.getElementById('chartBulan').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: bulanData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

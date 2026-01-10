<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id_pinjam = $_POST['id_pinjam'];
                $tgl_kembali = $_POST['tgl_kembali'];
                
                // Gunakan stored procedure
                $query = "CALL tambah_pengembalian($id_pinjam, '$tgl_kembali')";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Pengembalian berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan pengembalian: " . mysqli_error($koneksi);
                }
                break;
                
            case 'delete':
                $id_kembali = $_POST['id_kembali'];
                $query = "DELETE FROM pengembalian WHERE id_kembali=$id_kembali";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Pengembalian berhasil dihapus!";
                } else {
                    $error = "Gagal menghapus pengembalian: " . mysqli_error($koneksi);
                }
                break;
        }
    }
}

// Ambil data pengembalian dengan join termasuk denda
$query_pengembalian = "SELECT pg.*, p.tgl_pinjam, p.tgl_jatuh_tempo, a.nama as nama_anggota, b.judul as judul_buku,
                       d.jumlah_denda, d.status as status_denda, d.keterangan as keterangan_denda
                       FROM pengembalian pg 
                       JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam 
                       JOIN anggota a ON p.id_anggota = a.id_anggota 
                       JOIN buku b ON p.id_buku = b.id_buku 
                       LEFT JOIN denda d ON d.id_pinjam = p.id_pinjam
                       ORDER BY pg.created_at DESC";
$result_pengembalian = mysqli_query($koneksi, $query_pengembalian);

// Ambil data peminjaman yang belum dikembalikan untuk dropdown
$query_peminjaman_aktif = "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku 
                           FROM peminjaman p 
                           JOIN anggota a ON p.id_anggota = a.id_anggota 
                           JOIN buku b ON p.id_buku = b.id_buku 
                           WHERE p.status = 'dipinjam' 
                           ORDER BY p.tgl_pinjam";
$result_peminjaman_aktif = mysqli_query($koneksi, $query_peminjaman_aktif);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengembalian - Sistem Perpustakaan</title>
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
            min-height: 100vh;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-success {
            background: #28a745;
            border-color: #28a745;
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
            <a href="anggota.php" class="menu-item">
                <i class="fas fa-users"></i> Anggota
            </a>
            <a href="buku.php" class="menu-item">
                <i class="fas fa-book"></i> Buku
            </a>
            <a href="peminjaman.php" class="menu-item">
                <i class="fas fa-hand-holding"></i> Peminjaman
            </a>
            <a href="pengembalian.php" class="menu-item active">
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

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Kelola Pengembalian</h1>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Pengembalian
            </button>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Pengembalian</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="pengembalianTable" class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Lama Pinjam</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
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
                            <td><?php echo $row['tgl_jatuh_tempo'] ? date('d/m/Y', strtotime($row['tgl_jatuh_tempo'])) : '-'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tgl_kembali'])); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $lama_pinjam; ?></span>
                            </td>
                            <td>
                                <?php if ($row['jumlah_denda'] && $row['jumlah_denda'] > 0): ?>
                                    <span class="badge bg-danger">
                                        Rp. <?php echo number_format($row['jumlah_denda'], 0, ',', '.'); ?>
                                    </span>
                                    <?php if ($row['status_denda'] == 'belum_lunas'): ?>
                                        <br><small class="text-danger"><?php echo $row['keterangan_denda']; ?></small>
                                    <?php else: ?>
                                        <br><small class="text-success">Lunas</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-success">Tidak ada denda</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="deletePengembalian(<?php echo $row['id_kembali']; ?>, '<?php echo $row['judul_buku']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="id_pinjam" class="form-label">Peminjaman</label>
                            <select class="form-select" id="id_pinjam" name="id_pinjam" required>
                                <option value="">Pilih Peminjaman</option>
                                <?php while ($peminjaman = mysqli_fetch_assoc($result_peminjaman_aktif)): ?>
                                <option value="<?php echo $peminjaman['id_pinjam']; ?>">
                                    <?php echo $peminjaman['nama_anggota']; ?> - <?php echo $peminjaman['judul_buku']; ?> 
                                    (Pinjam: <?php echo date('d/m/Y', strtotime($peminjaman['tgl_pinjam'])); ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tgl_kembali" class="form-label">Tanggal Kembali</label>
                            <input type="date" class="form-control" id="tgl_kembali" name="tgl_kembali" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_kembali" id="delete_id_kembali">
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#pengembalianTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                },
                order: [[5, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
            });
        });
        
        function deletePengembalian(id, judul) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus pengembalian buku "${judul}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id_kembali').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html> 
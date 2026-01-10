<?php
session_start();
include '../koneksi.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
                $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
                $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
                
                // Untuk anggota yang dibuat manual oleh admin, id_user bisa NULL
                $query = "INSERT INTO anggota (nama, alamat, no_hp, id_user) VALUES ('$nama', '$alamat', '$no_hp', NULL)";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Anggota berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan anggota: " . mysqli_error($koneksi);
                }
                break;
                
            case 'edit':
                $id_anggota = $_POST['id_anggota'];
                $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
                $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
                $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
                
                $query = "UPDATE anggota SET nama='$nama', alamat='$alamat', no_hp='$no_hp' WHERE id_anggota=$id_anggota";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Anggota berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui anggota: " . mysqli_error($koneksi);
                }
                break;
                
            case 'delete':
                $id_anggota = $_POST['id_anggota'];
                $query = "DELETE FROM anggota WHERE id_anggota=$id_anggota";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Anggota berhasil dihapus!";
                } else {
                    $error = "Gagal menghapus anggota: " . mysqli_error($koneksi);
                }
                break;
        }
    }
}

// Ambil data anggota
$query_anggota = "SELECT * FROM anggota ORDER BY nama";
$result_anggota = mysqli_query($koneksi, $query_anggota);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Sistem Perpustakaan</title>
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
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid var(--border-color);
            padding: 12px 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.25);
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

        .btn-sm {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
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
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-book-open"></i> Perpustakaan</h3>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="anggota.php" class="menu-item active">
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
            <h1>Kelola Anggota</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Anggota
            </button>
        </div>

        <!-- Alerts -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="content-card">
            <div class="card-header">
                <h5 class="card-title">Daftar Anggota</h5>
            </div>
            <div class="table-responsive">
                <table id="anggotaTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. HP</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result_anggota)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['alamat']; ?></td>
                            <td><?php echo $row['no_hp']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editAnggota(<?php echo $row['id_anggota']; ?>, '<?php echo $row['nama']; ?>', '<?php echo $row['alamat']; ?>', '<?php echo $row['no_hp']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteAnggota(<?php echo $row['id_anggota']; ?>, '<?php echo $row['nama']; ?>')">
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

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Anggota</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Anggota</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_anggota" id="edit_id_anggota">
                        <div class="mb-3">
                            <label for="edit_nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_no_hp" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="edit_no_hp" name="no_hp" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_anggota" id="delete_id_anggota">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#anggotaTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                }
            });
        });

        function editAnggota(id, nama, alamat, no_hp) {
            document.getElementById('edit_id_anggota').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_no_hp').value = no_hp;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteAnggota(id, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus anggota "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id_anggota').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html> 
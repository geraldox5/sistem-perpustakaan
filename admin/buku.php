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
                $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
                $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
                $tahun = $_POST['tahun'];
                $stok = $_POST['stok'];
                $program_studi = mysqli_real_escape_string($koneksi, $_POST['program_studi']);
                $jenis_buku = mysqli_real_escape_string($koneksi, $_POST['jenis_buku']);
                $rak = mysqli_real_escape_string($koneksi, $_POST['rak']);
                
                $query = "INSERT INTO buku (judul, penulis, tahun, stok, program_studi, jenis_buku, rak) VALUES ('$judul', '$penulis', $tahun, $stok, '$program_studi', '$jenis_buku', '$rak')";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Buku berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan buku: " . mysqli_error($koneksi);
                }
                break;
                
            case 'edit':
                $id_buku = $_POST['id_buku'];
                $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
                $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
                $tahun = $_POST['tahun'];
                $stok = $_POST['stok'];
                $program_studi = mysqli_real_escape_string($koneksi, $_POST['program_studi']);
                $jenis_buku = mysqli_real_escape_string($koneksi, $_POST['jenis_buku']);
                $rak = mysqli_real_escape_string($koneksi, $_POST['rak']);
                
                $query = "UPDATE buku SET judul='$judul', penulis='$penulis', tahun=$tahun, stok=$stok, program_studi='$program_studi', jenis_buku='$jenis_buku', rak='$rak' WHERE id_buku=$id_buku";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Buku berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui buku: " . mysqli_error($koneksi);
                }
                break;
                
            case 'delete':
                $id_buku = $_POST['id_buku'];
                $query = "DELETE FROM buku WHERE id_buku=$id_buku";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Buku berhasil dihapus!";
                } else {
                    $error = "Gagal menghapus buku: " . mysqli_error($koneksi);
                }
                break;
        }
    }
}

$query_buku = "SELECT * FROM buku ORDER BY judul";
$result_buku = mysqli_query($koneksi, $query_buku);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Sistem Perpustakaan</title>
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
            <a href="buku.php" class="menu-item active">
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

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Kelola Buku</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Buku
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
                <h5 class="mb-0">Daftar Buku</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="bukuTable" class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Tahun</th>
                            <th>Stok</th>
                            <th>Program Studi</th>
                            <th>Jenis Buku</th>
                            <th>Rak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result_buku)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['judul']; ?></td>
                            <td><?php echo $row['penulis']; ?></td>
                            <td><?php echo $row['tahun']; ?></td>
                            <td>
                                <span class="badge <?php echo $row['stok'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $row['stok']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['program_studi']; ?></td>
                            <td><?php echo $row['jenis_buku']; ?></td>
                            <td><?php echo $row['rak']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editBuku(<?php echo $row['id_buku']; ?>, '<?php echo htmlspecialchars($row['judul'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['penulis'], ENT_QUOTES); ?>', <?php echo $row['tahun']; ?>, <?php echo $row['stok']; ?>, '<?php echo htmlspecialchars($row['program_studi'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['jenis_buku'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['rak'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteBuku(<?php echo $row['id_buku']; ?>, '<?php echo htmlspecialchars($row['judul'], ENT_QUOTES); ?>')">
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
                    <h5 class="modal-title">Tambah Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="penulis" class="form-label">Penulis</label>
                            <input type="text" class="form-control" id="penulis" name="penulis" required>
                        </div>
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control" id="tahun" name="tahun" min="1900" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="stok" name="stok" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="program_studi" class="form-label">Program Studi</label>
                            <input type="text" class="form-control" id="program_studi" name="program_studi" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis_buku" class="form-label">Jenis Buku</label>
                            <input type="text" class="form-control" id="jenis_buku" name="jenis_buku" required>
                        </div>
                        <div class="mb-3">
                            <label for="rak" class="form-label">Rak</label>
                            <input type="text" class="form-control" id="rak" name="rak" required>
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
                    <h5 class="modal-title">Edit Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_buku" id="edit_id_buku">
                        <div class="mb-3">
                            <label for="edit_judul" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control" id="edit_judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_penulis" class="form-label">Penulis</label>
                            <input type="text" class="form-control" id="edit_penulis" name="penulis" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tahun" class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control" id="edit_tahun" name="tahun" min="1900" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="edit_stok" name="stok" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_program_studi" class="form-label">Program Studi</label>
                            <input type="text" class="form-control" id="edit_program_studi" name="program_studi" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_jenis_buku" class="form-label">Jenis Buku</label>
                            <input type="text" class="form-control" id="edit_jenis_buku" name="jenis_buku" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_rak" class="form-label">Rak</label>
                            <input type="text" class="form-control" id="edit_rak" name="rak" required>
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
        <input type="hidden" name="id_buku" id="delete_id_buku">
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#bukuTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                },
                order: [[1, 'asc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
            });
        });
        
        function editBuku(id, judul, penulis, tahun, stok, program_studi, jenis_buku, rak) {
            document.getElementById('edit_id_buku').value = id;
            document.getElementById('edit_judul').value = judul;
            document.getElementById('edit_penulis').value = penulis;
            document.getElementById('edit_tahun').value = tahun;
            document.getElementById('edit_stok').value = stok;
            document.getElementById('edit_program_studi').value = program_studi;
            document.getElementById('edit_jenis_buku').value = jenis_buku;
            document.getElementById('edit_rak').value = rak;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteBuku(id, judul) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus buku "${judul}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id_buku').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html> 
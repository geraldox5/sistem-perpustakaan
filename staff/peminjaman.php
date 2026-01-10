<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id_anggota = $_POST['id_anggota'];
                $id_buku = $_POST['id_buku'];
                $tgl_pinjam = $_POST['tgl_pinjam'];
                $tgl_jatuh_tempo = $_POST['tgl_jatuh_tempo'] ?? null;
                $durasi_pinjam = $_POST['durasi_pinjam'] ?? 7;
                
                // Jika tgl_jatuh_tempo tidak diisi, hitung otomatis
                if (empty($tgl_jatuh_tempo)) {
                    $tgl_jatuh_tempo = date('Y-m-d', strtotime($tgl_pinjam . ' + ' . $durasi_pinjam . ' days'));
                }
                
                // Gunakan stored procedure dengan parameter tambahan
                $query = "CALL tambah_peminjaman($id_anggota, $id_buku, '$tgl_pinjam', '$tgl_jatuh_tempo', $durasi_pinjam)";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Peminjaman berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan peminjaman: " . mysqli_error($koneksi);
                }
                break;
                
            case 'delete':
                $id_pinjam = $_POST['id_pinjam'];
                $query = "DELETE FROM peminjaman WHERE id_pinjam=$id_pinjam";
                if (mysqli_query($koneksi, $query)) {
                    $success = "Peminjaman berhasil dihapus!";
                } else {
                    $error = "Gagal menghapus peminjaman: " . mysqli_error($koneksi);
                }
                break;
        }
    }
}

// Ambil data peminjaman dengan join (semua data real, tidak ada dummy)
$query_peminjaman = "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku, b.stok, b.penulis
                     FROM peminjaman p 
                     JOIN anggota a ON p.id_anggota = a.id_anggota 
                     JOIN buku b ON p.id_buku = b.id_buku 
                     ORDER BY p.created_at DESC";
$result_peminjaman = mysqli_query($koneksi, $query_peminjaman);

// Ambil data untuk dropdown
$query_anggota = "SELECT * FROM anggota ORDER BY nama";
$result_anggota = mysqli_query($koneksi, $query_anggota);

$query_buku = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul";
$result_buku = mysqli_query($koneksi, $query_buku);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman - Sistem Perpustakaan</title>
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
            <a href="peminjaman.php" class="menu-item active">
                <i class="fas fa-hand-holding"></i> Peminjaman
            </a>
            <a href="pengembalian.php" class="menu-item">
                <i class="fas fa-undo"></i> Pengembalian
            </a>
            <a href="laporan.php" class="menu-item">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Kelola Peminjaman</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Peminjaman
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
                <h5 class="mb-0">Daftar Peminjaman</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="peminjamanTable" class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result_peminjaman)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nama_anggota']; ?></td>
                            <td><?php echo $row['judul_buku']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td><?php echo $row['tgl_jatuh_tempo'] ? date('d/m/Y', strtotime($row['tgl_jatuh_tempo'])) : '-'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $row['status'] == 'dipinjam' ? 'status-dipinjam' : 'status-dikembalikan'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'dipinjam'): ?>
                                <button class="btn btn-success btn-sm" onclick="kembalikanBuku(<?php echo $row['id_pinjam']; ?>, '<?php echo $row['judul_buku']; ?>')">
                                    <i class="fas fa-undo"></i> Kembalikan
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm" onclick="deletePeminjaman(<?php echo $row['id_pinjam']; ?>, '<?php echo $row['judul_buku']; ?>')">
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
                    <h5 class="modal-title">Tambah Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="id_anggota" class="form-label">Anggota</label>
                            <select class="form-select" id="id_anggota" name="id_anggota" required>
                                <option value="">Pilih Anggota</option>
                                <?php while ($anggota = mysqli_fetch_assoc($result_anggota)): ?>
                                <option value="<?php echo $anggota['id_anggota']; ?>">
                                    <?php echo $anggota['nama']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_buku" class="form-label">Buku</label>
                            <select class="form-select" id="id_buku" name="id_buku" required>
                                <option value="">Pilih Buku</option>
                                <?php while ($buku = mysqli_fetch_assoc($result_buku)): ?>
                                <option value="<?php echo $buku['id_buku']; ?>">
                                    <?php echo $buku['judul']; ?> (Stok: <?php echo $buku['stok']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tgl_pinjam" class="form-label">Tanggal Pinjam</label>
                            <input type="date" class="form-control" id="tgl_pinjam" name="tgl_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="durasi_pinjam" class="form-label">Durasi Peminjaman (hari)</label>
                            <input type="number" class="form-control" id="durasi_pinjam" name="durasi_pinjam" value="7" min="1" required>
                            <small class="form-text text-muted">Jumlah hari peminjaman (default: 7 hari)</small>
                        </div>
                        <div class="mb-3">
                            <label for="tgl_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo">
                            <small class="form-text text-muted">Kosongkan untuk menghitung otomatis berdasarkan durasi</small>
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

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_pinjam" id="delete_id_pinjam">
    </form>

    <!-- Return Form -->
    <form id="returnForm" method="POST" action="pengembalian.php" style="display: none;">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id_pinjam" id="return_id_pinjam">
        <input type="hidden" name="tgl_kembali" id="return_tgl_kembali">
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#peminjamanTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                },
                order: [[3, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
            });
        });
        
        // Auto-calculate tanggal jatuh tempo
        document.addEventListener('DOMContentLoaded', function() {
            const tglPinjam = document.getElementById('tgl_pinjam');
            const durasiPinjam = document.getElementById('durasi_pinjam');
            const tglJatuhTempo = document.getElementById('tgl_jatuh_tempo');
            
            function calculateJatuhTempo() {
                if (tglPinjam.value && durasiPinjam.value) {
                    const tgl = new Date(tglPinjam.value);
                    tgl.setDate(tgl.getDate() + parseInt(durasiPinjam.value));
                    const year = tgl.getFullYear();
                    const month = String(tgl.getMonth() + 1).padStart(2, '0');
                    const day = String(tgl.getDate()).padStart(2, '0');
                    tglJatuhTempo.value = `${year}-${month}-${day}`;
                }
            }
            
            tglPinjam.addEventListener('change', calculateJatuhTempo);
            durasiPinjam.addEventListener('input', calculateJatuhTempo);
        });
        
        function deletePeminjaman(id, judul) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus peminjaman buku "${judul}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id_pinjam').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }

        function kembalikanBuku(id, judul) {
            Swal.fire({
                title: 'Konfirmasi Pengembalian',
                text: `Apakah Anda yakin ingin mengembalikan buku "${judul}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kembalikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('return_id_pinjam').value = id;
                    document.getElementById('return_tgl_kembali').value = new Date().toISOString().split('T')[0];
                    document.getElementById('returnForm').submit();
                }
            });
        }
    </script>
</body>
</html>

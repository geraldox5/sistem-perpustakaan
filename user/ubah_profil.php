<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: ../login.php');
    exit();
}

// Ambil data anggota berdasarkan id_user dari session
$query = "SELECT * FROM anggota WHERE id_user = " . $_SESSION['user_id'] . " LIMIT 1";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Jika user tidak ditemukan, redirect
if (!$user) {
    header('Location: index.php');
    exit();
}

$id_anggota = $user['id_anggota'];

// Handle update
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $update = mysqli_query($koneksi, "UPDATE anggota SET nama='$nama', nim='$nim', kelas='$kelas', program_studi='$prodi', password='$password' WHERE id_anggota='$id_anggota'");
    if ($update) {
        $_SESSION['username'] = $nama;
        $success = true;
        // Refresh data
        $result = mysqli_query($koneksi, $query);
        $user = mysqli_fetch_assoc($result);
    } else {
        $error = 'Gagal update data.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Pribadi - Sistem Perpustakaan</title>
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
        .btn-primary { background: var(--primary-color); border-color: var(--primary-color); }
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
            <a href="riwayat.php" class="menu-item">
                <i class="fas fa-history"></i> Riwayat Pinjam Buku
            </a>
            <a href="ubah_profil.php" class="menu-item active">
                <i class="fas fa-user-edit"></i> Ubah Data Pribadi
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Ubah Data Pribadi</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success">Data berhasil diupdate!</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($user['nama']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" name="nim" class="form-control" required value="<?php echo htmlspecialchars($user['nim']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" class="form-control" required value="<?php echo htmlspecialchars($user['kelas']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" name="prodi" class="form-control" required value="<?php echo htmlspecialchars($user['program_studi']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
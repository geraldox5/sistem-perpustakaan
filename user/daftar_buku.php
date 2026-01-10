<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: ../login.php');
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Ambil data unik untuk filter
$filter_tahun = [];
$filter_prodi = [];
$filter_jenis = [];
$filter_rak = [];
$filter_query = mysqli_query($koneksi, "SELECT DISTINCT tahun, program_studi, jenis_buku, rak FROM buku");
while ($f = mysqli_fetch_assoc($filter_query)) {
    if (!in_array($f['tahun'], $filter_tahun)) $filter_tahun[] = $f['tahun'];
    if (!in_array($f['program_studi'], $filter_prodi)) $filter_prodi[] = $f['program_studi'];
    if (!in_array($f['jenis_buku'], $filter_jenis)) $filter_jenis[] = $f['jenis_buku'];
    if (!in_array($f['rak'], $filter_rak)) $filter_rak[] = $f['rak'];
}

// Ambil filter dari GET
$ftahun = isset($_GET['ftahun']) ? $_GET['ftahun'] : '';
$fprodi = isset($_GET['fprodi']) ? $_GET['fprodi'] : '';
$fjenis = isset($_GET['fjenis']) ? $_GET['fjenis'] : '';
$frak = isset($_GET['frak']) ? $_GET['frak'] : '';

$where_clause = "WHERE stok > 0";
if ($search) {
    $where_clause .= " AND (judul LIKE '%$search%' OR penulis LIKE '%$search%')";
}
if ($ftahun) $where_clause .= " AND tahun = '".mysqli_real_escape_string($koneksi, $ftahun)."'";
if ($fprodi) $where_clause .= " AND program_studi = '".mysqli_real_escape_string($koneksi, $fprodi)."'";
if ($fjenis) $where_clause .= " AND jenis_buku = '".mysqli_real_escape_string($koneksi, $fjenis)."'";
if ($frak) $where_clause .= " AND rak = '".mysqli_real_escape_string($koneksi, $frak)."'";

$query_buku = "SELECT * FROM buku $where_clause ORDER BY judul";
$result_buku = mysqli_query($koneksi, $query_buku);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Buku - Sistem Perpustakaan</title>
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

    .book-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
        height: 100%;
    }

    .book-card:hover {
        transform: translateY(-5px);
    }

    .search-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
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
        <a href="daftar_buku.php" class="menu-item active">
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

<div class="main-content">
        <div class="alert alert-success mb-4">
            <h2>Selamat Datang di Perpustakaan!</h2>
            <p>Halo <b><?php echo $_SESSION['username']; ?></b>, selamat datang di sistem perpustakaan. Silakan cari dan pinjam buku favoritmu!</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Daftar Buku</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Search Box & Filter -->
        <div class="search-box">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Cari judul atau penulis buku..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select class="form-select" name="ftahun">
                        <option value="">Semua</option>
                        <?php foreach ($filter_tahun as $th): ?>
                        <option value="<?php echo $th; ?>" <?php if($ftahun==$th) echo 'selected'; ?>><?php echo $th; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prodi</label>
                    <select class="form-select" name="fprodi">
                        <option value="">Semua</option>
                        <?php foreach ($filter_prodi as $pr): ?>
                        <option value="<?php echo $pr; ?>" <?php if($fprodi==$pr) echo 'selected'; ?>><?php echo $pr; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jenis</label>
                    <select class="form-select" name="fjenis">
                        <option value="">Semua</option>
                        <?php foreach ($filter_jenis as $j): ?>
                        <option value="<?php echo $j; ?>" <?php if($fjenis==$j) echo 'selected'; ?>><?php echo $j; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rak</label>
                    <select class="form-select" name="frak">
                        <option value="">Semua</option>
                        <?php foreach ($filter_rak as $rk): ?>
                        <option value="<?php echo $rk; ?>" <?php if($frak==$rk) echo 'selected'; ?>><?php echo $rk; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Cari/Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Books Grid -->
        <div class="row">
            <?php if (mysqli_num_rows($result_buku) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result_buku)): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="book-card">
                        <div class="text-center p-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book fa-4x"></i>
                        </div>
                        <div class="p-3">
                            <h6 class="mb-2" style="font-weight: 600;"><?php echo $row['judul']; ?></h6>
                            <p class="text-muted mb-2" style="font-size: 14px;">
                                <i class="fas fa-user"></i> <?php echo $row['penulis']; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> <?php echo $row['tahun']; ?>
                                </small>
                                <span class="badge <?php echo $row['stok'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <i class="fas fa-cubes"></i> Stok: <?php echo $row['stok']; ?>
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
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada buku yang ditemukan</h5>
                        <p class="text-muted">Coba ubah kata kunci pencarian Anda</p>
                        <a href="daftar_buku.php" class="btn btn-primary">
                            <i class="fas fa-refresh"></i> Lihat Semua Buku
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (mysqli_num_rows($result_buku) > 12): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
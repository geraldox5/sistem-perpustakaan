<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: ../login.php');
    exit();
}

// Ambil data buku
$query_books = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul";
$result_books = mysqli_query($koneksi, $query_books);

// Ambil data user dari anggota berdasarkan id_user
$user = null;
$query_user = "SELECT * FROM anggota WHERE id_user = " . $_SESSION['user_id'] . " LIMIT 1";
$result_user = mysqli_query($koneksi, $query_user);
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
}

// Handle form
$show_preview = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['preview'])) {
    $show_preview = true;
    $nama = htmlspecialchars($_POST['nama']);
    $nim = htmlspecialchars($_POST['nim']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $prodi = htmlspecialchars($_POST['prodi']);
    $id_buku = $_POST['id_buku'];
    $tanggal = htmlspecialchars($_POST['tanggal']);
    // Ambil detail buku
    $buku = null;
    $q = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = '".mysqli_real_escape_string($koneksi, $id_buku)."'");
    if ($q && mysqli_num_rows($q) > 0) {
        $buku = mysqli_fetch_assoc($q);
    }
}

$today_id = date('Y-m-d');
function tgl_indo($tgl) {
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $exp = explode('-', $tgl);
    return $exp[2].' '.$bulan[(int)$exp[1]].' '.$exp[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Pinjam Buku - Perpustakaan</title>
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

        .card { border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        .btn-primary:hover { background: #00a085; border-color: #00a085; }
        .preview-surat {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            max-width: 700px;
            margin: 0 auto;
        }
        .surat-header {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .ttd-area {
            width: 320px;
            margin-left: auto;
            margin-top: 60px;
            text-align: right;
            font-size: 1rem;
        }
        .ttd-label {
            font-size: 1rem;
            margin-bottom: 0;
        }
        .ttd-nama {
            font-weight: 600;
            margin-bottom: 0;
        }
        .ttd-nim {
            font-size: 0.98rem;
            color: #444;
        }
        @media print {
            body * { visibility: hidden !important; }
            #surat-pengajuan, #surat-pengajuan * { visibility: visible !important; }
            #surat-pengajuan {
                position: absolute; left: 0; top: 0; width: 210mm; min-height: 297mm; max-width: 210mm;
                margin: 0 auto; background: #fff; box-shadow: none; border: none; padding: 32px;
                display: flex; flex-direction: column; align-items: center;
            }
            .no-print { display: none !important; }
            .ttd-area {
                position: absolute;
                right: 48px;
                bottom: 64px;
                width: 320px;
                text-align: right;
            }
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
            <a href="riwayat.php" class="menu-item">
                <i class="fas fa-history"></i> Riwayat Pinjam Buku
            </a>
            <a href="ubah_profil.php" class="menu-item">
                <i class="fas fa-user-edit"></i> Ubah Data Pribadi
            </a>
            <a href="pinjam_buku.php" class="menu-item active">
                <i class="fas fa-hand-holding"></i> Pinjam Buku
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Pinjam Buku</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h4 class="mb-0"><i class="fas fa-hand-holding"></i> Form Pengajuan Pinjam Buku</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!$show_preview): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Peminjam</label>
                                    <input type="text" name="nama" class="form-control" required value="<?php echo $user ? htmlspecialchars($user['nama']) : '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nomor Induk Mahasiswa (NIM)</label>
                                    <input type="text" name="nim" class="form-control" required value="<?php echo $user ? htmlspecialchars($user['id_anggota']) : '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kode Kelas</label>
                                    <input type="text" name="kelas" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Program Studi</label>
                                    <input type="text" name="prodi" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Judul Buku</label>
                                    <select name="id_buku" class="form-select" required>
                                        <option value="">Pilih Buku</option>
                                        <?php while ($b = mysqli_fetch_assoc($result_books)): ?>
                                        <option value="<?php echo $b['id_buku']; ?>"><?php echo $b['judul']; ?> (<?php echo $b['jenis_buku']; ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pinjam</label>
                                    <input type="date" name="tanggal" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <button type="submit" name="preview" class="btn btn-primary"><i class="fas fa-eye"></i> Preview Surat</button>
                            </form>
                            <?php else: ?>
                            <div class="preview-surat" id="surat-pengajuan">
                                <div class="surat-header">PERPUSTAKAAN UNIVERSITAS PAMULANG</div>
                                <h5 class="text-center mb-4">Surat Pengajuan Peminjaman Buku</h5>
                                <table class="table table-borderless mb-0">
                                    <tr><th width="200">Nama Peminjam</th><td>: <?php echo $nama; ?></td></tr>
                                    <tr><th>NIM</th><td>: <?php echo $nim; ?></td></tr>
                                    <tr><th>Kode Kelas</th><td>: <?php echo $kelas; ?></td></tr>
                                    <tr><th>Program Studi</th><td>: <?php echo $prodi; ?></td></tr>
                                    <tr><th>Judul Buku</th><td>: <?php echo $buku ? $buku['judul'] : '-'; ?></td></tr>
                                    <tr><th>Jenis Buku</th><td>: <?php echo $buku ? $buku['jenis_buku'] : '-'; ?></td></tr>
                                    <tr><th>Tanggal Pinjam</th><td>: <?php echo $tanggal; ?></td></tr>
                                </table>
                                <div class="mt-4 mb-2">Dengan ini saya mengajukan permohonan peminjaman buku di perpustakaan.</div>
                                <div class="ttd-area">
                                    <div style="font-size:1rem; margin-bottom:12px;">Tangerang, <?php echo tgl_indo($today_id); ?></div>
                                    <div class="ttd-label">Hormat saya,</div>
                                    <div style="height:48px;"></div>
                                    <div class="ttd-nama"><?php echo $nama; ?></div>
                                    <div class="ttd-nim">NIM: <?php echo $nim; ?></div>
                                </div>
                            </div>
                            <div class="mt-3 no-print">
                                <button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print"></i> Print Surat</button>
                                <button id="btn-pdf" class="btn btn-success ms-2"><i class="fas fa-download"></i> Unduh PDF</button>
                                <a href="pinjam_buku.php" class="btn btn-secondary ms-2">Buat Pengajuan Baru</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    document.getElementById('btn-pdf')?.addEventListener('click', function(e) {
        e.preventDefault();
        var element = document.getElementById('surat-pengajuan');
        var opt = {
            margin:       0.5,
            filename:     'Surat_Pengajuan_Pinjam_Buku.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    });
    </script>
</body>
</html> 
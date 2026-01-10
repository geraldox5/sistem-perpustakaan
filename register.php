<?php
session_start();
include 'koneksi.php';

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $role = mysqli_real_escape_string($koneksi, $_POST['role'] ?? 'member');
    
    // Validasi role
    if (!in_array($role, ['admin', 'staff', 'member'])) {
        $role = 'member';
    }

    // Jika role member, ambil data anggota
    if ($role == 'member') {
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
        $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
        $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    }

    // Validasi dasar
    if (!$username || !$password || !$password2) {
        $error = 'Username dan password wajib diisi!';
    } elseif ($password !== $password2) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif ($role == 'member' && (!$nama || !$nim || !$kelas || !$prodi || !$no_hp)) {
        $error = 'Semua field wajib diisi untuk anggota!';
    } else {
        // Cek username unik
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Insert ke users dengan role pilihan
            $insert_user = mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hash', '$role')");
            if ($insert_user) {
                $id_user = mysqli_insert_id($koneksi);
                
                // Jika role member, insert ke tabel anggota juga dengan id_user
                if ($role == 'member') {
                    $insert_anggota = mysqli_query($koneksi, "INSERT INTO anggota (id_user, nama, nim, kelas, program_studi, no_hp, password) VALUES ($id_user, '$nama', '$nim', '$kelas', '$prodi', '$no_hp', '$password_hash')");
                    if (!$insert_anggota) {
                        $error = 'Gagal menyimpan data anggota.';
                        // Rollback user
                        mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id_user'");
                    } else {
                        $success = 'Registrasi berhasil! Silakan login.';
                        header('Location: login.php?register=success');
                        exit();
                    }
                } else {
                    // Untuk admin/staff, tidak perlu insert ke anggota
                    $success = 'Registrasi berhasil! Silakan login.';
                    header('Location: login.php?register=success');
                    exit();
                }
            } else {
                $error = 'Gagal menyimpan data user.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f7fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 184, 148, 0.10), 0 1.5px 6px rgba(0,0,0,0.04);
            width: 100%;
            max-width: 420px;
            padding: 32px 28px 28px 28px;
            animation: fadeIn 0.7s cubic-bezier(.39,.575,.56,1.000);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-header {
            text-align: center;
            margin-bottom: 22px;
        }
        .register-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #00b894;
            margin-bottom: 6px;
        }
        .register-header p {
            color: #718096;
            font-size: 15px;
            margin-bottom: 0;
        }
        .register-form {
            width: 100%;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #2d3748;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            background: #f8fafc;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #00b894;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(0,184,148,0.08);
        }
        select.form-control {
            cursor: pointer;
        }
        .member-fields {
            display: block;
        }
        .member-fields.hidden {
            display: none;
        }
        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(90deg, #00b894 0%, #00a085 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(0,184,148,0.08);
            transition: background 0.2s, transform 0.15s;
        }
        .btn-register:hover, .btn-register:focus {
            background: linear-gradient(90deg, #00a085 0%, #00b894 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }
        @media (max-width: 480px) {
            .register-container {
                margin: 16px;
                padding: 18px 6px 12px 6px;
                border-radius: 12px;
            }
        }
        .login-link {
            text-align:center;
            margin-top:18px;
        }
        .login-link a {
            color:#00b894;
            font-size:14px;
            text-decoration:none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const memberFields = document.getElementById('memberFields');
            const memberInputs = memberFields.querySelectorAll('input');
            
            function toggleMemberFields() {
                if (roleSelect.value === 'member') {
                    memberFields.classList.remove('hidden');
                    memberInputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                } else {
                    memberFields.classList.add('hidden');
                    memberInputs.forEach(input => {
                        input.removeAttribute('required');
                        input.value = '';
                    });
                }
            }
            
            roleSelect.addEventListener('change', toggleMemberFields);
            toggleMemberFields(); // Initial call
        });
    </script>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="fas fa-user-plus"></i> Daftar Akun</h1>
            <p>Buat akun baru untuk akses perpustakaan</p>
        </div>
        <div class="register-form">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="role">Pilih Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="member" selected>Member (Anggota)</option>
                        <option value="staff">Staff Perpustakaan</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password2">Konfirmasi Password</label>
                    <input type="password" id="password2" name="password2" class="form-control" required>
                </div>
                
                <!-- Field khusus untuk Member -->
                <div id="memberFields" class="member-fields">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="nim">NIM</label>
                        <input type="text" id="nim" name="nim" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="prodi">Program Studi</label>
                        <input type="text" id="prodi" name="prodi" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="no_hp">No. HP</label>
                        <input type="text" id="no_hp" name="no_hp" class="form-control">
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </form>
            <div class="login-link">
                <a href="login.php">Sudah punya akun? Login di sini</a>
            </div>
        </div>
    </div>
</body>
</html> 
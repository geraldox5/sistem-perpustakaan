<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Trim spasi agar input lebih toleran
    $username_input = trim($_POST['username']);
    $password_input = $_POST['password'];

    // Hindari SQL injection dasar
    $username = mysqli_real_escape_string($koneksi, $username_input);

    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        $hash_in_db = $user['password'];

        // 1) Coba verifikasi sebagai password hash (standar PHP)
        $valid = false;
        if (!empty($hash_in_db) && password_verify($password_input, $hash_in_db)) {
            $valid = true;
        } 
        // 2) Fallback: jika di database tersimpan plaintext (misal '123'), izinkan juga
        elseif ($password_input === $hash_in_db) {
            $valid = true;
        }

        if ($valid) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } elseif ($user['role'] === 'staff') {
                header('Location: staff/index.php');
            } else {
                header('Location: user/index.php');
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f7fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-wrapper {
            display: flex;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 184, 148, 0.10), 0 1.5px 6px rgba(0,0,0,0.04);
            width: 100%;
            max-width: 1000px;
            overflow: hidden;
            animation: fadeIn 0.7s cubic-bezier(.39,.575,.56,1.000);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }
        .login-logo {
            font-size: 4rem;
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .login-logo i {
            color: white;
        }
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .login-description {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 40px;
            opacity: 0.95;
            max-width: 400px;
        }
        .btn-register {
            padding: 13px 30px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-register:hover {
            background: white;
            color: #00b894;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .login-right {
            flex: 1;
            padding: 50px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-form-header {
            margin-bottom: 30px;
        }
        .login-form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .login-form-header p {
            color: #718096;
            font-size: 15px;
        }
        .login-form {
            width: 100%;
        }
        .form-group {
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 500;
            color: #2d3748;
            font-size: 14px;
        }
        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b2bec3;
            font-size: 15px;
        }
        .form-control {
            width: 100%;
            padding: 13px 13px 13px 40px;
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
        .btn-login {
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
        .btn-login:hover, .btn-login:focus {
            background: linear-gradient(90deg, #00a085 0%, #00b894 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 100%;
            }
            .login-left {
                padding: 40px 30px;
            }
            .login-logo {
                font-size: 3rem;
                margin-bottom: 20px;
            }
            .login-title {
                font-size: 1.5rem;
            }
            .login-description {
                font-size: 14px;
                margin-bottom: 30px;
            }
            .login-right {
                padding: 40px 30px;
            }
        }
        @media (max-width: 480px) {
            .login-left {
                padding: 30px 20px;
            }
            .login-right {
                padding: 30px 20px;
            }
            .login-logo {
                font-size: 2.5rem;
            }
            .login-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Bagian Kiri: Logo, Deskripsi, Tombol Daftar -->
        <div class="login-left">
            <div class="login-logo">
                <i class="fas fa-book-open"></i>
            </div>
            <h1 class="login-title">Sistem Perpustakaan</h1>
            <p class="login-description">
                Kelola peminjaman buku dengan mudah dan efisien. Akses katalog buku, 
                riwayat peminjaman, dan fitur lainnya untuk mendukung kegiatan belajar Anda.
            </p>
            <a href="register.php" class="btn-register">
                <i class="fas fa-user-plus"></i> Daftar Akun
            </a>
        </div>
        
        <!-- Bagian Kanan: Form Login -->
        <div class="login-right">
            <div class="login-form-header">
                <h2>Selamat Datang Kembali</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>
            <div class="login-form">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            if (!username || !password) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Username dan password harus diisi!',
                    confirmButtonColor: '#00b894'
                });
            }
        });
    </script>
</body>
</html> 
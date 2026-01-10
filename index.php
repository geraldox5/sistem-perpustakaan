<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan - Portal Perpustakaan Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00b894;
            --secondary-color: #00a085;
            --accent-color: #667eea;
            --text-color: #2d3748;
            --light-bg: #f7fafc;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .logo i {
            font-size: 2rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-menu a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-menu a:hover {
            color: var(--primary-color);
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 184, 148, 0.4);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 1s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: var(--white);
            color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255,255,255,0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .btn-secondary:hover {
            background: var(--white);
            color: var(--primary-color);
        }

        /* Features Section */
        .features {
            padding: 80px 2rem;
            background: var(--light-bg);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #718096;
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .feature-card p {
            color: #718096;
            line-height: 1.8;
        }

        /* Quote Section */
        .quote-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 80px 2rem;
            text-align: center;
            color: white;
        }

        .quote {
            max-width: 800px;
            margin: 0 auto;
        }

        .quote-text {
            font-size: 2rem;
            font-weight: 600;
            font-style: italic;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .quote-author {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Stats Section */
        .stats {
            padding: 80px 2rem;
            background: var(--white);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #718096;
            font-weight: 500;
        }

        /* Footer */
        footer {
            background: #2d3748;
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-text {
            font-size: 1rem;
            opacity: 0.8;
            margin-top: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                gap: 1rem;
            }

            .nav-menu a {
                font-size: 0.9rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            nav {
                padding: 1rem;
            }
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <a href="#home" class="logo">
                <i class="fas fa-book-open"></i>
                <span>Perpustakaan</span>
            </a>
            <ul class="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#lokasi">Lokasi</a></li>
                <li><a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1><i class="fas fa-book-reader"></i> Selamat Datang di Perpustakaan</h1>
            <p>Membuka Jendela Dunia Melalui Buku, Menumbuhkan Semangat Membaca untuk Masa Depan yang Lebih Cerah</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Masuk/Registrasi</a>
                <a href="#about" class="btn-secondary"><i class="fas fa-info-circle"></i> Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="about" class="features">
        <div class="container">
            <h2 class="section-title">Tentang Perpustakaan</h2>
            <p class="section-subtitle">Rumah Pengetahuan, Tempat Semangat Membaca Berkembang</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Koleksi Lengkap</h3>
                    <p>Ratusan buku dari berbagai kategori tersedia untuk memenuhi kebutuhan literasi Anda. Dari fiksi hingga non-fiksi, kami punya semuanya.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Akses Mudah</h3>
                    <p>Sistem perpustakaan digital yang memudahkan Anda untuk meminjam, mengembalikan, dan mengelola buku dengan praktis dan efisien.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Layanan 24/7</h3>
                    <p>Akses katalog buku dan kelola peminjaman kapan saja, di mana saja. Perpustakaan digital siap melayani kebutuhan membaca Anda.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Edukasi & Literasi</h3>
                    <p>Mendukung program literasi dan pendidikan dengan menyediakan sumber belajar yang berkualitas untuk semua kalangan.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Semangat Membaca</h3>
                    <p>Membangun budaya membaca yang kuat. Setiap halaman yang dibaca adalah langkah menuju pengetahuan yang lebih luas.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Kualitas Terjamin</h3>
                    <p>Buku-buku terpilih dengan kualitas terbaik untuk memberikan pengalaman membaca yang memuaskan dan bermanfaat.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quote Section -->
    <section class="quote-section">
        <div class="quote">
            <p class="quote-text">
                "Buku adalah jendela dunia, dan membaca adalah kuncinya. 
                Mari bersama-sama membuka pintu pengetahuan dan menumbuhkan semangat membaca!"
            </p>
            <p class="quote-author">— Perpustakaan Digital</p>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <h2 class="section-title">Mengapa Memilih Kami?</h2>
            <p class="section-subtitle">Komitmen Kami untuk Membangun Budaya Literasi</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><i class="fas fa-book-open"></i></div>
                    <div class="stat-label">Koleksi Buku Lengkap</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><i class="fas fa-user-friends"></i></div>
                    <div class="stat-label">Layanan Ramah</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><i class="fas fa-laptop"></i></div>
                    <div class="stat-label">Sistem Digital</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><i class="fas fa-trophy"></i></div>
                    <div class="stat-label">Terpercaya</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lokasi Section -->
    <section id="lokasi" class="features" style="background: var(--white);">
        <div class="container">
            <h2 class="section-title">Lokasi Perpustakaan</h2>
            <p class="section-subtitle">Temukan Kami di Lokasi Strategis</p>
            
            <div class="feature-card" style="max-width: 600px; margin: 2rem auto;">
                <div class="feature-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Alamat Perpustakaan</h3>
                <p style="font-size: 1.1rem; line-height: 1.8; margin-top: 1rem;">
                    Perpustakaan Digital<br>
                    Jl. Pendidikan No. 123<br>
                    Kota Pendidikan, 12345<br><br>
                    <strong>Jam Operasional:</strong><br>
                    Senin - Jumat: 08:00 - 17:00 WIB<br>
                    Sabtu: 08:00 - 15:00 WIB
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-text">
                <p></i> © 2026 - Dibuat oleh Kelompok 9 - Kelas 07TPLP006 - G05</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header shadow on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
            } else {
                header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>

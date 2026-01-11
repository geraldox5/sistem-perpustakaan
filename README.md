# Sistem Perpustakaan - Web Application

Aplikasi sistem perpustakaan berbasis web yang dibangun menggunakan PHP, MySQL, HTML, CSS, dan JavaScript dengan desain modern dan responsif.

## 🎯 Fitur Utama

### 🔐 Login & User Role
- **Admin**: Akses penuh (anggota, buku, peminjaman, pengembalian, laporan)
- **User**: Hanya bisa melihat buku, meminjam, dan melihat riwayat
- Session-based authentication dengan role-based access control

### 🗄️ Manajemen Data
- **CRUD Anggota**: Tambah, edit, hapus data anggota perpustakaan
- **CRUD Buku**: Kelola katalog buku dengan stok otomatis
- **Peminjaman**: Sistem peminjaman dengan stored procedure
- **Pengembalian**: Proses pengembalian buku dengan trigger

### 📊 Laporan & Statistik
- Buku yang sedang dipinjam
- Statistik peminjaman per bulan (dengan Chart.js)
- Daftar peminjam paling aktif
- Rata-rata lama peminjaman
- Buku terpopuler


## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Charts**: Chart.js
- **Alerts**: SweetAlert2

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Browser modern dengan JavaScript enabled

## 🚀 Cara Instalasi

### 1. Clone Repository
```bash
git clone [repository-url]
cd Sistem-Aplikasi-perpustakaan
```

### 2. Setup Database
1. Buat database MySQL baru dengan nama `perpustakaan`
2. Import file `didy3854_perpus.sql` ke database tersebut
3. File ini akan membuat:
   - Semua tabel yang diperlukan
   - Stored procedures
   - Triggers
   - Data awal (users, anggota, buku)

### 3. Konfigurasi Database
Edit file `koneksi.php` dan sesuaikan dengan konfigurasi database Anda:
```php
$host = "localhost";
$user = "root";
$password = "";
$database = "perpustakaan";
```

### 4. Setup Web Server
1. Letakkan semua file di folder web server (htdocs untuk XAMPP)
2. Pastikan web server dan MySQL berjalan
3. Akses aplikasi melalui browser: `http://localhost/(folder yang digunakan)'

## 👥 Akun Default

### Admin
- **Username**: mia
- **Password**: 123
- **Akses**: Full access ke semua fitur

- ### staff
- **Username**: geraldo
- **Password**: 123
- **Akses**: Kelola Buku, Kelola Pinjaman, Kelola Pengembalian, Melihat Laporan

### User
- **Username**: emison
- **Password**: 123
- **Akses**: Terbatas (lihat buku, pinjam, riwayat), kelola profil pribadi

## 📁 Struktur File

```
Basdat-Aplikasi-perpustakaan/
├── login.php                 # Halaman login
├── logout.php                # Logout handler
├── koneksi.php               # Database connection
├── database.sql              # Database schema & data
├── README.md                 # Dokumentasi
├── admin/                    # Admin panel
│   ├── index.php            # Dashboard admin
│   ├── anggota.php          # CRUD anggota
│   ├── buku.php             # CRUD buku
│   ├── peminjaman.php       # Kelola peminjaman
│   ├── pengembalian.php     # Kelola pengembalian
│   ├── kelola_staff.php     # Kelola akun staff perpustakaan
│   └── laporan.php          # Laporan & statistik
── staff/                    # Admin panel
│   ├── index.php            # Dashboard staff
│   ├── anggota.php          # CRUD anggota
│   ├── buku.php             # CRUD buku
│   ├── peminjaman.php       # Kelola peminjaman
│   ├── pengembalian.php     # Kelola pengembalian
│   └── laporan.php          # Laporan & statistik
└── user/                     # User panel
    ├── index.php            # Dashboard user
    ├── daftar_buku.php      # Browse buku
    ├── pinjam_buku.php      # Form peminjaman
    └── riwayat.php          # Riwayat peminjaman
```

## 🗄️ Struktur Database

### Tabel Utama
- **users**: Data pengguna sistem (admin/user)
- **anggota**: Data anggota perpustakaan
- **buku**: Katalog buku dengan stok
- **peminjaman**: Data peminjaman buku
- **pengembalian**: Data pengembalian buku

### Stored Procedures
- **tambah_peminjaman()**: Menambah peminjaman + update stok
- **tambah_pengembalian()**: Menambah pengembalian + update stok

### Triggers
- **trigger_kurangi_stok**: Kurangi stok otomatis saat pinjam
- **trigger_tambah_stok**: Tambah stok otomatis saat kembalikan

## 🎨 Fitur Desain

### Color Scheme
- **Primary**: #00b894 (Hijau elegan)
- **Secondary**: #00a085 (Hijau gelap)
- **Accent**: #667eea (Biru)
- **Background**: #f7fafc (Abu-abu terang)

### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700

### Components
- Modern card design dengan shadow
- Rounded corners (12px border-radius)
- Smooth transitions dan hover effects
- Responsive grid layout
- Interactive modals dan alerts

## 🔧 Fitur Teknis

### Security
- Session-based authentication
- SQL injection prevention dengan mysqli_real_escape_string
- Role-based access control
- Password hashing (bcrypt)

### Performance
- Optimized SQL queries dengan JOIN
- Efficient database indexing
- Minimal JavaScript untuk performa cepat
- CDN untuk external libraries

### User Experience
- Intuitive navigation dengan sidebar
- Real-time form validation
- Interactive alerts dan confirmations
- Responsive design untuk semua device
- Loading states dan feedback

## 📊 Laporan yang Tersedia

1. **Buku yang Sedang Dipinjam**: Daftar buku belum dikembalikan
2. **Statistik Peminjaman per Bulan**: Grafik dengan Chart.js
3. **Peminjam Paling Aktif**: Top 10 peminjam berdasarkan frekuensi
4. **Rata-rata Lama Peminjaman**: Perhitungan otomatis
5. **Buku Terpopuler**: Ranking berdasarkan jumlah peminjaman

## 🚀 Cara Penggunaan

### Untuk Admin
1. Login dengan akun admin
2. Kelola data anggota dan buku
3. Proses peminjaman dan pengembalian
4. Kelola User Staff
5. Lihat laporan dan statistik
6. Monitor aktivitas perpustakaan

7. ### Untuk Staff
1. Login dengan akun staff
2. Kelola data anggota dan buku
3. Proses peminjaman dan pengembalian
4. Lihat laporan dan statistik
5. Monitor aktivitas perpustakaan

### Untuk User
1. Login dengan akun user
2. Browse katalog buku
3. Pinjam buku yang tersedia
4. Lihat riwayat peminjaman
5. Monitor status peminjaman

## 🐛 Troubleshooting

### Masalah Umum
1. **Database Connection Error**: Periksa konfigurasi di `koneksi.php`
2. **Page Not Found**: Pastikan file berada di folder web server yang benar
3. **Permission Denied**: Periksa permission folder dan file
4. **Session Error**: Pastikan session_start() ada di setiap file

### Log Error
- Periksa error log PHP di web server
- Periksa error log MySQL untuk masalah database
- Aktifkan error reporting untuk debugging

## 📝 Lisensi

2026 © geraldofirdaus@gmail.com. Proyek ini dibuat untuk keperluan akademis dan pembelajaran. Silakan digunakan dan dimodifikasi sesuai kebutuhan.

## 👨‍💻 Developer

Dibuat oleh @geraldofirdaus dengan ❤️ menggunakan teknologi modern untuk memberikan pengalaman terbaik dalam manajemen perpustakaan.

---


**Note**: Pastikan untuk selalu backup database sebelum melakukan perubahan atau update aplikasi. 


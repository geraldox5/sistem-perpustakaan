<?php
include 'koneksi.php';

echo "<h2>Test Koneksi Database</h2>";

// Test koneksi
if ($koneksi) {
    echo "✅ Koneksi database berhasil!<br><br>";
    
    // Test query data
    $query_users = "SELECT COUNT(*) as total FROM users";
    $result_users = mysqli_query($koneksi, $query_users);
    $users_count = mysqli_fetch_assoc($result_users)['total'];
    
    $query_anggota = "SELECT COUNT(*) as total FROM anggota";
    $result_anggota = mysqli_query($koneksi, $query_anggota);
    $anggota_count = mysqli_fetch_assoc($result_anggota)['total'];
    
    $query_buku = "SELECT COUNT(*) as total FROM buku";
    $result_buku = mysqli_query($koneksi, $query_buku);
    $buku_count = mysqli_fetch_assoc($result_buku)['total'];
    
    $query_peminjaman = "SELECT COUNT(*) as total FROM peminjaman";
    $result_peminjaman = mysqli_query($koneksi, $query_peminjaman);
    $peminjaman_count = mysqli_fetch_assoc($result_peminjaman)['total'];
    
    echo "<h3>Data yang tersedia:</h3>";
    echo "👥 Users: $users_count data<br>";
    echo "👤 Anggota: $anggota_count data<br>";
    echo "📚 Buku: $buku_count data<br>";
    echo "📖 Peminjaman: $peminjaman_count data<br><br>";
    
    // Tampilkan beberapa data sample
    echo "<h3>Sample Data:</h3>";
    
    // Sample anggota
    $query_sample_anggota = "SELECT * FROM anggota LIMIT 3";
    $result_sample_anggota = mysqli_query($koneksi, $query_sample_anggota);
    echo "<strong>Anggota (3 sample):</strong><br>";
    while ($row = mysqli_fetch_assoc($result_sample_anggota)) {
        echo "- {$row['nama']} ({$row['no_hp']})<br>";
    }
    echo "<br>";
    
    // Sample buku
    $query_sample_buku = "SELECT * FROM buku LIMIT 3";
    $result_sample_buku = mysqli_query($koneksi, $query_sample_buku);
    echo "<strong>Buku (3 sample):</strong><br>";
    while ($row = mysqli_fetch_assoc($result_sample_buku)) {
        echo "- {$row['judul']} oleh {$row['penulis']} (Stok: {$row['stok']})<br>";
    }
    
} else {
    echo "❌ Koneksi database gagal!";
}
?> 
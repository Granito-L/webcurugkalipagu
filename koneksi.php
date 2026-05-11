<?php
/**
 * koneksi.php
 * Koneksi PDO ke database MySQL untuk wisata_kalipagu
 * 
 * Cara pakai:
 *   require_once 'koneksi.php';   // dari root
 *   require_once '../koneksi.php'; // dari subfolder admin/
 * 
 * Variabel yang tersedia setelah include:
 *   $pdo  — objek PDO siap pakai
 *   $stmt — untuk query (buat di file masing-masing)
 */

// ============================================================
// Konfigurasi Database — sesuaikan dengan environment Anda
// ============================================================
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'wisata_kalipagu');
define('DB_USER',    'root');       // User MySQL XAMPP default
define('DB_PASS',    '');           // Password MySQL XAMPP default (kosong)
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// DSN (Data Source Name)
// ============================================================
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    DB_HOST,
    DB_PORT,
    DB_NAME,
    DB_CHARSET
);

// ============================================================
// Opsi PDO — best practice untuk keamanan & performa
// ============================================================
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lempar exception saat error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Hasil fetch sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Gunakan prepared statement asli
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",      // Paksa encoding UTF-8
];

// ============================================================
// Buat koneksi PDO
// ============================================================
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
} catch (PDOException $e) {
    // Jangan tampilkan pesan error detail ke user di production!
    // Ganti dengan halaman error yang ramah.
    $error_message = 'Koneksi database gagal. Pastikan XAMPP MySQL sedang berjalan.';
    
    // Mode development: tampilkan detail error
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        $error_message .= '<br><small style="color:red">Debug: ' . htmlspecialchars($e->getMessage()) . '</small>';
    }
    
    die('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Error Koneksi</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f0fdf4;}
    .box{background:white;padding:2rem;border-radius:12px;border-left:4px solid #ef4444;max-width:500px;text-align:center;}
    h3{color:#dc2626;}</style></head><body>
    <div class="box"><h3>⚠️ Koneksi Gagal</h3><p>' . $error_message . '</p></div></body></html>');
}

// ============================================================
// Helper function: ambil semua settings dari database
// ============================================================
function get_settings(PDO $pdo): array {
    $stmt = $pdo->query("SELECT setting_key, setting_val FROM settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_val'];
    }
    return $settings;
}

// ============================================================
// Helper function: sanitasi output ke HTML (XSS prevention)
// ============================================================
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ============================================================
// Helper function: format tanggal ke Bahasa Indonesia
// ============================================================
function format_tanggal(string $tanggal): string {
    $bulan = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

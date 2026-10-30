<?php
/**
 * config/database.php
 * ---------------------------------------------------------------
 * Pengaturan zona waktu + koneksi ke database MySQL memakai PDO.
 * File ini di-include oleh includes/auth.php, sehingga otomatis
 * dijalankan di setiap halaman.
 */

// --- ZONA WAKTU ------------------------------------------------
// Tanpa pengaturan ini PHP memakai zona waktu server (sering UTC)
// sehingga countdown deadline bergeser beberapa jam.
const APP_TIMEZONE = 'Asia/Jakarta';
const DB_TIMEZONE  = '+07:00';   // harus sama dengan APP_TIMEZONE (WITA: +08:00, WIT: +09:00)
date_default_timezone_set(APP_TIMEZONE);   // mengatur fungsi date(), strtotime(), time() di PHP

// --- KONFIGURASI DATABASE --------------------------------------
// Sesuaikan dengan MySQL milikmu (XAMPP default: root tanpa password)
const DB_HOST = 'localhost';
const DB_NAME = 'jadwalinaja';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Mengembalikan objek koneksi PDO.
 * Memakai pola "singleton": koneksi hanya dibuat sekali, lalu dipakai ulang
 * (variabel static tetap tersimpan antar pemanggilan fungsi).
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                // Tampilkan error sebagai Exception (mudah dilacak saat debugging)
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Hasil query berupa array asosiatif: $row['judul']
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // false = prepared statement asli dari MySQL (lebih aman dari SQL injection)
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Samakan zona waktu sesi MySQL dengan PHP, agar NOW() konsisten
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . DB_TIMEZONE . "'",
            ]
        );
    }
    return $pdo;
}

<?php
/**
 * api/_bootstrap.php
 * ---------------------------------------------------------------
 * "Pintu masuk" bersama untuk semua endpoint API.
 * Setiap file di folder api/ memanggil file ini lebih dulu, sehingga
 * pengecekan keamanan cukup ditulis sekali.
 */
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');   // respon berupa JSON, bukan HTML

// Helper: kirim respon JSON + kode status HTTP, lalu hentikan program
function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Tiga lapis pengamanan sebelum endpoint boleh bekerja:
if (!is_logged_in())                         json_out(['ok' => false, 'error' => 'Belum login'], 401);          // 1) harus login
if ($_SERVER['REQUEST_METHOD'] !== 'POST')   json_out(['ok' => false, 'error' => 'Method tidak valid'], 405);   // 2) hanya metode POST
if (!verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) json_out(['ok' => false, 'error' => 'Token tidak valid'], 403); // 3) token CSRF cocok

// Baca isi request (JSON dari fetch di app.js) menjadi array PHP
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$uid   = current_user_id();   // ID pengguna yang sedang login
$pdo   = db();                // koneksi database untuk dipakai endpoint

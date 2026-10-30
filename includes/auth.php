<?php
/**
 * includes/auth.php
 * ---------------------------------------------------------------
 * Urusan sesi login dan keamanan CSRF.
 * Di-include oleh hampir semua halaman.
 */

// Mulai sesi PHP (tempat menyimpan data login) bila belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Muat konfigurasi database & zona waktu
require_once __DIR__ . '/../config/database.php';

// Apakah pengguna sudah login? (user_id disimpan di sesi saat login berhasil)
function is_logged_in(): bool { return isset($_SESSION['user_id']); }

// Dipasang di halaman privat: jika belum login, arahkan ke halaman login
function require_login(): void {
    if (!is_logged_in()) { header('Location: login.php'); exit; }
}

// Ambil ID pengguna yang sedang login (0 jika belum login)
function current_user_id(): int { return (int) ($_SESSION['user_id'] ?? 0); }

/* ---------- PERLINDUNGAN CSRF ----------
 * CSRF = serangan yang memaksa browser korban mengirim request tanpa sepengetahuannya.
 * Pertahanan: setiap form/request membawa token rahasia acak yang hanya diketahui sesi ini.
 */

// Buat token acak (sekali per sesi) lalu kembalikan
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}

// Menghasilkan input tersembunyi berisi token, untuk ditempel di dalam <form>
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Cocokkan token kiriman dengan token di sesi (hash_equals aman dari timing attack)
function verify_csrf(?string $token): bool {
    return $token !== null && hash_equals($_SESSION['csrf'] ?? '', $token);
}

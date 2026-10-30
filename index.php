<?php
/**
 * index.php
 * Halaman pintu masuk: hanya mengarahkan pengunjung.
 * Sudah login -> dashboard, belum login -> halaman login.
 */
require_once 'includes/auth.php';
header('Location: ' . (is_logged_in() ? 'dashboard.php' : 'login.php'));
exit;

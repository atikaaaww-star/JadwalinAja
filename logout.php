<?php
/**
 * logout.php
 * Mengakhiri sesi login lalu kembali ke halaman login.
 */
require_once 'includes/auth.php';
$_SESSION = [];        // kosongkan semua data sesi
session_destroy();     // hancurkan sesi
header('Location: login.php');
exit;

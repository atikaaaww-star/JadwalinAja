<?php
/**
 * includes/header.php
 * ---------------------------------------------------------------
 * Bagian ATAS setiap halaman: <head>, navbar, dan pesan flash.
 * Dipakai dengan: include 'includes/header.php';
 * Halaman pemanggil boleh mengisi $pageTitle sebelum meng-include file ini.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'JadwalinAja';          // judul tab; default jika halaman tidak mengisi
$current = basename($_SERVER['PHP_SELF']);          // nama file halaman aktif, untuk menandai menu
// Daftar menu navbar: 'file tujuan' => 'teks menu'
$nav = ['dashboard.php' => 'Dashboard', 'tasks.php' => 'Tugas', 'schedule.php' => 'Jadwal', 'subjects.php' => 'Mata Pelajaran'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <?php // viewport: membuat tampilan menyesuaikan lebar layar HP ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php // token CSRF diselipkan di sini agar bisa dibaca JavaScript (app.js) untuk request fetch ?>
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($pageTitle) ?> | JadwalinAja</title>
  <link rel="icon" type="image/svg+xml" href="assets/img/jadwalinaja-icon.svg">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php // Navbar hanya tampil jika sudah login (halaman login/daftar tidak punya navbar) ?>
<?php if (is_logged_in()): ?>
<header class="topbar">
  <a href="dashboard.php" class="brand"><img src="assets/img/jadwalinaja-icon.svg" alt="" height="34"> <span>Jadwalin<b>Aja</b></span></a>
  <nav>
    <?php foreach ($nav as $file => $label): ?>
      <?php // kelas "active" menandai menu halaman yang sedang dibuka ?>
      <a href="<?= $file ?>" class="<?= $current === $file ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <a href="logout.php" class="logout">Keluar</a>
  </nav>
</header>
<?php endif; ?>
<main class="container">
<?php // Tampilkan pesan flash (sukses/peringatan/error) bila ada, lalu otomatis hilang ?>
<?php if ($f = get_flash()): ?>
  <div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div>
<?php endif; ?>

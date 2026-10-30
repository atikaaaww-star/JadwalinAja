<?php
/**
 * login.php
 * Halaman masuk. Memeriksa email + password, lalu menyimpan ID pengguna di sesi.
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Sesi tidak valid, muat ulang halaman.';
    } else {
        // Cari pengguna berdasarkan email
        $st = db()->prepare("SELECT id, nama, password FROM users WHERE email = ?");
        $st->execute([trim($_POST['email'] ?? '')]);
        $u = $st->fetch();   // false jika email tidak ditemukan

        // password_verify membandingkan password yang diketik dengan hash di database
        if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
            session_regenerate_id(true);          // ganti ID sesi (cegah session fixation)
            $_SESSION['user_id'] = (int) $u['id'];   // tanda bahwa pengguna sudah login
            $_SESSION['nama']    = $u['nama'];
            header('Location: dashboard.php');
            exit;
        }
        // Pesan sengaja umum: tidak membocorkan apakah email atau password yang salah
        $error = 'Email atau password salah.';
    }
}
$pageTitle = 'Masuk';
include 'includes/header.php';
?>
<?php // ===== TAMPILAN FORM LOGIN ===== ?>
<div class="auth-card">
  <img src="assets/img/jadwalinaja-logo.svg" alt="JadwalinAja" class="auth-logo">
  <h2>Masuk</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Email <input type="email" name="email" required></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn btn-primary" type="submit">Masuk</button>
  </form>
  <p class="muted">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<?php include 'includes/footer.php'; ?>

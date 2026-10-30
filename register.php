<?php
/**
 * register.php
 * Halaman pendaftaran akun baru.
 * Alur: tampilkan form -> (saat disubmit) validasi -> simpan -> arahkan ke login.
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
// Jika sudah login, tidak perlu mendaftar lagi
if (is_logged_in()) { header('Location: dashboard.php'); exit; }

$errors = [];            // menampung semua pesan kesalahan validasi
$nama = $email = '';     // nilai awal field, agar isian tidak hilang saat ada error

// Blok ini hanya jalan ketika form dikirim (metode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) { $errors[] = 'Sesi tidak valid, muat ulang halaman.'; }
    $nama  = trim($_POST['nama'] ?? '');     // trim: buang spasi di awal/akhir
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // --- Validasi input ---
    if ($nama === '')                                $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Email tidak valid.';
    if (strlen($pass) < 8)                           $errors[] = 'Password minimal 8 karakter.';

    // Lanjut hanya jika semua validasi lolos
    if (!$errors) {
        $pdo = db();
        // Cek apakah email sudah dipakai (prepared statement: aman dari SQL injection)
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        } else {
            // Simpan akun. Password di-HASH, tidak pernah disimpan dalam bentuk asli.
            $pdo->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)")
                ->execute([$nama, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            set_flash('Registrasi berhasil, silakan masuk.');
            header('Location: login.php');   // pola PRG: redirect agar refresh tidak mengirim ulang form
            exit;
        }
    }
}
$pageTitle = 'Daftar';
include 'includes/header.php';
?>
<?php // ===== TAMPILAN FORM PENDAFTARAN ===== ?>
<div class="auth-card">
  <img src="assets/img/jadwalinaja-logo.svg" alt="JadwalinAja" class="auth-logo">
  <h2>Buat Akun</h2>
  <?php // Tampilkan setiap pesan error (jika ada) ?>
  <?php foreach ($errors as $er): ?><div class="alert alert-danger"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Nama <input type="text" name="nama" value="<?= e($nama) ?>" required></label>
    <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Password <input type="password" name="password" minlength="8" required></label>
    <button class="btn btn-primary" type="submit">Daftar</button>
  </form>
  <p class="muted">Sudah punya akun? <a href="login.php">Masuk</a></p>
</div>
<?php include 'includes/footer.php'; ?>

<?php
/**
 * subjects.php
 * Mengelola mata pelajaran: menambah (dengan warna penanda) dan menghapus.
 * Mata pelajaran ini nanti dipilih di form tugas.
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_login();
$uid = current_user_id();
$pdo = db();

// ---------- PROSES FORM (tambah / hapus) ----------
// Satu halaman melayani dua aksi, dibedakan oleh input tersembunyi "action"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? null)) {
    if (($_POST['action'] ?? '') === 'add') {
        $nama  = trim($_POST['nama'] ?? '');
        // Validasi kode warna: harus berformat #RRGGBB, jika tidak pakai biru default
        $warna = preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['warna'] ?? '') ? $_POST['warna'] : '#2563EB';
        if ($nama !== '') {
            $pdo->prepare("INSERT INTO subjects (user_id, nama, warna) VALUES (?, ?, ?)")->execute([$uid, $nama, $warna]);
            set_flash('Mata pelajaran ditambahkan.');
        }
    } elseif (($_POST['action'] ?? '') === 'delete') {
        // AND user_id = ? mencegah pengguna menghapus data milik orang lain
        $pdo->prepare("DELETE FROM subjects WHERE id = ? AND user_id = ?")->execute([(int) $_POST['id'], $uid]);
        set_flash('Mata pelajaran dihapus.');
    }
    header('Location: subjects.php');   // redirect agar refresh tidak mengirim ulang form
    exit;
}

// Ambil daftar mata pelajaran untuk ditampilkan
$st = $pdo->prepare("SELECT * FROM subjects WHERE user_id = ? ORDER BY nama");
$st->execute([$uid]);
$subjects = $st->fetchAll();

$pageTitle = 'Mata Pelajaran';
include 'includes/header.php';
?>
<h1>Mata Pelajaran</h1>

<?php // ===== FORM TAMBAH (sejajar menyamping) ===== ?>
<form method="post" class="card inline-form">
  <?= csrf_field() ?><input type="hidden" name="action" value="add">
  <label>Nama <input type="text" name="nama" required></label>
  <?php // input type="color" menampilkan pemilih warna bawaan browser ?>
  <label>Warna <input type="color" name="warna" value="#2563EB"></label>
  <button class="btn btn-primary" type="submit">Tambah</button>
</form>

<?php // ===== DAFTAR MATA PELAJARAN ===== ?>
<div class="card">
  <?php foreach ($subjects as $s): ?>
    <?php // Setiap baris adalah form kecil sendiri untuk aksi hapus ?>
    <form method="post" class="row-between">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>">
      <span><span class="dot" style="background:<?= e($s['warna']) ?>"></span><?= e($s['nama']) ?></span>
      <?php // onclick return confirm: jika pengguna klik "Batal", form tidak dikirim ?>
      <button class="btn btn-ghost" onclick="return confirm('Hapus mata pelajaran ini?')">Hapus</button>
    </form>
  <?php endforeach; ?>
  <?php if (!$subjects): ?><p class="muted">Belum ada mata pelajaran.</p><?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>

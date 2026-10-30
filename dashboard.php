<?php
/**
 * dashboard.php
 * Halaman utama setelah login. Menampilkan:
 *   - peringatan tugas yang hampir jatuh tempo
 *   - statistik jumlah tugas per status
 *   - 5 tugas dengan prioritas tertinggi (+ countdown)
 *   - jadwal belajar hari ini
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_login();                 // wajib login
$uid = current_user_id();
$pdo = db();
refresh_priorities($pdo, $uid);  // hitung ulang skor agar urutan selalu terbaru

// ---------- 1) Statistik: jumlah tugas per status ----------
$st = $pdo->prepare("SELECT status, COUNT(*) c FROM tasks WHERE user_id = ? GROUP BY status");
$st->execute([$uid]);
$stat = ['belum' => 0, 'proses' => 0, 'selesai' => 0];   // nilai awal 0 untuk tiap status
foreach ($st as $r) { $stat[$r['status']] = (int) $r['c']; }

// ---------- 2) Tugas yang jatuh tempo <= 2 hari (termasuk yang sudah terlambat) ----------
$st = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status <> 'selesai' AND deadline <= NOW() + INTERVAL 2 DAY");
$st->execute([$uid]);
$dueSoon = (int) $st->fetchColumn();   // fetchColumn: ambil satu nilai saja

// ---------- 3) Lima tugas prioritas teratas ----------
// LEFT JOIN: tugas tanpa mata pelajaran tetap ikut tampil (mapel = NULL)
$st = $pdo->prepare("SELECT t.*, s.nama AS mapel, s.warna FROM tasks t
    LEFT JOIN subjects s ON s.id = t.subject_id
    WHERE t.user_id = ? AND t.status <> 'selesai'
    ORDER BY t.skor_prioritas DESC, t.deadline ASC LIMIT 5");
$st->execute([$uid]);
$top = $st->fetchAll();

// ---------- 4) Sesi belajar hari ini ----------
$st = $pdo->prepare("SELECT ss.*, t.judul FROM study_slots ss JOIN tasks t ON t.id = ss.task_id
    WHERE ss.user_id = ? AND ss.tanggal = CURDATE() ORDER BY ss.jam_mulai");
$st->execute([$uid]);
$today = $st->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<h1>Halo, <?= e($_SESSION['nama'] ?? '') ?> 👋</h1>

<?php // Banner peringatan hanya muncul jika ada tugas yang mendesak ?>
<?php if ($dueSoon > 0): ?>
  <div class="alert alert-warning">⚠️ <b><?= $dueSoon ?> tugas</b> jatuh tempo dalam 2 hari ke depan. Cek daftar prioritas di bawah.</div>
<?php endif; ?>

<?php // ===== KARTU STATISTIK (3 kolom) ===== ?>
<section class="stats">
  <div class="stat"><span><?= $stat['belum'] ?></span>Belum dikerjakan</div>
  <div class="stat"><span><?= $stat['proses'] ?></span>Sedang dikerjakan</div>
  <div class="stat"><span><?= $stat['selesai'] ?></span>Selesai</div>
</section>

<div class="grid-2">
  <?php // ===== KOLOM KIRI: PRIORITAS TERATAS ===== ?>
  <section class="card">
    <div class="card-head"><h2>Prioritas Teratas</h2><a class="btn btn-primary" href="task_form.php">+ Tugas</a></div>
    <?php if (!$top): ?>
      <p class="muted">Belum ada tugas. Yuk tambahkan tugas pertamamu!</p>
    <?php endif; ?>
    <?php // Ulangi untuk setiap tugas; [$lbl,$cls] = label & kelas warna dari skor ?>
    <?php foreach ($top as $t): [$lbl, $cls] = priority_label((float) $t['skor_prioritas']); ?>
      <?php // Kelas border-* memberi garis warna di sisi kiri sesuai kedekatan deadline ?>
      <div class="task-item border-<?= deadline_class($t['deadline'], $t['status']) ?>">
        <div>
          <a href="task_form.php?id=<?= $t['id'] ?>"><b><?= e($t['judul']) ?></b></a>
          <div class="muted small">
            <?php // Titik warna + nama mata pelajaran (hanya jika tugas punya mata pelajaran) ?>
            <?php if ($t['mapel']): ?><span class="dot" style="background:<?= e($t['warna']) ?>"></span><?= e($t['mapel']) ?> · <?php endif; ?>
            <?= format_tanggal($t['deadline']) ?>
          </div>
        </div>
        <div class="right">
          <span class="badge badge-<?= $cls ?>"><?= $lbl ?> (<?= (int) $t['skor_prioritas'] ?>)</span>
          <?php // data-ts = deadline dalam milidetik; teks countdown diisi oleh app.js ?>
          <div class="countdown small" data-ts="<?= strtotime($t['deadline']) * 1000 ?>"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <?php // ===== KOLOM KANAN: JADWAL HARI INI ===== ?>
  <section class="card">
    <div class="card-head"><h2>Jadwal Belajar Hari Ini</h2><a href="schedule.php">Lihat semua</a></div>
    <?php if (!$today): ?>
      <p class="muted">Belum ada jadwal hari ini. Atur waktu luang lalu buat jadwal di menu <a href="schedule.php">Jadwal</a>.</p>
    <?php endif; ?>
    <?php foreach ($today as $s): ?>
      <label class="slot">
        <?php // js-slot + data-id: app.js mengirim perubahan centang ke api/toggle_slot.php ?>
        <input type="checkbox" class="js-slot" data-id="<?= $s['id'] ?>" <?= $s['selesai'] ? 'checked' : '' ?>>
        <?php // substr(...,0,5) memotong "19:00:00" menjadi "19:00" ?>
        <span class="time"><?= substr($s['jam_mulai'], 0, 5) ?> – <?= substr($s['jam_selesai'], 0, 5) ?></span>
        <?= e($s['judul']) ?>
      </label>
    <?php endforeach; ?>
  </section>
</div>
<?php include 'includes/footer.php'; ?>

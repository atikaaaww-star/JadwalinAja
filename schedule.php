<?php
/**
 * schedule.php
 * Halaman Jadwal Belajar. Terdiri dari dua bagian:
 *   KIRI  : mengisi waktu luang mingguan + tombol "Buat Jadwal Otomatis"
 *   KANAN : menampilkan hasil jadwal 14 hari ke depan (bisa dicentang)
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_login();
$uid = current_user_id();
$pdo = db();

// ---------- PROSES FORM ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? null)) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_availability') {
        // Strategi sederhana: hapus semua waktu luang lama, lalu simpan ulang dari form
        $pdo->prepare("DELETE FROM availability WHERE user_id = ?")->execute([$uid]);
        $ins = $pdo->prepare("INSERT INTO availability (user_id, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?)");
        foreach (HARI as $n => $_) {              // $n = nomor hari 1..7
            $m = $_POST['mulai'][$n] ?? '';       // jam mulai hari ke-$n
            $s = $_POST['selesai'][$n] ?? '';     // jam selesai hari ke-$n
            // Simpan hanya jika keduanya terisi dan jam mulai lebih awal dari jam selesai
            if ($m && $s && $m < $s) $ins->execute([$uid, $n, $m, $s]);
        }
        set_flash('Waktu luang disimpan.');

    } elseif ($action === 'generate') {
        refresh_priorities($pdo, $uid);           // pastikan skor terbaru sebelum menyusun
        $r = generate_schedule($pdo, $uid);       // logika inti ada di includes/functions.php
        if ($r['gagal']) {
            // Ada tugas yang tidak muat di waktu luang: beri peringatan beserta daftar tugasnya
            set_flash('Jadwal dibuat, tetapi waktu tidak cukup untuk: ' . implode(', ', $r['gagal']) . '. Tambah waktu luang atau kurangi estimasi.', 'warning');
        } else {
            set_flash("Jadwal berhasil dibuat ({$r['dibuat']} sesi belajar).");
        }
    }
    header('Location: schedule.php');
    exit;
}

// ---------- DATA UNTUK DITAMPILKAN ----------
// Waktu luang yang sudah tersimpan -> array dengan kunci nomor hari agar mudah dicari
$av = $pdo->prepare("SELECT hari, jam_mulai, jam_selesai FROM availability WHERE user_id = ?");
$av->execute([$uid]);
$avail = [];
foreach ($av as $a) { $avail[(int) $a['hari']] = $a; }

// Sesi belajar mulai hari ini, lalu dikelompokkan per tanggal
$st = $pdo->prepare("SELECT ss.*, t.judul, t.deadline FROM study_slots ss JOIN tasks t ON t.id = ss.task_id
    WHERE ss.user_id = ? AND ss.tanggal >= CURDATE() ORDER BY ss.tanggal, ss.jam_mulai");
$st->execute([$uid]);
$byDate = [];   // bentuk: ['2026-10-30' => [sesi1, sesi2], '2026-10-31' => [...]]
foreach ($st as $s) { $byDate[$s['tanggal']][] = $s; }

$pageTitle = 'Jadwal Belajar';
include 'includes/header.php';
?>
<h1>Jadwal Belajar</h1>
<div class="grid-2">
  <?php // ===== KIRI: FORM WAKTU LUANG + TOMBOL GENERATE ===== ?>
  <section class="card">
    <h2>Waktu Luang Mingguan</h2>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="save_availability">
      <table class="avail">
        <?php // Satu baris per hari; nama input berbentuk array: mulai[1], selesai[1], dst. ?>
        <?php foreach (HARI as $n => $nama): ?>
          <tr>
            <td><?= $nama ?></td>
            <td><input type="time" name="mulai[<?= $n ?>]" value="<?= isset($avail[$n]) ? substr($avail[$n]['jam_mulai'], 0, 5) : '' ?>"></td>
            <td>–</td>
            <td><input type="time" name="selesai[<?= $n ?>]" value="<?= isset($avail[$n]) ? substr($avail[$n]['jam_selesai'], 0, 5) : '' ?>"></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <p class="muted small">Kosongkan jika tidak ada waktu belajar di hari tersebut.</p>
      <button class="btn btn-primary" type="submit">Simpan Waktu Luang</button>
    </form>
    <hr>
    <?php // Form terpisah untuk memicu penyusunan jadwal otomatis ?>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="generate">
      <button class="btn btn-accent" type="submit">⚡ Buat / Perbarui Jadwal Otomatis</button>
    </form>
  </section>

  <?php // ===== KANAN: HASIL JADWAL ===== ?>
  <section class="card">
    <h2>Jadwal 14 Hari ke Depan</h2>
    <?php if (!$byDate): ?><p class="muted">Belum ada jadwal. Isi waktu luang lalu klik tombol buat jadwal.</p><?php endif; ?>
    <?php // Luar: per tanggal. Dalam: per sesi pada tanggal itu. ?>
    <?php foreach ($byDate as $tgl => $rows): ?>
      <?php // Judul hari, contoh "Senin, 02 Nov" (date('N') memberi nomor hari untuk dicari di HARI) ?>
      <h3 class="day"><?= HARI[(int) date('N', strtotime($tgl))] ?>, <?= date('d M', strtotime($tgl)) ?></h3>
      <?php foreach ($rows as $s): ?>
        <label class="slot">
          <input type="checkbox" class="js-slot" data-id="<?= $s['id'] ?>" <?= $s['selesai'] ? 'checked' : '' ?>>
          <span class="time"><?= substr($s['jam_mulai'], 0, 5) ?> – <?= substr($s['jam_selesai'], 0, 5) ?></span>
          <?= e($s['judul']) ?>
        </label>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </section>
</div>
<?php include 'includes/footer.php'; ?>

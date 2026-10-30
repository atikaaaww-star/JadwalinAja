<?php
/**
 * task_form.php
 * Form untuk MENAMBAH tugas baru dan MENGEDIT tugas yang sudah ada.
 *   task_form.php        -> mode tambah
 *   task_form.php?id=5   -> mode edit tugas dengan id 5
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_login();
$uid = current_user_id();
$pdo = db();

$id   = (int) ($_GET['id'] ?? 0);     // 0 = mode tambah
// Nilai awal form untuk mode tambah (deadline default: 3 hari lagi pukul 23:59)
$task = ['judul' => '', 'subject_id' => '', 'deskripsi' => '', 'deadline' => date('Y-m-d H:i', strtotime('+3 days 23:59')),
         'kesulitan' => 3, 'bobot_nilai' => 3, 'estimasi_jam' => 1.0];

// MODE EDIT: muat data tugas dari database (sekaligus memastikan milik user ini)
if ($id) {
    $st = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $st->execute([$id, $uid]);
    $task = $st->fetch();
    if (!$task) { set_flash('Tugas tidak ditemukan.', 'danger'); header('Location: tasks.php'); exit; }
}

// Ambil daftar mata pelajaran untuk isi dropdown
$subjects = $pdo->prepare("SELECT id, nama FROM subjects WHERE user_id = ? ORDER BY nama");
$subjects->execute([$uid]);
$subjects = $subjects->fetchAll();

$errors = [];
// ---------- PROSES PENYIMPANAN (saat form disubmit) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) { $errors[] = 'Sesi tidak valid.'; }

    // Ambil & bersihkan input. max/min membatasi angka agar tetap di rentang yang valid.
    $task['judul']        = trim($_POST['judul'] ?? '');
    $task['subject_id']   = (int) ($_POST['subject_id'] ?? 0) ?: null;   // 0 -> null (tanpa mata pelajaran)
    $task['deskripsi']    = trim($_POST['deskripsi'] ?? '');
    $task['deadline']     = str_replace('T', ' ', $_POST['deadline'] ?? '');  // "2026-10-29T23:59" -> "2026-10-29 23:59"
    $task['kesulitan']    = max(1, min(5, (int) ($_POST['kesulitan'] ?? 3)));
    $task['bobot_nilai']  = max(1, min(5, (int) ($_POST['bobot_nilai'] ?? 3)));
    $task['estimasi_jam'] = max(0.5, min(99, (float) ($_POST['estimasi_jam'] ?? 1)));

    // Validasi
    if ($task['judul'] === '')                  $errors[] = 'Judul tugas wajib diisi.';
    if (!strtotime($task['deadline']))          $errors[] = 'Deadline tidak valid.';

    if (!$errors) {
        $deadline = date('Y-m-d H:i:s', strtotime($task['deadline']));   // format DATETIME MySQL
        // Skor langsung dihitung saat disimpan
        $skor = calculate_priority($deadline, $task['kesulitan'], $task['bobot_nilai']);
        // Nilai-nilai yang sama dipakai oleh INSERT maupun UPDATE (urutan harus sama dengan tanda ?)
        $vals = [$task['subject_id'], $task['judul'], $task['deskripsi'], $deadline,
                 $task['kesulitan'], $task['bobot_nilai'], $task['estimasi_jam'], $skor];
        if ($id) {
            // EDIT: perbarui baris yang ada. [...$vals, $id, $uid] = gabungkan array + parameter WHERE
            $pdo->prepare("UPDATE tasks SET subject_id=?, judul=?, deskripsi=?, deadline=?, kesulitan=?, bobot_nilai=?, estimasi_jam=?, skor_prioritas=?
                           WHERE id=? AND user_id=?")->execute([...$vals, $id, $uid]);
        } else {
            // TAMBAH: sisipkan baris baru
            $pdo->prepare("INSERT INTO tasks (subject_id, judul, deskripsi, deadline, kesulitan, bobot_nilai, estimasi_jam, skor_prioritas, user_id)
                           VALUES (?,?,?,?,?,?,?,?,?)")->execute([...$vals, $uid]);
        }
        set_flash('Tugas berhasil disimpan.');
        header('Location: tasks.php');
        exit;
    }
}

$pageTitle = $id ? 'Edit Tugas' : 'Tugas Baru';
include 'includes/header.php';
// Label pilihan tingkat kesulitan (angka => teks)
$levels = [1 => '1 - Sangat mudah', 2 => '2 - Mudah', 3 => '3 - Sedang', 4 => '4 - Sulit', 5 => '5 - Sangat sulit'];
?>
<h1><?= e($pageTitle) ?></h1>
<?php foreach ($errors as $er): ?><div class="alert alert-danger"><?= e($er) ?></div><?php endforeach; ?>

<?php // ===== FORM (grid 2 kolom; elemen ber-class "full" membentang penuh) ===== ?>
<form method="post" class="card form-grid" id="task-form">
  <?= csrf_field() ?>
  <label class="full">Judul tugas <input type="text" name="judul" value="<?= e($task['judul']) ?>" required></label>

  <label>Mata pelajaran
    <select name="subject_id">
      <option value="">- Tanpa mata pelajaran -</option>
      <?php foreach ($subjects as $s): ?>
        <?php // "selected" otomatis jika ini mata pelajaran tugas yang sedang diedit ?>
        <option value="<?= $s['id'] ?>" <?= (int) $task['subject_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['nama']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <?php // datetime-local butuh format "Y-m-d\TH:i" (huruf T sebagai pemisah) ?>
  <label>Deadline <input type="datetime-local" name="deadline" value="<?= e(date('Y-m-d\TH:i', strtotime($task['deadline']))) ?>" required></label>

  <?php // Dua dropdown di bawah adalah masukan untuk rumus skor prioritas ?>
  <label>Tingkat kesulitan
    <select name="kesulitan"><?php foreach ($levels as $k => $v): ?><option value="<?= $k ?>" <?= (int) $task['kesulitan'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
  </label>
  <label>Pengaruh ke nilai akhir
    <select name="bobot_nilai"><?php foreach ([1 => '1 - Kecil', 2 => '2', 3 => '3 - Sedang', 4 => '4', 5 => '5 - Besar'] as $k => $v): ?><option value="<?= $k ?>" <?= (int) $task['bobot_nilai'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
  </label>

  <?php // Estimasi jam dipakai penyusun jadwal untuk menentukan total waktu yang dialokasikan ?>
  <label>Estimasi pengerjaan (jam) <input type="number" name="estimasi_jam" step="0.5" min="0.5" value="<?= e((string) $task['estimasi_jam']) ?>"></label>
  <label class="full">Catatan <textarea name="deskripsi" rows="3"><?= e($task['deskripsi']) ?></textarea></label>

  <div class="full actions">
    <button class="btn btn-primary" type="submit">Simpan</button>
    <a class="btn btn-ghost" href="tasks.php">Batal</a>
  </div>
</form>
<?php include 'includes/footer.php'; ?>

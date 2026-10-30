<?php
/**
 * tasks.php
 * Daftar semua tugas dalam bentuk tabel, terurut berdasarkan skor prioritas.
 * Fitur: filter (Aktif/Selesai/Semua), ubah status, hapus, dan countdown.
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_login();
$uid = current_user_id();
$pdo = db();
refresh_priorities($pdo, $uid);   // skor terbaru sebelum diurutkan

// Filter diambil dari URL (?status=...), default "aktif"
$filter = $_GET['status'] ?? 'aktif';
// match: memilih potongan kondisi SQL sesuai filter. Nilai asing otomatis jatuh ke "default"
// sehingga aman (input pengguna tidak pernah ditempel langsung ke query).
$where  = match ($filter) {
    'selesai' => "t.status = 'selesai'",
    'semua'   => "1=1",                      // 1=1 selalu benar = tanpa filter
    default   => "t.status <> 'selesai'",    // aktif = belum + proses
};
$st = $pdo->prepare("SELECT t.*, s.nama AS mapel, s.warna FROM tasks t
    LEFT JOIN subjects s ON s.id = t.subject_id
    WHERE t.user_id = ? AND $where
    ORDER BY t.skor_prioritas DESC, t.deadline ASC");
$st->execute([$uid]);
$tasks = $st->fetchAll();

$pageTitle = 'Daftar Tugas';
include 'includes/header.php';
?>
<div class="card-head">
  <h1>Daftar Tugas</h1>
  <a class="btn btn-primary" href="task_form.php">+ Tugas Baru</a>
</div>

<?php // ===== TAB FILTER ===== ?>
<div class="tabs">
  <?php foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai', 'semua' => 'Semua'] as $k => $v): ?>
    <a href="?status=<?= $k ?>" class="<?= $filter === $k ? 'active' : '' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<?php // table-wrap: membuat tabel bisa digeser menyamping di layar kecil ?>
<div class="table-wrap">
<table>
  <thead><tr><th>Tugas</th><th>Mata Pelajaran</th><th>Deadline</th><th>Prioritas</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($tasks as $t): [$lbl, $cls] = priority_label((float) $t['skor_prioritas']); ?>
    <?php // data-row dipakai JavaScript untuk menghapus baris ini tanpa reload ?>
    <tr data-row="<?= $t['id'] ?>">
      <td><a href="task_form.php?id=<?= $t['id'] ?>"><?= e($t['judul']) ?></a></td>
      <td><?php if ($t['mapel']): ?><span class="dot" style="background:<?= e($t['warna']) ?>"></span><?= e($t['mapel']) ?><?php else: ?>-<?php endif; ?></td>
      <td>
        <?php // pill berwarna sesuai kedekatan deadline (merah/kuning/hijau/abu) ?>
        <span class="pill pill-<?= deadline_class($t['deadline'], $t['status']) ?>"><?= format_tanggal($t['deadline']) ?></span>
        <div class="countdown small" data-ts="<?= strtotime($t['deadline']) * 1000 ?>"></div>
      </td>
      <td><span class="badge badge-<?= $cls ?>"><?= $lbl ?> (<?= (int) $t['skor_prioritas'] ?>)</span></td>
      <td>
        <?php // Dropdown status; perubahan dikirim ke api/update_status.php oleh app.js ?>
        <select class="js-status" data-id="<?= $t['id'] ?>">
          <?php foreach (['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'] as $k => $v): ?>
            <option value="<?= $k ?>" <?= $t['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </td>
      <td><button class="btn btn-ghost js-delete" data-id="<?= $t['id'] ?>" title="Hapus">🗑</button></td>
    </tr>
  <?php endforeach; ?>
  <?php // Pesan jika tidak ada tugas; colspan=6 = membentang selebar 6 kolom ?>
  <?php if (!$tasks): ?><tr><td colspan="6" class="muted">Tidak ada tugas.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php include 'includes/footer.php'; ?>

<?php
/**
 * api/delete_task.php
 * Menghapus satu tugas. Dipanggil JavaScript saat tombol 🗑 diklik.
 * Sesi belajar (study_slots) milik tugas itu ikut terhapus otomatis (ON DELETE CASCADE).
 */
require __DIR__ . '/_bootstrap.php';

$id = (int) ($input['id'] ?? 0);
// Hanya menghapus jika tugas memang milik pengguna yang login
$st = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$st->execute([$id, $uid]);
json_out(['ok' => $st->rowCount() > 0]);   // true bila ada baris yang benar-benar terhapus

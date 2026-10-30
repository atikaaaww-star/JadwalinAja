<?php
/**
 * api/update_status.php
 * Mengubah status tugas (belum / proses / selesai).
 * Dipanggil JavaScript saat dropdown status di halaman Tugas diganti.
 */
require __DIR__ . '/_bootstrap.php';   // cek login, POST, CSRF

$id     = (int) ($input['id'] ?? 0);
$status = $input['status'] ?? '';
// Validasi ketat (whitelist): hanya 3 nilai ini yang diterima
if (!in_array($status, ['belum', 'proses', 'selesai'], true)) json_out(['ok' => false, 'error' => 'Status salah'], 422);

// "AND user_id = ?" memastikan pengguna hanya bisa mengubah tugas MILIKNYA sendiri
$st = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
$st->execute([$status, $id, $uid]);
json_out(['ok' => true]);

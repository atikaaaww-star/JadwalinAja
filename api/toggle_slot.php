<?php
/**
 * api/toggle_slot.php
 * Menandai sesi belajar sudah/belum dikerjakan (checkbox di dashboard & jadwal).
 */
require __DIR__ . '/_bootstrap.php';

$id      = (int) ($input['id'] ?? 0);
$selesai = !empty($input['selesai']) ? 1 : 0;   // ubah true/false dari JS menjadi 1/0
$st = $pdo->prepare("UPDATE study_slots SET selesai = ? WHERE id = ? AND user_id = ?");
$st->execute([$selesai, $id, $uid]);
json_out(['ok' => true]);

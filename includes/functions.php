<?php
/**
 * includes/functions.php
 * ---------------------------------------------------------------
 * Kumpulan fungsi bantu + LOGIKA INTI aplikasi:
 *   1) Fungsi umum (escape, flash message)
 *   2) Skor prioritas tugas
 *   3) Penanda warna deadline
 *   4) Penyusun jadwal belajar otomatis
 */

// Nama hari; kunci 1..7 mengikuti date('N') (1=Senin ... 7=Minggu)
const HARI = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];

// Singkatan e() = escape. Mengubah karakter berbahaya (< > " ') jadi aman ditampilkan
// di HTML. WAJIB dipakai untuk semua data dari pengguna/database untuk mencegah XSS.
function e(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

/* ---------- FLASH MESSAGE ----------
 * Pesan singkat yang hanya tampil SEKALI (mis. "Tugas berhasil disimpan")
 * lewat redirect: disimpan di sesi, lalu dihapus saat dibaca.
 */
function set_flash(string $msg, string $type = 'success'): void { $_SESSION['flash'] = [$msg, $type]; }
function get_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

/* =============================================================
 * SKOR PRIORITAS
 * Rumus: skor = urgensi*0.5 + kesulitan*0.3 + bobot_nilai*0.2
 * Hasil 1..5 lalu dikali 20 menjadi skala 0..100.
 * ============================================================= */

// Urgensi 1..5 berdasarkan sisa waktu menuju deadline (makin dekat = makin tinggi)
function urgency_level(string $deadline): int {
    $jam = (strtotime($deadline) - time()) / 3600;  // selisih detik -> jam
    if ($jam <= 24)  return 5;   // <= 1 hari (termasuk yang sudah lewat, karena nilainya negatif)
    if ($jam <= 72)  return 4;   // <= 3 hari
    if ($jam <= 168) return 3;   // <= 1 minggu
    if ($jam <= 336) return 2;   // <= 2 minggu
    return 1;                    // masih lama
}

// Hitung skor akhir 0..100. Bobot: urgensi 50%, kesulitan 30%, pengaruh nilai 20%.
function calculate_priority(string $deadline, int $kesulitan, int $bobot): float {
    return round((urgency_level($deadline) * 0.5 + $kesulitan * 0.3 + $bobot * 0.2) * 20, 2);
}

// Ubah skor angka menjadi label + nama kelas CSS (dipakai untuk warna badge)
function priority_label(float $skor): array {
    if ($skor >= 80) return ['Tinggi', 'danger'];
    if ($skor >= 55) return ['Sedang', 'warning'];
    return ['Rendah', 'safe'];
}

// Urgensi berubah seiring waktu berjalan, jadi skor semua tugas aktif
// dihitung ulang setiap halaman dashboard/tugas dibuka.
function refresh_priorities(PDO $pdo, int $uid): void {
    $rows = $pdo->prepare("SELECT id, deadline, kesulitan, bobot_nilai FROM tasks WHERE user_id = ? AND status <> 'selesai'");
    $rows->execute([$uid]);
    $upd = $pdo->prepare("UPDATE tasks SET skor_prioritas = ? WHERE id = ?");
    foreach ($rows as $t) {
        $upd->execute([calculate_priority($t['deadline'], (int)$t['kesulitan'], (int)$t['bobot_nilai']), $t['id']]);
    }
}

/* =============================================================
 * PENANDA WARNA DEADLINE
 * ============================================================= */

// Mengembalikan nama kelas CSS: merah < 24 jam, kuning 1-3 hari, hijau > 3 hari
function deadline_class(string $deadline, string $status = 'belum'): string {
    if ($status === 'selesai') return 'done';      // tugas selesai: abu-abu
    $jam = (strtotime($deadline) - time()) / 3600;
    if ($jam < 0)  return 'overdue';               // sudah lewat
    if ($jam < 24) return 'danger';
    if ($jam < 72) return 'warning';
    return 'safe';
}

// Format tanggal untuk tampilan, contoh: "29 Oct 2026, 23:59"
function format_tanggal(string $dt): string {
    return date('d M Y, H:i', strtotime($dt));
}

/* =============================================================
 * PENYUSUN JADWAL BELAJAR OTOMATIS
 * Ide: waktu luang dipecah menjadi "slot" per tanggal, lalu tugas
 * diisikan mulai dari skor tertinggi sampai estimasi jamnya terpenuhi.
 * ============================================================= */

// Konversi "19:30:00" -> 1170 (menit sejak tengah malam). Memudahkan hitung-hitungan.
function to_min(string $t): int { [$h, $m] = array_map('intval', explode(':', $t)); return $h * 60 + $m; }
// Kebalikannya: 1170 -> "19:30:00" (format TIME untuk MySQL)
function from_min(int $m): string { return sprintf('%02d:%02d:00', intdiv($m, 60), $m % 60); }

/**
 * Membuat jadwal belajar.
 * @param int $horizonDays jadwal disusun untuk berapa hari ke depan (default 14)
 * @param int $maxChunk    durasi maksimal satu sesi dalam menit (default 120)
 * @return array ['dibuat' => jumlah sesi, 'gagal' => daftar judul tugas yang tidak muat]
 */
function generate_schedule(PDO $pdo, int $uid, int $horizonDays = 14, int $maxChunk = 120): array {
    // LANGKAH 0: hapus sesi lama yang belum dikerjakan (dari hari ini ke depan) agar
    // jadwal bisa disusun ulang. Sesi yang sudah dicentang (selesai=1) tetap disimpan.
    $pdo->prepare("DELETE FROM study_slots WHERE user_id = ? AND selesai = 0 AND tanggal >= CURDATE()")->execute([$uid]);

    // LANGKAH 1: ambil waktu luang mingguan, kelompokkan per nomor hari
    $av = $pdo->prepare("SELECT hari, jam_mulai, jam_selesai FROM availability WHERE user_id = ? ORDER BY jam_mulai");
    $av->execute([$uid]);
    $byDay = [];   // contoh: [1 => [ {19:00-21:00} ], 3 => [ ... ]]
    foreach ($av as $a) { $byDay[(int)$a['hari']][] = $a; }

    // LANGKAH 2: ubah pola mingguan menjadi daftar slot tanggal nyata untuk N hari ke depan
    $slots = [];
    $nowMin = (int) date('G') * 60 + (int) date('i');   // menit sekarang sejak 00:00
    for ($i = 0; $i < $horizonDays; $i++) {
        $d = new DateTime("today +$i day");              // hari ke-i dari hari ini
        // $d->format('N') = nomor hari (1..7); ambil waktu luang untuk hari itu
        foreach ($byDay[(int) $d->format('N')] ?? [] as $w) {
            $s = to_min($w['jam_mulai']);                // awal slot (menit)
            $e = to_min($w['jam_selesai']);              // akhir slot (menit)
            if ($i === 0) $s = max($s, $nowMin);         // hari ini: jangan menjadwalkan waktu yang sudah lewat
            if ($e - $s >= 15) {                         // abaikan sisa slot kurang dari 15 menit
                $slots[] = ['date' => $d->format('Y-m-d'), 's' => $s, 'e' => $e];
            }
        }
    }

    // LANGKAH 3: ambil tugas aktif, urut dari skor tertinggi (yang paling penting dijadwalkan dulu)
    $q = $pdo->prepare("SELECT id, judul, deadline, estimasi_jam FROM tasks WHERE user_id = ? AND status <> 'selesai' ORDER BY skor_prioritas DESC, deadline ASC");
    $q->execute([$uid]);
    $tasks = $q->fetchAll();
    // Statement untuk menghitung menit yang SUDAH dikerjakan (sesi tercentang) pada suatu tugas
    $done = $pdo->prepare("SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, jam_mulai, jam_selesai)), 0) FROM study_slots WHERE task_id = ? AND selesai = 1");
    $ins  = $pdo->prepare("INSERT INTO study_slots (user_id, task_id, tanggal, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?, ?)");

    // LANGKAH 4: isikan tugas ke slot
    $dibuat = 0; $gagal = [];
    foreach ($tasks as $t) {
        $done->execute([$t['id']]);
        // $need = sisa menit yang harus dijadwalkan = estimasi total - yang sudah dikerjakan
        $need = (int) round($t['estimasi_jam'] * 60) - (int) $done->fetchColumn();
        $deadlineDate = date('Y-m-d', strtotime($t['deadline']));

        // Telusuri slot dari yang paling awal; & = referensi, agar perubahan $sl tersimpan ke $slots
        foreach ($slots as &$sl) {
            if ($need <= 0) break;                       // kebutuhan tugas ini sudah terpenuhi
            if ($sl['date'] > $deadlineDate) continue;   // slot setelah deadline tidak boleh dipakai
            $free = $sl['e'] - $sl['s'];                 // sisa menit di slot ini
            if ($free < 15) continue;
            $alloc = min($need, $free, $maxChunk);       // ambil sebanyak yang dibutuhkan/muat/diizinkan
            $ins->execute([$uid, $t['id'], $sl['date'], from_min($sl['s']), from_min($sl['s'] + $alloc)]);
            $sl['s'] += $alloc;                          // geser awal slot (bagian itu sudah terpakai)
            $need    -= $alloc;                          // kebutuhan tugas berkurang
            $dibuat++;
        }
        unset($sl);                                      // lepas referensi (praktik aman setelah foreach &)
        if ($need > 0) $gagal[] = $t['judul'];           // masih ada sisa = waktu luang tidak cukup
    }
    return ['dibuat' => $dibuat, 'gagal' => $gagal];
}

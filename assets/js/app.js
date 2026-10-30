/* =====================================================================
 * assets/js/app.js
 * JavaScript sisi browser untuk JadwalinAja. Isinya:
 *   1) Helper untuk memanggil API PHP (fetch)
 *   2) Countdown deadline
 *   3) Ubah status tugas tanpa reload halaman
 *   4) Hapus tugas tanpa reload halaman
 *   5) Centang sesi belajar
 * Script dimuat di akhir halaman (footer.php), jadi semua elemen sudah ada.
 * ===================================================================== */


/* ---------- 1) HELPER ---------- */

// Token CSRF diambil dari <meta name="csrf-token"> yang dibuat header.php.
// "?." dan "??" mencegah error bila meta tidak ditemukan (mis. halaman login).
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Fungsi umum untuk memanggil endpoint di folder api/.
//   endpoint : nama file API, mis. 'update_status.php'
//   data     : objek yang dikirim sebagai JSON
// Mengembalikan respon JSON dari PHP, mis. { ok: true }
async function api(endpoint, data) {
  const res = await fetch(`api/${endpoint}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrf,          // dicek oleh api/_bootstrap.php
    },
    body: JSON.stringify(data),      // ubah objek JS menjadi teks JSON
  });
  return res.json();                 // ubah teks JSON balasan menjadi objek JS
}


/* ---------- 2) COUNTDOWN DEADLINE ---------- */

// Ubah selisih waktu (milidetik) menjadi teks, mis. "Sisa 2 hari 5 jam".
function formatRemaining(ms) {
  const late = ms < 0;                       // negatif = deadline sudah lewat
  let s = Math.floor(Math.abs(ms) / 1000);   // total detik (nilai mutlak)
  const d = Math.floor(s / 86400); s %= 86400;   // 86400 detik = 1 hari
  const h = Math.floor(s / 3600);  s %= 3600;    // 3600 detik  = 1 jam
  const m = Math.floor(s / 60);                  // 60 detik    = 1 menit
  // Jika masih ada hari, tampilkan "hari + jam"; jika kurang dari sehari, "jam + menit"
  const text = d > 0 ? `${d} hari ${h} jam` : `${h} jam ${m} menit`;
  return late ? `Terlambat ${text}` : `Sisa ${text}`;
}

// Isi semua elemen .countdown. Deadline-nya ada di atribut data-ts (milidetik),
// yang ditulis oleh PHP (strtotime(deadline) * 1000).
function updateCountdowns() {
  document.querySelectorAll('.countdown[data-ts]').forEach(el => {
    const ms = Number(el.dataset.ts) - Date.now();   // sisa waktu = deadline - waktu sekarang
    el.textContent = formatRemaining(ms);
    el.classList.toggle('late', ms < 0);             // beri kelas "late" (merah) jika terlambat
  });
}
updateCountdowns();                    // jalankan sekali saat halaman dibuka
setInterval(updateCountdowns, 30000);  // lalu perbarui setiap 30 detik


/* ---------- 3) UBAH STATUS TUGAS TANPA RELOAD ---------- */

document.querySelectorAll('.js-status').forEach(sel => {
  // "change" terjadi saat pengguna memilih nilai lain di dropdown
  sel.addEventListener('change', async () => {
    const r = await api('update_status.php', { id: sel.dataset.id, status: sel.value });
    if (!r.ok) alert(r.error || 'Gagal mengubah status');
  });
});


/* ---------- 4) HAPUS TUGAS TANPA RELOAD ---------- */

document.querySelectorAll('.js-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Hapus tugas ini?')) return;      // minta konfirmasi dulu
    const r = await api('delete_task.php', { id: btn.dataset.id });
    // Jika sukses, hapus baris tabel yang punya data-row sesuai id (tanpa reload)
    if (r.ok) document.querySelector(`[data-row="${btn.dataset.id}"]`)?.remove();
    else alert(r.error || 'Gagal menghapus');
  });
});


/* ---------- 5) CENTANG SESI BELAJAR ---------- */

document.querySelectorAll('.js-slot').forEach(cb => {
  cb.addEventListener('change', async () => {
    const r = await api('toggle_slot.php', { id: cb.dataset.id, selesai: cb.checked });
    // Jika gagal disimpan, kembalikan centang ke kondisi semula
    if (!r.ok) { cb.checked = !cb.checked; alert('Gagal menyimpan'); }
  });
});

// TODO (pengembangan): timer Pomodoro, grafik statistik (Chart.js), pengingat email

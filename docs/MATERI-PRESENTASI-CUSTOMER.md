# Materi Presentasi — Agenda Kelas Digital
### Sistem Informasi Manajemen Agenda & Presensi Sekolah Terpadu

> **Target Audience:** Yayasan / Dinas Pendidikan (audiens pengambil keputusan, pengawas, dan pengelola sekolah)
> **Durasi estimasi:** 30–45 menit (termasuk sesi demo & tanya jawab)
> **Pembicara:** [Nama Anda] — [Tanggal Presentasi]

---

## Cara Menggunakan Dokumen Ini

Dokumen ini berisi **dua bagian**:

1. **Panduan Naratif (script)** — kalimat siap ucap untuk membawakan presentasi, disusun per segmen.
2. **Skrip Slide** — panduan visual per-slide (bisa dipakai untuk membuat deck PowerPoint / reveal.js / Canva).

> 💡 **Tips tampil:** Gunakan **demo langsung** setelah menjelaskan konsep. Customer paling terkesan dengan bukti nyata, bukan sekadar janji. Siapkan akun demo untuk semua role.

---

# BAGIAN 1 — PANDUAN NARATIF (SCRIPT)

---

## SEGMEN 1 — Pembukaan (2 menit)

> Selamat pagi / siang, Bapak/Ibu.
>
> Terima kasih atas kesempatannya. Hari ini saya ingin memperkenalkan sebuah solusi digital yang kami rancang khusus untuk membantu yayasan dan sekolah dalam mengelola **agenda kelas, presensi siswa, dan monitoring kinerja mengajar guru** — semuanya dalam satu sistem terpadu yang berbasis web.
>
> Saya yakin Bapak/Ibu semua sudah akrab dengan **buku agenda kelas fisik** — buku yang selama ini menjadi tumpuan pencatatan kehadiran dan jurnal mengajar di sekolah. Sistem ini hadir sebagai **pengganti digital** dari buku tersebut, sekaligus menjadi alat kontrol bagi pengelola di tingkat yayasan.

---

## SEGMEN 2 — Rumusan Masalah (3 menit)

> Sebelum masuk ke solusinya, izinkan saya menyampaikan beberapa masalah yang sering kami temui di lapangan. Mungkin Bapak/Ibu juga merasakan hal yang sama:
>
> 1. **Buku agenda hilang, rusak, atau tidak terisi.** Sulit untuk memastikan semua guru benar-benar mencatat jurnal mengajarnya setiap hari.
> 2. **Rekap kehadiran memakan waktu berhari-hari.** Wali kelas harus menghitung manual satu per satu, dan sering terjadi salah hitung.
> 3. **Tidak ada pengawasan real-time.** Kepala Sekolah dan pengawas baru tahu ada masalah setelah akhir bulan, saat rekap diserahkan — sudah terlambat untuk ditindaklanjuti.
> 4. **Komunikasi dengan orang tua terbatas.** Orang tua jarang tahu kehadiran anaknya sampai ada peringatan dari sekolah.
> 5. **Data tidak terpusat.** Setiap sekolah mencatat dengan caranya sendiri. Bagi yayasan yang mengelola banyak sekolah, membandingkan dan mengontrol menjadi sangat sulit.

> Kami merancang Agenda Kelas Digital untuk menjawab kelima masalah ini secara menyeluruh.

---

## SEGMEN 3 — Apa itu Agenda Kelas Digital? (3 menit)

> **Agenda Kelas Digital** adalah sistem informasi berbasis web yang mendigitalkan:
> - Pencatatan **jurnal mengajar guru** (agenda kelas),
> - **Presensi/kehadiran siswa** secara harian,
> - **Monitoring kinerja mengajar** dan **pelaporan** — secara otomatis, terintegrasi, dan real-time.
>
> Konsep besarnya sederhana: **mengganti buku fisik** dengan sistem digital yang lebih cepat, akurat, dan bisa diawasi dari mana saja.
>
> Ada **7 peran (role)** dalam sistem ini — masing-masing memiliki dashboard dan kewenangan yang sesuai dengan tugas pokok dan fungsinya. Ini penting bagi yayasan karena **setiap orang hanya melihat data yang menjadi haknya** — rapi, terstruktur, dan aman.2

---

## SEGMEN 4 — 7 Peran & Alur Kerja (10 menit)

> Mari saya jelaskan bagaimana sistem ini bekerja dari setiap sisi penggunanya.

### 1. Super Admin / Pengelola Pusat (Yayasan)
> Ini adalah **level tertinggi**. Di sinilah pengelola pusat (yayasan / dinas) mengelola **banyak instansi/sekolah sekaligus** dalam satu sistem.
> - Menambah, menghapus, atau menonaktifkan sekolah.
> - Mengatur admin/kepala sekolah di tiap instansi.
> - **Mengaktifkan/menonaktifkan fitur per sekolah** sesuai kebutuhan masing-masing.
> - Melihat **audit log** — jejak lengkap siapa mengubah apa, kapan, dan dari IP mana. Ini sangat berguna untuk transparansi dan pertanggungjawaban.
> - **Backup & restore** database dan file.
> - Mode maintenance untuk pemeliharaan sistem.

### 2. Admin / Kepala Sekolah
> Di tingkat sekolah, Admin mengelola seluruh **data master**:
> - Kelas, **siswa**, **guru**, mata pelajaran, jadwal, ruangan, dan tahun ajaran.
> - **Impor massal dari Excel/CSV** — tidak perlu mengetik satu per satu.
> - **Kenaikan kelas massal** di akhir tahun — sekali klik, semua siswa naik.
> - **Koreksi presensi** dan monitoring ruangan.
> - **Laporan kehadiran** (per kelas, per siswa) yang bisa diekspor ke PDF/Excel.

### 3. Guru
> Ini pengguna inti. Setiap hari, guru:
> - Mengisi **jurnal mengajar** (materi, alokasi jam, lampiran) dan **presensi siswa** di kelas yang diajar.
> - Mengajukan **izin/sakit/tugas luar** (multi-hari, bisa lampirkan surat).
> - Menginput **nilai tugas**.
> - Semua input **dibatasi jam operasional sekolah** — di luar jam, sistem otomatis memblokir. Ini menjamin kedisiplinan.

### 4. Wali Kelas
> Memantau **seluruh mata pelajaran di kelasnya** (bukan hanya yang diajar sendiri):
> - Grafik kehadiran mingguan, rekap harian.
> - **Verifikasi presensi** dan bantu siswa yang lupa check-out.
> - Menyetujui **koreksi presensi** dan **pulang cepat** siswa.
> - **Kirim ulang notifikasi WhatsApp** ke orang tua.
> - Rekap bulanan & semester siap export.

### 5. Wakil Kepala Sekolah (Kurikulum) — Koordinasi dengan Pengawas
> Ini **poin penting bagi yayasan/dinas**: Wakasek berfungsi sebagai *pengawas internal*.
> - **Monitoring kurikulum** — berapa banyak guru yang sudah mengisi jurnal sesuai jadwal.
> - **Kinerja mengajar** per guru — jumlah jurnal, jam, jurnal terlambat.
> - **Menyetujui/menolak** pengajuan izin guru.
> - **Evaluasi akademik** berbasis data kehadiran dan jurnal.
> - Soal ini, data bisa dijadikan bahan laporan ke pihak yayasan/dinas.

### 6. Sekretaris Kelas
> Siswa yang ditunjuk untuk membantu mencatat — bisa mengisi jurnal dan presensi kelasnya, sesuai kewenangan yang dibatasi.

### 7. Siswa
> Siswa mendapat pengalaman digital juga:
> - Melihat **jadwal pelajaran** mingguan.
> - **Check-in / check-out** harian (bisa disertai foto), terbatas jam operasional.
> - Mengajukan **koreksi presensi** dan **pulang cepat**.
> - Melihat jurnal mengajar dan **nilai tugas** mereka.

> **Fitur Multi-Role:** satu orang bisa punya lebih dari satu peran (misal seorang guru juga wali kelas dan wakasek), dan bisa berpindah portal lewat dropdown profil.

---

## SEGMEN 5 — Tingkat Yayasan: Kontrol Multi-Sekolah (5 menit)

> Khusus untuk Bapak/Ibu dari yayasan atau dinas, sistem ini dirancang agar **pengelolaan multi-sekolah menjadi rapi**:
>
> - **Satu sistem pusat** mengelola banyak sekolah, dengan data terpisah namun terpantau.
> - **Audit log lengkap** untuk transparansi tiap sekolah.
> - **Fitur bisa disesuaikan per sekolah** — sekolah yang belum siap tidak perlu langsung mengaktifkan semua modul.
> - **Format laporan seragam** di semua sekolah sehingga mudah dibandingkan dan direkap di tingkat pusat.
> - Evaluasi kurikulum dan kinerja guru yang **berbasis data**, bukan asumsi.

---

## SEGMEN 6 — Fitur & Keunggulan Kunci (5 menit)

> Mari saya ringkas beberapa keunggulan inti yang menurut saya paling relevan untuk sebuah yayasan:

### Paperless & Ramah Lingkungan
> Mengurangi penggunaan kertas secara signifikan — sejalan dengan semangat sekolah digital.

### Rekap Otomatis
> Laporan kehadiran dan jurnal mengajar tergenerate dalam hitungan detik. Tidak ada lagi rekap manual berhari-hari.

### Notifikasi WhatsApp ke Orang Tua
> Kehadiran siswa (check-in, check-out, keterlambatan) bisa otomatis dikirim ke orang tua melalui WhatsApp. Ini **meningkatkan kepercayaan orang tua** terhadap sekolah.

### Pembatasan Waktu (Disiplin)
> Sistem memblokir input di luar jam operasional — menjamin guru mengisi agenda tepat waktu.

### Audit Trail & Keamanan
> Seluruh aktivitas penting tercatat (Spatie Activity Log). Kata sandi terenkripsi, terlindung dari SQL Injection/CSRF, dan data terhapus masih bisa dikembalikan (*soft delete*).

### Data Aman & Historis Terjaga
> Kenaikan kelas tidak menghapus data lama — riwayat akademik siswa tersimpan rapi.

### Lokasi Geografis (Opsional)
> Bisa membatasi guru mengisi jurnal hanya dari dalam area sekolah (geofencing).

---

## SEGMEN 7 — Teknologi & Keandalan (2 menit)

> Sebagai catatan untuk tim teknis Bapak/Ibu:
> - Dibangun dengan **Laravel 12 (PHP)** — framework yang matang, aman, dan mendukung skala besar.
> - Database **PostgreSQL** untuk produksi — handal untuk volume data banyak sekolah.
> - Antarmuka **responsif** — bisa diakses dari laptop, tablet, maupun HP guru di kelas.
> - Sudah dilengkapi **pengujian otomatis (E2E)** untuk menjamin kualitas.

---

## SEGMEN 8 — Roadmap & Pengembangan (1 menit)

> Transparan juga kami sampaikan arah pengembangan ke depan:
> - **Aplikasi mobile native** untuk pengalaman yang lebih baik di ponsel.
> - **Integrasi dengan Dapodik** untuk sinkronisasi data pendidikan nasional.
> - **Integrasi mesin fingerprint / face recognition** untuk kehadiran yang lebih cepat.

---

## SEGMEN 9 — Demo Langsung (10 menit — opsional, sangat disarankan)

> Sekarang mari saya tunjukkan langsung bagaimana sistem ini bekerja. Saya akan perlihatkan:
> 1. Alur dari sisi **Super Admin** — mengelola beberapa sekolah.
> 2. Alur dari sisi **Admin sekolah** — input data master & impor Excel.
> 3. Alur dari sisi **Guru** — mengisi jurnal & presensi.
> 4. Alur dari sisi **Wakasek** — monitoring kinerja & kurikulum.
> 5. Alur dari sisi **Siswa** — check-in/check-out.
>
> *(Perlihatkan secara berurutan, jelaskan tiap layar dengan singkat dan padat.)*

---

## SEGMEN 10 — Penawaran & Langkah Selanjutnya (2 menit)

> Bapak/Ibu, Agenda Kelas Digital siap membantu yayasan dan sekolah untuk **tertib administrasi, disiplin mengajar, dan komunikasi yang lebih baik dengan orang tua** — dengan data yang terpusat dan bisa diawasi.
>
> **Langkah selanjutnya** yang bisa kita lakukan:
> 1. **Uji coba / pilot project** di salah satu sekolah yayasan.
> 2. Sesi **onboarding & pelatihan** untuk admin dan guru.
> 3. Pembahasan **skema kerja sama & harga** yang bisa disesuaikan dengan jumlah sekolah.
>
> Terima kasih. Saya persilakan untuk bertanya.

---

# BAGIAN 2 — SKRIP SLIDE

*(Untuk membuat deck PowerPoint / Canva / reveal.js. Total ± 15 slide.)*

---

### Slide 1 — Judul
- **Judul:** Agenda Kelas Digital
- **Sub:** Sistem Informasi Manajemen Agenda & Presensi Sekolah Terpadu
- Nama pembicara, tanggal, kontak

### Slide 2 — Masalah yang Dihadapi Sekolah
- Buku agenda hilang/rusak/tidak terisi
- Rekap manual lambat & rawan salah
- Tidak ada pengawasan real-time
- Komunikasi orang tua terbatas
- Data tidak terpusat antar sekolah

### Slide 3 — Solusi: Agenda Kelas Digital
- Pengganti buku agenda fisik
- Jurnal mengajar & presensi terdigitalisasi
- Otomatis, terintegrasi, real-time
- Dapat diawasi dari mana saja

### Slide 4 — 7 Peran dalam Sistem
(infografis 7 role)
1. Super Admin
2. Admin / Kepala Sekolah
3. Guru
4. Wali Kelas
5. Wakasek
6. Sekretaris Kelas
7. Siswa

### Slide 5 — Peran Super Admin / Yayasan
- Kelola banyak sekolah dalam satu sistem
- Audit log transparan
- Atur fitur per sekolah
- Backup & restore
- Mode maintenance

### Slide 6 — Peran Admin / Kepala Sekolah
- Data master: kelas, siswa, guru, mapel
- Impor massal Excel/CSV
- Kenaikan kelas massal
- Koreksi presensi
- Laporan PDF/Excel

### Slide 7 — Peran Guru
- Jurnal mengajar harian
- Presensi siswa
- Izin/sakit/tugas luar
- Nilai tugas
- Dibatasi jam operasional & geofencing opsional

### Slide 8 — Peran Wali Kelas
- Pantau semua mata pelajaran di kelas
- Grafik kehadiran mingguan
- Verifikasi & koreksi presensi
- Pulang cepat, kirim ulang notifikasi WhatsApp
- Rekap bulanan/semester

### Slide 9 — Peran Wakasek (Koordinasi Pengawas)
- Monitoring kurikulum & kinerja guru
- Setujui/tolak izin guru
- Evaluasi akademik berbasis data
- Laporan untuk yayasan/dinas

### Slide 10 — Peran Sekretaris & Siswa
- Sekretaris: input agenda & presensi kelas
- Siswa: jadwal, check-in/out, koreksi presensi, lihat nilai

### Slide 11 — Keunggulan Kunci
- Paperless
- Rekap otomatis
- Notifikasi WhatsApp ke orang tua
- Pembatasan waktu (disiplin)
- Audit trail & keamanan

### Slide 12 — Data Historis & Kenaikan Kelas
- Riwayat akademik terjaga
- Soft delete / data bisa dikembalikan
- Switch periode (arsip tahun lalu)

### Slide 13 — Teknologi
- Laravel 12 (PHP), PostgreSQL
- Responsif (laptop/HP)
- E2E testing otomatis

### Slide 14 — Roadmap
- Aplikasi mobile native
- Integrasi Dapodik
- Integrasi fingerprint / face recognition

### Slide 15 — Penawaran & Langkah Selanjutnya
- Pilot project di sekolah terpilih
- Onboarding & pelatihan
- Skema kerja sama & harga fleksibel
- **Kontak & CTA**

---

## Lampiran — Pertanyaan yang Mungkin Diajukan (Q&A)

Siapkan jawaban ringkas untuk pertanyaan umum berikut:

1. **"Berapa biaya / skema harga?"**
   → Menyesuaikan jumlah sekolah & kebutuhan. Bisa mulai dari pilot project 1 sekolah.

2. **"Apakah butuh perangkat khusus / server sendiri?"**
   → Berbasis web, cukup laptop/HP dan internet. Bisa di-host di server kami atau server sekolah.

3. **"Bagaimana jika guru/siswa tidak punya internet?"**
   → Sistem dirancang ringan & responsif; koneksi stabil disarankan. Ini juga menjadi pertimbangan untuk pengadaan sarana.

4. **"Apakah data aman?"**
   → Kata sandi terenkripsi, audit log lengkap, proteksi SQL Injection/CSRF, backup berkala, soft delete.

5. **"Bagaimana dengan data sekolah yang sudah ada (Excel)?"**
   → Mendukung impor massal Excel/CSV untuk siswa & guru.

6. **"Apakah banyak sekolah bisa digabung?"**
   → Ya, Super Admin dapat mengelola banyak instansi dalam satu sistem.

7. **"Apakah orang tua bisa melihat kehadiran?"**
   → Ya, via notifikasi WhatsApp (check-in/check-out & keterlambatan).

8. **"Apa bedanya dengan aplikasi absensi fingerprint?"**
   → Sistem ini lebih menyeluruh: agenda/jurnal mengajar, monitoring kurikulum, nilai, izin, dan pelaporan — bukan sekadar absensi.

---

## Checklist Persiapan Presentasi

- [ ] Menyiapkan akun demo untuk 7 role (data contoh realistis).
- [ ] Uji coba demo penuh minimal 1× sehari sebelum presentasi.
- [ ] Pastikan internet & server stabil (mode maintenance dimatikan).
- [ ] Menyiapkan data contoh dari sekolah yayasan (jika diberi izin).
- [ ] Mencetak satu salinan materi ini sebagai pengingat.
- [ ] Menyiapkan video/thumbnail laporan contoh di tingkat yayasan.

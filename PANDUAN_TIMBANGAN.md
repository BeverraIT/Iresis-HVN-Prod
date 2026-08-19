# Modul Timbangan (TIM RESI)

Memindahkan program `timbangan/scale-web-app` (Vite + Node + SQLite) menjadi menu di
dalam IRESIS. Logika inti yang dipertahankan: pembacaan indikator RS-232 lewat
**Web Serial API** di browser, dan set point UNDER / ACCEPT / OVER berikut interlock.
Backend Node/SQLite tidak dipakai lagi — diganti controller CodeIgniter + tabel MySQL.

## Perbedaan dengan program asli

| Program asli | Modul di IRESIS |
| --- | --- |
| Pilih Operator + Produk dari master sendiri | Scan nomor resi; SKU diambil dari `tbldetailprintresi` |
| Set point min/max diketik manual | Dihitung otomatis: `SUM(berat SKU × qty) + berat kemasan`, ± toleransi |
| Simpan ke SQLite via server Node | Simpan ke `tbltimbangan` (MySQL) |
| Operator dipilih dari dropdown | Operator = user yang sedang login |
| Export CSV di browser | Export Excel lewat menu Laporan Timbangan |

## Langkah pemasangan

### 1. Jalankan SQL setup

```bash
mysql -u root -p iresis < timbangan_setup.sql
```

Script ini membuat tabel `tbltimbangan_sku` dan `tbltimbangan`, menambahkan param
grup `TIMBANGAN`, membuat 3 menu di bawah **TIM RESI**, dan menyalin hak akses dari
menu *Scan Resi*. Aman dijalankan ulang.

Kalau hak akses perlu diatur lain, pakai menu **User Management → Access**.

### 2. Impor master berat SKU

Buka **TIM RESI → Master Berat SKU**, unggah `timbangan/sku.xlsx`.

Format yang dibaca: kolom **A = KODE SKU**, kolom **B = BERAT BARU** (gram), baris
pertama dianggap judul. Baris dengan berat kosong atau bukan angka dilewati dan
dilaporkan jumlahnya.

File yang ada sekarang berisi 453 SKU dengan berat 0–34.780 gram. Sebagian di
antaranya bernilai 0 — SKU tersebut akan tercatat tetapi tidak menyumbang berat,
dan halaman penimbangan akan menandainya.

### 3. Atur parameter global

Masih di halaman Master Berat SKU:

| Parameter | Arti | Default |
| --- | --- | --- |
| Toleransi Global | Rentang ACCEPT dalam persen dari berat standar | 5 % |
| Berat Kemasan | Tare per paket dalam gram, ikut dijumlahkan ke berat standar | 0 |
| Satuan Indikator | Satuan angka yang dikirim indikator: `g` atau `kg` | g |
| Baud Rate Default | Baud rate awal koneksi serial | 9600 |
| Interlock | Bila aktif, hanya status ACCEPT yang boleh disimpan | Aktif |

**Satuan Indikator wajib benar.** Master berat memakai gram; kalau indikator
mengirim kilogram tetapi setelan masih `g`, semua paket akan berstatus UNDER.
Cek angka "Data mentah terakhir" di halaman penimbangan untuk memastikan.

Toleransi bisa dibedakan per SKU lewat tombol **Ubah** di daftar. Bila sebuah resi
berisi beberapa SKU dan ada yang punya toleransi khusus, sistem memakai toleransi
terbesar di antaranya, supaya paket campuran tidak terikat aturan SKU paling ketat.

### 4. Syarat browser — Web Serial API

Web Serial API **hanya aktif pada secure context**: `https://` atau `localhost`.
Kalau IRESIS diakses lewat `http://<ip-lan>/`, tombol "Hubungkan Port" akan mati dan
halaman menampilkan peringatan. Pilihan:

1. **Pasang HTTPS** di server IRESIS. Sertifikat self-signed cukup, asal di-*trust*
   di PC timbangan.
2. **Daftarkan origin sebagai aman di Chrome PC timbangan** — buka
   `chrome://flags/#unsafely-treat-insecure-origin-as-secure`, isi dengan
   `http://<ip-server>`, lalu Enable dan restart Chrome.
3. **Input Berat Manual** — tersedia di halaman penimbangan sebagai cadangan.
   Operator mengetik angka sesuai indikator; sisanya (set point, status, penyimpanan)
   berjalan sama.

Browser yang didukung: Chrome / Edge terbaru di PC atau laptop. Firefox dan Safari
belum mendukung Web Serial API.

## Cara pakai

1. Buka **TIM RESI → Timbangan Resi**.
2. Klik **Hubungkan Port**, pilih port COM indikator, sesuaikan baud rate bila perlu.
3. Scan nomor resi di kolom Resi lalu tekan Enter.
   Sistem menampilkan daftar SKU, berat standar, dan rentang ACCEPT.
4. Letakkan paket di timbangan. Angka dan warna berubah otomatis:
   kuning UNDER, hijau ACCEPT, merah OVER.
5. Klik **Simpan Hasil Timbang**.

Catatan perilaku:

- Satu resi hanya bisa ditimbang sekali. Scan ulang akan menampilkan hasil sebelumnya.
- Status dan berat standar **dihitung ulang di server** saat menyimpan, jadi angka
  yang tersimpan tidak bisa dimanipulasi dari sisi browser.
- Kolom **Sumber** pada tabel item menunjukkan asal berat tiap SKU:
  - `Master` — dari `tbltimbangan_sku` (hasil impor Excel)
  - `tblsku` — cadangan dari kolom `tblsku.berat` yang sudah ada di sistem
  - `Belum ada` — belum ada berat sama sekali; berat standar paket jadi lebih kecil
    dari seharusnya dan sistem memberi peringatan
- Bila **semua** SKU pada resi belum punya berat, penyimpanan ditolak.

## Laporan

**TIM RESI → Laporan Timbangan**: filter periode dan status, lalu Export Excel.
Kolom ekspor mencakup berat standar, batas bawah/atas, berat aktual, selisih,
toleransi, status, jumlah SKU tanpa master, petugas, komputer, dan waktu.

## Berkas yang ditambahkan

```
timbangan_setup.sql                                   -- DDL + menu + param
application/controllers/Timbangan.php
application/models/Timbangan_fcd.php
application/views/timbangan/scan_timbangan.php        -- halaman penimbangan
application/views/timbangan/master_timbangan.php      -- impor Excel + pengaturan
application/views/timbangan/laporan_timbangan.php
application/views/template_report/timbangan_report.php
application/config/routes.php                         -- 11 route baru
```

## Catatan teknis

- Semua berat disimpan dalam **gram**. Konversi dari satuan indikator dilakukan di
  server berdasarkan param `SATUAN_INDIKATOR`.
- Berat standar per resi disimpan sebagai *snapshot* di `tbltimbangan`, sehingga
  perubahan master berat di kemudian hari tidak mengubah hasil penimbangan lama.
- Port serial yang sudah terhubung tetap terbuka bila operator berpindah menu
  tanpa menekan **Putuskan**. Port akan ditutup otomatis saat halaman penimbangan
  dibuka kembali, atau saat tab browser ditutup.

# Perbaikan Qty pada Report Jubelio "Daftar Serial Number Terjual"

**Dokumen ini untuk diajukan ke support Jubelio.**

Report berjalan di infrastruktur Jubelio (`report-prod.jubelio.com/xlsx/`), sehingga
definisinya tidak dapat diubah dari sisi merchant.

## Identitas report

| | |
|---|---|
| Nama report | **Daftar Serial Number Terjual** |
| Perusahaan | HAVEN |
| companyId | **59115** |
| tenantId | rjwh0bsvtbwpincydmzzvw |
| Layanan | `report-prod.jubelio.com/xlsx/` |

> Jangan sertakan token URL report saat mengirim tiket. Token tersebut memberi akses
> unduh ke data penjualan dan **tidak memiliki masa kedaluwarsa** (`exp`), jadi
> perlakukan seperti kata sandi. Nama report + companyId sudah cukup untuk identifikasi.

## Masalah

Report export yang dipakai iresis (kolom A–V, ditambah W–AA) melebur baris order-item
yang kembar menjadi satu, **tanpa menjumlahkan qty-nya**.

Akibatnya, kalau satu pesanan memuat SKU yang sama pada beberapa baris order-item,
qty yang keluar cuma milik satu baris.

### Tersangka utama: `SELECT DISTINCT`

Report ini bernama "daftar serial number terjual", **tetapi file hasilnya sudah tidak
punya kolom serial number sama sekali** (kolom berhenti di AA: NAMA BARANG, Status,
Tgl Last Status WMS, WAKTU_LAST_STATUS_WMS, Status WMS).

Urutan kejadian yang paling masuk akal:

1. Dulu report menampilkan kolom serial number → 1 baris = 1 unit, tiap baris unik
2. `DISTINCT` di query aman, karena tidak ada baris kembar
3. Kolom serial dicabut → 4 baris Tokopedia jadi identik seluruhnya
   (pesanan, SKU, qty 1, rak, tanggal — semua sama persis)
4. `DISTINCT` melebur keempatnya jadi satu baris qty 1

Hipotesis ini menjelaskan semuanya sekaligus:

- kenapa hanya Tokopedia yang kena — Shopee mengirim 1 baris qty 300 yang memang unik,
  tidak punya kembaran untuk dilebur
- kenapa di seluruh file **tidak ada satu pun baris duplikat** (0 dari 1.642 baris)
- kenapa nama report menyebut serial number yang sudah tidak ada isinya

Kemungkinan kedua: `GROUP BY` yang ikut menyertakan kolom `qty` di dalamnya. Efeknya
identik dan perbaikannya sama.

## Yang paling terdampak: TOKOPEDIA

Tokopedia mengirim item pesanan sebagai baris terpisah masing-masing qty 1. Begitu
report meng-group tanpa `SUM`, seluruhnya runtuh jadi satu baris qty 1. Shopee
mengirim satu baris dengan qty asli, sehingga selamat dari group.

Sebaran qty di satu file export yang sama:

| Marketplace | Baris | % qty=1 | Qty tertinggi | Rata-rata |
|---|---|---|---|---|
| Shopee | 1.335 | 34% | 400 | 7,20 |
| **Tokopedia** | **81** | **100%** | **1** | **1,00** |
| shop \| Tokopedia | 10 | 100% | 1 | 1,00 |
| Internal | 1 | 0% | 100 | 100,00 |

**Seluruh baris Tokopedia berqty 1 tanpa kecuali, qty tertingginya pun 1.** Mustahil
91 pesanan berturut-turut kebetulan semuanya berisi 1 pcs, sementara Shopee di file
yang sama rata-rata 7,20 dan ada yang sampai 400.

### Dampak historis

Di database iresis, dari **24.609 baris detail Tokopedia, 24.018 (98%) berqty 1**
(rata-rata 1,28). Bandingkan Shopee: hanya 34% yang berqty 1, rata-rata 8,72.

Satu-satunya kumpulan data Tokopedia yang qty-nya sehat adalah 591 baris yang
diimport **7–9 Januari 2026** (rata-rata 8,99 pada 7 Januari). Di luar rentang itu,
baik sebelum maupun sesudah, seluruh baris Tokopedia berqty 1. Perlu dicek apa yang
berbeda pada periode tersebut — kemungkinan report atau sumber file yang dipakai
saat itu memang lain.

Untuk modul timbangan: dari 14 resi yang berat fisiknya ≥1,9× dari hitungan iresis,
**13 di antaranya Tokopedia**.

### Contoh nyata

Pesanan `TP-585432019627313028-72116`, resi `JY1312613006`:

| Sumber | Isi |
|---|---|
| Layar Jubelio | SKU `B6F2EC` muncul 4 baris, masing-masing qty 1 |
| File export | 1 baris, `QTY = 1` |
| Seharusnya | 1 baris, `QTY = 4` |

Terbukti dari timbangan fisik: paket ditimbang **1.700 g**. Berat 1 pcs `B6F2EC`
adalah 416 g, kemasan 10 g.

- Dengan qty 1 → berat standar 426 g → status **OVER** (selisih 1.274 g)
- Dengan qty 4 → berat standar 1.674 g, rentang 1.590–1.758 g → **ACCEPT**

## Bukti bahwa masalahnya bukan di iresis

Perbandingan file export vs database iresis, **1.427 kombinasi** (no_resi + no_pesanan + SKU):

```
qty file == qty db  : 1427
qty file != qty db  : 0
```

Import iresis 100% setia pada isi file. Data sudah hilang sebelum file dibuat.

Penguat lain: di seluruh file 1.642 baris, **kombinasi duplikat = 0**. Artinya
report memang sudah meng-group, dan di situlah qty-nya hilang.

Kolom QTY sendiri tidak rusak — di file yang sama ada nilai 200, 11, 10, 5. Jadi
kalau pembeli order 4 pcs dalam **satu** baris, angkanya benar. Rusaknya hanya
saat Jubelio memecah item yang sama menjadi beberapa baris.

## Perbaikan yang diminta

Dua opsi, keduanya menyelesaikan masalah di sisi merchant. **Opsi 2 lebih disarankan**
karena tidak ada agregat yang bisa salah pilih.

Catatan untuk tim Jubelio: contoh SQL di bawah adalah dugaan bentuk query berdasarkan
gejala yang terlihat pada file hasil export, bukan salinan query yang sebenarnya.
Yang kami minta adalah perilakunya — qty per pesanan+SKU harus mencerminkan total
unit yang dipesan, bukan qty satu baris order-item saja.

> ### Cek dulu: apakah join ke tabel serial/WMS masih ada?
>
> Perbaikannya berbeda tergantung ini, dan salah pilih berakibat fatal.
>
> | Kondisi | Perbaikan | Kalau salah |
> |---|---|---|
> | Join serial sudah dibuang | cukup hapus `DISTINCT` | aman |
> | Join serial masih ada | **jangan** hapus `DISTINCT` begitu saja | Shopee qty 300 jadi 300 baris × 300 = 90.000 |
>
> Kalau join-nya masih ada, buang duplikat di level order-item dulu, baru jumlahkan:
>
> ```sql
> SELECT no_pesanan, no_resi, item_code, SUM(qty) AS qty, ...
> FROM (
>     SELECT DISTINCT o.no_pesanan, o.no_resi, d.detail_id, d.item_code, d.qty, ...
>     FROM   sales_order o
>     JOIN   sales_order_detail d ON ...
>     JOIN   wms_status w ON ...          -- join yang menggandakan baris
> ) x
> GROUP BY no_pesanan, no_resi, item_code, ...
> ```
>
> Kunci ada pada `d.detail_id` di dalam `DISTINCT`: itu yang membuat tiap order-item
> tetap terhitung sekali walau join WMS menggandakannya.

### Opsi 2 — keluarkan 1 baris per order-item (DISARANKAN)

Biarkan SKU yang sama muncul berkali-kali, jangan dilebur.

Di Jubelio, pesanan Tokopedia qty 10 memang sudah tersimpan sebagai 10 baris @1.
Jadi cukup berhenti meleburnya — tidak perlu menulis fungsi agregat apa pun.

iresis sudah menjumlahkan sendiri baris dengan kombinasi no_resi + no_pesanan + SKU
yang sama (`Receipt_fcd.php`, `$batch_detail_map[$detail_key]['jumlah'] += ...`).
Sudah diuji: 4 baris qty 1 masuk sebagai qty 4, dan 3 baris qty 2 masuk sebagai qty 6.

Hasilnya untuk kedua channel:

| Channel | Keluar dari report | Hasil di iresis |
|---|---|---|
| Tokopedia | 10 baris @1 | dijumlah → 10 |
| Shopee | 1 baris @400 | lewat apa adanya → 400 |

Ukuran file akan bertambah karena baris tidak lagi dilebur. Untuk gambaran, file
1.642 baris pada contoh di atas akan tumbuh sebanding dengan total qty Tokopedia.

### Opsi 1 — jumlahkan saat group

```sql
SELECT ...,
       SUM(qty) AS qty      -- sebelumnya qty diambil apa adanya
FROM   ...
GROUP  BY no_pesanan, sku, ...
```

Pastikan tidak ada kolom lain yang memaksa pecahnya group (mis. serial number,
id order-item). Kalau ada, kolom itu harus dikeluarkan dari `GROUP BY`.

> ### JANGAN pakai `COUNT(*)`
>
> Di Jubelio, pesanan Tokopedia qty 10 tampil sebagai 10 baris @1. Karena itu
> `COUNT(*)` **kebetulan** menghasilkan angka benar untuk Tokopedia — dan justru
> di situ bahayanya.
>
> Shopee tidak dipecah: satu baris membawa qty asli. Pesanan Shopee 400 pcs ada di
> **satu** baris, sehingga `COUNT(*)` akan mengubahnya jadi **1**.
>
> Perbaikan pakai `COUNT(*)` akan memperbaiki Tokopedia tapi merusak Shopee —
> dan Shopee adalah 211.913 dari 237.065 baris detail, jauh lebih besar.
>
> **`SUM(qty)` benar untuk keduanya**: Tokopedia 10 baris @1 → 10, Shopee 1 baris
> @400 → 400.

### Opsi 2 — jangan di-group sama sekali

Keluarkan 1 baris per order-item apa adanya (SKU sama boleh muncul berkali-kali).

iresis sudah menjumlahkan sendiri baris dengan kombinasi no_resi + no_pesanan + SKU
yang sama, jadi opsi ini aman. Sudah diuji: 4 baris qty 1 masuk sebagai qty 4.

## Cara memverifikasi setelah diperbaiki

Dari folder `iresis-prod-new`:

```bash
C:\xampp\php\php.exe resync.php periksa "C:\path\export-baru.xlsx"
```

Perintah ini **tidak menulis apa pun**, hanya melaporkan dampaknya.

Ada dua hal yang harus dilihat.

**Pertama**, bagian sebaran qty per marketplace. Selama report masih rusak, muncul:

```
  tokopedia                    81       81      100%          1     1.00

  *** PERINGATAN ***
  Channel 'tokopedia': seluruh 81 baris berqty 1, qty tertinggi pun 1.
```

Setelah report diperbaiki, peringatan itu harus **hilang**, dan kolom `qty maks`
untuk Tokopedia harus lebih dari 1. Ini indikator paling cepat bahwa perbaikannya
sudah kena.

**Kedua**, baris `Detail qty berubah` harus memunculkan koreksi seperti

```
JY1312613006   B6F2EC   qty 1 -> 4
```

Kalau laporannya sudah sesuai, jalankan:

```bash
C:\xampp\php\php.exe resync.php terapkan "C:\path\export-baru.xlsx" <id_user>
```

## Catatan tambahan

Sebanyak **214 dari 1.642 baris (13%)** di file punya kolom `NO_RESI` kosong —
pesanan `SO-`/`SP-` yang belum dapat nomor resi, sebagian qty-nya besar (200, 11, 10).
Baris seperti ini memang dilewati iresis dan akan masuk pada upload berikutnya
setelah resinya terbit. Bukan bug, tapi perlu diketahui saat merekonsiliasi angka.

# 📋 Panduan Perbaikan Database JNT vs JNT Cargo

## 🎯 Tujuan
Memastikan database memiliki 2 kurir terpisah (JNT dan JNT Cargo) dan memperbaiki data lama yang salah.

---

## 📝 LANGKAH-LANGKAH

### **LANGKAH 1: Cek Kurir yang Ada di Database**

Jalankan query SQL ini di database Anda:

```sql
SELECT id_kurir, nama_kurir, isactive 
FROM tblkurir 
WHERE LOWER(nama_kurir) LIKE '%jnt%' 
   OR LOWER(nama_kurir) LIKE '%j&t%'
ORDER BY nama_kurir;
```

**Hasil yang diharapkan:**
- Harus ada **2 kurir terpisah**:
  - Satu untuk **JNT** atau **J&T** (bukan cargo)
  - Satu untuk **JNT Cargo** atau **J&T Cargo**

---

### **LANGKAH 2: Tambahkan Kurir yang Belum Ada**

#### **OPSI A: Melalui Aplikasi (Lebih Mudah)**

1. Login ke aplikasi sebagai Admin
2. Buka menu **Master Kurir** (biasanya di menu Master Data)
3. Klik **"Tambah kurir baru"**
4. Tambahkan kurir yang belum ada:
   - Jika belum ada **JNT** (bukan cargo): Tambahkan dengan nama **"JNT"** atau **"J&T"**
   - Jika belum ada **JNT Cargo**: Tambahkan dengan nama **"JNT Cargo"** atau **"J&T Cargo"**
5. Simpan

#### **OPSI B: Melalui SQL (Langsung ke Database)**

Jalankan query ini:

```sql
-- Tambah JNT (bukan cargo) jika belum ada
INSERT INTO tblkurir (nama_kurir, isactive) 
SELECT 'JNT', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tblkurir 
    WHERE LOWER(nama_kurir) = 'jnt' 
    AND LOWER(nama_kurir) NOT LIKE '%cargo%'
);

-- Tambah JNT Cargo jika belum ada
INSERT INTO tblkurir (nama_kurir, isactive) 
SELECT 'JNT Cargo', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tblkurir 
    WHERE LOWER(nama_kurir) LIKE '%jnt%' 
    AND LOWER(nama_kurir) LIKE '%cargo%'
);
```

---

### **LANGKAH 3: Catat ID Kurir**

Jalankan query ini untuk mencatat `id_kurir` yang akan digunakan:

```sql
SELECT id_kurir, nama_kurir 
FROM tblkurir 
WHERE (LOWER(nama_kurir) LIKE '%jnt%' OR LOWER(nama_kurir) LIKE '%j&t%')
ORDER BY 
    CASE 
        WHEN LOWER(nama_kurir) LIKE '%cargo%' THEN 1 
        ELSE 2 
    END;
```

**Contoh hasil:**
```
id_kurir | nama_kurir
---------|------------
5        | JNT Cargo
3        | JNT
```

**Catat id_kurir ini!** (Akan digunakan di langkah berikutnya)

---

### **LANGKAH 4: Cek Data yang Perlu Diperbaiki**

Cek berapa banyak data yang `id_kurir = 99` (Tidak diketahui):

```sql
SELECT COUNT(*) as total_data_salah 
FROM tblprintresi 
WHERE id_kurir = 99;
```

**Jika hasilnya 0**, berarti tidak ada data yang perlu diperbaiki. ✅

**Jika hasilnya > 0**, lanjut ke Langkah 5.

---

### **LANGKAH 5: Perbaiki Data Lama**

⚠️ **PENTING: Backup database dulu sebelum menjalankan update!**

#### **5A. Cek Contoh Data yang Akan Diupdate (Opsional)**

```sql
SELECT noresi, id_kurir, created_at 
FROM tblprintresi 
WHERE id_kurir = 99 
LIMIT 10;
```

#### **5B. Update Data Berdasarkan Pola Noresi**

**Untuk JNT Cargo** (biasanya prefix: JP, JX, JO, JZ, TJ, atau 20):

```sql
-- GANTI [ID_KURIR_JNT_CARGO] dengan id_kurir JNT Cargo dari Langkah 3
-- Contoh: jika id_kurir JNT Cargo = 5, maka ganti menjadi:
-- UPDATE tblprintresi SET id_kurir = 5 WHERE ...

UPDATE tblprintresi 
SET id_kurir = (
    SELECT id_kurir FROM tblkurir 
    WHERE LOWER(nama_kurir) LIKE '%jnt%' 
    AND LOWER(nama_kurir) LIKE '%cargo%' 
    LIMIT 1
)
WHERE id_kurir = 99
AND (
    SUBSTRING(noresi, 1, 2) IN ('JP', 'JX', 'JO', 'JZ', 'TJ')
    OR SUBSTRING(noresi, 1, 2) = '20'
);
```

**Untuk JNT biasa** (jika ada pola khusus, sesuaikan):

```sql
-- Jika ada pola khusus untuk JNT biasa, tambahkan kondisi di sini
-- UPDATE tblprintresi 
-- SET id_kurir = (
--     SELECT id_kurir FROM tblkurir 
--     WHERE LOWER(nama_kurir) = 'jnt' 
--     AND LOWER(nama_kurir) NOT LIKE '%cargo%' 
--     LIMIT 1
-- )
-- WHERE id_kurir = 99
-- AND [TAMBAHKAN KONDISI];
```

---

### **LANGKAH 6: Verifikasi Hasil**

#### **6A. Cek Apakah Masih Ada Data dengan id_kurir = 99**

```sql
SELECT COUNT(*) as masih_ada_tidak_diketahui 
FROM tblprintresi 
WHERE id_kurir = 99;
```

**Harusnya hasilnya 0** atau sangat sedikit (hanya data yang benar-benar tidak diketahui).

#### **6B. Cek Distribusi Kurir**

```sql
SELECT 
    k.id_kurir,
    k.nama_kurir,
    COUNT(p.id_printresi) as total_resi
FROM tblkurir k
LEFT JOIN tblprintresi p ON p.id_kurir = k.id_kurir
WHERE LOWER(k.nama_kurir) LIKE '%jnt%' OR LOWER(k.nama_kurir) LIKE '%j&t%'
GROUP BY k.id_kurir, k.nama_kurir
ORDER BY k.nama_kurir;
```

**Hasil yang diharapkan:**
- JNT Cargo memiliki total resi
- JNT (bukan cargo) memiliki total resi
- Tidak ada lagi yang "Tidak diketahui" (id_kurir = 99)

#### **6C. Test di Laporan**

1. Buka aplikasi
2. Buka menu **Laporan** → **Laporan Resi Dikirim** (Shipping Report)
3. Pilih periode tertentu
4. Cek apakah:
   - ✅ **JNT Cargo** muncul terpisah
   - ✅ **JNT** (bukan cargo) muncul terpisah
   - ✅ Tidak ada lagi **"Tidak diketahui"**

---

## ✅ Checklist

- [ ] Langkah 1: Sudah cek kurir yang ada di database
- [ ] Langkah 2: Sudah tambah kurir yang belum ada (JNT dan JNT Cargo)
- [ ] Langkah 3: Sudah catat id_kurir untuk JNT dan JNT Cargo
- [ ] Langkah 4: Sudah cek data yang perlu diperbaiki
- [ ] Langkah 5: Sudah perbaiki data lama (jika ada)
- [ ] Langkah 6: Sudah verifikasi hasil
- [ ] Test upload Excel baru dengan "JNT Cargo" → harus ter-mapping benar
- [ ] Test upload Excel baru dengan "JNT" → harus ter-mapping benar
- [ ] Test laporan → JNT dan JNT Cargo muncul terpisah

---

## ⚠️ Catatan Penting

1. **Backup database** sebelum menjalankan update!
2. **Test di environment development** dulu sebelum production
3. Jika ragu, jalankan query **SELECT** dulu untuk melihat hasilnya
4. Data baru setelah perbaikan akan otomatis ter-mapping dengan benar
5. Data lama yang sudah salah perlu diperbaiki manual (langkah 5)

---

## 🆘 Troubleshooting

### Masalah: Masih muncul "Tidak diketahui" di laporan

**Solusi:**
1. Cek apakah masih ada data dengan `id_kurir = 99`
2. Jika ada, perbaiki dengan langkah 5
3. Pastikan kurir sudah ada di `tblkurir`

### Masalah: Upload Excel masih salah mapping

**Solusi:**
1. Pastikan nama kurir di Excel sesuai dengan yang ada di database
2. Cek apakah kurir sudah ada di `tblkurir`
3. Pastikan kode sudah di-deploy dengan benar

### Masalah: Tidak tahu pola noresi untuk JNT vs JNT Cargo

**Solusi:**
1. Cek contoh noresi yang sudah benar di database
2. Identifikasi pola prefix noresi
3. Sesuaikan query update di langkah 5

---

## 📞 Bantuan

Jika masih ada masalah, cek:
- Log aplikasi untuk error
- Query yang dijalankan di database
- Data di `tblprintresi` dan `tblkurir`


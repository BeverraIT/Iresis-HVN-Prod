# 🚀 Cara Setup Kurir JNT dan JNT Cargo

## ✅ Controller sudah dibuat!

Saya sudah membuat controller khusus untuk setup kurir JNT dan JNT Cargo secara otomatis.

---

## 📋 Cara Menggunakan

### **CARA 1: Via Browser (Paling Mudah)**

1. **Login ke aplikasi** sebagai Admin

2. **Buka URL berikut di browser:**
   ```
   setup_kurir/init_jnt
   ```
   Atau lengkapnya: `http://localhost/iresis-dev/setup_kurir/init_jnt`

3. **Halaman akan menampilkan:**
   - ✅ Status apakah kurir JNT sudah ada atau baru ditambahkan
   - ✅ Status apakah kurir JNT Cargo sudah ada atau baru ditambahkan
   - 📝 ID kurir yang sudah dibuat (catat ID ini!)

4. **Selesai!** Kurir sudah otomatis ditambahkan ke database.

---

### **CARA 2: Via AJAX/API**

Jika ingin mengakses via AJAX atau API:

```javascript
// Via JavaScript/jQuery
$.get('setup_kurir/init_jnt', function(response) {
    console.log(response);
});
```

Response JSON:
```json
{
    "success": true,
    "results": [
        "✅ Kurir 'JNT' berhasil ditambahkan (ID: 3)",
        "✅ Kurir 'JNT Cargo' berhasil ditambahkan (ID: 5)"
    ],
    "errors": [],
    "jnt_id": 3,
    "jnt_cargo_id": 5
}
```

---

### **CARA 3: Cek Status Kurir**

Untuk mengecek status kurir JNT yang sudah ada:

**URL:**
```
setup_kurir/check_jnt
```
Atau lengkapnya: `http://localhost/iresis-dev/setup_kurir/check_jnt`

**Response JSON:**
```json
{
    "jnt": [
        {
            "id_kurir": 3,
            "nama_kurir": "JNT",
            "isactive": 1
        }
    ],
    "jnt_cargo": [
        {
            "id_kurir": 5,
            "nama_kurir": "JNT Cargo",
            "isactive": 1
        }
    ],
    "status": {
        "jnt_exists": true,
        "jnt_cargo_exists": true,
        "ready": true
    }
}
```

---

## 🔍 Fitur Controller

### **1. init_jnt()**
- ✅ Otomatis cek apakah kurir JNT sudah ada
- ✅ Otomatis cek apakah kurir JNT Cargo sudah ada
- ✅ Otomatis tambahkan kurir yang belum ada
- ✅ Tampilkan ID kurir yang sudah dibuat
- ✅ Tidak akan duplikat (cek dulu sebelum insert)

### **2. check_jnt()**
- ✅ Cek status kurir JNT yang sudah ada
- ✅ Return JSON dengan detail kurir
- ✅ Tampilkan status ready atau belum

---

## 📝 Langkah Selanjutnya

Setelah kurir sudah ditambahkan:

1. **Catat ID kurir** yang ditampilkan:
   - ID JNT: `3` (contoh)
   - ID JNT Cargo: `5` (contoh)

2. **Perbaiki data lama** (jika ada):
   - Gunakan script SQL di `fix_jnt_cargo_mapping.sql`
   - Atau jalankan query update manual

3. **Test upload Excel baru:**
   - Upload Excel dengan kurir "JNT Cargo" → harus ter-mapping ke ID JNT Cargo
   - Upload Excel dengan kurir "JNT" → harus ter-mapping ke ID JNT

4. **Test laporan:**
   - Buka laporan Shipping Report
   - Pastikan JNT dan JNT Cargo muncul terpisah

---

## ⚠️ Catatan

- Controller ini **aman** karena:
  - Cek dulu sebelum insert (tidak akan duplikat)
  - Menggunakan model yang sudah ada (`courrier_fcd`)
  - Mengikuti struktur database yang sudah ada

- Jika kurir sudah ada, controller akan:
  - Menampilkan info bahwa kurir sudah ada
  - Menampilkan ID kurir yang sudah ada
  - Tidak akan membuat duplikat

---

## 🆘 Troubleshooting

### Masalah: URL tidak bisa diakses

**Solusi:**
1. Pastikan controller sudah ada di `application/controllers/Setup_kurir.php`
2. Pastikan sudah login sebagai Admin
3. Cek apakah ada error di log aplikasi

### Masalah: Kurir tidak terbuat

**Solusi:**
1. Cek apakah user sudah login
2. Cek apakah ada error di log aplikasi
3. Cek apakah model `courrier_fcd` sudah ada
4. Cek apakah tabel `tblkurir` sudah ada

### Masalah: Ingin menggunakan nama kurir berbeda

**Solusi:**
Edit file `application/controllers/Setup_kurir.php`:
- Baris 48: Ganti `'JNT'` dengan nama yang diinginkan
- Baris 78: Ganti `'JNT Cargo'` dengan nama yang diinginkan

---

## ✅ Checklist

- [ ] Controller `Setup_kurir.php` sudah dibuat
- [ ] Akses URL `setup_kurir/init_jnt` berhasil
- [ ] Kurir JNT sudah ditambahkan
- [ ] Kurir JNT Cargo sudah ditambahkan
- [ ] ID kurir sudah dicatat
- [ ] Data lama sudah diperbaiki (jika ada)
- [ ] Test upload Excel baru berhasil
- [ ] Test laporan berhasil

---

## 🎉 Selesai!

Setelah setup kurir selesai, sistem akan otomatis:
- ✅ Mapping "JNT Cargo" ke kurir JNT Cargo
- ✅ Mapping "JNT" ke kurir JNT biasa
- ✅ Menampilkan dengan benar di laporan


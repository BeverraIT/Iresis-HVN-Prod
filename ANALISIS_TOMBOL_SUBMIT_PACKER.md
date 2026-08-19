# Analisis Detail: Tombol Submit pada Scan Packer

## ⚠️ PENTING: Dokumen ini dibuat untuk analisis sebelum perubahan

---

## 📍 Lokasi File

### **1. View (Frontend)**
- **File**: `application/views/packer/scan_packer.php`
- **Tombol HTML**: Baris 38-40
- **JavaScript Handler**: Baris 677-733

### **2. Controller (Backend)**
- **File**: `application/controllers/Packer.php`
- **Method**: `save_packer()` - Baris 187-233

### **3. Model (Database)**
- **File**: `application/models/Packer_fcd.php`
- **Method**: `save()` - Baris 7-70

### **4. Routes**
- **File**: `application/config/routes.php`
- **Route**: `packer/save-packer` → `packer/save_packer` (Baris 85)

---

## 🔄 Flow Lengkap Tombol Submit

### **A. Frontend Flow (JavaScript)**

```
1. User klik tombol #submit-selected
   ↓
2. Validasi: Cek noresi tidak kosong
   ├─→ Jika kosong: Show error modal, return
   └─→ Jika ada: Continue
   ↓
3. AJAX POST ke 'packer/save-packer'
   Data: { noresi: noresi }
   ↓
4. Success Handler:
   ├─→ Show success modal (2 detik)
   ├─→ Hide: #result-info, #table-scan-packer, #button-footer
   ├─→ Focus ke input #noresi
   └─→ Siap untuk scan berikutnya
   ↓
5. Error Handler:
   ├─→ Parse error message dari response
   ├─→ Show error modal (1 detik)
   └─→ User bisa coba lagi
```

### **B. Backend Flow (Controller)**

```
1. Validasi Method: Harus POST
   ├─→ Jika GET: Return 400 error
   └─→ Jika POST: Continue
   ↓
2. Ambil noresi dari POST
   ↓
3. Ambil Status Performa (Prioritas):
   ├─→ Prioritas 1: Dari POST (status_performa)
   ├─→ Prioritas 2: Dari Session (user_status_performa)
   └─→ Prioritas 3: Default NORMAL_PACKER
   ↓
4. Panggil Model: packer_fcd->save($packer, $user)
   ↓
5. Handle Response:
   ├─→ Jika error: Return error dengan code & message
   ├─→ Jika affected_rows > 0: Return 201 success
   └─→ Jika affected_rows = 0: Return 200 nothing to save
```

### **C. Database Flow (Model)**

```
1. Validasi: Cek resi ada di tblprintresi
   ├─→ Jika tidak ada: Return error "Nomor resi tidak ditemukan"
   └─→ Jika ada: Continue
   ↓
2. Validasi: Cek status_pesanan
   ├─→ Jika COMPLETED/CANCELED: Return error
   └─→ Jika valid: Continue
   ↓
3. Validasi: Cek resi sudah di-pick
   ├─→ Cek di tblresiambilbarang
   ├─→ Jika belum di-pick: Return error "Nomor Resi belum di-picker"
   └─→ Jika sudah di-pick: Continue
   ↓
4. Validasi: Cek resi belum di-pack
   ├─→ Cek di tblpacking
   ├─→ Jika sudah di-pack: Return error "Nomor resi sudah di-packing"
   └─→ Jika belum di-pack: Continue
   ↓
5. Insert ke tblpacking:
   ├─→ id_resi
   ├─→ tanggal_packing (current timestamp)
   ├─→ packer_pegawai (user id)
   ├─→ keterangan (nama_komputer)
   └─→ status_performa_id
   ↓
6. Update tblresiambilbarang:
   └─→ Reset pending = ''
   ↓
7. Log KPI:
   └─→ log_kpi_transaksi() untuk tracking
   ↓
8. Return: affected_rows
```

---

## 🔍 Dependencies & Relationships

### **1. Database Tables**

#### **tblprintresi** (Master Receipt)
- **Field**: `id_printresi`, `noresi`, `status_pesanan`
- **Validasi**: Harus exist, status tidak boleh COMPLETED/CANCELED

#### **tblresiambilbarang** (Data Picking)
- **Field**: `id_resiambilbarang`, `id_resi`, `pending`
- **Validasi**: Harus exist (resi sudah di-pick)
- **Update**: Reset `pending = ''` setelah packing

#### **tblpacking** (Data Packing)
- **Field**: `id_packing`, `id_resi`, `tanggal_packing`, `packer_pegawai`, `keterangan`, `status_performa_id`
- **Validasi**: Harus belum exist (resi belum di-pack)
- **Insert**: Data packing baru

#### **tblkpi** (KPI Tracking)
- **Field**: `id_log`, `id_user`, `id_statusperforma`, `tanggal`, `tipe_transaksi`, `jumlah_resi`
- **Insert/Update**: Log transaksi untuk tracking KPI

#### **tblstatusperforma** (Status Performa Harian)
- **Field**: `id_log`, `id_user`, `id_statusperforma`, `tanggal`
- **Read**: Ambil status performa user hari ini

#### **tblmasterstatusperforma** (Master Status)
- **Field**: `id_statusperforma`, `kode_status`, `role`, `status_name`
- **Read**: Ambil ID status berdasarkan kode/nama

### **2. Session Data**

#### **user** (User Info)
- **Field**: `id_user`, `nama_komputer`, `username`
- **Digunakan**: User ID untuk logging, nama_komputer untuk keterangan

#### **user_status_performa** (Status Performa User)
- **Field**: `id_statusperforma`
- **Digunakan**: Status performa default jika tidak ada di POST

### **3. JavaScript Variables**

#### **Global Variables**
```javascript
var table;           // DataTable instance
var idPrintResi;     // ID print resi (untuk modal masalah)
var noresi;          // No resi (untuk modal masalah)
var sku;             // SKU (untuk modal masalah)
var qty;             // Quantity (untuk modal masalah)
```

#### **jQuery Selectors**
```javascript
$('#submit-selected')      // Tombol Submit
$('#noresi-detail')        // Input noresi (readonly)
$('#result-info')          // Div info hasil scan
$('#table-scan-packer')    // Tabel data items
$('#button-footer')        // Footer dengan tombol
$('#successModal')          // Modal success
$('#errorModal')           // Modal error
```

### **4. AJAX Endpoints**

#### **packer/save-packer**
- **Method**: POST
- **Controller**: `Packer::save_packer()`
- **Data Input**: `{ noresi: string }`
- **Response**: JSON `{ message: string, data?: object }`

---

## ✅ Validasi yang Ada

### **Frontend Validasi (JavaScript)**

1. **No Resi Tidak Kosong**
   ```javascript
   if (!noresi || noresi.trim() === '') {
       // Show error modal
       return;
   }
   ```

### **Backend Validasi (Controller)**

1. **Method Harus POST**
   ```php
   if ($this->input->method() == 'get') {
       return 400 error;
   }
   ```

### **Database Validasi (Model)**

1. **Resi Harus Exist**
   ```php
   if (empty($receipt)) {
       return error "Nomor resi tidak ditemukan";
   }
   ```

2. **Status Tidak Boleh COMPLETED/CANCELED**
   ```php
   if (in_array($receipt->status_pesanan, ['COMPLETED', 'CANCELED'])) {
       return error "Status pesanan sudah COMPLETED/CANCELED";
   }
   ```

3. **Resi Harus Sudah Di-Pick**
   ```php
   if (!$picking_exist) {
       return error "Nomor Resi belum di-picker";
   }
   ```

4. **Resi Belum Di-Pack**
   ```php
   if ($packer_exist) {
       return error "Nomor resi sudah di-packing";
   }
   ```

---

## 🔗 Koneksi dengan Fitur Lain

### **1. KPI Tracking**
- Setiap submit packing → Log ke `tblkpi`
- Tracking berdasarkan `id_user`, `id_statusperforma`, `tanggal`
- Increment `jumlah_resi` jika sudah ada log hari ini

### **2. Status Performa**
- Status performa diambil dari:
  1. POST data (jika user pilih manual)
  2. Session user (dari login)
  3. Default NORMAL_PACKER

### **3. Masalah Picker**
- Tombol "Masalah Picker" di tabel (bukan tombol Submit utama)
- Menggunakan endpoint berbeda: `packer/masalah-picker-save`
- Tidak terkait langsung dengan tombol Submit

### **4. Reset Button**
- Tombol Reset (`#btn-reset`) untuk clear form
- Hide: result-info, table, footer
- Focus kembali ke input noresi

---

## 📊 Data yang Disimpan

### **tblpacking**
```php
[
    'id_resi' => $receipt->id_printresi,
    'tanggal_packing' => date('Y-m-d H:i:s'),
    'packer_pegawai' => $user['id_user'],
    'keterangan' => $user['nama_komputer'],
    'status_performa_id' => $packer['status_performa_id'] ?? null
]
```

### **tblresiambilbarang** (Update)
```php
['pending' => '']
```

### **tblkpi** (Insert/Update)
```php
// Jika belum ada log hari ini:
[
    'id_user' => $user_id,
    'id_statusperforma' => $status_log->id_statusperforma,
    'tanggal' => date('Y-m-d'),
    'tipe_transaksi' => 'PACKER',
    'jumlah_resi' => 1
]

// Jika sudah ada log:
// Update: jumlah_resi = jumlah_resi + 1
```

---

## 🎯 Response Codes

### **Success**
- **201**: Data berhasil disimpan (affected_rows > 0)
- **200**: Nothing to save (affected_rows = 0)

### **Error**
- **400**: Bad Request
  - Invalid request method (GET)
  - Nomor resi tidak ditemukan
  - Status pesanan COMPLETED/CANCELED
  - Nomor Resi belum di-picker
  - Nomor resi sudah di-packing

---

## ⚠️ Poin Kritis yang Harus Diperhatikan

### **1. Transaction Safety**
- Model menggunakan transaction untuk konsistensi data
- Jika error, semua perubahan di-rollback

### **2. Status Performa Priority**
- Prioritas 1: POST data
- Prioritas 2: Session user
- Prioritas 3: Default NORMAL_PACKER
- **JANGAN** ubah prioritas ini tanpa analisis mendalam

### **3. KPI Logging**
- Setiap submit packing → Log KPI
- Jika tidak ada status performa → Skip logging (warning)
- **JANGAN** hapus logging ini

### **4. Pending Reset**
- Setelah packing, reset `pending = ''` di tblresiambilbarang
- **JANGAN** hapus update ini

### **5. Duplicate Prevention**
- Validasi: Resi tidak boleh di-pack 2x
- **JANGAN** hapus validasi ini

### **6. Status Pesanan Check**
- Validasi: Status tidak boleh COMPLETED/CANCELED
- **JANGAN** hapus validasi ini

### **7. Picking Dependency**
- Validasi: Resi harus sudah di-pick dulu
- **JANGAN** hapus validasi ini

---

## 🔧 File yang Terkait

### **Direct Dependencies**
1. `application/views/packer/scan_packer.php` - View & JavaScript
2. `application/controllers/Packer.php` - Controller
3. `application/models/Packer_fcd.php` - Model
4. `application/config/routes.php` - Routes

### **Indirect Dependencies**
1. `application/models/Kpi_fcd.php` - KPI functions
2. `application/core/MY_Controller.php` - Base controller
3. `application/helpers/menu_helper.php` - Menu helper (tidak langsung)

---

## 📝 Catatan Penting

1. **Tombol Submit hanya muncul setelah resi di-scan** (ada data di tabel)
2. **Status performa otomatis dari session** (tidak perlu input manual)
3. **Setelah submit sukses, form di-reset** untuk scan berikutnya
4. **KPI tracking otomatis** setiap submit packing
5. **Validasi ketat** untuk mencegah duplikasi dan error

---

## ❓ Pertanyaan Sebelum Perubahan

Sebelum melakukan perubahan, pastikan jawaban untuk:

1. **Apa yang ingin diubah?**
   - Fungsi tombol Submit?
   - Validasi?
   - UI/UX?
   - Flow?

2. **Apakah perubahan mempengaruhi:**
   - KPI tracking?
   - Status performa?
   - Validasi database?
   - Flow picking → packing?

3. **Apakah ada requirement baru?**
   - Fitur baru?
   - Validasi baru?
   - Business rule baru?

---

**Dokumen ini dibuat untuk memastikan tidak ada yang terlewat saat melakukan perubahan.**


# Analisis Program IRESIS (BEVERRA - Manajemen Resi)

## 📋 Ringkasan Eksekutif

**IRESIS** adalah sistem manajemen warehouse/fulfillment center berbasis **CodeIgniter 3** yang digunakan untuk mengelola proses penerimaan, picking, packing, dan pengiriman resi/order. Sistem ini dirancang khusus untuk operasional gudang dengan fitur tracking KPI dan monitoring performa karyawan.

---

## 🏗️ Arsitektur Sistem

### **Framework & Teknologi**
- **Framework**: CodeIgniter 3 (PHP)
- **Database**: MySQL (database: `iresis-dev`)
- **PHP Version**: 7.4.33
- **Dependencies**:
  - Guzzle HTTP Client (v7.9) - untuk API calls
  - PhpSpreadsheet (v1.12) - untuk export Excel
  - DomPDF - untuk generate PDF

### **Struktur Direktori**
```
iresis-dev/
├── application/          # Kode aplikasi utama
│   ├── config/          # Konfigurasi (database, routes, dll)
│   ├── controllers/     # Controller (logika bisnis)
│   ├── models/          # Model (akses database)
│   ├── views/           # View (tampilan HTML)
│   ├── libraries/       # Library custom (PDF generator, dll)
│   └── helpers/         # Helper functions
├── system/              # Core CodeIgniter
├── assets/              # Static files (CSS, JS, images, audio)
├── vendor/              # Composer dependencies
└── index.php            # Entry point aplikasi
```

---

## 🔑 Fitur Utama

### 1. **Manajemen Receipt (Resi)**
- **Scan Receipt**: Input resi baru dengan marketplace dan kurir
- **Detail Receipt**: Lihat detail resi dan item
- **List Receipt**: Daftar semua resi dengan filter
- **Reprint Receipt**: Cetak ulang resi
- **Upload Receipt**: Upload resi via file

**Controller**: `Receipt.php`  
**Model**: `Receipt_fcd.php`

### 2. **Proses Picking**
- **Scan Picker**: Scan resi untuk proses picking
- **Master Picker**: Manajemen data picker
- **Pending Picker**: Resi yang pending
- **Kurangan Picker**: Tracking item yang kurang
- **Search Picker**: Pencarian resi picker
- **Update Picker**: Update data picking

**Controller**: `Picker.php`  
**Model**: `Picking_fcd.php`

**Fitur Khusus**:
- Tracking status performa picker (NORMAL, FAST, dll)
- Logging performa harian untuk KPI
- Audio alerts untuk notifikasi

### 3. **Proses Packing**
- **Scan Packer**: Scan resi untuk packing
- **Save Packer**: Simpan data packing
- **Masalah Picker**: Report masalah dari picker
- **Search Packer**: Pencarian data packing

**Controller**: `Packer.php`  
**Model**: `Packer_fcd.php`

**Fitur Khusus**:
- Tracking status performa packer (GTL, NDD, 1_SKU, dll)
- Validasi item sebelum packing
- Problem type tracking

### 4. **Handover (Serah Terima)**
- Scan handover
- Print handover
- Search handover

**Controller**: `Handover.php`

### 5. **Retur Management**
- **Scan Retur**: Input retur masuk
- **Save Retur**: Simpan data retur
- **Buka Retur**: Proses buka retur
- **Laporan Retur**: Laporan retur
- **Complain**: Manajemen komplain retur

**Controller**: `Retur.php`  
**Model**: `Retur_fcd.php`

### 6. **KPI & Performance Tracking**
- **Dashboard KPI**: Dashboard performa harian
- **KPI Reports**: Laporan KPI picker dan packer
- **Target KPI**: Setting target harian
- **Status Performa**: Master status performa

**Controller**: `Kpi_reports.php`, `Target_kpi.php`, `Status_performa.php`  
**Model**: `Kpi_fcd.php`, `Status_performa_fcd.php`

**Fitur**:
- Tracking performa berdasarkan status (NORMAL, FAST, GTL, NDD, dll)
- Target harian per status
- Export Excel untuk laporan
- Dashboard real-time

### 7. **Reporting System**
- **Receipt Reports**: Laporan resi (in process, shipped, daily, dll)
- **Production Team Report**: Laporan tim produksi
- **Shipping Report**: Laporan pengiriman
- **Retur Report**: Laporan retur
- **Export Excel**: Semua laporan bisa di-export ke Excel

**Controller**: `Report.php`, `Report_production_team.php`

### 8. **User & Access Management**
- **User Management**: CRUD user
- **Menu Management**: Manajemen menu
- **Access Control**: Kontrol akses per role
- **Role Management**: Manajemen role

**Controller**: `User.php`, `Menu.php`, `Access.php`  
**Model**: `User_fcd.php`, `Menu_fcd.php`, `Access_fcd.php`

### 9. **Master Data**
- **Marketplace**: Master marketplace (Shopee, Lazada, dll)
- **Courier**: Master kurir (JNE, JNT, Ninja, dll)
- **Employee**: Master karyawan
- **SKU**: Master SKU
- **Location**: Master lokasi/rak

**Controller**: `Marketplace.php`, `Courrier.php`, `Employee.php`, `Sku.php`, `Location.php`

### 10. **Customer Service (CS)**
- **Laporan Kurangan Picker**: Laporan item kurang
- **Retur Complain**: Manajemen komplain
- **Masalah Picker**: Tracking masalah picker

**Controller**: `Cs.php`

---

## 🔐 Sistem Autentikasi & Keamanan

### **Login System**
- Username & password (MD5 hash)
- Pilih nama komputer (PK) saat login
- Pilih status performa (untuk tracking KPI)
- Session management
- Menu access control berdasarkan role

**Controller**: `Login.php`

### **Session Management**
- Session name: `siresi_session`
- Expiration: 86400 detik (24 jam)
- Session data:
  - User info
  - Menu tree
  - Nama PK (komputer)
  - Status performa

---

## 📊 Database Schema (Key Tables)

### **Core Tables**
1. **tblprintresi** - Data resi/order
2. **tbldetailprintresi** - Detail item per resi
3. **tblpicking** - Data picking
4. **tblpacker** - Data packing
5. **tblhandover** - Data handover
6. **tblretur** - Data retur

### **KPI Tables**
1. **tblmasterstatusperforma** - Master status performa
2. **tblstatusperforma** - Log status performa harian
3. **tblkpi** - Data KPI harian

### **User & Access Tables**
1. **tbluser** - Data user
2. **tblmenu** - Master menu
3. **tblakses** - Akses per role

### **Master Data Tables**
1. **tblmarketplace** - Master marketplace
2. **tblkurir** - Master kurir
3. **tblemployee** - Master karyawan
4. **tblsku** - Master SKU

---

## 🎯 Alur Kerja Utama

### **Workflow Receipt → Picking → Packing → Handover**

```
1. RECEIPT (Input Resi)
   ↓
2. PICKING (Picker scan resi, ambil item)
   ↓
3. PACKING (Packer scan resi, packing item)
   ↓
4. HANDOVER (Serah terima ke kurir)
   ↓
5. SHIPPED (Status: Terkirim)
```

### **Workflow Retur**

```
1. SCAN RETUR (Input retur masuk)
   ↓
2. TERIMA RETUR (Terima retur)
   ↓
3. BUKA RETUR (Buka retur untuk proses)
   ↓
4. COMPLAIN (Jika ada komplain)
```

---

## 🎨 Frontend & UI

### **Framework & Libraries**
- **Theme**: Custom theme (theme-black.css)
- **jQuery**: Untuk AJAX dan DOM manipulation
- **DataTables**: Untuk tabel interaktif
- **NVD3**: Untuk chart/grafik
- **Summernote**: Rich text editor
- **Noty**: Notifikasi
- **DateRangePicker**: Date picker

### **Audio Alerts**
Sistem menggunakan audio alerts untuk notifikasi:
- `alert.mp3` - Alert umum
- `error.mp3` - Error
- `jne.mp3`, `jnt.mp3`, `ninja.mp3` - Notifikasi kurir
- `shopee.mp3`, `lazada.mp3` - Notifikasi marketplace

---

## 📝 Konfigurasi Penting

### **Database Config** (`application/config/database.php`)
```php
hostname: 127.0.0.1
username: root
password: (kosong)
database: iresis-dev
```

### **Base URL** (`application/config/config.php`)
- Auto-detect dari `$_SERVER`
- Timezone: Asia/Jakarta

### **Routes** (`application/config/routes.php`)
- Default controller: `welcome`
- Custom routes untuk semua endpoint
- RESTful API pattern

---

## 🔧 Custom Components

### **MY_Controller** (`application/core/MY_Controller.php`)
Base controller dengan:
- Session validation
- Helper methods untuk AJAX response
- Message handling

### **Libraries**
- **Pdfgenerator**: Generate PDF menggunakan DomPDF
- **Bgprocess**: Background process handler

### **Helpers**
- **menu_helper**: Helper untuk build menu tree

---

## 📈 Fitur KPI & Performance

### **Status Performa Picker**
- NORMAL_PICKER
- FAST_PICKER
- (dapat dikonfigurasi)

### **Status Performa Packer**
- GTL (Good To Live)
- NDD (Next Day Delivery)
- 1_SKU (Single SKU)
- MOONKLAZ
- PAYUNG
- QTY_BANYAK
- NINJA
- NORMAL

### **KPI Tracking**
- Tracking per user per hari
- Tracking per status performa
- Target harian per status
- Dashboard real-time
- Export Excel

---

## 🚀 Cara Menjalankan

### **Requirements**
- PHP 7.4.33
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Composer

### **Setup**
1. Clone/Download project
2. Install dependencies: `composer install`
3. Setup database: Import SQL files
4. Konfigurasi database di `application/config/database.php`
5. Set permissions untuk `application/logs/`
6. Akses via browser

### **Scripts**
- `beverra-siresi-windows.bat` - Setup untuk Windows
- `beverra-siresi-linux.sh` - Setup untuk Linux

---

## 📦 File SQL Penting

1. **production_setup.sql** - Setup database production
2. **create_kpi_tables_v2.sql** - Setup tabel KPI
3. **final_kpi_parent_menu.sql** - Setup menu KPI
4. **fix_kpi_menu_access.sql** - Fix akses menu KPI
5. **patch.sql** - Patch database

---

## 🔍 Poin Penting untuk Pengembangan

### **1. Security**
- Password menggunakan MD5 (disarankan upgrade ke bcrypt)
- CSRF protection: DISABLED (disarankan enable)
- XSS filtering: DISABLED (disarankan enable)

### **2. Performance**
- Query optimization mungkin diperlukan
- Caching belum diimplementasi
- Static cache untuk status performa sudah ada di Picker controller

### **3. Code Quality**
- Menggunakan CodeIgniter 3 (legacy)
- Bisa dipertimbangkan upgrade ke CodeIgniter 4
- Beberapa hardcoded values

### **4. Testing**
- Tidak ada unit test
- Manual testing

### **5. Documentation**
- README.md kosong
- Tidak ada API documentation
- Code comments minimal

---

## 🎯 Rekomendasi Pengembangan

### **Short Term**
1. ✅ Enable CSRF protection
2. ✅ Upgrade password hashing ke bcrypt
3. ✅ Add input validation
4. ✅ Improve error handling
5. ✅ Add logging untuk audit trail

### **Medium Term**
1. ✅ API documentation
2. ✅ Unit testing
3. ✅ Code refactoring
4. ✅ Performance optimization
5. ✅ Mobile responsive improvements

### **Long Term**
1. ✅ Migrate ke CodeIgniter 4
2. ✅ Implement caching (Redis/Memcached)
3. ✅ Real-time dashboard dengan WebSocket
4. ✅ Mobile app
5. ✅ Microservices architecture

---

## 📞 Kontak & Support

Untuk pertanyaan atau pengembangan lebih lanjut, silakan analisis kode di:
- Controllers: `application/controllers/`
- Models: `application/models/`
- Views: `application/views/`
- Config: `application/config/`

---

**Dokumen ini dibuat untuk membantu memahami struktur dan fungsionalitas sistem IRESIS.**
**Terakhir diupdate: 2025-01-27**


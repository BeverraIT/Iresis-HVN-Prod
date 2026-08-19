# Struktur Database & Model IRESIS

## 📊 Database Schema Overview

### **Core Tables**

#### 1. **tblprintresi** - Master Receipt/Resi
```sql
- id_printresi (PK)
- noresi (UNIQUE)
- id_marketplace (FK → tblmarketplace)
- id_kurir (FK → tblkurir)
- nomorpicklist
- tanggal_printresi
- status_pesanan (NEW, PICKED, PACKED, HANDOVER, SHIPPED, COMPLETED, CANCELED)
- created_by (FK → tbluser)
- created_at
```

#### 2. **tbldetailprintresi** - Detail Item per Resi
```sql
- id_detail (PK)
- id_resi (FK → tblprintresi)
- sku
- jumlah
- no_rak
```

#### 3. **tblresiambilbarang** (tblpicking) - Data Picking
```sql
- id_resiambilbarang (PK)
- id_resi (FK → tblprintresi)
- tanggal_resiambilbarang
- admin_pegawai (FK → tbluser)
- yangambil_pegawai (FK → tblpegawai)
- nama_komputer
- pending
- status_performa_id (FK → tblmasterstatusperforma)
```

#### 4. **tblpacker** - Data Packing
```sql
- id_packer (PK)
- id_resi (FK → tblprintresi)
- tanggal_packer
- admin_pegawai (FK → tbluser)
- nama_komputer
- status_performa_id (FK → tblmasterstatusperforma)
```

#### 5. **tblhandover** - Data Handover
```sql
- id_handover (PK)
- id_resi (FK → tblprintresi)
- tanggal_handover
- admin_pegawai (FK → tbluser)
```

#### 6. **tblretur** - Data Retur
```sql
- id_retur (PK)
- noresi_retur
- noresi_asal
- tanggal_terima
- tanggal_buka
- status (TERIMA, BUKA, COMPLAIN)
```

---

### **KPI & Performance Tables**

#### 7. **tblmasterstatusperforma** - Master Status Performa
```sql
- id_statusperforma (PK)
- kode_status (UNIQUE) - NORMAL, FAST, GTL, NDD, dll
- role - PICKER atau PACKER
- status_name
- deskripsi
- target_harian
- isactive
- createdby, created
- updatedby, updated
```

**Status Performa Picker:**
- `NORMAL_PICKER` - Picking normal
- `FAST_PICKER` - Picking cepat

**Status Performa Packer:**
- `GTL` - Good To Live
- `NDD` - Next Day Delivery
- `1_SKU` - Single SKU Order
- `MOONKLAZ` - Moonklaz Order
- `PAYUNG` - Payung Order
- `QTY_BANYAK` - Large Quantity Order
- `NINJA` - Ninja Express Order
- `NORMAL` - Normal Order

#### 8. **tblstatusperforma** - Log Status Performa Harian
```sql
- id_log (PK)
- id_user (FK → tbluser)
- id_statusperforma (FK → tblmasterstatusperforma)
- tanggal (DATE)
- jam_login (TIME)
- isactive
- createdby, created
- updatedby, updated
- UNIQUE KEY (id_user, tanggal)
```

#### 9. **tblkpi** - Data KPI Harian
```sql
- id_kpi (PK)
- id_user (FK → tbluser)
- id_statusperforma (FK → tblmasterstatusperforma)
- tanggal (DATE)
- total_scan
- target_harian
- achievement
- createdby, created
- updatedby, updated
```

#### 10. **tbltargetkpi** - Target KPI
```sql
- id_target (PK)
- id_user (FK → tbluser)
- id_statusperforma (FK → tblmasterstatusperforma)
- tanggal (DATE)
- target_harian
- isactive
- createdby, created
- updatedby, updated
```

---

### **User & Access Tables**

#### 11. **tbluser** - Master User
```sql
- id_user (PK)
- username (UNIQUE)
- password (MD5)
- name
- hakakses (FK → tblrole)
- akses
- nama_komputer
- last_login
- bypass (boolean)
- isactive
```

#### 12. **tblmenu** - Master Menu
```sql
- id_menu (PK)
- nama_menu
- url
- icon
- parent_id (FK → tblmenu)
- urutan
- isactive
```

#### 13. **tblakses** - Access Control
```sql
- id_akses (PK)
- id_role (FK → tblrole)
- id_menu (FK → tblmenu)
- isactive
```

#### 14. **tblrole** - Master Role
```sql
- id_role (PK)
- nama_role
- isactive
```

---

### **Master Data Tables**

#### 15. **tblmarketplace** - Master Marketplace
```sql
- id_marketplace (PK)
- nama_marketplace
- isactive
```

**Contoh:**
- Shopee
- Lazada
- Tokopedia
- dll

#### 16. **tblkurir** - Master Kurir
```sql
- id_kurir (PK)
- nama_kurir
- isactive
```

**Contoh:**
- JNE
- JNT
- Ninja Express
- SiCepat
- dll

#### 17. **tblpegawai** - Master Karyawan
```sql
- id_pegawai (PK)
- kode_pegawai
- nama_pegawai
- status_aktif (AKTIF/NONAKTIF)
```

#### 18. **tblnamaambilbarang** - Master Picker
```sql
- id_pegawai (FK → tblpegawai)
- status_aktif
```

#### 19. **tblsku** - Master SKU
```sql
- id_sku (PK)
- sku
- nama_sku
- link_foto
- isactive
```

#### 20. **tbllokasi** - Master Lokasi/Rak
```sql
- id_lokasi (PK)
- no_rak
- isactive
```

---

## 🔗 Relationship Diagram

```
tbluser
  ├─→ tblprintresi (created_by)
  ├─→ tblresiambilbarang (admin_pegawai)
  ├─→ tblpacker (admin_pegawai)
  ├─→ tblhandover (admin_pegawai)
  ├─→ tblstatusperforma (id_user)
  ├─→ tblkpi (id_user)
  └─→ tbltargetkpi (id_user)

tblprintresi
  ├─→ tbldetailprintresi (id_resi)
  ├─→ tblresiambilbarang (id_resi)
  ├─→ tblpacker (id_resi)
  ├─→ tblhandover (id_resi)
  └─→ tblmarketplace (id_marketplace)
  └─→ tblkurir (id_kurir)

tblmasterstatusperforma
  ├─→ tblstatusperforma (id_statusperforma)
  ├─→ tblkpi (id_statusperforma)
  ├─→ tbltargetkpi (id_statusperforma)
  ├─→ tblresiambilbarang (status_performa_id)
  └─→ tblpacker (status_performa_id)

tblpegawai
  ├─→ tblnamaambilbarang (id_pegawai)
  └─→ tblresiambilbarang (yangambil_pegawai)

tblmenu
  ├─→ tblmenu (parent_id) - Self reference
  └─→ tblakses (id_menu)

tblrole
  └─→ tblakses (id_role)
  └─→ tbluser (hakakses)
```

---

## 📁 Model Structure

### **Core Models**

#### **Receipt_fcd.php**
```php
Methods:
- get_data($data) - Get list receipt dengan filter
- get_total_data($data) - Count total receipt
- get_detail_receipt($noresi) - Get detail receipt
- get_detail($noresi) - Get receipt info
- get_detail_items($noresi) - Get items per receipt
- save($receipt, $user_id) - Save new receipt
- get_header_daily_report($start, $end) - Daily report header
- get_receipt_for_packer($data, $noresi) - Receipt data for packer
```

#### **Picking_fcd.php**
```php
Methods:
- get_picker($status) - Get list picker
- save($picking, $user, $mode) - Save picking data
- get_total_scan_user($user_id) - Get total scan per user
- get_picker_detail($noresi) - Get picker detail
- get_picker_by_date($date, $user_id) - Get picker by date
```

#### **Packer_fcd.php**
```php
Methods:
- save($packer, $user) - Save packing data
- get_total_scan_user($user_id) - Get total scan per user
- get_picker_detail_for_packer($noresi) - Get picker info for packer
- get_packer_by_date($date, $user_id) - Get packer by date
```

#### **Kpi_fcd.php**
```php
Methods:
- get_status_performa($id) - Get status performa
- get_status_performa_by_kategori($kategori) - Get by role
- get_status_id_by_name($status_name) - Get ID by name
- log_status_performa($user_id, $status_id) - Log status
- get_user_status_performa($user_id) - Get user status
- get_kpi_data($filters) - Get KPI data
- save_target_kpi($target, $user_id) - Save target
```

---

## 🔍 Key Queries

### **Get Receipt dengan Join**
```sql
SELECT 
    t.noresi, 
    t.tanggal_printresi, 
    t3.nama_kurir, 
    t2.nama_marketplace, 
    t.nomorpicklist, 
    t.status_pesanan
FROM tblprintresi t
LEFT JOIN tblmarketplace t2 ON t.id_marketplace = t2.id_marketplace
LEFT JOIN tblkurir t3 ON t.id_kurir = t3.id_kurir
ORDER BY t.created_at DESC
```

### **Get Picking dengan Status Performa**
```sql
SELECT 
    rab.*,
    sp.status_name,
    sp.kode_status
FROM tblresiambilbarang rab
LEFT JOIN tblmasterstatusperforma sp ON rab.status_performa_id = sp.id_statusperforma
WHERE rab.id_resi = ?
```

### **Get KPI Data**
```sql
SELECT 
    k.*,
    u.username,
    sp.status_name,
    sp.kode_status
FROM tblkpi k
INNER JOIN tbluser u ON k.id_user = u.id_user
INNER JOIN tblmasterstatusperforma sp ON k.id_statusperforma = sp.id_statusperforma
WHERE k.tanggal BETWEEN ? AND ?
```

---

## 📝 Indexes (Recommended)

```sql
-- Performance indexes
CREATE INDEX idx_receipt_noresi ON tblprintresi(noresi);
CREATE INDEX idx_receipt_status ON tblprintresi(status_pesanan);
CREATE INDEX idx_receipt_created ON tblprintresi(created_at);

CREATE INDEX idx_picking_resi ON tblresiambilbarang(id_resi);
CREATE INDEX idx_picking_user ON tblresiambilbarang(admin_pegawai);
CREATE INDEX idx_picking_date ON tblresiambilbarang(tanggal_resiambilbarang);

CREATE INDEX idx_packer_resi ON tblpacker(id_resi);
CREATE INDEX idx_packer_user ON tblpacker(admin_pegawai);

CREATE INDEX idx_kpi_user_date ON tblkpi(id_user, tanggal);
CREATE INDEX idx_kpi_status ON tblkpi(id_statusperforma);

CREATE INDEX idx_status_user_date ON tblstatusperforma(id_user, tanggal);
```

---

## 🔄 Transaction Flow

### **Picking Transaction**
```php
1. Start Transaction
2. Check receipt exists
3. Check receipt status
4. Check duplicate picking
5. Insert picking record
6. Update receipt status
7. Commit/Rollback
```

### **Packing Transaction**
```php
1. Start Transaction
2. Check receipt exists
3. Check receipt is picked
4. Check duplicate packing
5. Insert packing record
6. Update receipt status
7. Commit/Rollback
```

---

## 📊 Data Flow

```
INPUT → VALIDATION → PROCESS → DATABASE → RESPONSE
  │         │           │          │          │
  │         │           │          │          └─→ JSON/AJAX
  │         │           │          └─→ Transaction
  │         │           └─→ Business Logic
  │         └─→ Input Sanitization
  └─→ POST/GET Data
```

---

**Dokumen ini menjelaskan struktur database dan model yang digunakan dalam sistem IRESIS.**


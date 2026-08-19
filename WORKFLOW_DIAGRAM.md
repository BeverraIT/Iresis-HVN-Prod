# Diagram Alur Kerja Sistem IRESIS

## 🔄 Workflow Utama: Receipt → Picking → Packing → Handover

```
┌─────────────────────────────────────────────────────────────────┐
│                    WORKFLOW UTAMA PRODUKSI                      │
└─────────────────────────────────────────────────────────────────┘

1. RECEIPT (Input Resi)
   │
   ├─→ Scan Receipt
   │   ├─→ Input: No Resi, Marketplace, Kurir, No Picklist
   │   ├─→ Validasi: Cek duplikasi resi
   │   └─→ Save: tblprintresi
   │
   ├─→ Detail Receipt
   │   └─→ View: Detail resi + items
   │
   └─→ List Receipt
       └─→ View: Daftar semua resi (filter, search, pagination)

2. PICKING (Ambil Item dari Gudang)
   │
   ├─→ Scan Picker
   │   ├─→ Input: No Resi, Picker ID, Status Performa
   │   ├─→ Validasi: Resi harus sudah di-receipt
   │   ├─→ Update: Status resi → "Picked"
   │   └─→ Save: tblpicking + Log KPI
   │
   ├─→ Pending Picker
   │   └─→ Resi yang pending (belum selesai picking)
   │
   ├─→ Kurangan Picker
   │   └─→ Report item yang kurang saat picking
   │
   └─→ Master Picker
       └─→ CRUD data picker

3. PACKING (Packing Item)
   │
   ├─→ Scan Packer
   │   ├─→ Input: No Resi
   │   ├─→ Validasi: Resi harus sudah di-pick
   │   ├─→ View: Detail items, Picker info
   │   └─→ Save: tblpacker + Log KPI
   │
   ├─→ Masalah Picker
   │   └─→ Report masalah dari picker
   │
   └─→ Search Packer
       └─→ Cari data packing

4. HANDOVER (Serah Terima ke Kurir)
   │
   ├─→ Scan Handover
   │   ├─→ Input: No Resi
   │   ├─→ Validasi: Resi harus sudah di-pack
   │   └─→ Save: tblhandover
   │
   └─→ Print Handover
       └─→ Cetak dokumen handover

5. SHIPPED (Status: Terkirim)
   │
   └─→ Update: Status resi → "Shipped"
```

---

## 🔄 Workflow Retur

```
┌─────────────────────────────────────────────────────────────────┐
│                        WORKFLOW RETUR                            │
└─────────────────────────────────────────────────────────────────┘

1. SCAN RETUR (Input Retur Masuk)
   │
   ├─→ Input: No Resi Retur
   ├─→ Validasi: Cek resi asal
   └─→ Save: tblretur (status: "Terima")

2. TERIMA RETUR
   │
   ├─→ Update: Status retur → "Diterima"
   └─→ Log: Tanggal terima

3. BUKA RETUR
   │
   ├─→ Update: Status retur → "Dibuka"
   └─→ Log: Tanggal buka

4. COMPLAIN (Jika Ada Masalah)
   │
   ├─→ Input: Type complain (Refund/Replacement)
   ├─→ Save: Data complain
   └─→ Update: Status complain

5. LAPORAN RETUR
   │
   ├─→ Terima Retur Report
   └─→ Buka Retur Report
```

---

## 🔄 Workflow KPI & Performance Tracking

```
┌─────────────────────────────────────────────────────────────────┐
│                    WORKFLOW KPI TRACKING                        │
└─────────────────────────────────────────────────────────────────┘

1. LOGIN
   │
   ├─→ User Login
   ├─→ Pilih: Nama PK (Komputer)
   └─→ Pilih: Status Performa
       │
       └─→ Save: tblstatusperforma (log status harian)

2. PICKING (Dengan Status Performa)
   │
   ├─→ Scan Picker
   ├─→ Pilih: Status Performa (NORMAL/FAST)
   └─→ Save: 
       ├─→ tblpicking (data picking)
       └─→ Tracking KPI (jika diperlukan)

3. PACKING (Dengan Status Performa)
   │
   ├─→ Scan Packer
   ├─→ Auto-detect: Status Performa dari resi
   └─→ Save:
       ├─→ tblpacker (data packing)
       └─→ Tracking KPI

4. KPI DASHBOARD
   │
   ├─→ Dashboard KPI (Overall)
   ├─→ Dashboard Picker
   ├─→ Dashboard Packer
   └─→ Real-time: Total scan, Target, Achievement

5. KPI REPORTS
   │
   ├─→ Filter: Tanggal, User, Status
   ├─→ View: Data KPI harian
   └─→ Export: Excel

6. TARGET KPI
   │
   ├─→ Set: Target harian per status
   ├─→ Copy: Target dari periode sebelumnya
   └─→ Update: Target per user/status
```

---

## 🔄 Workflow User & Access Management

```
┌─────────────────────────────────────────────────────────────────┐
│                 WORKFLOW USER MANAGEMENT                        │
└─────────────────────────────────────────────────────────────────┘

1. USER MANAGEMENT
   │
   ├─→ Add User
   │   ├─→ Input: Username, Password, Role, Akses
   │   └─→ Save: tbluser
   │
   ├─→ Edit User
   │   └─→ Update: Data user
   │
   ├─→ Delete User
   │   └─→ Soft delete (isactive = 0)
   │
   └─→ Generate Password
       └─→ Generate random password

2. MENU MANAGEMENT
   │
   ├─→ Add Menu
   │   ├─→ Input: Nama, URL, Parent, Icon
   │   └─→ Save: tblmenu
   │
   ├─→ Edit Menu
   │   └─→ Update: Data menu
   │
   └─→ Delete Menu
       └─→ Soft delete

3. ACCESS CONTROL
   │
   ├─→ Edit Access
   │   ├─→ Pilih: Role
   │   ├─→ Pilih: Menu yang diizinkan
   │   └─→ Save: tblakses
   │
   └─→ Validation
       └─→ Cek akses saat akses menu
```

---

## 🔄 Workflow Reporting

```
┌─────────────────────────────────────────────────────────────────┐
│                      WORKFLOW REPORTING                          │
└─────────────────────────────────────────────────────────────────┘

1. RECEIPT REPORTS
   │
   ├─→ Receipt In Process
   │   ├─→ Tab 0: Receipt baru
   │   ├─→ Tab 1: Receipt di-pick
   │   └─→ Tab 2: Receipt di-pack
   │
   ├─→ Daily Receipt Report
   │   └─→ Laporan harian resi
   │
   ├─→ Per Day Receipt Report
   │   └─→ Laporan per hari
   │
   ├─→ Receipt Report
   │   ├─→ Tab 0: Receipt summary
   │   └─→ Tab 1: Receipt detail
   │
   └─→ Shipped Receipt Report
       └─→ Laporan resi terkirim

2. PRODUCTION TEAM REPORT
   │
   ├─→ Tab 0: Summary tim
   └─→ Tab 1: Detail per user

3. SHIPPING REPORT
   │
   └─→ Laporan pengiriman per kurir

4. RETUR REPORT
   │
   ├─→ Terima Retur Report
   └─→ Buka Retur Report

5. EXPORT
   │
   └─→ Export ke Excel (PhpSpreadsheet)
```

---

## 🔄 Workflow CS (Customer Service)

```
┌─────────────────────────────────────────────────────────────────┐
│                    WORKFLOW CUSTOMER SERVICE                     │
└─────────────────────────────────────────────────────────────────┘

1. LAPORAN KURANGAN PICKER
   │
   ├─→ View: Daftar kurangan picker
   ├─→ Filter: Tanggal, Picker
   └─→ Export: Excel

2. RETUR COMPLAIN
   │
   ├─→ View: Daftar complain
   ├─→ Action: Refund/Replacement
   └─→ Update: Status complain

3. MASALAH PICKER
   │
   ├─→ View: Daftar masalah
   ├─→ Detail: Detail masalah
   └─→ Submit: Submit masalah
```

---

## 📊 Status Flow Diagram

```
RECEIPT STATUS FLOW:
┌─────────┐
│  NEW    │ → Receipt baru diinput
└────┬────┘
     │
     ▼
┌─────────┐
│ PICKED  │ → Sudah di-pick oleh picker
└────┬────┘
     │
     ▼
┌─────────┐
│ PACKED  │ → Sudah di-pack oleh packer
└────┬────┘
     │
     ▼
┌─────────┐
│ HANDOVER│ → Sudah di-handover ke kurir
└────┬────┘
     │
     ▼
┌─────────┐
│ SHIPPED │ → Sudah terkirim
└─────────┘

RETUR STATUS FLOW:
┌─────────┐
│ TERIMA  │ → Retur diterima
└────┬────┘
     │
     ▼
┌─────────┐
│  BUKA   │ → Retur dibuka untuk proses
└────┬────┘
     │
     ▼
┌─────────┐
│ COMPLAIN│ → Ada complain (optional)
└─────────┘
```

---

## 🔐 Authentication Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    AUTHENTICATION FLOW                           │
└─────────────────────────────────────────────────────────────────┘

1. USER ACCESS PAGE
   │
   ├─→ Check: Session user exists?
   │   ├─→ YES → Continue
   │   └─→ NO → Redirect to Login
   │
   └─→ MY_Controller::__construct()
       └─→ Validate session

2. LOGIN PROCESS
   │
   ├─→ Input: Username, Password
   ├─→ Input: Nama PK (Komputer)
   ├─→ Input: Status Performa
   │
   ├─→ Validate: Username & Password (MD5)
   │   ├─→ Valid → Continue
   │   └─→ Invalid → Error message
   │
   ├─→ Get: Menu access berdasarkan role
   │
   ├─→ Build: Menu tree
   │
   ├─→ Save: Status performa ke database
   │
   └─→ Create: Session
       ├─→ User data
       ├─→ Menu tree
       ├─→ Nama PK
       └─→ Status Performa

3. MENU ACCESS VALIDATION
   │
   ├─→ Check: User role
   ├─→ Check: Menu access (tblakses)
   └─→ Allow/Deny access
```

---

## 📱 API Endpoints (Routes)

### Receipt
- `POST /receipt/save-receipt` - Simpan resi baru
- `POST /receipt/detail-receipt` - Detail resi
- `GET /receipt/get-list-receipt-data` - List resi (DataTables)

### Picker
- `POST /picker/save-scan-picker` - Simpan scan picker
- `GET /picker/get-search-picker-data` - Data search picker

### Packer
- `POST /packer/save-packer` - Simpan packing
- `GET /packer/get-scan-packer-data/:noresi` - Data scan packer

### Handover
- `POST /handover/save-handover` - Simpan handover

### Retur
- `POST /retur/save-retur` - Simpan retur
- `POST /retur/save-buka-retur` - Buka retur

### Report
- `GET /report/get-receipt-in-process-data-tab0` - Data receipt in process
- `GET /report/export-to-excel-*` - Export Excel

### KPI
- `GET /kpi/dashboard` - Dashboard KPI
- `GET /kpi_reports/get-kpi-data` - Data KPI
- `POST /target_kpi/save-targets` - Simpan target KPI

---

**Diagram ini membantu memahami alur kerja sistem IRESIS secara visual.**


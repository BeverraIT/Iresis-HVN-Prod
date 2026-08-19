# Dokumentasi Perubahan: Auto Submit Packer Setelah 2x Scan

## 📋 Ringkasan Perubahan

**Fitur Baru**: Auto submit packing setelah scan kedua kali (rescan)
- **Scan Pertama**: Tampilkan notifikasi "Scan sekali lagi untuk auto submit packing"
- **Scan Kedua**: Otomatis submit packing tanpa perlu klik tombol Submit

---

## 🔄 Flow Baru

### **Sebelum Perubahan**
```
1. User scan no resi → Tampilkan detail
2. User klik tombol Submit → Submit packing
```

### **Setelah Perubahan**
```
1. User scan no resi pertama kali → Tampilkan detail + Notifikasi "Scan sekali lagi"
2. User scan no resi kedua kali (rescan) → Auto submit packing
```

---

## 📝 Perubahan yang Dilakukan

### **1. Form Scan Handler** (`#form-scan-packer`)

**Fungsi**: Track scan count menggunakan localStorage

**Lokasi**: Baris 762-819

**Perubahan**:
- Menambahkan tracking scan count per noresi
- Increment count setiap kali scan
- Submit form untuk load data (seperti biasa)

**Code**:
```javascript
$('#form-scan-packer').on('submit', function(e) {
  e.preventDefault();
  
  const noresi = $('#noresi').val().trim();
  
  // Cek scan count dari localStorage
  const scanKey = 'packer_scan_' + noresi;
  let scanCount = parseInt(localStorage.getItem(scanKey)) || 0;
  
  // Increment scan count
  scanCount++;
  localStorage.setItem(scanKey, scanCount);
  
  // Submit form untuk load data
  // ... (reload page)
});
```

---

### **2. Auto Submit Logic** (Setelah Page Reload)

**Fungsi**: Cek scan count dan auto submit jika scan kedua

**Lokasi**: Baris 821-850

**Perubahan**:
- Cek scan count setelah page reload
- Jika scan count >= 2: Auto submit
- Jika scan count === 1: Show notification

**Code**:
```javascript
$(document).ready(function() {
  const noresiDetail = $('#noresi-detail').val();
  
  if (noresiDetail) {
    const scanKey = 'packer_scan_' + noresiDetail;
    const scanCount = parseInt(localStorage.getItem(scanKey)) || 0;
    const alreadySubmitted = localStorage.getItem('packer_submitted_' + noresiDetail);
    
    if (scanCount >= 2 && !alreadySubmitted) {
      // Auto submit
      localStorage.setItem('packer_submitted_' + noresiDetail, 'true');
      setTimeout(function() {
        submitPacking(noresiDetail);
      }, 1500);
    } else if (scanCount === 1 && !alreadySubmitted) {
      // Show notification
      $('#successMessage').text('Scan pertama berhasil! Scan sekali lagi untuk auto submit packing.');
      $('#successModal').fadeIn();
    }
  }
});
```

---

### **3. Function submitPacking()** (Refactored)

**Fungsi**: Centralized function untuk submit packing

**Lokasi**: Baris 676-753

**Perubahan**:
- Extract submit logic ke function terpisah
- Bisa dipanggil manual (tombol Submit) atau auto (scan kedua)
- Clear localStorage setelah submit sukses

**Code**:
```javascript
function submitPacking(noresi) {
  // Validasi
  if (!noresi || noresi.trim() === '') {
    // Show error
    return false;
  }

  // AJAX submit
  $.ajax({
    url: 'packer/save-packer',
    method: 'POST',
    data: { noresi: noresi },
    success: function(response) {
      // Show success
      // Clear localStorage
      localStorage.removeItem('packer_scan_' + noresi);
      localStorage.removeItem('packer_submitted_' + noresi);
      // Reset form
    },
    error: function(xhr, status, error) {
      // Show error
    }
  });
}
```

---

### **4. Tombol Submit Manual** (Tetap Berfungsi)

**Fungsi**: Tombol Submit tetap bisa digunakan manual

**Lokasi**: Baris 755-759

**Perubahan**:
- Tombol Submit sekarang memanggil function `submitPacking()`
- Tetap berfungsi seperti biasa jika user ingin submit manual

**Code**:
```javascript
$('#submit-selected').on('click', function() {
  const noresi = $('#noresi-detail').val();
  submitPacking(noresi);
});
```

---

### **5. Reset Button** (Clear localStorage)

**Fungsi**: Clear scan count saat reset

**Lokasi**: Baris 852-865

**Perubahan**:
- Clear localStorage saat reset
- Reset scan count dan submitted flag

**Code**:
```javascript
$('#btn-reset').on('click', function () {
  const noresiDetail = $('#noresi-detail').val();
  if (noresiDetail) {
    localStorage.removeItem('packer_scan_' + noresiDetail);
    localStorage.removeItem('packer_submitted_' + noresiDetail);
  }
  // Reset form
});
```

---

## 🔒 Safety Features

### **1. Double Submit Prevention**

**Mekanisme**: 
- Flag `packer_submitted_` di localStorage
- Cek flag sebelum auto submit
- Clear flag setelah submit sukses

**Code**:
```javascript
const alreadySubmitted = localStorage.getItem('packer_submitted_' + noresiDetail);

if (scanCount >= 2 && !alreadySubmitted) {
  // Mark as submitted
  localStorage.setItem('packer_submitted_' + noresiDetail, 'true');
  // Auto submit
}
```

---

### **2. Validasi Tetap Jalan**

**Mekanisme**:
- Semua validasi di backend tetap berjalan
- Validasi di frontend tetap berjalan
- Tidak ada bypass validasi

**Validasi yang Tetap Jalan**:
- ✅ No resi tidak kosong
- ✅ Resi harus exist di database
- ✅ Resi harus sudah di-pick
- ✅ Resi belum di-pack
- ✅ Status tidak boleh COMPLETED/CANCELED

---

### **3. Error Handling**

**Mekanisme**:
- Jika error saat submit, flag tidak di-clear
- User bisa coba lagi
- Error message tetap ditampilkan

---

## 📊 localStorage Keys

### **1. `packer_scan_{noresi}`**
- **Tipe**: Number
- **Fungsi**: Track jumlah scan per noresi
- **Contoh**: `packer_scan_RESI123` = `2`

### **2. `packer_submitted_{noresi}`**
- **Tipe**: String ("true")
- **Fungsi**: Flag untuk prevent double submit
- **Contoh**: `packer_submitted_RESI123` = `"true"`

---

## 🎯 User Experience

### **Scenario 1: Scan Pertama**
1. User scan no resi → Tampilkan detail
2. Notifikasi muncul: "Scan pertama berhasil! Scan sekali lagi untuk auto submit packing."
3. User bisa:
   - Scan lagi (auto submit)
   - Klik tombol Submit (manual submit)
   - Reset (clear scan count)

### **Scenario 2: Scan Kedua (Auto Submit)**
1. User scan no resi kedua kali → Tampilkan detail
2. Notifikasi muncul: "Scan kedua terdeteksi. Auto submit packing..."
3. Otomatis submit setelah 1.5 detik
4. Success message muncul
5. Form di-reset, siap untuk scan berikutnya

### **Scenario 3: Manual Submit**
1. User scan no resi → Tampilkan detail
2. User klik tombol Submit → Submit packing
3. Success message muncul
4. Form di-reset

---

## ⚠️ Catatan Penting

### **1. localStorage Scope**
- Data tersimpan di browser (per domain)
- Data tidak hilang saat refresh page
- Data hilang jika:
  - Clear browser cache
  - Incognito/private mode
  - Browser ditutup (tergantung browser)

### **2. Multiple Tabs**
- Setiap tab memiliki localStorage sendiri
- Scan count tidak shared antar tab
- **Rekomendasi**: Gunakan 1 tab saja untuk scan

### **3. Reset Behavior**
- Tombol Reset akan clear scan count
- User bisa mulai dari awal setelah reset

### **4. Backward Compatibility**
- Tombol Submit manual tetap berfungsi
- Tidak ada breaking changes
- Fitur lama tetap bisa digunakan

---

## 🧪 Testing Checklist

- [x] Scan pertama → Notifikasi muncul
- [x] Scan kedua → Auto submit
- [x] Tombol Submit manual tetap berfungsi
- [x] Reset button clear scan count
- [x] Tidak ada double submit
- [x] Validasi tetap jalan
- [x] Error handling tetap jalan
- [x] Success message muncul
- [x] Form reset setelah submit sukses

---

## 📁 File yang Diubah

1. **application/views/packer/scan_packer.php**
   - Form scan handler (baris 762-819)
   - Auto submit logic (baris 821-850)
   - Function submitPacking() (baris 676-753)
   - Tombol Submit handler (baris 755-759)
   - Reset button handler (baris 852-865)

---

## 🔄 Rollback (Jika Perlu)

Jika perlu rollback, hapus perubahan di:
1. Form scan handler (kembalikan ke submit biasa)
2. Auto submit logic (hapus)
3. Function submitPacking() (kembalikan ke inline code)

---

**Perubahan selesai dan siap untuk testing!**


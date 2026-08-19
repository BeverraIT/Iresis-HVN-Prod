-- =====================================================================
-- Kolom penanda qty yang dikoreksi manual
-- =====================================================================
--
-- Dibutuhkan selama report Jubelio "Daftar Serial Number Terjual" masih
-- melebur qty pesanan Tokopedia menjadi 1.
--
-- Tanpa kolom ini, qty hasil koreksi manual akan dikembalikan ke angka
-- yang salah setiap kali file export diupload ulang, karena proses import
-- menyamakan isi tbldetailprintresi dengan isi file.
--
-- Baris dengan qty_manual = 1 tetap ikut disinkronkan untuk kolom lain
-- (mis. no_rak) dan tetap bisa dihapus kalau hilang dari file. Hanya
-- kolom `jumlah` yang dilindungi.
--
-- Jalankan sekali per database.
-- =====================================================================

ALTER TABLE tbldetailprintresi
  ADD COLUMN qty_manual TINYINT(1) NOT NULL DEFAULT 0
  COMMENT 'Qty dikoreksi manual, jangan ditimpa saat import ulang'
  AFTER jumlah;

-- Verifikasi
SELECT COUNT(*) AS total_baris,
       SUM(qty_manual = 1) AS dikoreksi_manual
FROM   tbldetailprintresi;

-- =====================================================================
-- Setelah report Jubelio diperbaiki
-- =====================================================================
--
-- Koreksi manual tidak lagi diperlukan. Lepaskan penguncian supaya data
-- kembali mengikuti sumbernya, lalu jalankan import ulang:
--
--   UPDATE tbldetailprintresi SET qty_manual = 0 WHERE qty_manual = 1;
--
-- Lakukan HANYA setelah memastikan export baru sudah benar, dengan:
--   php resync.php periksa "<export-baru.xlsx>"
-- dan peringatan "seluruh N baris berqty 1" sudah tidak muncul.
-- =====================================================================

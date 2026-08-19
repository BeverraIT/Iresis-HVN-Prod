-- =====================================================
-- SCRIPT PERBAIKAN MAPPING JNT vs JNT CARGO
-- =====================================================
-- Script ini untuk memperbaiki database agar JNT dan JNT Cargo terpisah
-- Jalankan script ini di database Anda
-- =====================================================

-- LANGKAH 1: Cek kurir yang ada di database
-- Jalankan query ini dulu untuk melihat kurir yang sudah ada:
SELECT * FROM tblkurir WHERE LOWER(nama_kurir) LIKE '%jnt%' OR LOWER(nama_kurir) LIKE '%j&t%';

-- =====================================================
-- LANGKAH 2: Pastikan ada 2 kurir terpisah
-- =====================================================

-- 2A. Jika belum ada kurir "JNT" (bukan cargo), tambahkan:
-- (Sesuaikan nama_kurir sesuai yang Anda gunakan, contoh: "JNT" atau "J&T")
INSERT INTO tblkurir (nama_kurir, isactive) 
SELECT 'JNT', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tblkurir 
    WHERE LOWER(nama_kurir) = 'jnt' 
    AND LOWER(nama_kurir) NOT LIKE '%cargo%'
);

-- 2B. Jika belum ada kurir "JNT Cargo", tambahkan:
-- (Sesuaikan nama_kurir sesuai yang Anda gunakan, contoh: "JNT Cargo" atau "J&T Cargo")
INSERT INTO tblkurir (nama_kurir, isactive) 
SELECT 'JNT Cargo', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tblkurir 
    WHERE LOWER(nama_kurir) LIKE '%jnt%' 
    AND LOWER(nama_kurir) LIKE '%cargo%'
);

-- =====================================================
-- LANGKAH 3: Ambil id_kurir yang baru dibuat
-- =====================================================
-- Catat id_kurir untuk JNT biasa dan JNT Cargo
-- Contoh:
-- SELECT id_kurir, nama_kurir FROM tblkurir WHERE LOWER(nama_kurir) = 'jnt' AND LOWER(nama_kurir) NOT LIKE '%cargo%';
-- SELECT id_kurir, nama_kurir FROM tblkurir WHERE LOWER(nama_kurir) LIKE '%jnt%' AND LOWER(nama_kurir) LIKE '%cargo%';

-- =====================================================
-- LANGKAH 4: Perbaiki data lama yang id_kurir = 99 (Tidak diketahui)
-- =====================================================
-- PERHATIAN: Script ini akan mengupdate data yang id_kurir = 99
-- Pastikan Anda sudah backup database sebelum menjalankan!

-- 4A. Cek dulu berapa banyak data yang akan diupdate:
SELECT COUNT(*) as total_data_salah 
FROM tblprintresi 
WHERE id_kurir = 99;

-- 4B. Lihat contoh data yang akan diupdate (opsional):
-- SELECT noresi, id_kurir, created_at 
-- FROM tblprintresi 
-- WHERE id_kurir = 99 
-- LIMIT 10;

-- 4C. Update data yang id_kurir = 99
-- GANTI id_kurir_jnt_cargo dan id_kurir_jnt dengan id_kurir yang sebenarnya dari database Anda!
-- Contoh: jika id_kurir JNT Cargo = 5, dan id_kurir JNT = 3

-- OPSI 1: Jika Anda yakin semua data dengan id_kurir=99 adalah JNT Cargo
-- UPDATE tblprintresi 
-- SET id_kurir = [ID_KURIR_JNT_CARGO]  -- GANTI dengan id_kurir JNT Cargo yang benar
-- WHERE id_kurir = 99;

-- OPSI 2: Jika Anda ingin lebih hati-hati, update berdasarkan pola noresi
-- (JNT Cargo biasanya punya prefix: JP, JX, JO, JZ, TJ, 20)
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

-- Update untuk JNT biasa (bukan cargo) - jika ada pola khusus
-- UPDATE tblprintresi 
-- SET id_kurir = (
--     SELECT id_kurir FROM tblkurir 
--     WHERE LOWER(nama_kurir) = 'jnt' 
--     AND LOWER(nama_kurir) NOT LIKE '%cargo%' 
--     LIMIT 1
-- )
-- WHERE id_kurir = 99
-- AND [TAMBAHKAN KONDISI UNTUK JNT BIASA JIKA ADA];

-- =====================================================
-- LANGKAH 5: Verifikasi hasil perbaikan
-- =====================================================

-- 5A. Cek apakah masih ada data dengan id_kurir = 99:
SELECT COUNT(*) as masih_ada_tidak_diketahui 
FROM tblprintresi 
WHERE id_kurir = 99;

-- 5B. Cek distribusi kurir di tblprintresi:
SELECT 
    k.id_kurir,
    k.nama_kurir,
    COUNT(p.id_printresi) as total_resi
FROM tblkurir k
LEFT JOIN tblprintresi p ON p.id_kurir = k.id_kurir
WHERE LOWER(k.nama_kurir) LIKE '%jnt%' OR LOWER(k.nama_kurir) LIKE '%j&t%'
GROUP BY k.id_kurir, k.nama_kurir
ORDER BY k.nama_kurir;

-- 5C. Cek di laporan shipping report:
SELECT 
    COALESCE(k.nama_kurir, '- Tidak diketahui -') as nama_kurir,
    COUNT(1) as total
FROM tblresikeluar r
JOIN tblprintresi p ON p.id_printresi = r.id_resi
LEFT JOIN tblkurir k ON k.id_kurir = p.id_kurir
WHERE r.tanggal_resikeluar >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY k.nama_kurir
ORDER BY k.nama_kurir;

-- =====================================================
-- CATATAN PENTING:
-- =====================================================
-- 1. Backup database SEBELUM menjalankan script update!
-- 2. Sesuaikan nama_kurir dengan yang ada di database Anda
-- 3. Ganti [ID_KURIR_JNT_CARGO] dan [ID_KURIR_JNT] dengan id_kurir yang sebenarnya
-- 4. Test di environment development dulu sebelum production
-- 5. Jika ragu, jalankan query SELECT dulu untuk melihat hasilnya
-- =====================================================


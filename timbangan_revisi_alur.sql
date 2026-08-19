-- =====================================================================
--  Revisi alur verifikasi berat (modul Timbangan)
-- ---------------------------------------------------------------------
--  Perubahan alur:
--   * Setiap hasil timbang SELALU tersimpan, termasuk yang tidak sesuai.
--   * Percobaan ke-1 yang meleset: peringatan saja, tanpa kode atasan.
--   * Percobaan ke-2 dst pada resi yang sama: wajib kode atasan.
--   * Resi yang meleset dikumpulkan di menu baru "Berat Resi Tidak Sesuai"
--     untuk ditindaklanjuti admin (pakai kode atasan yang sama).
--
--  Aman dijalankan berulang: semua perintah memakai penjaga IF NOT EXISTS
--  atau pengecekan terlebih dahulu.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Kolom baru pada tabel hasil timbang
-- ---------------------------------------------------------------------
ALTER TABLE `tbltimbangan`
  ADD COLUMN IF NOT EXISTS `percobaan_ke` INT(11) NOT NULL DEFAULT 1
    COMMENT 'urutan penimbangan untuk nomor resi yang sama' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `tindak_lanjut` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 = sudah ditindaklanjuti admin' AFTER `percobaan_ke`,
  ADD COLUMN IF NOT EXISTS `tindak_lanjut_oleh` INT(11) DEFAULT NULL AFTER `tindak_lanjut`,
  ADD COLUMN IF NOT EXISTS `tindak_lanjut_waktu` DATETIME DEFAULT NULL AFTER `tindak_lanjut_oleh`,
  ADD COLUMN IF NOT EXISTS `tindak_lanjut_catatan` VARCHAR(255) DEFAULT NULL AFTER `tindak_lanjut_waktu`;

-- Daftar "berat tidak sesuai" selalu disaring dengan dua kolom ini.
ALTER TABLE `tbltimbangan`
  ADD INDEX IF NOT EXISTS `idx_timbangan_tindak_lanjut` (`status`, `tindak_lanjut`);

-- ---------------------------------------------------------------------
-- 2. Menu baru di bawah "Laporan Timbangan"
-- ---------------------------------------------------------------------
INSERT INTO `menu` (`parentid`, `name`, `uri`, `icon`, `sortorder`, `description`, `isactive`, `createdby`, `created`)
SELECT 6, 'Berat Resi Tidak Sesuai', 'timbangan/resi-tidak-sesuai', 'fa fa-exclamation-triangle', 100,
       'Daftar resi yang berat timbangnya di luar rentang, untuk ditindaklanjuti admin', 1, 1, NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `menu` WHERE `uri` = 'timbangan/resi-tidak-sesuai');

-- ---------------------------------------------------------------------
-- 3. Hak akses: samakan dengan role yang sudah bisa membuka
--    "Laporan Timbangan"
-- ---------------------------------------------------------------------
INSERT INTO `roleaccess` (`roleid`, `menuid`, `created`)
SELECT ra.`roleid`, m_baru.`id`, NOW()
FROM `roleaccess` ra
JOIN `menu` m_lama ON m_lama.`id` = ra.`menuid` AND m_lama.`uri` = 'timbangan/laporan-timbangan'
JOIN `menu` m_baru ON m_baru.`uri` = 'timbangan/resi-tidak-sesuai'
WHERE NOT EXISTS (
    SELECT 1 FROM `roleaccess` x WHERE x.`roleid` = ra.`roleid` AND x.`menuid` = m_baru.`id`
);

-- ---------------------------------------------------------------------
-- 4. INTERLOCK dimatikan.
--    Alur baru mewajibkan hasil yang meleset tetap tersimpan, sehingga
--    penguncian "hanya ACCEPT yang boleh disimpan" tidak berlaku lagi.
-- ---------------------------------------------------------------------
UPDATE `param`
SET `paramvalue2` = '0',
    `description` = 'Tidak dipakai lagi sejak alur 2 percobaan: semua hasil timbang selalu disimpan'
WHERE `paramgroup` = 'TIMBANGAN' AND `paramvalue1` = 'INTERLOCK';

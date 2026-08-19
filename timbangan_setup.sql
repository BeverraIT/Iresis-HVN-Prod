-- =====================================================================
-- SETUP MODUL TIMBANGAN (TIM RESI)
-- Jalankan sekali di database iresis sebelum memakai menu Timbangan.
-- Aman dijalankan ulang (idempotent).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Master berat standar per SKU (sumber: timbangan/sku.xlsx)
--    Semua berat dalam satuan GRAM.
--    Kalau sebuah SKU tidak ada di sini, sistem otomatis pakai
--    tblsku.berat sebagai cadangan.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbltimbangan_sku` (
  `id_timbangan_sku` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_sku` varchar(100) NOT NULL,
  `berat_standar` int(11) NOT NULL DEFAULT 0 COMMENT 'satuan gram',
  `toleransi_persen` decimal(5,2) DEFAULT NULL COMMENT 'override toleransi global, NULL = ikut global',
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `createdby` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updatedby` int(11) DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id_timbangan_sku`),
  UNIQUE KEY `uq_timbangan_sku_kode` (`kode_sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 2. Hasil penimbangan per resi
--    berat_* disimpan dalam GRAM supaya konsisten dengan master.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbltimbangan` (
  `id_timbangan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_resi` bigint(20) UNSIGNED DEFAULT NULL,
  `noresi` varchar(255) NOT NULL,
  `berat_aktual` decimal(12,2) NOT NULL COMMENT 'gram, hasil baca indikator',
  `berat_standar` decimal(12,2) NOT NULL COMMENT 'gram, snapshot saat ditimbang',
  `berat_min` decimal(12,2) NOT NULL COMMENT 'gram',
  `berat_max` decimal(12,2) NOT NULL COMMENT 'gram',
  `selisih` decimal(12,2) NOT NULL COMMENT 'gram, berat_aktual - berat_standar',
  `toleransi_persen` decimal(5,2) NOT NULL,
  `berat_kemasan` decimal(12,2) NOT NULL DEFAULT 0 COMMENT 'gram, sudah termasuk di berat_standar',
  `status` varchar(20) NOT NULL COMMENT 'UNDER / ACCEPT / OVER',
  `sku_tanpa_master` int(11) NOT NULL DEFAULT 0 COMMENT 'jumlah baris SKU yang belum punya berat standar',
  `raw_data` varchar(255) DEFAULT NULL COMMENT 'baris mentah terakhir dari indikator',
  `admin_pegawai` int(11) NOT NULL,
  `nama_komputer` varchar(100) DEFAULT NULL,
  `tanggal_timbangan` datetime NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id_timbangan`),
  KEY `idx_timbangan_noresi` (`noresi`),
  KEY `idx_timbangan_resi` (`id_resi`),
  KEY `idx_timbangan_tanggal` (`tanggal_timbangan`),
  KEY `idx_timbangan_user` (`admin_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 3. Parameter global modul timbangan (paramgroup = 'TIMBANGAN')
--    paramvalue1 = kode, paramvalue2 = nilai
-- ---------------------------------------------------------------------
INSERT INTO `param` (`paramvalue1`, `paramvalue2`, `paramgroup`, `sortorder`, `description`, `isactive`, `createdby`, `created`)
SELECT * FROM (
  SELECT 'TOLERANSI_PERSEN' AS a, '5'  AS b, 'TIMBANGAN' AS c, 10 AS d, 'Toleransi ACCEPT dalam persen dari berat standar' AS e, 1 AS f, 0 AS g, NOW() AS h
  UNION ALL SELECT 'BERAT_KEMASAN',   '0',  'TIMBANGAN', 20, 'Berat kemasan/tare per paket dalam gram', 1, 0, NOW()
  UNION ALL SELECT 'SATUAN_INDIKATOR','g',  'TIMBANGAN', 30, 'Satuan angka yang dikirim indikator timbangan: g atau kg', 1, 0, NOW()
  UNION ALL SELECT 'INTERLOCK',       '1',  'TIMBANGAN', 40, 'Jika 1, hanya status ACCEPT yang boleh disimpan', 1, 0, NOW()
  UNION ALL SELECT 'BAUD_RATE',       '9600','TIMBANGAN',50, 'Baud rate default koneksi serial RS-232', 1, 0, NOW()
  UNION ALL SELECT 'KODE_SUPERVISOR', '8888','TIMBANGAN',60, 'Kode atasan untuk membuka kunci saat berat tidak lolos verifikasi', 1, 0, NOW()
  UNION ALL SELECT 'AUTO_SIMPAN',     '1',   'TIMBANGAN',70, 'Jika 1, hasil lolos langsung disimpan dan lanjut ke resi berikutnya', 1, 0, NOW()
  UNION ALL SELECT 'STABIL_MS',       '800', 'TIMBANGAN',80, 'Lama bacaan indikator harus diam (milidetik) sebelum dinilai lolos / tidak', 1, 0, NOW()
) AS baru
WHERE NOT EXISTS (
  SELECT 1 FROM `param` p WHERE p.`paramgroup` = 'TIMBANGAN' AND p.`paramvalue1` = baru.a
);

-- ---------------------------------------------------------------------
-- 3b. Log kunci verifikasi berat.
--     Setiap paket yang tidak lolos mengunci layar operator sampai atasan
--     memasukkan kode; kejadiannya dicatat di sini untuk penelusuran.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbltimbangan_lock` (
  `id_lock` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `noresi` varchar(255) NOT NULL,
  `berat_aktual` decimal(12,2) NOT NULL COMMENT 'gram',
  `berat_standar` decimal(12,2) NOT NULL COMMENT 'gram',
  `berat_min` decimal(12,2) NOT NULL COMMENT 'gram',
  `berat_max` decimal(12,2) NOT NULL COMMENT 'gram',
  `selisih` decimal(12,2) NOT NULL COMMENT 'gram',
  `status` varchar(20) NOT NULL COMMENT 'UNDER / OVER',
  `admin_pegawai` int(11) NOT NULL COMMENT 'operator yang terkunci',
  `nama_komputer` varchar(100) DEFAULT NULL,
  `percobaan_gagal` int(11) NOT NULL DEFAULT 0 COMMENT 'jumlah kode salah sebelum kunci terbuka',
  `waktu_lock` datetime NOT NULL,
  `waktu_unlock` datetime DEFAULT NULL COMMENT 'NULL = belum pernah dibuka',
  PRIMARY KEY (`id_lock`),
  KEY `idx_timbangan_lock_noresi` (`noresi`),
  KEY `idx_timbangan_lock_user` (`admin_pegawai`),
  KEY `idx_timbangan_lock_waktu` (`waktu_lock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4. Menu di bawah TIM RESI (menu.id = 6)
-- ---------------------------------------------------------------------
INSERT INTO `menu` (`parentid`, `name`, `uri`, `icon`, `sortorder`, `description`, `isactive`, `createdby`, `created`)
SELECT * FROM (
  SELECT 6 AS a, 'Timbangan Resi'   AS b, 'timbangan/scan-timbangan'    AS c, 'fa fa-balance-scale' AS d, 70 AS e, '' AS f, 1 AS g, NULL AS h, NOW() AS i
  UNION ALL SELECT 6, 'Master Berat SKU',   'timbangan/master-timbangan',  'fa fa-database', 80, '', 1, NULL, NOW()
  UNION ALL SELECT 6, 'Laporan Timbangan',  'timbangan/laporan-timbangan', 'fa fa-table',    90, '', 1, NULL, NOW()
) AS baru
WHERE NOT EXISTS (
  SELECT 1 FROM `menu` m WHERE m.`uri` = baru.c
);

-- ---------------------------------------------------------------------
-- 5. Hak akses: samakan dengan role yang sudah bisa membuka
--    menu 'Scan Resi' (receipt/scan_receipt).
--    Kalau perlu diatur manual, pakai menu User Management > Access.
-- ---------------------------------------------------------------------
INSERT INTO `roleaccess` (`roleid`, `menuid`, `created`)
SELECT DISTINCT ra.`roleid`, CAST(m.`id` AS CHAR), NOW()
FROM `roleaccess` ra
CROSS JOIN `menu` m
WHERE ra.`menuid` = (SELECT CAST(id AS CHAR) FROM `menu` WHERE `uri` = 'receipt/scan_receipt' LIMIT 1)
  AND m.`uri` IN ('timbangan/scan-timbangan', 'timbangan/master-timbangan', 'timbangan/laporan-timbangan')
  AND NOT EXISTS (
    SELECT 1 FROM `roleaccess` x
    WHERE x.`roleid` = ra.`roleid` AND x.`menuid` = CAST(m.`id` AS CHAR)
  );

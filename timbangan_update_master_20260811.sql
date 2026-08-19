-- =====================================================================
--  Pembaruan master berat SKU - hasil timbang ulang 11 Agustus 2026
-- ---------------------------------------------------------------------
--  Sumber angka: penimbangan ulang oleh tim (disampaikan langsung),
--  bukan dari sku.xlsx. Tiga kode di bawah sempat diluruskan lebih dulu
--  karena penulisannya berbeda dengan kode di sistem:
--
--    ADP-05022B   -> ADP-0502B    (kode yang sama, 69 g)
--    3100a31100b  -> 3100A3100B   (kelebihan satu angka 1)
--    PLCR ...     -> 20 varian PLCR*, semuanya 12 g
--
--  Kolom berat_standar sudah diubah dari int menjadi decimal(12,2)
--  supaya MCBTYPEC-4056 (1,6 g) tersimpan apa adanya. Pada pesanan
--  400 pcs, pembulatan ke 2 g menggeser berat standar sejauh 160 g.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. SKU yang sudah ada di master
-- ---------------------------------------------------------------------
UPDATE `tbltimbangan_sku` SET `berat_standar` = 1.6,  `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'MCBTYPEC-4056';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 24,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'STU-0512';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 9,    `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'ADP-2401';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 48,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'HDMI-1';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 115,  `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'PLCA1-8';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 12,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'KSCSC-1';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 236,  `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'B2F4EC';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 10,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'STD-2596';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 69,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'ADP-0502B';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 11,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'HSCSC-1';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 43,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = 'HVC20';
UPDATE `tbltimbangan_sku` SET `berat_standar` = 274,  `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` = '3100A3100B';

-- Seluruh varian PLCR memakai berat yang sama.
UPDATE `tbltimbangan_sku` SET `berat_standar` = 12,   `updatedby` = 27, `updated` = NOW() WHERE `kode_sku` LIKE 'PLCR%';

-- ---------------------------------------------------------------------
-- 2. SKU yang belum punya master (termasuk daftar berisi "-" di sku.xlsx)
-- ---------------------------------------------------------------------
INSERT INTO `tbltimbangan_sku` (`kode_sku`, `berat_standar`, `isactive`, `createdby`, `created`)
VALUES  ('ESP-C-38',    10,  1, 27, NOW()),
        ('MODINFRA-1',   3,  1, 27, NOW()),
        ('B3F3EC-ABB', 264,  1, 27, NOW())
ON DUPLICATE KEY UPDATE
        `berat_standar` = VALUES(`berat_standar`),
        `isactive` = 1,
        `updatedby` = 27,
        `updated` = NOW();

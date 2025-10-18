-- =====================================================
-- SCRIPT PRODUCTION SETUP - KPI SYSTEM
-- =====================================================
-- File ini menggabungkan semua script yang diperlukan untuk production
-- Berdasarkan file yang sudah ada: create_kpi_tables_v2, final_kpi_parent_menu, fix_kpi_menu_access

-- =====================================================
-- 1. TABEL KPI SYSTEM
-- =====================================================

-- 1.1 Table master status performa
CREATE TABLE IF NOT EXISTS `tblmasterstatusperforma` (
  `id_statusperforma` int(11) NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(20) NOT NULL,
  `role` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `deskripsi` text,
  `target_harian` int(11) DEFAULT 0,
  `isactive` tinyint(1) DEFAULT 1,
  `createdby` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedby` int unsigned DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_statusperforma`),
  UNIQUE KEY `kode_status_UNIQUE` (`kode_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 1.2 Insert data master status performa (PACKER)
INSERT INTO `tblmasterstatusperforma` (`kode_status`, `role`, `status_name`, `deskripsi`, `target_harian`, `isactive`, `createdby`) VALUES
('GTL', 'PACKER', 'Good To Live', 'Status performa untuk order GTL', 0, 1, 1),
('NDD', 'PACKER', 'Next Day Delivery', 'Status performa untuk order NDD', 0, 1, 1),
('1_SKU', 'PACKER', 'Single SKU Order', 'Status performa untuk order 1 SKU', 0, 1, 1),
('MOONKLAZ', 'PACKER', 'Moonklaz Order', 'Status performa untuk order Moonklaz', 0, 1, 1),
('PAYUNG', 'PACKER', 'Payung Order', 'Status performa untuk order Payung', 0, 1, 1),
('QTY_BANYAK', 'PACKER', 'Large Quantity Order', 'Status performa untuk order quantity banyak', 0, 1, 1),
('NINJA', 'PACKER', 'Ninja Express Order', 'Status performa untuk order Ninja Express', 0, 1, 1),
('NORMAL', 'PACKER', 'Normal Order', 'Status performa untuk order normal', 0, 1, 1);

-- 1.3 Insert data master status performa (PICKER)
INSERT INTO `tblmasterstatusperforma` (`kode_status`, `role`, `status_name`, `deskripsi`, `target_harian`, `isactive`, `createdby`) VALUES
('1_SKU', 'PICKER', 'Single SKU Order', 'Status performa untuk order 1 SKU', 0, 1, 1),
('NORMAL', 'PICKER', 'Normal Order', 'Status performa untuk order normal', 0, 1, 1);

-- 1.4 Table log status performa harian per user (transaksi login)
CREATE TABLE IF NOT EXISTS `tblstatusperforma` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `id_statusperforma` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_login` time DEFAULT NULL,
  `isactive` tinyint(1) DEFAULT 1,
  `createdby` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedby` int unsigned DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  UNIQUE KEY `user_date_UNIQUE` (`id_user`, `tanggal`),
  KEY `fk_statusperforma_user` (`id_user`),
  KEY `fk_statusperforma_status` (`id_statusperforma`),
  CONSTRAINT `fk_statusperforma_user` FOREIGN KEY (`id_user`) REFERENCES `tbluser` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_statusperforma_status` FOREIGN KEY (`id_statusperforma`) REFERENCES `tblmasterstatusperforma` (`id_statusperforma`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 1.5 Table log transaksi harian per user (KPI packing/picking)
CREATE TABLE IF NOT EXISTS `tblkpi` (
  `id_log_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `id_statusperforma` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `tipe_transaksi` enum('PACKING','PICKING') NOT NULL,
  `jumlah_resi` int(11) DEFAULT 1,
  `total_transaksi` int(11) DEFAULT 1,
  `createdby` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedby` int unsigned DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_transaksi`),
  KEY `fk_kpi_user` (`id_user`),
  KEY `fk_kpi_status` (`id_statusperforma`),
  KEY `idx_user_date` (`id_user`, `tanggal`),
  CONSTRAINT `fk_kpi_user` FOREIGN KEY (`id_user`) REFERENCES `tbluser` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kpi_status` FOREIGN KEY (`id_statusperforma`) REFERENCES `tblmasterstatusperforma` (`id_statusperforma`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 1.6 Table summary KPI harian per status
CREATE TABLE IF NOT EXISTS `tblsummarykpiharian` (
  `id_summary` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_statusperforma` int(11) NOT NULL,
  `total_user_aktif` int(11) DEFAULT 0,
  `total_packing` int(11) DEFAULT 0,
  `total_picking` int(11) DEFAULT 0,
  `total_transaksi` int(11) DEFAULT 0,
  `rata_rata_per_user` decimal(10,2) DEFAULT 0.00,
  `persentase_capai` decimal(5,2) DEFAULT 0.00,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_summary`),
  UNIQUE KEY `date_status_UNIQUE` (`tanggal`, `id_statusperforma`),
  KEY `fk_summary_status` (`id_statusperforma`),
  CONSTRAINT `fk_summary_status` FOREIGN KEY (`id_statusperforma`) REFERENCES `tblmasterstatusperforma` (`id_statusperforma`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- =====================================================
-- 2. VIEW KPI DASHBOARD
-- =====================================================

-- 2.1 View untuk dashboard KPI real-time
CREATE OR REPLACE VIEW `vw_kpi_dashboard` AS
SELECT 
    t.tanggal,
    sp.kode_status,
    sp.status_name,
    sp.target_harian,
    sp.role,
    COUNT(DISTINCT t.id_user) as total_user_aktif,
    COALESCE(SUM(CASE WHEN k.tipe_transaksi = 'PACKING' THEN k.jumlah_resi ELSE 0 END), 0) as total_packing,
    COALESCE(SUM(CASE WHEN k.tipe_transaksi = 'PICKING' THEN k.jumlah_resi ELSE 0 END), 0) as total_picking,
    COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
    CASE 
        WHEN COUNT(DISTINCT t.id_user) > 0 THEN 
            ROUND(COALESCE(SUM(k.jumlah_resi), 0) / COUNT(DISTINCT t.id_user), 2)
        ELSE 0 
    END as rata_rata_per_user,
    CASE 
        WHEN sp.target_harian > 0 THEN 
            ROUND((COALESCE(SUM(k.jumlah_resi), 0) / COUNT(DISTINCT t.id_user)) / sp.target_harian * 100, 2)
        ELSE 0 
    END as persentase_capai,
    CASE 
        WHEN sp.target_harian > 0 AND (COALESCE(SUM(k.jumlah_resi), 0) / COUNT(DISTINCT t.id_user)) / sp.target_harian >= 1.0 THEN 'EXCELLENT'
        WHEN sp.target_harian > 0 AND (COALESCE(SUM(k.jumlah_resi), 0) / COUNT(DISTINCT t.id_user)) / sp.target_harian >= 0.8 THEN 'GOOD'
        WHEN sp.target_harian > 0 AND (COALESCE(SUM(k.jumlah_resi), 0) / COUNT(DISTINCT t.id_user)) / sp.target_harian >= 0.6 THEN 'FAIR'
        ELSE 'POOR'
    END as status_performa
FROM tblstatusperforma t
JOIN tblmasterstatusperforma sp ON sp.id_statusperforma = t.id_statusperforma
LEFT JOIN tblkpi k ON k.id_user = t.id_user 
    AND k.id_statusperforma = t.id_statusperforma 
    AND k.tanggal = t.tanggal
WHERE t.isactive = 1
GROUP BY t.tanggal, sp.id_statusperforma, sp.kode_status, sp.status_name, sp.target_harian, sp.role
ORDER BY t.tanggal DESC, total_transaksi DESC;

-- =====================================================
-- 3. MENU KPI REPORTS
-- =====================================================

-- 3.1 Hapus menu KPI Reports yang ada (jika ada)
DELETE FROM menu WHERE uri = 'kpi_reports' OR name = 'KPI Reports';

-- 3.2 Hapus role access yang lama
DELETE FROM roleaccess WHERE menuid IN (SELECT id FROM menu WHERE uri = 'kpi_reports' OR name = 'KPI Reports');

-- 3.3 Buat menu KPI Reports sebagai parent menu
INSERT INTO menu (parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES (1, 'KPI Reports', NULL, 'fa fa-line-chart', 100, 'Key Performance Indicators Dashboard', 1, 1, NOW(), NULL, NULL);

-- 3.4 Dapatkan ID menu KPI Reports yang baru
SET @kpi_menu_id = LAST_INSERT_ID();

-- 3.5 Buat sub menu Dashboard KPI
INSERT INTO menu (parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES (@kpi_menu_id, 'Dashboard KPI', 'kpi_reports', 'fa fa-dashboard', 10, 'Dashboard Key Performance Indicators', 1, 1, NOW(), NULL, NULL);

-- 3.6 Buat sub menu Export KPI
INSERT INTO menu (parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES (@kpi_menu_id, 'Export KPI', 'kpi_reports/export', 'fa fa-download', 20, 'Export Laporan KPI ke Excel', 1, 1, NOW(), NULL, NULL);

-- =====================================================
-- 4. ROLE ACCESS KPI REPORTS
-- =====================================================

-- 4.1 Tambahkan role access untuk menu parent KPI Reports (Admin)
INSERT INTO roleaccess (menuid, roleid, createdby, created)
VALUES (@kpi_menu_id, 1, 1, NOW());

-- 4.2 Tambahkan role access untuk menu parent KPI Reports (Webmaster/Manager)
INSERT INTO roleaccess (menuid, roleid, createdby, created)
VALUES (@kpi_menu_id, 2, 1, NOW());

-- 4.3 Tambahkan role access untuk sub menu KPI Reports (Admin)
INSERT INTO roleaccess (menuid, roleid, createdby, created)
SELECT id, 1, 1, NOW()
FROM menu 
WHERE parentid = @kpi_menu_id;

-- 4.4 Tambahkan role access untuk sub menu KPI Reports (Webmaster/Manager)
INSERT INTO roleaccess (menuid, roleid, createdby, created)
SELECT id, 2, 1, NOW()
FROM menu 
WHERE parentid = @kpi_menu_id;

-- =====================================================
-- 5. VERIFIKASI HASIL
-- =====================================================

-- 5.1 Tampilkan data status performa
SELECT 'Data Status Performa:' as info;
SELECT `kode_status`, `role`, `status_name`, `target_harian` 
FROM `tblmasterstatusperforma` 
WHERE `isactive` = 1 
ORDER BY `role`, `kode_status`;

-- 5.2 Tampilkan menu KPI Reports
SELECT 'Menu KPI Reports:' as info;
SELECT 
    m.id,
    m.name,
    m.parentid,
    p.name as parent_name,
    m.uri,
    m.icon,
    m.sortorder,
    m.isactive
FROM menu m
LEFT JOIN menu p ON p.id = m.parentid
WHERE (m.name LIKE '%KPI%' OR m.uri LIKE '%kpi_reports%') AND m.isactive = 1
ORDER BY m.parentid, m.sortorder;

-- 5.3 Tampilkan role access KPI Reports
SELECT 'Role Access KPI Reports:' as info;
SELECT 
    m.name as menu_name,
    m.uri,
    GROUP_CONCAT(ra.roleid ORDER BY ra.roleid) as allowed_roles
FROM menu m
LEFT JOIN roleaccess ra ON ra.menuid = m.id
WHERE m.name LIKE '%KPI%' OR m.uri LIKE '%kpi_reports%'
GROUP BY m.id, m.name, m.uri
ORDER BY m.parentid, m.sortorder;

-- =====================================================
-- SELESAI - PRODUCTION SETUP KPI SYSTEM
-- =====================================================

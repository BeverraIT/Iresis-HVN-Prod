-- Script untuk membuat table KPI system (VERSION 2 - NEW TABLE NAMES)
-- Berdasarkan requirements: tracking performa harian berdasarkan status performa

-- NAMA TABEL BARU:
-- tblstatusperforma → tblmasterstatusperforma
-- tbllogstatusperforma → tblstatusperforma
-- tbllogtransaksiharian → tblkpi

-- 1. Table master status performa
CREATE TABLE IF NOT EXISTS `tblmasterstatusperforma` (
  `id_statusperforma` int(11) NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(20) NOT NULL,
  `role` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `deskripsi` text,
  `target_harian` int(11) DEFAULT 50,
  `isactive` tinyint(1) DEFAULT 1,
  `createdby` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedby` int unsigned DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_statusperforma`),
  UNIQUE KEY `kode_status_UNIQUE` (`kode_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 2. Insert data master status performa
INSERT INTO `tblmasterstatusperforma` (`kode_status`, `role`, `status_name`, `deskripsi`, `target_harian`, `isactive`, `createdby`) VALUES
('GTL', 'PACKER', 'Good To Live', 'Status performa untuk order GTL', 80, 1, 1),
('NDD', 'PACKER', 'Next Day Delivery', 'Status performa untuk order NDD', 70, 1, 1),
('1_SKU', 'PACKER', 'Single SKU Order', 'Status performa untuk order 1 SKU', 60, 1, 1),
('MOONKLAZ', 'PACKER', 'Moonklaz Order', 'Status performa untuk order Moonklaz', 65, 1, 1),
('PAYUNG', 'PACKER', 'Payung Order', 'Status performa untuk order Payung', 55, 1, 1),
('QTY_BANYAK', 'PACKER', 'Large Quantity Order', 'Status performa untuk order quantity banyak', 50, 1, 1),
('NINJA', 'PACKER', 'Ninja Express Order', 'Status performa untuk order Ninja Express', 75, 1, 1),
('NORMAL', 'PACKER', 'Normal Order', 'Status performa untuk order normal', 40, 1, 1);

-- 3. Table log status performa harian per user (transaksi login)
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

-- 4. Table log transaksi harian per user (KPI packing/picking)
CREATE TABLE IF NOT EXISTS `tblkpi` (
  `id_log_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `id_statusperforma` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `tipe_transaksi` enum('PACKING','PICKING') NOT NULL,
  `jumlah_resi` int(11) DEFAULT 1,
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

-- 5. Table summary KPI harian per status
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

-- 6. View untuk dashboard KPI real-time
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
    END as persentase_capai
FROM tblstatusperforma t
JOIN tblmasterstatusperforma sp ON sp.id_statusperforma = t.id_statusperforma
LEFT JOIN tblkpi k ON k.id_user = t.id_user 
    AND k.id_statusperforma = t.id_statusperforma 
    AND k.tanggal = t.tanggal
WHERE t.isactive = 1
GROUP BY t.tanggal, sp.id_statusperforma, sp.kode_status, sp.status_name, sp.target_harian, sp.role
ORDER BY t.tanggal DESC, total_transaksi DESC;

-- 7. View untuk top performers
CREATE OR REPLACE VIEW `vw_top_performers` AS
SELECT 
    t.id_user,
    u.username,
    u.name as nama_user,
    sp.kode_status,
    sp.status_name,
    t.tanggal,
    COALESCE(SUM(CASE WHEN k.tipe_transaksi = 'PACKING' THEN k.jumlah_resi ELSE 0 END), 0) as total_packing,
    COALESCE(SUM(CASE WHEN k.tipe_transaksi = 'PICKING' THEN k.jumlah_resi ELSE 0 END), 0) as total_picking,
    COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
    sp.target_harian,
    CASE 
        WHEN sp.target_harian > 0 THEN 
            ROUND((COALESCE(SUM(k.jumlah_resi), 0) / sp.target_harian) * 100, 2)
        ELSE 0 
    END as persentase_capai
FROM tblstatusperforma t
JOIN tbluser u ON u.id_user = t.id_user
JOIN tblmasterstatusperforma sp ON sp.id_statusperforma = t.id_statusperforma
LEFT JOIN tblkpi k ON k.id_user = t.id_user 
    AND k.id_statusperforma = t.id_statusperforma 
    AND k.tanggal = t.tanggal
WHERE t.isactive = 1
GROUP BY t.id_user, u.username, u.name, sp.kode_status, sp.status_name, t.tanggal, sp.target_harian
ORDER BY total_transaksi DESC;

-- 8. Stored procedure untuk update KPI harian
DELIMITER //
CREATE PROCEDURE `sp_update_kpi_harian`(IN p_tanggal DATE)
BEGIN
    -- Update summary KPI harian untuk semua status
    INSERT INTO tblsummarykpiharian (
        tanggal, id_statusperforma, total_user_aktif, 
        total_packing, total_picking, total_transaksi, 
        rata_rata_per_user, persentase_capai
    )
    SELECT 
        p_tanggal,
        sp.id_statusperforma,
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
            WHEN sp.target_harian > 0 AND COUNT(DISTINCT t.id_user) > 0 THEN 
                ROUND((COALESCE(SUM(k.jumlah_resi), 0) / COUNT(DISTINCT t.id_user)) / sp.target_harian * 100, 2)
            ELSE 0 
        END as persentase_capai
    FROM tblmasterstatusperforma sp
    LEFT JOIN tblstatusperforma t ON t.id_statusperforma = sp.id_statusperforma 
        AND t.tanggal = p_tanggal AND t.isactive = 1
    LEFT JOIN tblkpi k ON k.id_user = t.id_user 
        AND k.id_statusperforma = t.id_statusperforma 
        AND k.tanggal = p_tanggal
    WHERE sp.isactive = 1
    GROUP BY sp.id_statusperforma, sp.target_harian
    ON DUPLICATE KEY UPDATE
        total_user_aktif = VALUES(total_user_aktif),
        total_packing = VALUES(total_packing),
        total_picking = VALUES(total_picking),
        total_transaksi = VALUES(total_transaksi),
        rata_rata_per_user = VALUES(rata_rata_per_user),
        persentase_capai = VALUES(persentase_capai),
        updated = CURRENT_TIMESTAMP;
END //
DELIMITER ;

-- 9. Trigger untuk auto-update KPI saat ada transaksi baru
DELIMITER //
CREATE TRIGGER `tr_update_kpi_after_transaction`
AFTER INSERT ON `tblkpi`
FOR EACH ROW
BEGIN
    CALL sp_update_kpi_harian(NEW.tanggal);
END //
DELIMITER ;

-- 10. Trigger untuk auto-update KPI saat ada status baru
DELIMITER //
CREATE TRIGGER `tr_update_kpi_after_status`
AFTER INSERT ON `tblstatusperforma`
FOR EACH ROW
BEGIN
    CALL sp_update_kpi_harian(NEW.tanggal);
END //
DELIMITER ;

SELECT 'KPI Tables created successfully with NEW table names!' as status;
SELECT 'Table names:' as info;
SELECT '- tblmasterstatusperforma (master status)' as table_1;
SELECT '- tblstatusperforma (log login status)' as table_2;
SELECT '- tblkpi (log transaksi packing/picking)' as table_3;
SELECT '- tblsummarykpiharian (summary)' as table_4;


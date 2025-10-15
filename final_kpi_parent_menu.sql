-- Script final untuk membuat KPI Reports sebagai menu parent
-- Hapus menu KPI Reports yang ada dan buat ulang sebagai parent menu

-- 1. Hapus menu KPI Reports yang ada (jika ada)
DELETE FROM menu WHERE uri = 'kpi_reports' OR name = 'KPI Reports';

-- 2. Hapus role access yang lama
DELETE FROM roleaccess WHERE menuid IN (SELECT id FROM menu WHERE uri = 'kpi_reports' OR name = 'KPI Reports');

-- 3. Buat menu KPI Reports sebagai parent menu (sejajar dengan Laporan, TIM RESI, dll)
INSERT INTO menu (parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES (1, 'KPI Reports', NULL, 'fa fa-line-chart', 100, 'Key Performance Indicators Dashboard', 1, 1, NOW(), NULL, NULL);

-- 4. Dapatkan ID menu KPI Reports yang baru
SET @kpi_menu_id = LAST_INSERT_ID();

-- 5. Buat sub menu Dashboard KPI
INSERT INTO menu (parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES (@kpi_menu_id, 'Dashboard KPI', 'kpi_reports', 'fa fa-dashboard', 10, 'Dashboard Key Performance Indicators', 1, 1, NOW(), NULL, NULL);

-- 6. Buat sub menu Export KPI
INSERT INTO menu (parentid, name, uri, icon, sortorder, description, isactive, createdby, created, updatedby, updated)
VALUES (@kpi_menu_id, 'Export KPI', 'kpi_reports/export', 'fa fa-download', 20, 'Export Laporan KPI ke Excel', 1, 1, NOW(), NULL, NULL);

-- 7. Tambahkan role access untuk menu parent KPI Reports
INSERT INTO roleaccess (menuid, roleid, createdby, created)
VALUES (@kpi_menu_id, 1, 1, NOW());

-- 8. Tambahkan role access untuk sub menu KPI Reports
INSERT INTO roleaccess (menuid, roleid, createdby, created)
SELECT id, 1, 1, NOW()
FROM menu 
WHERE parentid = @kpi_menu_id;

-- 9. Tampilkan hasil - semua menu parent
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
WHERE m.parentid = 1 AND m.isactive = 1
ORDER BY m.sortorder;

-- 10. Tampilkan sub menu KPI Reports
SELECT 
    m.id,
    m.name,
    m.parentid,
    p.name as parent_name,
    m.uri,
    m.icon,
    m.sortorder
FROM menu m
LEFT JOIN menu p ON p.id = m.parentid
WHERE m.parentid = @kpi_menu_id
ORDER BY m.sortorder;

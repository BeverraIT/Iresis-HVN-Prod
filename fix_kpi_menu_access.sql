-- Script untuk membatasi akses menu KPI Reports hanya untuk Admin dan Webmaster
-- Hanya role 1 (admin) dan role 2 (webmaster/manager) yang bisa akses

-- 1. Hapus semua role access untuk menu KPI Reports
DELETE FROM roleaccess 
WHERE menuid IN (
    SELECT id FROM menu 
    WHERE name LIKE '%KPI%' OR uri LIKE '%kpi_reports%'
);

-- 2. Dapatkan ID menu KPI Reports parent
SELECT @kpi_parent := id 
FROM menu 
WHERE name = 'KPI Reports' AND parentid = 1 AND uri IS NULL 
LIMIT 1;

-- 3. Tambahkan akses untuk role 1 (admin) ke menu parent
INSERT INTO roleaccess (menuid, roleid, createdby, created)
VALUES (@kpi_parent, 1, 1, NOW());

-- 4. Tambahkan akses untuk role 2 (webmaster/manager) ke menu parent
INSERT INTO roleaccess (menuid, roleid, createdby, created)
VALUES (@kpi_parent, 2, 1, NOW());

-- 5. Tambahkan akses untuk role 1 (admin) ke semua sub menu KPI Reports
INSERT INTO roleaccess (menuid, roleid, createdby, created)
SELECT id, 1, 1, NOW()
FROM menu 
WHERE parentid = @kpi_parent;

-- 6. Tambahkan akses untuk role 2 (webmaster/manager) ke semua sub menu KPI Reports
INSERT INTO roleaccess (menuid, roleid, createdby, created)
SELECT id, 2, 1, NOW()
FROM menu 
WHERE parentid = @kpi_parent;

-- 7. Verifikasi hasil
SELECT 
    'Menu KPI Reports' as menu_type,
    m.id as menu_id,
    m.name as menu_name,
    m.uri,
    GROUP_CONCAT(ra.roleid ORDER BY ra.roleid) as allowed_roles
FROM menu m
LEFT JOIN roleaccess ra ON ra.menuid = m.id
WHERE m.name LIKE '%KPI%' OR m.uri LIKE '%kpi_reports%'
GROUP BY m.id, m.name, m.uri
ORDER BY m.parentid, m.sortorder;

-- 8. Tampilkan info role
SELECT 
    DISTINCT roleid,
    CASE 
        WHEN roleid = 1 THEN 'Admin'
        WHEN roleid = 2 THEN 'Webmaster/Manager'
        ELSE CONCAT('Role ', roleid)
    END as role_name
FROM roleaccess
WHERE menuid IN (
    SELECT id FROM menu 
    WHERE name LIKE '%KPI%' OR uri LIKE '%kpi_reports%'
)
ORDER BY roleid;


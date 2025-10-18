#!/bin/bash
# Cleanup file untuk persiapan deploy ke server
# Hanya hapus file temporary/migration, pertahankan yang essential

echo "🧹 Cleaning up temporary files for server deployment..."
echo ""

# Buat folder archive untuk backup
BACKUP_DIR="_archive_local_only"
mkdir -p "$BACKUP_DIR"

echo "📦 Moving temporary files to archive..."

# 1. Migration scripts (sudah dijalankan di lokal)
mv migrate_rename_tables*.sql "$BACKUP_DIR/" 2>/dev/null
mv fix_id_user_datatype.sql "$BACKUP_DIR/" 2>/dev/null
mv fix_column_names_final*.sql "$BACKUP_DIR/" 2>/dev/null

# 2. SQL files lama (pakai nama tabel lama)
mv create_kpi_tables.sql "$BACKUP_DIR/" 2>/dev/null
mv create_kpi_views.sql "$BACKUP_DIR/" 2>/dev/null
mv create_kpi_procedures.sql "$BACKUP_DIR/" 2>/dev/null

# 3. Temporary/testing files
mv check_session_menu.php "$BACKUP_DIR/" 2>/dev/null
mv force_refresh_menu_session.php "$BACKUP_DIR/" 2>/dev/null
mv composer.jsonZone.Identifier "$BACKUP_DIR/" 2>/dev/null
mv beverra-siresi-windows.bat "$BACKUP_DIR/" 2>/dev/null

# 4. Old backups
mv dump-iresis-prod-*.sql "$BACKUP_DIR/" 2>/dev/null
mv patch.sql "$BACKUP_DIR/" 2>/dev/null

# 5. Troubleshooting files
mv fix_kpi_data_import.sql "$BACKUP_DIR/" 2>/dev/null
mv populate_kpi_from_real_data.sql "$BACKUP_DIR/" 2>/dev/null
mv verify_kpi_ready.sql "$BACKUP_DIR/" 2>/dev/null

# 6. Shell scripts
mv install_kpi.sh "$BACKUP_DIR/" 2>/dev/null
mv cleanup_sql_files.sh "$BACKUP_DIR/" 2>/dev/null
mv rename_tables_in_php.sh "$BACKUP_DIR/" 2>/dev/null

# 7. Documentation (opsional - comment jika mau pertahankan)
# mv INTEGRATE_REAL_KPI_DATA.md "$BACKUP_DIR/" 2>/dev/null
# mv KPI_DASHBOARD_IMPLEMENTATION.md "$BACKUP_DIR/" 2>/dev/null
# mv KPI_REPORTS_IMPLEMENTATION.md "$BACKUP_DIR/" 2>/dev/null
# mv KPI_SYSTEM_DOCUMENTATION.md "$BACKUP_DIR/" 2>/dev/null

# 8. Log files lama
if [ -d "application/logs" ]; then
    tar -czf "$BACKUP_DIR/logs_2024_backup.tar.gz" application/logs/log-2024-*.php 2>/dev/null
    rm -f application/logs/log-2024-*.php 2>/dev/null
fi

echo ""
echo "✅ Cleanup completed!"
echo ""
echo "📊 Files structure for server deployment:"
echo ""
echo "Essential SQL files (3 files):"
echo "  ✅ create_kpi_tables_v2.sql"
echo "  ✅ final_kpi_parent_menu.sql"
echo "  ✅ fix_kpi_menu_access.sql"
echo ""
echo "Optional (if needed):"
echo "  ⚠️  add_status_performa_menu.sql"
echo ""
echo "Documentation:"
echo "  📄 DEPLOY_TO_SERVER.md"
echo "  📄 RENAME_TABLES_GUIDE.md"
echo ""
echo "Core application:"
echo "  ✅ application/ (with updated models)"
echo "  ✅ assets/"
echo "  ✅ system/"
echo "  ✅ vendor/"
echo "  ✅ composer.json & composer.lock"
echo "  ✅ index.php, .htaccess, etc"
echo ""
echo "📦 Archived files saved to: $BACKUP_DIR/"
echo ""
echo "🚀 Ready to deploy to server!"
echo ""
echo "Next steps:"
echo "  1. Upload all files to server"
echo "  2. Run: composer install"
echo "  3. Run SQL files (in order):"
echo "     mysql -u root -p dbname < create_kpi_tables_v2.sql"
echo "     mysql -u root -p dbname < final_kpi_parent_menu.sql"
echo "     mysql -u root -p dbname < fix_kpi_menu_access.sql"
echo "  4. Test: Login → Pilih status → Scan resi → Check KPI"
echo ""


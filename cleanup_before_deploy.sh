#!/bin/bash
# Script untuk cleanup files sebelum deploy ke server
# HATI-HATI: File akan dihapus permanen!

echo "🗑️  Cleaning up files before deployment..."
echo ""
echo "⚠️  WARNING: This will permanently delete temporary files!"
echo "Press Ctrl+C to cancel, or Enter to continue..."
read

DELETED=0

echo ""
echo "Deleting temporary files..."

# 1. Migration scripts
rm -f migrate_rename_tables*.sql && echo "  ❌ migrate_rename_tables*.sql" && DELETED=$((DELETED+3))
rm -f rename_tbltransaksi_to_tblstatusperforma.sql && echo "  ❌ rename_tbltransaksi_to_tblstatusperforma.sql" && DELETED=$((DELETED+1))
rm -f fix_views_procedures_triggers.sql && echo "  ❌ fix_views_procedures_triggers.sql" && DELETED=$((DELETED+1))
rm -f fix_id_user_datatype.sql && echo "  ❌ fix_id_user_datatype.sql" && DELETED=$((DELETED+1))
rm -f fix_column_names_final*.sql && echo "  ❌ fix_column_names_final*.sql" && DELETED=$((DELETED+2))

# 2. Old SQL files
rm -f create_kpi_tables.sql && echo "  ❌ create_kpi_tables.sql" && DELETED=$((DELETED+1))
rm -f create_kpi_views.sql && echo "  ❌ create_kpi_views.sql" && DELETED=$((DELETED+1))
rm -f create_kpi_procedures.sql && echo "  ❌ create_kpi_procedures.sql" && DELETED=$((DELETED+1))

# 3. Troubleshooting files
rm -f fix_kpi_data_import.sql && echo "  ❌ fix_kpi_data_import.sql" && DELETED=$((DELETED+1))
rm -f populate_kpi_from_real_data.sql && echo "  ❌ populate_kpi_from_real_data.sql" && DELETED=$((DELETED+1))
rm -f verify_kpi_ready.sql && echo "  ❌ verify_kpi_ready.sql" && DELETED=$((DELETED+1))
rm -f check_session_menu.php && echo "  ❌ check_session_menu.php" && DELETED=$((DELETED+1))
rm -f force_refresh_menu_session.php && echo "  ❌ force_refresh_menu_session.php" && DELETED=$((DELETED+1))

# 4. Old backups
rm -f dump-iresis-prod-*.sql && echo "  ❌ dump-iresis-prod-*.sql" && DELETED=$((DELETED+1))
rm -f patch.sql && echo "  ❌ patch.sql" && DELETED=$((DELETED+1))

# 5. Shell scripts
rm -f install_kpi.sh && echo "  ❌ install_kpi.sh" && DELETED=$((DELETED+1))
rm -f cleanup_sql_files.sh && echo "  ❌ cleanup_sql_files.sh" && DELETED=$((DELETED+1))
rm -f cleanup_for_server.sh && echo "  ❌ cleanup_for_server.sh" && DELETED=$((DELETED+1))
rm -f delete_before_push.sh && echo "  ❌ delete_before_push.sh" && DELETED=$((DELETED+1))
rm -f beverra-siresi-windows.bat && echo "  ❌ beverra-siresi-windows.bat" && DELETED=$((DELETED+1))

# 6. Composer zone identifier
rm -f composer.jsonZone.Identifier && echo "  ❌ composer.jsonZone.Identifier" && DELETED=$((DELETED+1))

# 7. Documentation (uncomment jika mau hapus)
# rm -f INTEGRATE_REAL_KPI_DATA.md && echo "  ❌ INTEGRATE_REAL_KPI_DATA.md" && DELETED=$((DELETED+1))
# rm -f KPI_DASHBOARD_IMPLEMENTATION.md && echo "  ❌ KPI_DASHBOARD_IMPLEMENTATION.md" && DELETED=$((DELETED+1))
# rm -f KPI_REPORTS_IMPLEMENTATION.md && echo "  ❌ KPI_REPORTS_IMPLEMENTATION.md" && DELETED=$((DELETED+1))
# rm -f KPI_SYSTEM_DOCUMENTATION.md && echo "  ❌ KPI_SYSTEM_DOCUMENTATION.md" && DELETED=$((DELETED+1))
# rm -f FINAL_TABLE_STRUCTURE.md && echo "  ❌ FINAL_TABLE_STRUCTURE.md" && DELETED=$((DELETED+1))
# rm -f DEPLOY_CHECKLIST.md && echo "  ❌ DEPLOY_CHECKLIST.md" && DELETED=$((DELETED+1))
# rm -f DEPLOY_TO_SERVER.md && echo "  ❌ DEPLOY_TO_SERVER.md" && DELETED=$((DELETED+1))
# rm -f RENAME_TABLES_GUIDE.md && echo "  ❌ RENAME_TABLES_GUIDE.md" && DELETED=$((DELETED+1))

# 8. Log files lama (2024)
LOG_COUNT=$(ls application/logs/log-2024-*.php 2>/dev/null | wc -l)
if [ $LOG_COUNT -gt 0 ]; then
    rm -f application/logs/log-2024-*.php
    echo "  ❌ Deleted $LOG_COUNT log files (2024)"
    DELETED=$((DELETED+LOG_COUNT))
fi

echo ""
echo "✅ Cleanup completed!"
echo "   Total files deleted: $DELETED"
echo ""
echo "📦 Files ready for deployment:"
echo "   ✅ application/ (updated models)"
echo "   ✅ assets/"
echo "   ✅ system/"
echo "   ✅ composer.json & composer.lock"
echo "   ✅ index.php, .htaccess, etc"
echo ""
echo "📄 SQL Files to run on server:"
echo "   1. create_kpi_tables_v2.sql"
echo "   2. final_kpi_parent_menu.sql"
echo "   3. fix_kpi_menu_access.sql"
echo ""
echo "🚀 Ready to deploy!"
echo ""

# Self-delete
rm -f cleanup_before_deploy.sh


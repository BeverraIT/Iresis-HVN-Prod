<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'welcome/page_notfound';
$route['translate_uri_dashes'] = FALSE;

$route['auth'] = 'login/auth';

$route['logout'] = 'welcome/logout';

$route['receipt/save-receipt'] = 'receipt/save_receipt';
$route['receipt/detail-receipt'] = 'receipt/detail_receipt';
$route['receipt/get-list-receipt-data'] = 'receipt/get_list_receipt_data';
$route['receipt/delete-list-receipt-data/(:any)'] = 'receipt/delete_list_receipt_data/$1';
$route['receipt/delete-receipt-action'] = 'receipt/delete_receipt_action';
$route['receipt/save-reprint-receipt'] = 'receipt/save_reprint_receipt';
$route['receipt/upload-receipt-action'] = 'receipt/upload_receipt_action';

$route['picker/scan-picker'] = 'picker/scan_picker';
$route['picker/save-scan-picker'] = 'picker/save_scan_picker';
$route['picker/search_picker'] = 'picker/search_picker';
$route['picker/get-search-picker-data'] = 'picker/get_search_picker_data';
$route['picker/master_picker'] = 'picker/master_picker';
$route['picker/save-master-picker'] = 'picker/save_master_picker';
$route['picker/delete_master_picker'] = 'picker/delete_master_picker';
$route['picker/pending_picker'] = 'picker/pending_picker';
$route['picker/save-pending-picker'] = 'picker/save_pending_picker';
$route['picker/update_picker'] = 'picker/update_picker';
$route['picker/save-update-picker'] = 'picker/save_update_picker';
$route['picker/kurangan-picker'] = 'picker/kurangan_picker';
$route['picker/save-kurangan-picker'] = 'picker/save_kurangan_picker';
$route['picker/get-kurangan-picker-data'] = 'picker/get_kurangan_picker_data';
$route['picker/get-kurangan-picker-data/(:any)'] = 'picker/get_kurangan_picker_data/$1';

$route['packer/scan-packer'] = 'packer/scan_packer';
$route['packer/save-packer'] = 'packer/save_packer';
$route['packer/get-data-packer'] = 'packer/get_data_packer';
$route['packer/get-scan-packer-data/(:any)'] = 'packer/get_scan_packer_data/$1';
$route['packer/masalah-picker-save'] = 'packer/masalah_picker_save';

$route['handover/save-handover'] = 'handover/save_handover';
$route['handover/get-data-handover'] = 'handover/get_data_handover';

$route['retur/scan-retur'] = 'retur/scan_retur';
$route['retur/save-retur'] = 'retur/save_retur';
$route['retur/save-buka-retur'] = 'retur/save_buka_retur';
$route['retur/search-retur'] = 'retur/search_retur';
$route['retur/get-data-retur'] = 'retur/get_data_retur';
$route['retur/laporan-retur'] = 'retur/laporan_retur';
$route['retur/get-data-terima-retur-laporan'] = 'retur/get_data_terima_retur_laporan';
$route['retur/get-data-buka-retur-laporan'] = 'retur/get_data_buka_retur_laporan';
$route['retur/export-excel-terima-retur'] = 'retur/export_excel_terima_retur';
$route['retur/export-excel-buka-retur'] = 'retur/export_excel_buka_retur';
$route['retur/complain'] = 'retur/complain';
$route['retur/save-refund-complain'] = 'retur/save_refund_complain';
$route['retur/save-replacement-complain'] = 'retur/save_replacement_complain';
$route['retur/get-complain-data'] = 'retur/get_complain_data';
$route['retur/get-receipt-info'] = 'retur/get_receipt_info';
$route['retur/update-complain-status'] = 'retur/update_complain_status';

$route['report/get-receipt-in-process-data-tab0'] = 'report/get_receipt_in_process_data_tab0';
$route['report/get-receipt-in-process-data-tab1'] = 'report/get_receipt_in_process_data_tab1';
$route['report/get-receipt-in-process-data-tab2'] = 'report/get_receipt_in_process_data_tab2';
$route['report/export-to-excel-receipt-in-process-tab0'] = 'report/export_to_excel_receipt_in_process_tab0';
$route['report/export-to-excel-receipt-in-process-tab1'] = 'report/export_to_excel_receipt_in_process_tab1';
$route['report/export-to-excel-receipt-in-process-tab2'] = 'report/export_to_excel_receipt_in_process_tab2';
$route['report/daily-receipt-report'] = 'report/daily_receipt_report';
$route['report/get-daily-receipt-report-data'] = 'report/get_daily_receipt_report_data';
$route['report/export-to-excel-daily-receipt-report'] = 'report/export_to_excel_daily_receipt_report';
$route['report/per-day-receipt-report'] = 'report/per_day_receipt_report';
$route['report/get-per-day-receipt-report-data'] = 'report/get_per_day_receipt_report_data';
$route['report/export-to-excel-per-day-receipt-report'] = 'report/export_to_excel_per_day_receipt_report';
$route['report/get-receipt-report-data-tab0'] = 'report/get_receipt_report_data_tab0';
$route['report/get-receipt-report-data-tab1'] = 'report/get_receipt_report_data_tab1';
$route['report/export-to-excel-receipt-report-tab0'] = 'report/export_to_excel_receipt_report_tab0';
$route['report/export-to-excel-receipt-report-tab1'] = 'report/export_to_excel_receipt_report_tab1';
$route['report/shipped-receipt-report'] = 'report/shipped_receipt_report';
$route['report/get-shipped-receipt-report-data'] = 'report/get_shipped_receipt_report_data';
$route['report/export-to-excel-shipped-receipt-report'] = 'report/export_to_excel_shipped_receipt_report';
$route['report/shipping-report'] = 'report/shipping_report';
$route['report/export-to-excel-shipping-report'] = 'report/export_to_excel_shipping_report';
$route['report/retur-receipt-report'] = 'report/retur_receipt_report';
$route['report/get-retur-receipt-report-data'] = 'report/get_retur_receipt_report_data';
$route['report/export-to-excel-retur-receipt-report'] = 'report/export_to_excel_retur_receipt_report';
$route['report/get-terima-retur-report-data'] = 'report/get_terima_retur_report_data';
$route['report/get-buka-retur-report-data'] = 'report/get_buka_retur_report_data';
$route['report/export-to-excel-terima-retur-report'] = 'report/export_to_excel_terima_retur_report';
$route['report/export-to-excel-buka-retur-report'] = 'report/export_to_excel_buka_retur_report';
$route['report/delete-terima-retur/(:num)'] = 'report/delete_terima_retur/$1';
$route['report/delete-buka-retur/(:num)'] = 'report/delete_buka_retur/$1';
$route['report/get-production-team-report-data-tab0'] = 'report/get_production_team_report_data_tab0';
$route['report/get-production-team-report-data-tab1'] = 'report/get_production_team_report_data_tab1';
$route['report/export-to-excel-production-team-report-tab0'] = 'report/export_to_excel_production_team_report_tab0';
$route['report/export-to-excel-production-team-report-tab1'] = 'report/export_to_excel_production_team_report_tab1';

$route['kpi_reports'] = 'kpi_reports';
$route['kpi_reports/export'] = 'kpi_reports/export';
$route['kpi_reports/get-kpi-data'] = 'kpi_reports/get_kpi_data';
$route['kpi_reports/export-to-excel'] = 'kpi_reports/export_to_excel';
$route['kpi_reports/export-excel-picker'] = 'kpi_reports/export_excel_picker';
$route['kpi_reports/export-excel-packer'] = 'kpi_reports/export_excel_packer';

$route['kpi/dashboard'] = 'kpi_reports/dashboard';
$route['kpi/dashboard-picker'] = 'kpi_reports/dashboard_picker';
$route['kpi/dashboard-packer'] = 'kpi_reports/dashboard_packer';
$route['kpi/target-kpi'] = 'target_kpi/index';
$route['target_kpi/save-targets'] = 'target_kpi/save_targets';
$route['target_kpi/update-target'] = 'target_kpi/update_target';
$route['target_kpi/delete-target'] = 'target_kpi/delete_target';
$route['target_kpi/copy-targets'] = 'target_kpi/copy_targets';

$route['user'] = 'user';
$route['user/add_user'] = 'user/edit_user';
$route['user/edit_user/(:any)'] = 'user/edit_user/$1';
$route['user/generate_password_user/(:any)'] = 'user/generate_password_user/$1';
$route['user/delete_user/(:any)'] = 'user/delete_user/$1';
$route['user/save_user'] = 'user/save_user';
$route['user/update_password_user'] = 'user/update_password_user';

$route['menu/add-menu'] = 'menu/edit_menu';
$route['menu/edit-menu/(:any)'] = 'menu/edit_menu/$1';
$route['menu/delete-menu/(:any)'] = 'menu/delete_menu/$1';
$route['menu/save-menu'] = 'menu/save_menu';

$route['access/save-access'] = 'access/save_access';
$route['access/edit-access/(:any)'] = 'access/edit_access/$1';

$route['cs/laporan-kurangan-picker'] = 'cs/laporan_kurangan_picker';
$route['cs/get-laporan-kurangan-picker-data'] = 'cs/get_laporan_kurangan_picker_data';
$route['cs/export-excel-laporan-kurangan-picker'] = 'cs/export_excel_laporan_kurangan_picker';
$route['cs/retur-complain'] = 'cs/retur_complain';
$route['cs/get-retur-complain-data'] = 'cs/get_retur_complain_data';
$route['cs/export-excel-retur-complain'] = 'cs/export_excel_retur_complain';
$route['cs/submit-kurangan-picker'] = 'cs/submit_kurangan_picker';
$route['cs/masalah-picker'] = 'cs/masalah_picker';
$route['cs/get-masalah-picker-data'] = 'cs/get_masalah_picker_data';
$route['cs/get-detail-masalah-picker'] = 'cs/get_detail_masalah_picker';
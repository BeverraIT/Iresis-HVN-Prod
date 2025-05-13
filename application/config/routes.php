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

$route['add-user'] = 'user/edit';
$route['save-user'] = 'user/save';
$route['edit-user-(:num)'] = 'user/edit/$1';
$route['gen-pass-user-(:num)'] = 'user/generate_password/$1';
$route['update-user-password'] = 'user/update_password';
$route['delete-user-(:num)'] = 'user/delete/$1';

$route['add-menu'] = 'menu/edit';
$route['save-menu'] = 'menu/save';
$route['edit-menu-(:num)'] = 'menu/edit/$1';
$route['delete-menu-(:num)'] = 'menu/delete/$1';

$route['edit-access-(:num)'] = 'access/edit/$1';
$route['save-access'] = 'access/save';

$route['logout'] = 'welcome/logout';

$route['receipt/scan_receipt'] = 'receipt/scan_receipt';
$route['receipt/save_receipt'] = 'receipt/save_receipt';
$route['receipt/detail_receipt'] = 'receipt/detail_receipt';
$route['receipt/list_receipt'] = 'receipt/list_receipt';
$route['receipt/get_list_receipt_data'] = 'receipt/get_list_receipt_data';
$route['receipt/delete_receipt'] = 'receipt/delete_receipt';
$route['receipt/delete_receipt_action'] = 'receipt/delete_receipt_action';
$route['receipt/reprint_receipt'] = 'receipt/reprint_receipt';
$route['receipt/save_reprint_receipt'] = 'receipt/save_reprint_receipt';
$route['receipt/upload_reprint_receipt_file'] = 'receipt/upload_reprint_receipt_file';
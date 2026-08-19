<?php
/**
 * Pembungkus CLI untuk controller Audit_berat.
 *
 * Pakai:
 *   php audit_berat.php        (default: minimal 2 sampel per SKU)
 *   php audit_berat.php 3      (minimal 3 sampel per SKU, hasil lebih meyakinkan)
 */

if (PHP_SAPI !== 'cli') {
    exit('Hanya untuk baris perintah.');
}

putenv('AUDIT_MIN_SAMPEL=' . (string) ($argv[1] ?? 2));

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SCRIPT_NAME'] = 'index.php';

$_SERVER['argv'] = ['index.php', 'audit_berat', 'index'];
$argv            = $_SERVER['argv'];
$argc            = count($argv);

require __DIR__ . '/index.php';

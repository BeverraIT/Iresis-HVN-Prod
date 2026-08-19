<?php
/**
 * Pembungkus CLI untuk controller Resync.
 *
 * Path file Windows tidak bisa dilewatkan sebagai segmen URI CodeIgniter
 * (backslash dan spasi ditolak permitted_uri_chars), jadi path dikirim lewat
 * environment variable dan argv ditulis ulang jadi segmen yang aman.
 *
 * Pakai:
 *   php resync.php periksa  "C:\Users\user\Downloads\export.xlsx"
 *   php resync.php terapkan "C:\Users\user\Downloads\export.xlsx" 118
 */

if (PHP_SAPI !== 'cli') {
    exit('Hanya untuk baris perintah.');
}

$perintah = $argv[1] ?? null;

$bantuan = function () {
    echo "\n";
    echo "  php resync.php periksa   \"<file.xlsx>\"                  -> laporan dampak, tidak menulis apa pun\n";
    echo "  php resync.php terapkan  \"<file.xlsx>\" <id_user>        -> jalankan import beneran\n";
    echo "  php resync.php tersangka                                -> daftar resi yang qty-nya patut dicurigai\n";
    echo "  php resync.php qty <noresi> <sku> <jumlah> <id_user>    -> koreksi qty satu baris detail\n";
    echo "\n";
    exit(1);
};

if (!in_array($perintah, ['periksa', 'terapkan', 'tersangka', 'qty'], true)) {
    $bantuan();
}

if ($perintah === 'qty') {
    if (count($argv) < 6) {
        echo "ERROR: perintah qty butuh 4 argumen.\n";
        $bantuan();
    }

    putenv('RESYNC_NORESI=' . $argv[2]);
    putenv('RESYNC_SKU=' . $argv[3]);
    putenv('RESYNC_JUMLAH=' . $argv[4]);
    putenv('RESYNC_USER=' . $argv[5]);
} elseif ($perintah === 'periksa' || $perintah === 'terapkan') {
    $file    = $argv[2] ?? null;
    $id_user = $argv[3] ?? null;

    if (!$file) {
        $bantuan();
    }

    if (!is_file($file)) {
        echo "ERROR: file tidak ditemukan -> $file\n";
        echo "Ganti <file.xlsx> dengan path file yang sebenarnya, atau seret file ke periksa.bat\n";
        exit(1);
    }

    putenv('RESYNC_FILE=' . $file);
    putenv('RESYNC_USER=' . (string) $id_user);
}

// CodeIgniter membaca dua nilai ini walaupun dijalankan dari CLI.
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SCRIPT_NAME'] = 'index.php';

// Segmen URI dibuat bersih supaya lolos permitted_uri_chars.
$_SERVER['argv'] = ['index.php', 'resync', $perintah];
$argv            = $_SERVER['argv'];
$argc            = count($argv);

require __DIR__ . '/index.php';

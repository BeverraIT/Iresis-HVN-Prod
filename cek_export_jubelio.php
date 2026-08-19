<?php
/**
 * Diagnostik: cek apa yang SEBENARNYA ada di file export Jubelio untuk 1 nomor resi.
 *
 * Pakai:
 *   C:\xampp\php\php.exe cek_export_jubelio.php "<file>" [noresi]
 *
 * Output menjawab pertanyaan kunci:
 *   - Jubelio mengekspor 4 baris (qty 1,1,1,1) atau cuma 1 baris (qty 1)?
 *     -> 4 baris  = data hilang di iresis (baris ke-2..4 di-skip saat import)
 *     -> 1 baris  = data sudah hilang di sisi export Jubelio
 *
 * File ini cuma alat bantu sekali pakai, aman dihapus setelah selesai.
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file   = $argv[1] ?? null;
$noresi = $argv[2] ?? null;

if (!$file) {
    echo "Pakai: C:\\xampp\\php\\php.exe cek_export_jubelio.php \"<file>\" [noresi]\n";
    echo "Contoh: C:\\xampp\\php\\php.exe cek_export_jubelio.php \"C:\\Users\\user\\Downloads\\export.xlsx\" JY1312613006\n";
    exit(1);
}

if (is_dir($file)) {
    echo "ERROR: yang Anda kasih itu FOLDER, bukan file -> $file\n";
    exit(1);
}

if (!is_file($file)) {
    echo "ERROR: file tidak ditemukan -> $file\n";
    exit(1);
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$size = filesize($file);

echo "=========================================================\n";
echo "File      : $file\n";
echo "Ekstensi  : ." . ($ext ?: '(tidak ada)') . "\n";
echo "Ukuran    : " . number_format($size) . " byte\n";
echo "=========================================================\n\n";

// Jaga-jaga kalau yang ke-drag malah skripnya sendiri.
if (in_array($ext, ['bat', 'php', 'exe', 'lnk', 'sql', 'md', 'txt', 'pdf', 'png', 'jpg'], true)) {
    echo "*** SALAH FILE ***\n";
    echo "Ekstensi '.$ext' bukan file export Jubelio.\n";
    echo "Yang harus di-drag itu file hasil DOWNLOAD DARI JUBELIO (.xlsx / .xls / .csv),\n";
    echo "bukan file skrip ini dan bukan file lain di folder project.\n";
    exit(1);
}

// Intip isi mentahnya dulu -- ini yang menentukan file itu sebenarnya apa.
$fh = fopen($file, 'rb');
$head = fread($fh, 512);
fclose($fh);

$preview = preg_replace('/[^\x20-\x7E]/', '.', substr($head, 0, 200));
echo "--- 200 byte pertama (mentah) ---\n$preview\n\n";

$signature = 'tidak dikenali';
if (strncmp($head, "PK\x03\x04", 4) === 0)                        $signature = 'ZIP -> kemungkinan .xlsx / .ods asli';
elseif (strncmp($head, "\xD0\xCF\x11\xE0", 4) === 0)              $signature = 'OLE2 -> .xls lama asli';
elseif (stripos($head, '<html') !== false
     || stripos($head, '<table') !== false
     || stripos($head, '<?xml') !== false)                        $signature = 'HTML/XML -> file .xls PALSU (sering dipakai export marketplace)';
elseif (preg_match('/^[^\r\n]*[,;\t][^\r\n]*(\r?\n|$)/', $head))   $signature = 'teks berpemisah -> kemungkinan CSV';

echo "Tebakan format berdasar isi: $signature\n\n";

// Coba semua reader satu per satu, jangan cuma andalkan identify().
$kandidat = ['Xlsx', 'Xls', 'Csv', 'Html', 'Ods', 'Xml', 'Slk', 'Gnumeric'];
$sheet = null;
$dipakai = null;

echo "--- Uji reader ---\n";
foreach ($kandidat as $tipe) {
    try {
        $reader = IOFactory::createReader($tipe);
        if (!$reader->canRead($file)) {
            echo "  $tipe : tidak cocok\n";
            continue;
        }
        $reader->setReadDataOnly(true);      // sama persis dengan Receipt.php
        $sheet = $reader->load($file)->getActiveSheet();
        $dipakai = $tipe;
        echo "  $tipe : BERHASIL dibaca\n";
        break;
    } catch (\Throwable $e) {
        echo "  $tipe : gagal (" . $e->getMessage() . ")\n";
    }
}

if ($sheet === null) {
    echo "\n*** TIDAK ADA READER YANG BISA MEMBACA FILE INI ***\n";
    echo "Kemungkinan: file rusak, masih terkunci Excel, atau bukan file spreadsheet.\n";
    echo "Solusi: buka file di Excel, lalu Save As -> 'Excel Workbook (*.xlsx)', jalankan ulang.\n";
    exit(1);
}

echo "\nReader terpakai: $dipakai\n";

if ($dipakai !== 'Xlsx') {
    echo "\n*** PERHATIAN ***\n";
    echo "Receipt.php memakai IOFactory::createReader('Xlsx') secara hardcoded.\n";
    echo "File yang cuma bisa dibaca reader '$dipakai' TIDAK akan bisa diupload ke iresis.\n";
    echo "Harus di-Save As sebagai .xlsx dulu sebelum upload.\n";
}

$rows = $sheet->toArray(null, true, true, true);

echo "\nTotal baris    : " . count($rows) . "\n";
echo "Kolom tertinggi: " . $sheet->getHighestColumn() . "\n\n";

echo "--- HEADER (baris 1) ---\n";
foreach ($rows[1] ?? [] as $col => $val) {
    echo "  $col = " . var_export($val, true) . "\n";
}

if ($noresi) {
    echo "\n--- SEMUA BARIS UNTUK RESI $noresi ---\n";
    $found = 0;
    $total_qty = 0;
    foreach ($rows as $rn => $r) {
        if (trim((string) ($r['B'] ?? '')) !== $noresi) continue;
        $found++;
        $total_qty += (int) ($r['Q'] ?? 0);
        printf(
            "  baris %-5d | A(no_pesanan)=%-32s | P(sku)=%-14s | Q(qty)=%-5s | R(rak)=%-14s | T(status)=%s\n",
            $rn,
            (string) ($r['A'] ?? ''),
            (string) ($r['P'] ?? ''),
            (string) ($r['Q'] ?? ''),
            (string) ($r['R'] ?? ''),
            (string) ($r['T'] ?? '')
        );
    }

    echo "\n  >> Jumlah baris ditemukan : $found\n";
    echo "  >> Total qty (kolom Q)    : $total_qty\n";

    if ($found === 0) {
        echo "  >> KESIMPULAN: resi ini TIDAK ADA di file. Salah file / salah rentang tanggal export.\n";
    } elseif ($found === 1 && $total_qty <= 1) {
        echo "  >> KESIMPULAN: Jubelio cuma kirim 1 baris qty 1 -> data hilang di SISI EXPORT JUBELIO.\n";
    } else {
        echo "  >> KESIMPULAN: file berisi $found baris (total qty $total_qty) -> data hilang di SISI IRESIS.\n";
    }
}

echo "\n--- BARIS DENGAN no_pesanan / noresi / sku KOSONG (akan DI-SKIP oleh insert_receipt) ---\n";
$skipped = 0;
foreach ($rows as $rn => $r) {
    if ($rn === 1) continue;
    $a = $r['A'] ?? null; $b = $r['B'] ?? null; $p = $r['P'] ?? null;
    if (!$a || !$b || !$p) {
        $skipped++;
        if ($skipped <= 20) {
            printf("  baris %-5d | A=%s | B=%s | P=%s | Q=%s\n", $rn, var_export($a, true), var_export($b, true), var_export($p, true), var_export($r['Q'] ?? null, true));
        }
    }
}
echo "  >> Total baris ter-skip : $skipped\n";
if ($skipped > 0) {
    echo "  >> Kalau angka ini besar, berarti banyak item hilang saat import (merge cell / kolom kosong).\n";
}

echo "\n--- CEK DUPLIKAT (noresi + no_pesanan + sku muncul >1 kali) ---\n";
$keys = [];
foreach ($rows as $rn => $r) {
    if ($rn === 1) continue;
    $k = ($r['B'] ?? '') . '|' . ($r['A'] ?? '') . '|' . ($r['P'] ?? '');
    if ($k === '||') continue;
    $keys[$k][] = ['row' => $rn, 'qty' => (int) ($r['Q'] ?? 0)];
}
$dup = array_filter($keys, function ($v) { return count($v) > 1; });
echo "  >> Jumlah kombinasi duplikat: " . count($dup) . "\n";
$n = 0;
foreach ($dup as $k => $v) {
    echo "     $k -> " . count($v) . " baris, qty: " . implode(',', array_column($v, 'qty'))
        . " (total " . array_sum(array_column($v, 'qty')) . ")\n";
    if (++$n >= 15) { echo "     ...\n"; break; }
}
if (count($dup) === 0) {
    echo "  >> Tidak ada duplikat sama sekali -> Jubelio sudah meng-group per SKU sebelum export.\n";
}

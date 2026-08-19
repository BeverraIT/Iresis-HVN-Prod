<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Alat baris perintah untuk menyegarkan detail resi dari file export Jubelio.
 *
 * Dipakai untuk dua hal:
 *   1. Memeriksa dampak sebuah file SEBELUM diupload (mode periksa, tidak menulis apa pun).
 *   2. Backfill data lama dalam jumlah besar, tanpa lewat form upload yang mudah timeout.
 *
 * Pakai:
 *   php index.php resync periksa "D:\export.xlsx"
 *   php index.php resync terapkan "D:\export.xlsx" 118
 *
 * Mode "periksa" adalah default yang aman. Menulis ke database hanya terjadi
 * kalau perintah "terapkan" dipanggil secara eksplisit.
 */
class Resync extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_cli()) {
            show_404();
        }

        $this->load->model('receipt_fcd');

        ini_set('memory_limit', '3072M');
        set_time_limit(0);
    }

    public function index()
    {
        $this->_usage();
    }

    private function _usage()
    {
        echo "\n";
        echo "  php resync.php periksa   \"<file.xlsx>\"                  -> laporan dampak, tidak menulis apa pun\n";
        echo "  php resync.php terapkan  \"<file.xlsx>\" <id_user>        -> jalankan import beneran\n";
        echo "  php resync.php tersangka                                -> daftar resi yang qty-nya patut dicurigai\n";
        echo "  php resync.php qty <noresi> <sku> <jumlah> <id_user>    -> koreksi qty satu baris detail\n";
        echo "\n";
    }

    /**
     * Daftar resi yang qty-nya patut dicurigai kurang tercatat.
     *
     * Buktinya diambil dari timbangan: kalau berat paket mendekati kelipatan bulat dari
     * berat standar yang dihitung iresis, kemungkinan besar jumlah barangnya lebih banyak
     * daripada yang tercatat. Ini dipakai supaya pengecekan ke Jubelio terarah, bukan
     * membuka semua pesanan satu per satu.
     */
    public function tersangka()
    {
        $sql = "
            SELECT p.noresi, p.nomorpicklist, m.nama_marketplace, p.status_pesanan,
                   d.no_pesanan, d.sku, d.jumlah,
                   t.berat_aktual, t.berat_standar, t.berat_kemasan,
                   (t.berat_aktual - t.berat_kemasan)
                       / NULLIF(t.berat_standar - t.berat_kemasan, 0) AS rasio,
                   (SELECT COUNT(*) FROM tbldetailprintresi x WHERE x.id_resi = t.id_resi) AS jml_sku,
                   DATE(t.tanggal_timbangan) AS tgl
            FROM tbltimbangan t
            JOIN tblprintresi p ON p.id_printresi = t.id_resi
            LEFT JOIN tblmarketplace m ON m.id_marketplace = p.id_marketplace
            JOIN tbldetailprintresi d ON d.id_resi = t.id_resi
            WHERE t.status = 'OVER'
              AND t.sku_tanpa_master = 0
              AND (t.berat_standar - t.berat_kemasan) > 5
              AND (t.berat_aktual - t.berat_kemasan)
                    / NULLIF(t.berat_standar - t.berat_kemasan, 0) >= 1.8
              AND p.status_pesanan <> 'COMPLETED'
            ORDER BY rasio DESC
        ";

        $baris = $this->db->query($sql)->result_array();

        echo "\n";
        echo "DAFTAR RESI YANG QTY-NYA PATUT DICURIGAI\n";
        echo "Bukti dari timbangan: berat paket jauh di atas hitungan iresis.\n";
        echo "Resi COMPLETED tidak ditampilkan karena sudah tidak bisa dikoreksi.\n";
        echo str_repeat('=', 112) . "\n\n";

        if (empty($baris)) {
            echo "  Tidak ada. Semua resi yang tidak lolos timbang sudah terjelaskan sebab lain.\n\n";
            return;
        }

        printf("  %-20s %-12s %-22s %-6s %-6s %8s %10s\n",
            'NO RESI', 'MARKETPLACE', 'SKU', 'qty', 'usulan', 'rasio', 'picklist');
        echo '  ' . str_repeat('-', 104) . "\n";

        $bisa_diusulkan = 0;
        foreach ($baris as $r) {
            $rasio = (float) $r['rasio'];

            // Usulan qty hanya masuk akal kalau resi berisi satu jenis SKU. Untuk resi
            // multi-SKU, berat berlebih tidak bisa diatribusikan ke SKU tertentu.
            $usulan = '-';
            if ((int) $r['jml_sku'] === 1) {
                $bulat = (int) round($rasio);
                if ($bulat >= 2 && abs($rasio - $bulat) <= 0.15) {
                    $usulan = (string) $bulat;
                    $bisa_diusulkan++;
                }
            }

            printf("  %-20s %-12s %-22s %-6s %-6s %8.2f %10s\n",
                $r['noresi'], substr((string) $r['nama_marketplace'], 0, 12),
                substr($r['sku'], 0, 22), $r['jumlah'], $usulan, $rasio, $r['nomorpicklist']);
        }

        echo "\n  Total tersangka        : " . count($baris) . " baris\n";
        echo "  Punya usulan qty jelas : $bisa_diusulkan\n";
        echo "\n";
        echo "  Langkah berikutnya: buka nomor pesanannya di Jubelio, hitung jumlah baris SKU-nya,\n";
        echo "  lalu koreksi dengan:\n";
        echo "    php resync.php qty <noresi> <sku> <jumlah_sebenarnya> <id_user>\n";
        echo "\n  Kolom 'usulan' hanya perkiraan dari berat. Tetap cocokkan ke Jubelio dulu.\n\n";
    }

    /**
     * Baca file export dan bandingkan dengan isi database. Tidak menulis apa pun.
     */
    public function periksa($file = null)
    {
        $file = $file ?: getenv('RESYNC_FILE');

        if (!$file || !is_file($file)) {
            echo "ERROR: file tidak ditemukan -> " . var_export($file, true) . "\n";
            $this->_usage();
            return;
        }

        $agregat = $this->_baca_file($file);
        if ($agregat === null) {
            return;
        }

        echo "File           : $file\n";
        echo "Baris terpakai : {$agregat['dipakai']}\n";
        echo "Baris dilewati : {$agregat['skip_no_resi']} (belum ada resi) + {$agregat['skip_invalid']} (sku/no_pesanan kosong)\n";
        echo "Kombinasi unik : " . count($agregat['map']) . "\n\n";

        $this->_laporan_qty_per_marketplace($agregat['marketplace']);

        $noresi_list = array_values(array_unique(array_map(
            function ($k) { return explode('|', $k)[0]; },
            array_keys($agregat['map'])
        )));

        // Status resi di database menentukan apa yang akan terjadi
        $status_map = [];
        $id_map = [];
        foreach (array_chunk($noresi_list, 1000) as $chunk) {
            $this->db->select('id_printresi, noresi, status_pesanan');
            $this->db->from('tblprintresi');
            $this->db->where_in('noresi', $chunk);
            foreach ($this->db->get()->result() as $row) {
                $status_map[$row->noresi] = strtoupper(trim($row->status_pesanan ?? ''));
                $id_map[$row->noresi] = $row->id_printresi;
            }
        }

        // Detail lama, dikunci sama seperti proses import
        $detail_lama = [];
        if (!empty($id_map)) {
            foreach (array_chunk(array_values($id_map), 1000) as $chunk) {
                $this->db->select('id_resi, no_pesanan, sku, jumlah, no_rak, qty_manual');
                $this->db->from('tbldetailprintresi');
                $this->db->where_in('id_resi', $chunk);
                foreach ($this->db->get()->result() as $row) {
                    $detail_lama[$row->id_resi . '|' . $row->no_pesanan . '|' . $row->sku] = $row;
                }
            }
        }

        $resi_baru = [];
        $resi_completed = [];
        $qty_berubah = [];
        $qty_dilindungi = [];
        $detail_baru = [];
        $detail_hilang = [];

        foreach ($agregat['map'] as $key => $baris) {
            $noresi = $baris['noresi'];

            if (!isset($id_map[$noresi])) {
                $resi_baru[$noresi] = true;
                continue;
            }

            if (($status_map[$noresi] ?? '') === 'COMPLETED') {
                $resi_completed[$noresi] = true;
                continue;
            }

            $kunci_db = $id_map[$noresi] . '|' . $baris['no_pesanan'] . '|' . $baris['sku'];

            if (!isset($detail_lama[$kunci_db])) {
                $detail_baru[] = "$noresi / {$baris['sku']} qty {$baris['jumlah']}";
                continue;
            }

            $lama = $detail_lama[$kunci_db];
            if ((int) $lama->jumlah !== (int) $baris['jumlah']) {
                if ((int) $lama->qty_manual === 1) {
                    // Sudah dikoreksi manual, kebal dari penimpaan. Ditampilkan terpisah
                    // supaya tidak terbaca sebagai perubahan yang akan terjadi.
                    $qty_dilindungi[] = sprintf(
                        "  %-20s %-24s tetap %s (file berisi %s)",
                        $noresi, $baris['sku'], $lama->jumlah, $baris['jumlah']
                    );
                } else {
                    $qty_berubah[] = sprintf(
                        "  %-20s %-24s qty %s -> %s",
                        $noresi, $baris['sku'], $lama->jumlah, $baris['jumlah']
                    );
                }
            }
            unset($detail_lama[$kunci_db]);
        }

        // Sisa di $detail_lama adalah baris yang tidak ada lagi di file, khusus resi yang tersentuh
        $noresi_by_id = array_flip($id_map);
        foreach ($detail_lama as $kunci => $row) {
            $noresi = $noresi_by_id[$row->id_resi] ?? null;
            if ($noresi === null) continue;
            if (isset($resi_completed[$noresi])) continue;
            $detail_hilang[] = "  $noresi / {$row->sku} qty {$row->jumlah}";
        }

        echo "=== DAMPAK KALAU FILE INI DIUPLOAD ===\n";
        echo "  Resi baru (insert)             : " . count($resi_baru) . "\n";
        echo "  Resi COMPLETED (dilewati)      : " . count($resi_completed) . "\n";
        echo "  Detail qty berubah             : " . count($qty_berubah) . "\n";
        echo "  Detail baru pada resi lama     : " . count($detail_baru) . "\n";
        echo "  Detail hilang dari file        : " . count($detail_hilang) . "\n";

        if (!empty($qty_dilindungi)) {
            echo "  Qty dikoreksi manual (dikunci) : " . count($qty_dilindungi) . "\n";
        }

        if (!empty($qty_berubah)) {
            echo "\n--- Perubahan qty (maks 40 baris) ---\n";
            foreach (array_slice($qty_berubah, 0, 40) as $baris) echo "$baris\n";
            if (count($qty_berubah) > 40) echo "  ... dan " . (count($qty_berubah) - 40) . " lainnya\n";
        }

        if (!empty($qty_dilindungi)) {
            echo "\n--- Qty terkunci, TIDAK akan ditimpa file (maks 20) ---\n";
            foreach (array_slice($qty_dilindungi, 0, 20) as $baris) echo "$baris\n";
            if (count($qty_dilindungi) > 20) echo "  ... dan " . (count($qty_dilindungi) - 20) . " lainnya\n";
        }

        if (!empty($detail_hilang)) {
            echo "\n--- Detail yang akan DIHAPUS karena tidak ada di file (maks 20) ---\n";
            foreach (array_slice($detail_hilang, 0, 20) as $baris) echo "$baris\n";
            if (count($detail_hilang) > 20) echo "  ... dan " . (count($detail_hilang) - 20) . " lainnya\n";
        }

        echo "\nTidak ada satu pun data yang diubah. Jalankan 'terapkan' kalau laporan ini sudah sesuai.\n";
    }

    /**
     * Jalankan import beneran lewat jalur yang sama dengan form upload.
     */
    public function terapkan($file = null, $id_user = null)
    {
        $file    = $file ?: getenv('RESYNC_FILE');
        $id_user = $id_user ?: getenv('RESYNC_USER');

        if (!$file || !is_file($file)) {
            echo "ERROR: file tidak ditemukan -> " . var_export($file, true) . "\n";
            $this->_usage();
            return;
        }

        if (!$id_user) {
            echo "ERROR: id_user wajib diisi supaya jejak admin_pegawai tetap tercatat.\n";
            $this->_usage();
            return;
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $dataRaw = $reader->load($file)->getActiveSheet()->toArray(null, true, true, true);

        echo "Memproses " . count($dataRaw) . " baris dari $file ...\n";

        $mulai = microtime(true);
        $hasil = $this->receipt_fcd->insert_receipt($dataRaw, $id_user);
        $durasi = round(microtime(true) - $mulai, 1);

        echo "$hasil\n";
        echo "Selesai dalam {$durasi} detik.\n";
    }

    /**
     * Koreksi qty satu baris detail resi secara manual.
     *
     * Dipakai selama qty Tokopedia dari export Jubelio masih salah. Perubahan dicatat ke
     * log dan menandai tblprintresi sebagai termodifikasi, supaya jejaknya tidak hilang.
     *
     * Catatan penting: nilai ini akan tertimpa lagi kalau file export yang qty-nya masih
     * salah diupload ulang untuk resi yang sama. Jadi koreksi manual bersifat sementara
     * sampai report Jubelio diperbaiki.
     */
    public function qty($noresi = null, $sku = null, $jumlah = null, $id_user = null)
    {
        $noresi  = $noresi  ?: getenv('RESYNC_NORESI');
        $sku     = $sku     ?: getenv('RESYNC_SKU');
        $jumlah  = $jumlah  !== null ? $jumlah : getenv('RESYNC_JUMLAH');
        $id_user = $id_user ?: getenv('RESYNC_USER');

        if (!$noresi || !$sku || $jumlah === '' || $jumlah === false || !$id_user) {
            echo "ERROR: lengkapi semua argumen.\n";
            $this->_usage();
            return;
        }

        if (!ctype_digit((string) $jumlah) || (int) $jumlah < 1) {
            echo "ERROR: jumlah harus bilangan bulat minimal 1. Diterima: " . var_export($jumlah, true) . "\n";
            return;
        }
        $jumlah = (int) $jumlah;

        $this->db->select('d.id_detail_resi, d.id_resi, d.no_pesanan, d.sku, d.jumlah, d.qty_kurang, p.status_pesanan');
        $this->db->from('tbldetailprintresi d');
        $this->db->join('tblprintresi p', 'p.id_printresi = d.id_resi');
        $this->db->where('p.noresi', $noresi);
        $this->db->where('d.sku', $sku);
        $baris = $this->db->get()->result_array();

        if (empty($baris)) {
            echo "ERROR: tidak ada baris detail untuk resi '$noresi' dengan SKU '$sku'.\n";
            echo "Cek ejaan SKU-nya, atau jalankan 'php resync.php tersangka' untuk melihat daftar.\n";
            return;
        }

        if (count($baris) > 1) {
            echo "ERROR: ada " . count($baris) . " baris dengan SKU sama pada resi ini. Koreksi manual dibatalkan\n";
            echo "supaya tidak salah sasaran. Laporkan kondisi ini, perlu ditangani terpisah.\n";
            return;
        }

        $b = $baris[0];

        if (strtoupper(trim((string) $b['status_pesanan'])) === 'COMPLETED') {
            echo "ERROR: resi ini sudah COMPLETED. Tidak dikoreksi supaya data pesanan yang\n";
            echo "sudah tutup buku tidak berubah.\n";
            return;
        }

        if ((int) $b['jumlah'] === $jumlah) {
            echo "Qty sudah bernilai $jumlah. Tidak ada yang diubah.\n";
            return;
        }

        // Ditandai manual supaya import berikutnya tidak mengembalikannya ke angka
        // dari file yang masih salah.
        $update = ['jumlah' => $jumlah, 'qty_manual' => 1];

        // Kurangan tidak boleh melebihi jumlah pesanan
        if ((int) $b['qty_kurang'] > $jumlah) {
            $update['qty_kurang'] = $jumlah;
        }

        $this->db->trans_start();

        $this->db->where('id_detail_resi', $b['id_detail_resi']);
        $this->db->update('tbldetailprintresi', $update);

        $this->db->where('id_printresi', $b['id_resi']);
        $this->db->update('tblprintresi', [
            'modified_at' => date('Y-m-d H:i:s'),
            'modified_by' => $id_user
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            echo "ERROR: gagal menyimpan, perubahan dibatalkan.\n";
            return;
        }

        $pesan = "koreksi qty manual: resi $noresi sku $sku "
               . "{$b['jumlah']} -> $jumlah oleh user $id_user";
        log_message('info', $pesan);

        echo "\n  BERHASIL\n";
        echo "  Resi    : $noresi\n";
        echo "  Pesanan : {$b['no_pesanan']}\n";
        echo "  SKU     : $sku\n";
        echo "  Qty     : {$b['jumlah']} -> $jumlah\n";

        $this->_tampilkan_berat_baru($b['id_resi']);

        echo "\n  Resi ini perlu DITIMBANG ULANG supaya status timbangannya ikut diperbarui.\n";
        echo "  Baris ini ditandai qty_manual, jadi upload berikutnya TIDAK akan menimpanya.\n\n";
    }

    /**
     * Tampilkan berat standar baru setelah qty dikoreksi, supaya operator langsung tahu
     * rentang ACCEPT yang seharusnya.
     */
    private function _tampilkan_berat_baru($id_resi)
    {
        $this->load->model('timbangan_fcd');

        $setting = $this->timbangan_fcd->get_setting();
        $items = $this->timbangan_fcd->get_resi_items($id_resi)->result_array();

        $berat = 0.0;
        $tanpa_master = 0;
        foreach ($items as $item) {
            $per_pcs = (float) $item['berat_standar'];
            if ($per_pcs <= 0) $tanpa_master++;
            $berat += $per_pcs * (int) $item['jumlah'];
        }

        $berat += (float) $setting['BERAT_KEMASAN'];
        $toleransi = (float) $setting['TOLERANSI_PERSEN'];
        $delta = $berat * $toleransi / 100;

        echo "\n  Berat standar baru : " . number_format($berat, 0, ',', '.') . " g"
            . " (termasuk kemasan " . number_format((float) $setting['BERAT_KEMASAN'], 0, ',', '.') . " g)\n";
        echo "  Rentang ACCEPT     : " . number_format(max(0, $berat - $delta), 0, ',', '.')
            . " g s/d " . number_format($berat + $delta, 0, ',', '.') . " g"
            . " (toleransi {$toleransi}%)\n";

        if ($tanpa_master > 0) {
            echo "  Peringatan         : $tanpa_master SKU belum punya master berat.\n";
        }
    }

    /**
     * Agregasi file persis seperti insert_receipt: key noresi|no_pesanan|sku, qty dijumlah.
     */
    private function _baca_file($file)
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $rows = $reader->load($file)->getActiveSheet()->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            echo "ERROR: gagal membaca file sebagai Xlsx -> " . $e->getMessage() . "\n";
            echo "Form upload iresis juga akan gagal untuk file ini. Save As ulang ke .xlsx dulu.\n";
            return null;
        }

        $map = [];
        $marketplace = [];
        $baris_per_key = [];
        $mp_per_key = [];
        $dipakai = 0;
        $skip_no_resi = 0;
        $skip_invalid = 0;

        foreach ($rows as $rn => $row) {
            $noresi     = $row['B'] ?? '';
            $no_pesanan = $row['A'] ?? null;
            $sku        = $row['P'] ?? '';

            if ($no_pesanan === 'NO_PESANAN') continue;

            if (!$no_pesanan || !$sku || !$noresi) {
                if (!$noresi) $skip_no_resi++;
                else $skip_invalid++;
                continue;
            }

            $dipakai++;

            // Rekap qty per marketplace. Ini yang memperlihatkan gejala qty Tokopedia
            // yang tidak dijumlah: seluruh barisnya berqty 1 sementara channel lain bervariasi.
            $mp = strtolower(trim((string) ($row['N'] ?? ''))) ?: '(kosong)';
            if (!isset($marketplace[$mp])) {
                $marketplace[$mp] = ['n' => 0, 'qty1' => 0, 'maks' => 0, 'total' => 0];
            }
            $qty = (int) ($row['Q'] ?? 0);
            $marketplace[$mp]['n']++;
            $marketplace[$mp]['total'] += $qty;
            if ($qty === 1) $marketplace[$mp]['qty1']++;
            if ($qty > $marketplace[$mp]['maks']) $marketplace[$mp]['maks'] = $qty;

            $key = "$noresi|$no_pesanan|$sku";

            // Dicatat supaya bisa dibedakan: qty=1 karena datanya hilang, atau qty=1
            // karena file memang 1 baris per unit (angkanya ada di jumlah barisnya).
            $baris_per_key[$key] = ($baris_per_key[$key] ?? 0) + 1;
            $mp_per_key[$key] = $mp;

            if (!isset($map[$key])) {
                $map[$key] = [
                    'noresi'     => $noresi,
                    'no_pesanan' => $no_pesanan,
                    'sku'        => $sku,
                    'jumlah'     => (int) ($row['Q'] ?? 0)
                ];
            } else {
                $map[$key]['jumlah'] += (int) ($row['Q'] ?? 0);
            }
        }

        // Berapa kombinasi per marketplace yang punya baris kembar? Ini pembeda antara
        // file yang qty-nya hilang dan file yang formatnya satu baris per unit.
        foreach ($baris_per_key as $key => $jml_baris) {
            $mp = $mp_per_key[$key];
            $marketplace[$mp]['kombinasi'] = ($marketplace[$mp]['kombinasi'] ?? 0) + 1;
            if ($jml_baris > 1) {
                $marketplace[$mp]['kembar'] = ($marketplace[$mp]['kembar'] ?? 0) + 1;
            }
        }

        return [
            'map' => $map,
            'marketplace' => $marketplace,
            'dipakai' => $dipakai,
            'skip_no_resi' => $skip_no_resi,
            'skip_invalid' => $skip_invalid
        ];
    }

    /**
     * Rekap qty per marketplace, sekaligus pemeriksaan kesehatan file.
     *
     * Channel yang 100% berqty 1 hampir pasti qty-nya tidak ikut dijumlah saat export,
     * karena mustahil semua pesanan di satu channel kebetulan berisi 1 pcs sementara
     * channel lain di file yang sama punya qty puluhan sampai ratusan.
     */
    private function _laporan_qty_per_marketplace(array $marketplace)
    {
        if (empty($marketplace)) return;

        uasort($marketplace, function ($a, $b) { return $b['n'] <=> $a['n']; });

        echo "=== SEBARAN QTY PER MARKETPLACE ===\n";
        printf("  %-22s %8s %9s %10s %8s %12s\n",
            'MARKETPLACE', 'baris', '% qty=1', 'qty maks', 'rata2', 'baris kembar');
        echo '  ' . str_repeat('-', 74) . "\n";

        $tersangka = [];
        foreach ($marketplace as $nama => $d) {
            $pct = $d['qty1'] / $d['n'] * 100;
            $kembar = $d['kembar'] ?? 0;

            printf("  %-22s %8d %8.0f%% %10d %8.2f %12d\n",
                substr($nama, 0, 22), $d['n'], $pct, $d['maks'], $d['total'] / $d['n'], $kembar);

            // Qty mentok di 1 baru jadi masalah kalau tidak ada baris kembar sama sekali.
            // Kalau file-nya satu baris per unit, qty 1 per baris justru wajar - jumlahnya
            // tersimpan pada banyaknya baris, dan iresis menjumlahkannya sendiri.
            if ($d['n'] >= 10 && $d['maks'] <= 1 && $kembar === 0) {
                $tersangka[] = $nama;
            }
        }

        if (!empty($tersangka)) {
            echo "\n  *** PERINGATAN ***\n";
            foreach ($tersangka as $nama) {
                $d = $marketplace[$nama];
                echo "  Channel '$nama': seluruh {$d['n']} baris berqty 1, tanpa satu pun baris kembar.\n";
            }
            echo "  Ini gejala qty hilang saat export. Lihat PERBAIKAN_QTY_EXPORT_JUBELIO.md.\n";
        } else {
            echo "\n  Sebaran qty wajar. Channel yang qty per barisnya 1 punya baris kembar,\n";
            echo "  jadi jumlah sebenarnya tersimpan pada banyaknya baris dan akan dijumlahkan.\n";
        }

        echo "\n";
    }
}

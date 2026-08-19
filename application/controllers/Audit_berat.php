<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Audit master berat SKU memakai hasil timbang yang sudah terkumpul.
 *
 * Latar belakang: mayoritas resi berstatus OVER/UNDER bukan karena salah packing,
 * melainkan karena master berat SKU meleset. Alat ini membandingkan berat yang
 * benar-benar terukur di timbangan dengan angka master, lalu menyusun daftar SKU
 * yang perlu ditimbang ulang, diurutkan dari yang dampaknya paling besar.
 *
 * Sampel hanya diambil dari resi yang isinya SATU baris SKU saja, karena hanya di
 * situ berat per pcs bisa dihitung tanpa tebakan. Nilai wakil memakai median supaya
 * satu-dua timbangan aneh tidak menggeser hasil.
 *
 * Pakai:
 *   php audit_berat.php
 *   php audit_berat.php 3      (minimal 3 sampel per SKU)
 */
class Audit_berat extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_cli()) {
            show_404();
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(0);
    }

    public function index()
    {
        $min_sampel = (int) (getenv('AUDIT_MIN_SAMPEL') ?: 2);
        if ($min_sampel < 1) $min_sampel = 1;

        // Ambil sampel dari resi ber-SKU tunggal saja.
        $sql = "
            SELECT d.sku,
                   d.jumlah,
                   t.berat_aktual,
                   t.berat_kemasan,
                   t.status,
                   t.toleransi_persen,
                   COALESCE(m.berat_standar, s.berat) AS berat_master,
                   CASE WHEN m.berat_standar IS NOT NULL THEN 'MASTER'
                        WHEN s.berat > 0 THEN 'TBLSKU'
                        ELSE 'KOSONG' END AS sumber
            FROM tbltimbangan t
            JOIN tbldetailprintresi d ON d.id_resi = t.id_resi
            LEFT JOIN tbltimbangan_sku m ON m.kode_sku = d.sku AND m.isactive = 1
            LEFT JOIN tblsku s ON s.id_sku = d.sku
            WHERE t.id_resi IN (
                    SELECT id_resi FROM tbldetailprintresi GROUP BY id_resi HAVING COUNT(*) = 1
                  )
              AND d.jumlah > 0
              AND (t.berat_aktual - t.berat_kemasan) > 0
        ";

        $baris = $this->db->query($sql)->result_array();

        if (empty($baris)) {
            echo "Belum ada data timbang yang bisa dipakai.\n";
            return;
        }

        $per_sku = [];
        foreach ($baris as $r) {
            $per_pcs = ((float) $r['berat_aktual'] - (float) $r['berat_kemasan']) / (int) $r['jumlah'];

            $sku = $r['sku'];
            if (!isset($per_sku[$sku])) {
                $per_sku[$sku] = [
                    'ukur'      => [],
                    'master'    => $r['berat_master'] !== null ? (float) $r['berat_master'] : null,
                    'sumber'    => $r['sumber'],
                    'toleransi' => (float) $r['toleransi_persen'],
                    'n_mismatch' => 0
                ];
            }

            $per_sku[$sku]['ukur'][] = $per_pcs;
            if ($r['status'] !== 'ACCEPT') {
                $per_sku[$sku]['n_mismatch']++;
            }
        }

        // Resolusi timbangan di lapangan sekitar 2 g. Selisih di bawah ambang ini tidak
        // bisa dibedakan dari derau alat, jadi jangan sampai menyuruh orang menimbang ulang
        // hanya karena beda 1 g pada barang yang beratnya belasan gram.
        $ambang_derau_gram = 3.0;

        $perlu_timbang_ulang = [];
        $tanpa_master        = [];
        $indikasi_qty        = [];
        $tidak_konsisten     = [];
        $sehat               = 0;

        foreach ($per_sku as $sku => $data) {
            $n_total = count($data['ukur']);
            if ($n_total < $min_sampel) continue;

            sort($data['ukur']);

            if (empty($data['master'])) {
                $tanpa_master[] = [
                    'sku' => $sku, 'n' => $n_total, 'median' => $this->_median($data['ukur']),
                    'min' => $data['ukur'][0], 'max' => $data['ukur'][$n_total - 1],
                    'n_mismatch' => $data['n_mismatch']
                ];
                continue;
            }

            $master    = $data['master'];
            $toleransi = $data['toleransi'] > 0 ? $data['toleransi'] : 5.0;

            // Pisahkan dulu sampel yang beratnya mendekati kelipatan bulat master. Sampel
            // seperti itu hampir pasti berasal dari resi yang qty-nya kurang tercatat, dan
            // kalau ikut dihitung akan menarik median menjauh sehingga master yang sebenarnya
            // benar ikut dituduh salah.
            $bersih   = [];
            $kelipatan = [];
            foreach ($data['ukur'] as $ukur) {
                $rasio = $ukur / $master;
                if ($rasio >= 1.8 && abs($rasio - round($rasio)) <= 0.12) {
                    $kelipatan[] = round($rasio);
                } else {
                    $bersih[] = $ukur;
                }
            }

            if (!empty($kelipatan) && count($bersih) < $min_sampel) {
                $indikasi_qty[] = [
                    'sku' => $sku, 'n' => $n_total, 'n_qty' => count($kelipatan),
                    'master' => $master, 'kelipatan' => max($kelipatan),
                    'n_mismatch' => $data['n_mismatch']
                ];
                continue;
            }

            if (count($bersih) < $min_sampel) {
                continue;
            }

            $n      = count($bersih);
            $median = $this->_median($bersih);
            $min    = $bersih[0];
            $max    = $bersih[$n - 1];

            // Sampel yang saling berjauhan berarti ada sebab lain yang belum terjelaskan
            // (qty, SKU tertukar, atau isi paket berbeda). Median dari data seperti ini tidak
            // layak dipakai sebagai usulan master.
            if ($min > 0 && ($max / $min) >= 1.5) {
                $tidak_konsisten[] = [
                    'sku' => $sku, 'n' => $n, 'master' => $master,
                    'min' => $min, 'max' => $max, 'n_mismatch' => $data['n_mismatch']
                ];
                continue;
            }

            $selisih_gram  = $median - $master;
            $selisih_persen = $selisih_gram / $master * 100;

            if (abs($selisih_persen) <= $toleransi || abs($selisih_gram) < $ambang_derau_gram) {
                $sehat++;
                continue;
            }

            $perlu_timbang_ulang[] = [
                'sku' => $sku, 'n' => $n, 'master' => $master, 'median' => $median,
                'min' => $min, 'max' => $max, 'selisih' => $selisih_persen,
                'sumber' => $data['sumber'], 'n_mismatch' => $data['n_mismatch']
            ];
        }

        usort($tidak_konsisten, function ($a, $b) { return $b['n_mismatch'] <=> $a['n_mismatch']; });

        usort($perlu_timbang_ulang, function ($a, $b) {
            if ($a['n_mismatch'] === $b['n_mismatch']) return $b['n'] <=> $a['n'];
            return $b['n_mismatch'] <=> $a['n_mismatch'];
        });
        usort($tanpa_master, function ($a, $b) { return $b['n_mismatch'] <=> $a['n_mismatch']; });
        usort($indikasi_qty, function ($a, $b) { return $b['n_mismatch'] <=> $a['n_mismatch']; });

        echo "\n";
        echo "AUDIT MASTER BERAT SKU\n";
        echo "Sampel dari resi ber-SKU tunggal, minimal $min_sampel timbangan per SKU.\n";
        echo str_repeat('=', 108) . "\n\n";

        echo "A. MASTER BERAT MELESET - PERLU DITIMBANG ULANG (" . count($perlu_timbang_ulang) . " SKU)\n";
        echo "   Urut dari yang paling banyak bikin resi tidak lolos timbang.\n\n";
        printf("   %-26s %5s %5s %10s %10s %9s %17s\n", 'SKU', 'n', 'gagal', 'master(g)', 'terukur(g)', 'selisih', 'rentang ukur(g)');
        echo '   ' . str_repeat('-', 100) . "\n";
        foreach ($perlu_timbang_ulang as $r) {
            printf("   %-26s %5d %5d %10s %10s %8.0f%% %17s\n",
                substr($r['sku'], 0, 26), $r['n'], $r['n_mismatch'],
                number_format($r['master'], 0), number_format($r['median'], 0),
                $r['selisih'], number_format($r['min'], 0) . ' - ' . number_format($r['max'], 0));
        }
        if (empty($perlu_timbang_ulang)) echo "   (tidak ada)\n";

        echo "\n\nB. BELUM PUNYA MASTER BERAT SAMA SEKALI (" . count($tanpa_master) . " SKU)\n";
        echo "   Angka 'terukur' bisa langsung dipakai sebagai nilai awal master.\n\n";
        printf("   %-26s %5s %5s %10s %17s\n", 'SKU', 'n', 'gagal', 'terukur(g)', 'rentang ukur(g)');
        echo '   ' . str_repeat('-', 70) . "\n";
        foreach ($tanpa_master as $r) {
            printf("   %-26s %5d %5d %10s %17s\n",
                substr($r['sku'], 0, 26), $r['n'], $r['n_mismatch'],
                number_format($r['median'], 0),
                number_format($r['min'], 0) . ' - ' . number_format($r['max'], 0));
        }
        if (empty($tanpa_master)) echo "   (tidak ada)\n";

        echo "\n\nC. MASTER KEMUNGKINAN BENAR, QTY-NYA YANG KURANG TERCATAT (" . count($indikasi_qty) . " SKU)\n";
        echo "   Berat terukur mendekati kelipatan bulat dari master. Jangan ditimbang ulang,\n";
        echo "   ini gejala qty dari export Jubelio yang tidak dijumlah.\n\n";
        printf("   %-26s %5s %5s %10s %11s\n", 'SKU', 'n', 'gagal', 'master(g)', 'kelipatan');
        echo '   ' . str_repeat('-', 62) . "\n";
        foreach ($indikasi_qty as $r) {
            printf("   %-26s %5d %5d %10s %10dx\n",
                substr($r['sku'], 0, 26), $r['n'], $r['n_mismatch'],
                number_format($r['master'], 0), $r['kelipatan']);
        }
        if (empty($indikasi_qty)) echo "   (tidak ada)\n";

        echo "\n\nD. HASIL TIMBANG SALING BERJAUHAN - PERLU DILIHAT MANUAL (" . count($tidak_konsisten) . " SKU)\n";
        echo "   Sampelnya terlalu beragam untuk disimpulkan. Biasanya SKU tertukar, isi paket\n";
        echo "   berbeda-beda, atau qty yang salah tercatat dengan pola tidak bulat.\n\n";
        printf("   %-26s %5s %5s %10s %17s\n", 'SKU', 'n', 'gagal', 'master(g)', 'rentang ukur(g)');
        echo '   ' . str_repeat('-', 70) . "\n";
        foreach ($tidak_konsisten as $r) {
            printf("   %-26s %5d %5d %10s %17s\n",
                substr($r['sku'], 0, 26), $r['n'], $r['n_mismatch'],
                number_format($r['master'], 0),
                number_format($r['min'], 0) . ' - ' . number_format($r['max'], 0));
        }
        if (empty($tidak_konsisten)) echo "   (tidak ada)\n";

        echo "\n\nRINGKASAN\n";
        echo "  SKU master sudah akurat        : $sehat\n";
        echo "  SKU perlu ditimbang ulang      : " . count($perlu_timbang_ulang) . "\n";
        echo "  SKU belum punya master         : " . count($tanpa_master) . "\n";
        echo "  SKU kena masalah qty           : " . count($indikasi_qty) . "\n";
        echo "  SKU perlu dilihat manual       : " . count($tidak_konsisten) . "\n";
        echo "\nTidak ada data yang diubah. Alat ini hanya membaca.\n\n";
    }

    private function _median(array $angka)
    {
        sort($angka);
        $n = count($angka);
        $tengah = (int) floor($n / 2);

        return ($n % 2) ? $angka[$tengah] : ($angka[$tengah - 1] + $angka[$tengah]) / 2;
    }
}

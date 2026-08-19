<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller untuk setup kurir JNT dan JNT Cargo
 * Akses: setup_kurir/init_jnt
 */
class Setup_kurir extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('courrier_fcd');
    }

    /**
     * Setup kurir JNT dan JNT Cargo
     * Akses: setup_kurir/init_jnt
     */
    public function init_jnt()
    {
        // Cek apakah user sudah login (opsional, bisa dihapus jika ingin public)
        if (!isset($this->data['user'])) {
            $this->make_ajax_response(401, 'Unauthorized');
            return;
        }

        $results = [];
        $errors = [];

        // 1. Cek kurir yang sudah ada
        $existing_kurir = $this->courrier_fcd->get_courrier()->result_array();
        $existing_names = array_map(function($k) {
            return strtolower($k['nama_kurir']);
        }, $existing_kurir);

        // 2. Tambahkan JNT (bukan cargo) jika belum ada
        $jnt_names = ['jnt', 'j&t'];
        $jnt_exists = false;
        $jnt_id = null;
        
        foreach ($existing_kurir as $kurir) {
            $nama_lower = strtolower($kurir['nama_kurir']);
            if (in_array($nama_lower, $jnt_names) && stripos($nama_lower, 'cargo') === false) {
                $jnt_exists = true;
                $jnt_id = $kurir['id_kurir'];
                break;
            }
        }

        if (!$jnt_exists) {
            $jnt_data = [
                'nama_kurir' => 'JNT',
                'isactive' => 1
            ];
            $save = $this->courrier_fcd->save($jnt_data);
            if ($save['affected_rows'] > 0) {
                $jnt_id = $save['id_kurir'];
                $results[] = "✅ Kurir 'JNT' berhasil ditambahkan (ID: {$jnt_id})";
            } else {
                $errors[] = "❌ Gagal menambahkan kurir 'JNT'";
            }
        } else {
            $results[] = "ℹ️ Kurir 'JNT' sudah ada (ID: {$jnt_id})";
        }

        // 3. Tambahkan JNT Cargo jika belum ada
        $jnt_cargo_exists = false;
        $jnt_cargo_id = null;
        
        foreach ($existing_kurir as $kurir) {
            $nama_lower = strtolower($kurir['nama_kurir']);
            if ((stripos($nama_lower, 'jnt') !== false || stripos($nama_lower, 'j&t') !== false) 
                && stripos($nama_lower, 'cargo') !== false) {
                $jnt_cargo_exists = true;
                $jnt_cargo_id = $kurir['id_kurir'];
                break;
            }
        }

        if (!$jnt_cargo_exists) {
            $jnt_cargo_data = [
                'nama_kurir' => 'JNT Cargo',
                'isactive' => 1
            ];
            $save = $this->courrier_fcd->save($jnt_cargo_data);
            if ($save['affected_rows'] > 0) {
                $jnt_cargo_id = $save['id_kurir'];
                $results[] = "✅ Kurir 'JNT Cargo' berhasil ditambahkan (ID: {$jnt_cargo_id})";
            } else {
                $errors[] = "❌ Gagal menambahkan kurir 'JNT Cargo'";
            }
        } else {
            $results[] = "ℹ️ Kurir 'JNT Cargo' sudah ada (ID: {$jnt_cargo_id})";
        }

        // 4. Tampilkan ringkasan
        $output = [
            'success' => empty($errors),
            'results' => $results,
            'errors' => $errors,
            'jnt_id' => $jnt_id,
            'jnt_cargo_id' => $jnt_cargo_id
        ];

        // Jika diakses via browser, tampilkan HTML
        if ($this->input->method() == 'get') {
            echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Kurir JNT</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Setup Kurir JNT dan JNT Cargo</h1>
    <div class='box'>";
            
            foreach ($results as $result) {
                echo "<p class='success'>{$result}</p>";
            }
            
            foreach ($errors as $error) {
                echo "<p class='error'>{$error}</p>";
            }
            
            if (!empty($jnt_id) && !empty($jnt_cargo_id)) {
                echo "<hr>
                <h3>ID Kurir:</h3>
                <ul>
                    <li><strong>JNT:</strong> ID {$jnt_id}</li>
                    <li><strong>JNT Cargo:</strong> ID {$jnt_cargo_id}</li>
                </ul>
                <p><em>Catat ID ini untuk digunakan di langkah perbaikan data berikutnya.</em></p>";
            }
            
            echo "<hr>
            <p><a href='" . base_url('courrier') . "'>Kembali ke Master Kurir</a></p>
            </div>
</body>
</html>";
        } else {
            // Jika diakses via AJAX, return JSON
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($output));
        }
    }

    /**
     * Cek status kurir JNT
     * Akses: setup_kurir/check_jnt
     */
    public function check_jnt()
    {
        $all_kurir = $this->courrier_fcd->get_courrier()->result_array();
        
        $jnt_kurir = [];
        $jnt_cargo_kurir = [];
        
        foreach ($all_kurir as $kurir) {
            $nama_lower = strtolower($kurir['nama_kurir']);
            if ((stripos($nama_lower, 'jnt') !== false || stripos($nama_lower, 'j&t') !== false)) {
                if (stripos($nama_lower, 'cargo') !== false) {
                    $jnt_cargo_kurir[] = $kurir;
                } else {
                    $jnt_kurir[] = $kurir;
                }
            }
        }

        $output = [
            'jnt' => $jnt_kurir,
            'jnt_cargo' => $jnt_cargo_kurir,
            'status' => [
                'jnt_exists' => !empty($jnt_kurir),
                'jnt_cargo_exists' => !empty($jnt_cargo_kurir),
                'ready' => !empty($jnt_kurir) && !empty($jnt_cargo_kurir)
            ]
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output, JSON_PRETTY_PRINT));
    }

    /**
     * Perbaiki data lama yang id_kurir = 99 (Tidak diketahui)
     * Update berdasarkan pola noresi
     * Akses: setup_kurir/fix_data_lama
     */
    public function fix_data_lama()
    {
        // Cek apakah user sudah login
        if (!isset($this->data['user'])) {
            $this->make_ajax_response(401, 'Unauthorized');
            return;
        }

        $this->load->database();
        $results = [];
        $errors = [];
        $total_updated = 0;

        // 1. Cek apakah kurir JNT Cargo sudah ada
        $jnt_cargo = $this->db
            ->get_where('tblkurir', "LOWER(nama_kurir) LIKE '%jnt%' AND LOWER(nama_kurir) LIKE '%cargo%'")
            ->row();

        if (!$jnt_cargo) {
            $errors[] = "❌ Kurir JNT Cargo belum ada. Jalankan setup_kurir/init_jnt terlebih dahulu!";
            $output = [
                'success' => false,
                'results' => $results,
                'errors' => $errors,
                'total_updated' => 0
            ];
            
            if ($this->input->method() == 'get') {
                echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Data Lama</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .error { color: red; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Perbaiki Data Lama (id_kurir = 99)</h1>
    <div class='box'>";
                foreach ($errors as $error) {
                    echo "<p class='error'>{$error}</p>";
                }
                echo "<p><a href='" . base_url('setup_kurir/init_jnt') . "'>Setup Kurir JNT Dulu</a></p>
                </div>
</body>
</html>";
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($output));
            }
            return;
        }

        $jnt_cargo_id = $jnt_cargo->id_kurir;

        // 2. Cek berapa banyak data yang id_kurir = 99
        $total_salah = $this->db->where('id_kurir', 99)->count_all_results('tblprintresi');
        $results[] = "📊 Total data dengan id_kurir = 99: <strong>{$total_salah}</strong>";

        if ($total_salah == 0) {
            $results[] = "✅ Tidak ada data yang perlu diperbaiki!";
        } else {
            // 3. Update data berdasarkan pola noresi JNT Cargo
            // Pola: JP, JX, JO, JZ, TJ, atau prefix '20'
            $this->db->set('id_kurir', $jnt_cargo_id);
            $this->db->where('id_kurir', 99);
            $this->db->group_start();
            $this->db->where("SUBSTRING(noresi, 1, 2) IN ('JP', 'JX', 'JO', 'JZ', 'TJ')", NULL, FALSE);
            $this->db->or_where("SUBSTRING(noresi, 1, 2) = '20'", NULL, FALSE);
            $this->db->group_end();
            $this->db->update('tblprintresi');

            $total_updated = $this->db->affected_rows();
            $results[] = "✅ Berhasil memperbaiki <strong>{$total_updated}</strong> data menjadi JNT Cargo (ID: {$jnt_cargo_id})";

            // 4. Cek sisa data yang masih id_kurir = 99
            $sisa = $this->db->where('id_kurir', 99)->count_all_results('tblprintresi');
            if ($sisa > 0) {
                $results[] = "⚠️ Masih ada <strong>{$sisa}</strong> data dengan id_kurir = 99 (bukan JNT Cargo, mungkin kurir lain)";
            } else {
                $results[] = "✅ Semua data yang id_kurir = 99 sudah diperbaiki!";
            }
        }

        // 5. Verifikasi: Cek distribusi kurir
        $verifikasi = $this->db
            ->select('k.nama_kurir, COUNT(p.id_printresi) as total')
            ->from('tblkurir k')
            ->join('tblprintresi p', 'p.id_kurir = k.id_kurir', 'left')
            ->where("(LOWER(k.nama_kurir) LIKE '%jnt%' OR LOWER(k.nama_kurir) LIKE '%j&t%')")
            ->group_by('k.id_kurir, k.nama_kurir')
            ->get()
            ->result_array();

        if (!empty($verifikasi)) {
            $results[] = "<hr><h3>Distribusi Kurir Setelah Perbaikan:</h3>";
            foreach ($verifikasi as $v) {
                $results[] = "  - {$v['nama_kurir']}: {$v['total']} resi";
            }
        }

        $output = [
            'success' => empty($errors),
            'results' => $results,
            'errors' => $errors,
            'total_updated' => $total_updated,
            'jnt_cargo_id' => $jnt_cargo_id
        ];

        // Jika diakses via browser, tampilkan HTML
        if ($this->input->method() == 'get') {
            echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Data Lama</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        h3 { margin-top: 20px; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Perbaiki Data Lama (id_kurir = 99)</h1>
    <div class='box'>";
            
            foreach ($results as $result) {
                if (strpos($result, '❌') !== false || strpos($result, '⚠️') !== false) {
                    echo "<p class='error'>{$result}</p>";
                } elseif (strpos($result, '✅') !== false) {
                    echo "<p class='success'>{$result}</p>";
                } else {
                    echo "<p class='info'>{$result}</p>";
                }
            }
            
            foreach ($errors as $error) {
                echo "<p class='error'>{$error}</p>";
            }
            
            echo "<hr>
            <p><strong>Total data yang diperbaiki:</strong> {$total_updated}</p>
            <p><em>Data yang diperbaiki adalah data dengan id_kurir = 99 yang memiliki pola noresi JNT Cargo (JP, JX, JO, JZ, TJ, atau prefix 20)</em></p>
            <p><a href='" . base_url('courrier') . "'>Kembali ke Master Kurir</a> | <a href='" . base_url('report/shipping_report') . "'>Lihat Laporan</a></p>
            </div>
</body>
</html>";
        } else {
            // Jika diakses via AJAX, return JSON
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($output));
        }
    }
}


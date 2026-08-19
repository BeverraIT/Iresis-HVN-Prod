<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Modul Timbangan (TIM RESI).
 *
 * Alur: operator scan nomor resi -> sistem menghitung berat standar paket dari
 * daftar SKU pada resi tersebut -> indikator timbangan RS-232 dibaca langsung
 * oleh browser lewat Web Serial API -> hasilnya disimpan dengan status
 * UNDER / ACCEPT / OVER.
 *
 * CATATAN PENTING: Web Serial API hanya aktif pada secure context (https://
 * atau localhost). Bila aplikasi diakses lewat http://<ip-lan>/, browser tidak
 * akan menampilkan daftar port. Lihat catatan di view scan_timbangan.
 */
class Timbangan extends MY_Controller
{
    /**
     * Jarak waktu minimal antar penimbangan dengan bacaan yang sama persis
     * pada satu nomor resi. Lebih rapat dari ini dianggap paket yang belum
     * diangkat dari timbangan, bukan penimbangan baru.
     */
    const JEDA_TIMBANG_DETIK = 15;

    /**
     * Awalan kode SKU yang ditimbang terpisah dari isi resi lainnya.
     *
     * Resi yang memuat SKU ini BERSAMA SKU lain ditimbang dua kali: tahap 1
     * hanya SKU berawalan ini, tahap 2 sisanya. Resi yang isinya seluruhnya
     * SKU ini -- atau tidak memuatnya sama sekali -- ditimbang seperti biasa.
     */
    const AWALAN_SKU_TERPISAH = 'PLCA';

    /**
     * Batas atas jumlah dus per paket. Semata penjaga terhadap angka nyasar
     * dari browser -- satu paket tidak mungkin memakai ratusan dus.
     */
    const MAKS_JUMLAH_KEMASAN = 99;

    const TAHAP_BIASA = 0;
    const TAHAP_TERPISAH = 1;   // kelompok PLCA
    const TAHAP_SISANYA = 2;    // SKU selain PLCA


    function __construct()
    {
        parent::__construct();

        $this->load->model('timbangan_fcd');
    }

    // ==================================================================
    // Halaman penimbangan
    // ==================================================================

    public function scan_timbangan()
    {
        $setting = $this->timbangan_fcd->get_setting();

        $data['setting'] = $this->setting_publik($setting);
        $data['pilihan_kemasan'] = $this->timbangan_fcd->get_kemasan();
        $data['nama_komputer'] = $this->data['user']['nama_komputer'];
        $data['total_scan'] = $this->timbangan_fcd->get_total_scan_user($this->data['user']['id_user'])->row()->total_scan;

        $this->show($data);
    }

    /**
     * Ajax: ambil data resi + hitung berat standar & rentang set point.
     */
    public function get_resi_timbangan()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $noresi = trim((string) $this->input->post('noresi'));

        if (empty($noresi)) {
            $this->make_ajax_response(400, 'Nomor resi masih kosong');
        }

        $resi = $this->timbangan_fcd->get_resi($noresi)->row_array();

        if (empty($resi)) {
            $this->make_ajax_response(404, DATA_NOT_FOUND);
        }

        $items = $this->timbangan_fcd->get_resi_items($resi['id_printresi'])->result_array();

        if (empty($items)) {
            $this->make_ajax_response(404, 'Resi ditemukan tetapi belum punya detail item/SKU');
        }

        // Tahap boleh dipaksa oleh layar (operator meneruskan ke tahap 2 walau
        // tahap 1 meleset); tanpa itu, tahapnya ditentukan dari riwayat resi.
        $dua_tahap = $this->perlu_dua_tahap($items);
        $tahap = $this->tahap_diminta($items);

        // Resi yang pernah meleset boleh ditimbang ulang -- justru itu alur
        // percobaan ke-2. Yang ditolak hanya tahap yang sudah pernah lolos,
        // supaya paket yang beres tidak tercatat dua kali.
        $terakhir = $this->timbangan_fcd->get_timbangan_lolos($noresi, $tahap)->row_array();

        if (!empty($terakhir)) {
            $this->make_ajax_response(409, ($dua_tahap ? 'Tahap ' . $tahap . ' resi ini' : 'Resi ini')
                . ' sudah ditimbang pada '
                . date('d-m-Y H:i:s', strtotime($terakhir['tanggal_timbangan']))
                . ' (' . $terakhir['status'] . ', ' . $this->format_gram($terakhir['berat_aktual']) . ')');
        }

        $items_tahap = $this->items_tahap($items, $tahap);

        $setting = $this->timbangan_fcd->get_setting();
        $setpoint = $this->hitung_setpoint($items_tahap, $setting, $this->kemasan_tahap($tahap));

        $this->make_ajax_response(200, 'Resi ditemukan', array(
            'resi' => $resi,
            'items' => $items_tahap,
            'setpoint' => $setpoint,
            // Percobaan keberapa yang sedang dimulai. Dipakai layar operator
            // untuk tahu peringatan mana yang akan muncul bila meleset.
            'percobaan_ke' => $this->timbangan_fcd->hitung_percobaan_meleset($noresi) + 1,
            'dua_tahap' => $dua_tahap,
            'tahap' => $tahap,
        ));
    }

    /**
     * Ajax: simpan hasil penimbangan.
     *
     * Berat standar dan status dihitung ulang di server berdasarkan data resi
     * terbaru; nilai dari browser hanya dipakai untuk angka bacaan indikator.
     */
    public function save_timbangan()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $noresi = trim((string) $this->input->post('noresi'));
        $berat_indikator = $this->input->post('berat_indikator');
        $raw_data = $this->input->post('raw_data');

        if (empty($noresi)) {
            $this->make_ajax_response(400, 'Nomor resi masih kosong');
        }

        if ($berat_indikator === null || $berat_indikator === '' || !is_numeric($berat_indikator)) {
            $this->make_ajax_response(400, 'Berat dari indikator tidak terbaca');
        }

        $resi = $this->timbangan_fcd->get_resi($noresi)->row_array();

        if (empty($resi)) {
            $this->make_ajax_response(404, DATA_NOT_FOUND);
        }

        $items = $this->timbangan_fcd->get_resi_items($resi['id_printresi'])->result_array();

        if (empty($items)) {
            $this->make_ajax_response(404, 'Resi ditemukan tetapi belum punya detail item/SKU');
        }

        $tahap = $this->tahap_diminta($items);

        if ($this->timbangan_fcd->get_timbangan_lolos($noresi, $tahap)->num_rows() > 0) {
            $this->make_ajax_response(409, DATA_ALREADY_EXISTS);
        }

        $items_tahap = $this->items_tahap($items, $tahap);

        $setting = $this->timbangan_fcd->get_setting();
        $setpoint = $this->hitung_setpoint($items_tahap, $setting, $this->kemasan_tahap($tahap));

        // Satuan diambil dari bacaan indikator bila layar mengirimkannya,
        // karena hanya di sanalah terbaca "kg" atau "g" yang sebenarnya.
        // Pengaturan SATUAN_INDIKATOR dipakai sebagai cadangan saja.
        $berat_aktual = $this->to_gram($berat_indikator, $this->satuan_terpakai($setting));
        $status = $this->status_berat($berat_aktual, $setpoint);

        // Hasil yang meleset TIDAK lagi ditolak: justru itu yang harus tercatat
        // supaya muncul di menu "Berat Resi Tidak Sesuai". Percobaan dihitung
        // dari riwayat resi ini, bukan dari browser, supaya tidak bisa diakali
        // dengan memuat ulang halaman.
        // Pengaman terhadap kiriman beruntun: bacaan yang sama persis dalam
        // hitungan detik dianggap paket yang belum diangkat, bukan
        // penimbangan baru. Tanpa ini satu resi pernah tercatat 187 kali.
        $terakhir = $this->timbangan_fcd->get_timbangan_by_noresi($noresi)->row_array();

        if (!empty($terakhir)
            && abs((float) $terakhir['berat_aktual'] - $berat_aktual) < 0.01
            && strtotime($terakhir['created']) >= time() - self::JEDA_TIMBANG_DETIK) {

            $this->make_ajax_response(200, 'Bacaan yang sama baru saja tercatat', array(
                'noresi' => $terakhir['noresi'],
                'status' => $terakhir['status'],
                'berat_aktual' => (float) $terakhir['berat_aktual'],
                'berat_standar' => (float) $terakhir['berat_standar'],
                'berat_min' => (float) $terakhir['berat_min'],
                'berat_max' => (float) $terakhir['berat_max'],
                'selisih' => (float) $terakhir['selisih'],
                'percobaan_ke' => (int) $terakhir['percobaan_ke'],
                'duplikat' => TRUE,
                'total_scan' => $this->timbangan_fcd->get_total_scan_user($this->data['user']['id_user'])->row()->total_scan,
            ));
        }

        $percobaan_ke = $this->timbangan_fcd->hitung_percobaan_meleset($noresi) + 1;

        if ($setpoint['sku_tanpa_master'] > 0 && $setpoint['berat_standar'] <= 0) {
            $this->make_ajax_response(422, 'Berat standar resi ini 0 karena semua SKU belum punya master berat. Lengkapi dulu di menu Master Berat SKU.');
        }

        $save = $this->timbangan_fcd->save_timbangan(array(
            'id_resi' => $resi['id_printresi'],
            'noresi' => $resi['noresi'],
            'berat_aktual' => $berat_aktual,
            'berat_standar' => $setpoint['berat_standar'],
            'berat_min' => $setpoint['berat_min'],
            'berat_max' => $setpoint['berat_max'],
            'selisih' => $berat_aktual - $setpoint['berat_standar'],
            'toleransi_persen' => $setpoint['toleransi_persen'],
            'berat_kemasan' => $setpoint['berat_kemasan'],
            'status' => $status,
            'percobaan_ke' => $percobaan_ke,
            'tahap' => $tahap,
            'sku_tanpa_master' => $setpoint['sku_tanpa_master'],
            'raw_data' => substr((string) $raw_data, 0, 255),
            'admin_pegawai' => $this->data['user']['id_user'],
            'nama_komputer' => $this->data['user']['nama_komputer'],
            // Penimbangan berjalan di beberapa meja sekaligus; alamat IP
            // membuat jelas hasil ini datang dari perangkat yang mana.
            'ip_address' => $this->input->ip_address(),
            'tanggal_timbangan' => date('Y-m-d H:i:s'),
        ));

        if ($save['affected_rows'] < 1) {
            $this->make_ajax_response(200, NOTHING_TO_SAVE);
        }

        $info = array(
            'noresi' => $resi['noresi'],
            'status' => $status,
            'berat_aktual' => $berat_aktual,
            'berat_standar' => $setpoint['berat_standar'],
            'berat_min' => $setpoint['berat_min'],
            'berat_max' => $setpoint['berat_max'],
            'selisih' => $berat_aktual - $setpoint['berat_standar'],
            'percobaan_ke' => $percobaan_ke,
            'tahap' => $tahap,
            // Tahap 1 yang selesai memberi tahu layar untuk lanjut ke tahap 2,
            // baik hasilnya lolos maupun meleset.
            'tahap_berikutnya' => $tahap == self::TAHAP_TERPISAH ? self::TAHAP_SISANYA : 0,
        );

        // Layar operator tidak pernah dikunci: setiap hasil yang meleset cukup
        // memunculkan peringatan, dan resinya masuk daftar "Berat Resi Tidak
        // Sesuai". Kode atasan hanya dipakai admin saat menindaklanjuti daftar
        // tersebut, bukan di meja timbangan.
        $this->make_ajax_response(201, SUCCESS_SAVE_DATA, array_merge($info, array(
            'total_scan' => $this->timbangan_fcd->get_total_scan_user($this->data['user']['id_user'])->row()->total_scan,
        )));
    }

    // ==================================================================
    // Master berat SKU
    // ==================================================================

    public function master_timbangan()
    {
        $data['setting'] = $this->setting_publik($this->timbangan_fcd->get_setting());
        $data['message'] = $this->session->flashdata('message');

        $this->show($data);
    }

    public function get_data_master_timbangan()
    {
        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $col = 0;
        $dir = '';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $data['dir'] = $dir;

        $data['valid_columns'] = array(
            0 => 't.kode_sku',
            1 => 't2.nama_sku',
            2 => 't.berat_standar',
            3 => 't.toleransi_persen',
            4 => 't.updated',
            5 => null,
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list = $this->timbangan_fcd->get_data_master($data);
        $total = $this->timbangan_fcd->get_total_data_master($data);

        $rows = array();
        foreach ($list->result() as $row) {
            $rows[] = array(
                $row->kode_sku,
                $row->nama_sku ?: '-',
                // Desimal hanya ditampilkan bila memang ada, supaya daftar
                // tetap enak dibaca untuk SKU yang beratnya bulat.
                rtrim(rtrim(number_format($row->berat_standar, 2, ',', '.'), '0'), ','),
                $row->toleransi_persen === null ? 'Global' : rtrim(rtrim(number_format($row->toleransi_persen, 2, ',', '.'), '0'), ',') . ' %',
                empty($row->updated) ? '-' : date('d-m-Y H:i', strtotime($row->updated)),
                '<button type="button" class="btn btn-xs btn-primary btn-edit-master"'
                    . ' data-kode="' . html_escape((string) $row->kode_sku) . '"'
                    . ' data-berat="' . html_escape((string) $row->berat_standar) . '"'
                    . ' data-toleransi="' . html_escape((string) $row->toleransi_persen) . '">'
                    . '<i class="fa fa-pencil"></i> Ubah</button>',
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ));
        exit();
    }

    /**
     * Unduh riwayat perubahan berat master SKU.
     */
    public function export_to_excel_log_master()
    {
        ini_set('memory_limit', '-1');

        list($start_date, $end_date) = $this->parse_reportrange($this->input->post('reportrange'));

        $data['reportrange'] = $this->input->post('reportrange');
        $data['list_data'] = $this->timbangan_fcd->get_log_master($start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Log_Perubahan_Berat_SKU_" . date('Ymd_His') . ".xls");

        $this->load->view('template_report/timbangan_log_master_report', $data);
    }

    /**
     * Impor sku.xlsx. Kolom A = KODE SKU, kolom B = BERAT BARU (gram).
     * Baris pertama dianggap header.
     */
    public function upload_master_timbangan_action()
    {
        if ($this->input->method() !== 'post') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        if (!isset($_FILES['skuFile']) || $_FILES['skuFile']['error'] != 0) {
            $this->make_ajax_response(400, 'File belum dipilih atau gagal diunggah');
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($_FILES['skuFile']['tmp_name'])->getActiveSheet();

            $raw = $sheet->toArray(null, true, true, true);

            $rows = array();
            $dilewati = 0;
            $baris = 0;

            foreach ($raw as $line) {
                $baris++;

                if ($baris == 1) continue; // header

                $kode = trim((string) ($line['A'] ?? ''));
                $berat = $line['B'] ?? null;

                if ($kode === '') continue; // baris kosong

                if ($berat === null || $berat === '' || !is_numeric($berat)) {
                    $dilewati++;
                    continue;
                }

                $rows[] = array(
                    'kode_sku' => $kode,
                    // Dibulatkan ke 2 desimal, bukan ke bilangan bulat. SKU
                    // sangat ringan memang ada -- MCBTYPEC-4056 beratnya
                    // 1,6 g, dan pada pesanan 400 pcs pembulatan ke 2 g
                    // menggeser berat standar sejauh 160 g.
                    'berat_standar' => round((float) $berat, 2),
                );
            }

            if (empty($rows)) {
                $this->make_ajax_response(422, 'Tidak ada baris yang bisa diproses. Pastikan kolom A = KODE SKU dan kolom B = BERAT BARU.');
            }

            $result = $this->timbangan_fcd->save_master_bulk($rows, $this->data['user']['id_user']);

            if ($result === FALSE) {
                $this->make_ajax_response(500, FAILED_SAVE_DATA);
            }

            $pesan = 'Impor selesai: ' . $result['inserted'] . ' SKU baru, '
                . $result['updated'] . ' diperbarui, '
                . $result['unchanged'] . ' tidak berubah';

            if ($dilewati > 0) {
                $pesan .= ', ' . $dilewati . ' baris dilewati (berat kosong / bukan angka)';
            }

            $this->make_ajax_response(201, $pesan);
        } catch (Exception $e) {
            log_message('error', 'Gagal impor master timbangan: ' . $e->getMessage());

            $this->make_ajax_response(500, 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    public function save_master_timbangan()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $kode_sku = trim((string) $this->input->post('kode_sku'));
        $berat_standar = $this->input->post('berat_standar');
        $toleransi = $this->input->post('toleransi_persen');

        if (empty($kode_sku)) {
            $this->make_ajax_response(400, 'Kode SKU masih kosong');
        }

        if (!is_numeric($berat_standar) || $berat_standar < 0) {
            $this->make_ajax_response(400, 'Berat standar harus berupa angka >= 0');
        }

        if ($toleransi !== null && $toleransi !== '' && (!is_numeric($toleransi) || $toleransi < 0)) {
            $this->make_ajax_response(400, 'Toleransi harus berupa angka >= 0 atau dikosongkan');
        }

        $save = $this->timbangan_fcd->save_master(array(
            'kode_sku' => $kode_sku,
            'berat_standar' => round((float) $berat_standar, 2),
            'toleransi_persen' => ($toleransi === null || $toleransi === '') ? null : (float) $toleransi,
        ), $this->data['user']['id_user']);

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }

    public function save_setting_timbangan()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $allowed = array(
            'TOLERANSI_PERSEN' => 'angka',
            'BERAT_KEMASAN' => 'angka',
            'SATUAN_INDIKATOR' => 'satuan',
            'INTERLOCK' => 'boolean',
            'BAUD_RATE' => 'angka',
            'AUTO_SIMPAN' => 'boolean',
            'STABIL_MS' => 'angka',
            'KODE_SUPERVISOR' => 'kode',
        );

        $tersimpan = 0;

        foreach ($allowed as $code => $tipe) {
            $value = $this->input->post($code);

            if ($value === null) continue;

            if ($tipe == 'angka' && (!is_numeric($value) || $value < 0)) {
                $this->make_ajax_response(400, $code . ' harus berupa angka >= 0');
            }

            if ($tipe == 'satuan' && !in_array($value, array('g', 'kg'))) {
                $this->make_ajax_response(400, 'Satuan indikator hanya boleh g atau kg');
            }

            if ($tipe == 'boolean') {
                $value = $value ? '1' : '0';
            }

            if ($tipe == 'kode') {
                $value = trim((string) $value);

                // Field sengaja dikosongkan = kode lama dipertahankan.
                if ($value === '') continue;

                if (strlen($value) < 4) {
                    $this->make_ajax_response(400, 'Kode atasan minimal 4 karakter');
                }
            }

            $this->timbangan_fcd->save_setting($code, (string) $value, $this->data['user']['id_user']);
            $tersimpan++;
        }

        if ($tersimpan == 0) {
            $this->make_ajax_response(200, NOTHING_TO_SAVE);
        }

        $this->make_ajax_response(201, 'Pengaturan timbangan tersimpan');
    }

    // ==================================================================
    // Laporan
    // ==================================================================

    public function laporan_timbangan()
    {
        $data['message'] = $this->session->flashdata('message');

        // Rentang bawaan 7 hari terakhir, bukan hari berjalan. Dengan rentang
        // "hari ini", laporan tampak kosong setiap pagi sebelum penimbangan
        // pertama -- dan itu sempat disangka datanya hilang.
        $data['reportrange'] = date('Y-m-d 00:00:00', strtotime('-6 days')) . ' - ' . date('Y-m-d H:i:s');

        $this->show($data);
    }

    // ==================================================================
    // Berat resi tidak sesuai
    //
    // Kumpulan resi yang berat timbangnya di luar rentang. Satu baris per
    // nomor resi (penimbangan terakhirnya), supaya percobaan ke-1 dan ke-2
    // tidak tampil sebagai dua pekerjaan berbeda bagi admin.
    // ==================================================================

    public function resi_tidak_sesuai()
    {
        $data['message'] = $this->session->flashdata('message');

        $this->show($data);
    }

    public function get_data_resi_tidak_sesuai()
    {
        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $col = 0;
        $dir = '';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $data['dir'] = $dir;

        $data['valid_columns'] = array(
            0 => null,
            1 => 't.noresi',
            2 => 't.berat_standar',
            3 => 't.berat_aktual',
            4 => 't.selisih',
            5 => 't.status',
            6 => 't.tahap',
            7 => 't.percobaan_ke',
            8 => 't2.name',
            9 => 't.nama_komputer',
            10 => 't.tanggal_timbangan',
            11 => null,
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $selesai = $this->input->post('tindak_lanjut') == '1' ? 1 : 0;

        $list = $this->timbangan_fcd->get_data_tidak_sesuai($data, $selesai);
        $total = $this->timbangan_fcd->get_total_data_tidak_sesuai($data, $selesai);

        $i = $data['start'] + 1;
        $rows = array();
        foreach ($list->result() as $row) {
            if ($selesai) {
                $aksi = '<small class="text-muted">oleh ' . html_escape((string) ($row->admin_tindak_lanjut ?: '-'))
                    . '<br>' . (empty($row->tindak_lanjut_waktu) ? '-' : date('d-m-Y H:i', strtotime($row->tindak_lanjut_waktu)))
                    . (empty($row->tindak_lanjut_catatan) ? '' : '<br>' . html_escape((string) $row->tindak_lanjut_catatan))
                    . '</small>';
            } else {
                $aksi = '<button type="button" class="btn btn-xs btn-danger btn-tindak-lanjut"'
                    . ' data-noresi="' . html_escape((string) $row->noresi) . '"'
                    . ' data-status="' . html_escape((string) $row->status) . '"'
                    . ' data-detail="' . html_escape(
                        $this->format_gram($row->berat_aktual) . ' vs standar ' . $this->format_gram($row->berat_standar)
                        . ' (rentang ' . $this->format_gram($row->berat_min) . ' - ' . $this->format_gram($row->berat_max) . ')'
                    ) . '">'
                    . '<i class="fa fa-check"></i> Tindak Lanjut</button>';
            }

            $rows[] = array(
                $i++ . '.',
                $row->noresi,
                $this->format_gram($row->berat_standar) . '<br><small class="text-muted">' . $this->format_gram($row->berat_min) . ' &ndash; ' . $this->format_gram($row->berat_max) . '</small>',
                $this->format_gram($row->berat_aktual),
                ($row->selisih > 0 ? '+' : '') . $this->format_gram($row->selisih),
                $this->badge_status($row->status),
                $this->label_tahap($row->tahap),
                $row->percobaan_ke . 'x',
                $row->petugas ?: '-',
                ($row->nama_komputer ?: '-') . '<br><small class="text-muted">' . ($row->ip_address ?: 'IP tidak tercatat') . '</small>',
                date('d-m-Y H:i:s', strtotime($row->tanggal_timbangan)),
                $aksi,
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ));
        exit();
    }

    public function export_to_excel_resi_tidak_sesuai()
    {
        ini_set('memory_limit', '-1');

        $selesai = $this->input->post('tindak_lanjut') == '1' ? 1 : 0;

        $data['tindak_lanjut'] = $selesai;
        $data['list_data'] = $this->timbangan_fcd->get_data_export_tidak_sesuai($selesai)->result_array();
        $data['rincian_sku'] = $this->rincian_sku_per_resi($data['list_data']);

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Berat_Resi_Tidak_Sesuai_" . date('Ymd_His') . ".xls");

        $this->load->view('template_report/timbangan_tidak_sesuai_report', $data);
    }

    /**
     * Rangkum isi tiap resi jadi satu teks, mis.
     * "B2F4EP x2 @46 g = 92 g; ABC-1 x1 @100 g = 100 g".
     *
     * SKU yang belum punya berat master ditandai eksplisit, karena justru
     * itulah yang paling sering membuat berat standar sebuah resi meleset.
     *
     * @return array id_resi => array('teks' => string, 'jumlah_sku' => int)
     */
    private function rincian_sku_per_resi($list_data)
    {
        $daftar_id = array();

        foreach ($list_data as $row) {
            if (!empty($row['id_resi'])) {
                $daftar_id[] = $row['id_resi'];
            }
        }

        $items = $this->timbangan_fcd->get_items_beberapa_resi(array_unique($daftar_id));

        $hasil = array();

        foreach ($items as $item) {
            $id = $item['id_resi'];
            $berat = $item['berat_standar'];

            $potongan = $item['sku'] . ' x' . $item['jumlah'];

            if ($item['sumber_berat'] == 'KOSONG' || $berat === null) {
                $potongan .= ' @BERAT BELUM ADA';
            } else {
                $potongan .= ' @' . $this->format_gram($berat)
                    . ' = ' . $this->format_gram($berat * $item['jumlah']);
            }

            if (!isset($hasil[$id])) {
                $hasil[$id] = array('teks' => array(), 'jumlah_sku' => 0);
            }

            $hasil[$id]['teks'][] = $potongan;
            $hasil[$id]['jumlah_sku']++;
        }

        foreach ($hasil as $id => $isi) {
            $hasil[$id]['teks'] = implode('; ', $isi['teks']);
        }

        return $hasil;
    }

    /**
     * Ajax: perbandingan resi yang sudah diunggah dengan yang sudah ditimbang,
     * dikelompokkan per batch unggahan (nomor picklist).
     *
     * Resi dihitung sekali saja: satu resi yang ditimbang berkali-kali tetap
     * terhitung satu.
     */
    public function get_progres_batch()
    {
        list($start_date, $end_date) = $this->parse_reportrange($this->input->post('reportrange'));

        $rows = $this->timbangan_fcd->get_progres_batch($start_date, $end_date)->result_array();

        $total_resi = 0;
        $total_timbang = 0;

        foreach ($rows as $row) {
            $total_resi += (int) $row['jumlah_resi'];
            $total_timbang += (int) $row['jumlah_ditimbang'];
        }

        $this->make_ajax_response(200, 'OK', array(
            'batch' => $rows,
            'total_resi' => $total_resi,
            'total_ditimbang' => $total_timbang,
        ));
    }

    /**
     * Ajax: tandai satu nomor resi sudah ditindaklanjuti admin.
     *
     * Butuh kode atasan yang sama dengan pembuka kunci di layar operator.
     * Kodenya dicocokkan di sini, tidak pernah dikirim ke browser.
     */
    public function tindak_lanjut_timbangan()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $noresi = trim((string) $this->input->post('noresi'));
        $catatan = trim((string) $this->input->post('catatan'));
        $semua = $this->input->post('semua') == '1';

        if (!$semua && $noresi === '') {
            $this->make_ajax_response(400, 'Nomor resi masih kosong');
        }

        $setting = $this->timbangan_fcd->get_setting();
        $kode_benar = trim((string) $setting['KODE_SUPERVISOR']);

        if ($kode_benar === '') {
            $this->make_ajax_response(500, 'Kode atasan belum diatur. Atur dulu lewat menu Master Berat SKU > Pengaturan Timbangan.');
        }

        if (!hash_equals($kode_benar, trim((string) $this->input->post('kode_supervisor')))) {
            $this->make_ajax_response(401, 'Kode atasan salah');
        }

        if ($semua) {
            $jumlah_resi = $this->timbangan_fcd->hitung_menunggu_tindak_lanjut();
            $jumlah = $this->timbangan_fcd->tindak_lanjut_semua($this->data['user']['id_user'], $catatan);

            if ($jumlah < 1) {
                $this->make_ajax_response(200, 'Tidak ada resi yang menunggu ditindaklanjuti');
            }

            $this->make_ajax_response(200, $jumlah_resi . ' resi ditandai sudah ditindaklanjuti', array(
                'jumlah_resi' => $jumlah_resi,
                'jumlah_baris' => $jumlah,
            ));
        }

        $jumlah = $this->timbangan_fcd->tindak_lanjut_timbangan($noresi, $this->data['user']['id_user'], $catatan);

        if ($jumlah < 1) {
            $this->make_ajax_response(200, 'Resi ini sudah ditindaklanjuti sebelumnya');
        }

        $this->make_ajax_response(200, 'Resi ' . $noresi . ' ditandai sudah ditindaklanjuti', array(
            'noresi' => $noresi,
            'jumlah_baris' => $jumlah,
        ));
    }

    public function get_data_timbangan()
    {
        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $col = 0;
        $dir = '';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $data['dir'] = $dir;

        $data['valid_columns'] = array(
            0 => null,
            1 => 't.noresi',
            2 => 't.berat_standar',
            3 => 't.berat_aktual',
            4 => 't.selisih',
            5 => 't.status',
            6 => 't2.name',
            7 => 't.tanggal_timbangan',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        list($start_date, $end_date) = $this->parse_reportrange($this->input->post('reportrange'));
        $status = $this->input->post('status');

        $list = $this->timbangan_fcd->get_data_timbangan($data, $start_date, $end_date, $status);
        $total = $this->timbangan_fcd->get_total_data_timbangan($data, $start_date, $end_date, $status);

        $i = $data['start'] + 1;
        $rows = array();
        foreach ($list->result() as $row) {
            $rows[] = array(
                $i++ . '.',
                $row->noresi,
                $this->format_gram($row->berat_standar) . '<br><small class="text-muted">' . $this->format_gram($row->berat_min) . ' &ndash; ' . $this->format_gram($row->berat_max) . '</small>',
                $this->format_gram($row->berat_aktual),
                ($row->selisih > 0 ? '+' : '') . $this->format_gram($row->selisih),
                $this->badge_status($row->status),
                $row->petugas ?: '-',
                date('d-m-Y H:i:s', strtotime($row->tanggal_timbangan)),
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ));
        exit();
    }

    public function export_to_excel_timbangan()
    {
        ini_set('memory_limit', '-1');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        $status = null;

        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
            $status = $this->input->post('status');
        }

        list($start_date, $end_date) = $this->parse_reportrange($reportrange);

        $data['reportrange'] = $reportrange;
        $data['status'] = $status;
        $data['list_data'] = $this->timbangan_fcd->get_data_export($start_date, $end_date, $status)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Timbangan.xls");

        $this->load->view('template_report/timbangan_report', $data);
    }

    // ==================================================================
    // Helper
    // ==================================================================

    /**
     * Hitung berat standar paket + rentang ACCEPT.
     *
     * Berat standar = SUM(berat standar SKU x jumlah) + berat kemasan.
     * Toleransi memakai nilai global; bila ada SKU pada resi yang punya
     * override, dipakai override terbesar (paling longgar) supaya paket
     * campuran tidak ikut aturan SKU paling ketat.
     */
    /**
     * Setengah lebar pita ACCEPT, dalam gram.
     *
     * Tiga aturan bekerja bersama:
     *
     *   1. PERSEN  - dasar lama, sebanding dengan berat paket.
     *   2. MINIMUM - lantai dalam gram. Pita persen jadi mustahil dicapai pada
     *      paket ringan: paket 57 g hanya mendapat pita +/- 2,85 g, padahal
     *      selembar zipper saja bisa berbeda 3 g dan indikator membaca dalam
     *      kelipatan 1 g.
     *   3. BATAS SKU - langit-langit. Pita tidak boleh selebar barang teringan
     *      di dalam resi; kalau dilanggar, kehilangan 1 pcs tidak akan pernah
     *      terdeteksi -- padahal itu justru alasan penimbangan ini ada.
     *
     * Batas SKU sengaja tidak boleh menarik pita lebih sempit daripada pita
     * persen. Pada pesanan borongan (mis. 300 kartu @1,47 g), 80% dari satu
     * kartu cuma 1,2 g -- mustahil dicapai, dan kehilangan satu kartu pun
     * memang sudah tenggelam dalam variasi 300 kartu lainnya. Jadi aturan ini
     * hanya bisa MELONGGARKAN dibanding perilaku lama, tidak pernah
     * memperketat.
     *
     * @return array array(delta_gram, dasar_pita)
     */
    private function hitung_pita($berat_standar, $toleransi, $sku_teringan, $setting)
    {
        $persen = $berat_standar * $toleransi / 100;
        $minimum = (float) $setting['TOLERANSI_MIN_GRAM'];

        $delta = max($persen, $minimum);
        $dasar = $delta > $persen ? 'MINIMUM' : 'PERSEN';

        if ($sku_teringan > 0) {
            $batas = $sku_teringan * (float) $setting['BATAS_SKU_PERSEN'] / 100;

            // Tidak pernah lebih sempit daripada pita persen, dan tidak pernah
            // lebih sempit daripada sebaran berat kemasan itu sendiri.
            //
            // Berat kemasan terukur menyebar 34-48 g (P10-P90 dari 1.356
            // penimbangan). Barang yang lebih ringan daripada sebaran itu
            // memang tidak mungkin dideteksi hilangnya: derau kemasan sudah
            // menelannya lebih dulu. Menyempitkan pita di bawah angka ini
            // tidak menambah kemampuan deteksi sedikit pun, hanya melahirkan
            // peringatan palsu.
            $batas = max($batas, $persen, (float) $setting['VARIASI_KEMASAN_GRAM']);

            if ($batas < $delta) {
                $delta = $batas;
                $dasar = 'BATAS_SKU';
            }
        }

        return array($delta, $dasar);
    }

    /**
     * @param float|null $kemasan_pilihan berat kemasan yang dipilih operator
     *        untuk paket ini. Diisi hanya bila operator mencentang jenis
     *        kemasan tertentu (mis. kardus 300 g); selain itu dipakai nilai
     *        bawaan dari pengaturan.
     */
    private function hitung_setpoint($items, $setting, $kemasan_pilihan = null)
    {
        $berat_kemasan = $kemasan_pilihan === null
            ? (float) $setting['BERAT_KEMASAN']
            : (float) $kemasan_pilihan;
        $toleransi = (float) $setting['TOLERANSI_PERSEN'];

        $berat_standar = 0;
        $sku_tanpa_master = 0;
        $override = null;
        $sku_teringan = null;

        // Sebagian barang memang tidak seragam antar unit -- PLCA1-16 misalnya
        // terukur 172 sampai 193 g di dalam satu dus yang sama. Untuk barang
        // seperti itu, batas bawah dan batas atas resi dihitung terpisah:
        // sepuluh unit bisa saja semuanya kebetulan di sisi berat.
        $berat_atas = 0;
        $ada_rentang = FALSE;

        foreach ($items as $item) {
            $berat_pcs = (float) $item['berat_standar'];
            $jumlah = (int) $item['jumlah'];

            if ($berat_pcs <= 0) {
                $sku_tanpa_master++;
            } elseif ($sku_teringan === null || $berat_pcs < $sku_teringan) {
                $sku_teringan = $berat_pcs;
            }

            // Barang seragam: batas atasnya sama dengan beratnya sendiri.
            $atas_pcs = (isset($item['berat_max_pcs']) && $item['berat_max_pcs'] > $berat_pcs)
                ? (float) $item['berat_max_pcs']
                : $berat_pcs;

            if ($atas_pcs > $berat_pcs) {
                $ada_rentang = TRUE;
            }

            $berat_atas += $atas_pcs * $jumlah;
            $berat_standar += $berat_pcs * $jumlah;

            if ($item['toleransi_persen'] !== null) {
                $override = max((float) $item['toleransi_persen'], $override === null ? 0 : $override);
            }
        }

        if ($override !== null) {
            $toleransi = $override;
        }

        $berat_standar += $berat_kemasan;
        $berat_atas += $berat_kemasan;

        // Untuk resi berisi barang bervariasi, pita 5% tidak dipakai lagi:
        // rentang barangnya sendiri yang menentukan lebar, dan lantai gram
        // ditambahkan di kedua ujung sebagai jatah variasi kemasan serta
        // pembulatan indikator. Resi berisi barang seragam tetap dihitung
        // seperti sebelumnya.
        list($delta, $dasar_pita) = $ada_rentang
            ? array((float) $setting['TOLERANSI_MIN_GRAM'], 'RENTANG_SKU')
            : $this->hitung_pita($berat_standar, $toleransi, $sku_teringan, $setting);

        $batas_bawah = $berat_standar - $delta;
        $batas_atas = $berat_atas + $delta;

        // Langit-langit tetap berlaku: pita tidak boleh selebar barang
        // teringan, kalau tidak kehilangan 1 pcs tidak akan terdeteksi.
        if ($sku_teringan > 0) {
            $batas_sku = max(
                $sku_teringan * (float) $setting['BATAS_SKU_PERSEN'] / 100,
                $berat_standar * $toleransi / 100
            );

            $setengah = ($batas_atas - $batas_bawah) / 2;

            if ($setengah > $batas_sku) {
                $tengah = ($batas_atas + $batas_bawah) / 2;

                $batas_bawah = $tengah - $batas_sku;
                $batas_atas = $tengah + $batas_sku;
                $dasar_pita = 'BATAS_SKU';
            }
        }

        return array(
            'berat_standar' => round($berat_standar, 2),
            'berat_standar_atas' => round($berat_atas, 2),
            'ada_rentang' => $ada_rentang,
            'berat_min' => round(max(0, $batas_bawah), 2),
            'berat_max' => round($batas_atas, 2),
            'toleransi_persen' => $toleransi,
            'berat_kemasan' => $berat_kemasan,
            'sku_tanpa_master' => $sku_tanpa_master,
            'lebar_pita' => round(($batas_atas - $batas_bawah) / 2, 2),
            'dasar_pita' => $dasar_pita,
            'satuan_indikator' => $setting['SATUAN_INDIKATOR'],
            'interlock' => $setting['INTERLOCK'] == '1',
        );
    }

    private function status_berat($berat_aktual, $setpoint)
    {
        if ($berat_aktual < $setpoint['berat_min']) {
            return 'UNDER';
        }

        if ($berat_aktual > $setpoint['berat_max']) {
            return 'OVER';
        }

        return 'ACCEPT';
    }

    /**
     * Buang kode atasan sebelum setting dikirim ke view, supaya kodenya tidak
     * pernah ikut terbaca di halaman operator.
     */
    private function setting_publik($setting)
    {
        unset($setting['KODE_SUPERVISOR']);

        return $setting;
    }

    /**
     * Satuan yang dipakai untuk menafsirkan angka indikator.
     *
     * Layar operator membaca satuan langsung dari data mentah indikator
     * (mis. "wn000.102kg"), dan itu lebih bisa dipercaya daripada pengaturan
     * di database. Nilai dari browser tetap disaring supaya hanya 'g' atau
     * 'kg' yang diterima.
     */
    // ==================================================================
    // Penimbangan dua tahap untuk resi campuran
    // ==================================================================

    /**
     * Pisahkan isi resi menjadi kelompok PLCA dan kelompok sisanya.
     *
     * @return array array(items_plca, items_lain)
     */
    private function pisah_items($items)
    {
        $plca = array();
        $lain = array();

        foreach ($items as $item) {
            if (stripos((string) $item['sku'], self::AWALAN_SKU_TERPISAH) === 0) {
                $plca[] = $item;
            } else {
                $lain[] = $item;
            }
        }

        return array($plca, $lain);
    }

    /**
     * Resi butuh dua tahap hanya bila memuat PLCA DAN SKU lain sekaligus.
     * Resi yang isinya seluruhnya PLCA ditimbang seperti biasa.
     */
    private function perlu_dua_tahap($items)
    {
        list($plca, $lain) = $this->pisah_items($items);

        return !empty($plca) && !empty($lain);
    }

    /**
     * Tahap mana yang harus dikerjakan berikutnya untuk resi ini.
     *
     * Sebuah tahap dianggap selesai hanya bila sudah pernah berstatus ACCEPT,
     * sehingga tahap yang meleset masih bisa diulang dengan men-scan resinya
     * lagi. Operator tetap boleh meneruskan ke tahap 2 dalam sesi yang sama
     * walau tahap 1 meleset -- itu ditentukan oleh layar, bukan di sini.
     */
    private function tahap_berikutnya($noresi, $items)
    {
        if (!$this->perlu_dua_tahap($items)) {
            return self::TAHAP_BIASA;
        }

        if ($this->timbangan_fcd->get_timbangan_lolos($noresi, self::TAHAP_TERPISAH)->num_rows() < 1) {
            return self::TAHAP_TERPISAH;
        }

        return self::TAHAP_SISANYA;
    }

    /**
     * Isi resi yang ditimbang pada tahap tertentu.
     */
    /**
     * Tahap yang diminta layar, disaring terhadap isi resi.
     *
     * Layar boleh meminta tahap 2 walau tahap 1 meleset -- operator memang
     * diizinkan meneruskan. Permintaan yang tidak masuk akal (mis. tahap 2
     * untuk resi yang tidak campuran) dikembalikan ke perhitungan riwayat.
     */
    private function tahap_diminta($items)
    {
        if (!$this->perlu_dua_tahap($items)) {
            return self::TAHAP_BIASA;
        }

        $diminta = (int) $this->input->post('tahap');

        if (in_array($diminta, array(self::TAHAP_TERPISAH, self::TAHAP_SISANYA), TRUE)) {
            return $diminta;
        }

        return $this->tahap_berikutnya($this->input->post('noresi'), $items);
    }

    private function items_tahap($items, $tahap)
    {
        if ($tahap == self::TAHAP_BIASA) {
            return $items;
        }

        list($plca, $lain) = $this->pisah_items($items);

        return $tahap == self::TAHAP_TERPISAH ? $plca : $lain;
    }

    /**
     * Berat kemasan yang berlaku untuk tahap tertentu.
     *
     * Tiap tahap memakai kemasannya sendiri -- satu resi campuran memang
     * menghabiskan dua kemasan. Yang menentukan tetap operator lewat pilihan
     * di layar: PLCA yang dibungkus dusnya sendiri tinggal dipilih "Dus PLCA",
     * dan PLCA tanpa dus cukup dibiarkan memakai zipper bawaan.
     *
     * Karena itu tidak ada perlakuan khusus per tahap di sini; operator
     * menyesuaikan pilihannya saat berpindah tahap.
     */
    private function kemasan_tahap($tahap)
    {
        return $this->kemasan_pilihan();
    }

    /**
     * Berat kemasan yang dipilih operator untuk paket yang sedang ditangani.
     *
     * Pilihan ini berlaku PER PAKET, tidak mengubah pengaturan global -- di
     * gudang ada beberapa meja timbang yang bekerja bersamaan, dan menulis
     * pilihan satu meja ke pengaturan bersama akan merusak hitungan meja lain.
     *
     * Yang dikirim browser hanya KODE kemasan; beratnya dicari sendiri di sini.
     * Dengan begitu angka berat tidak bisa dikarang dari sisi klien, dan jenis
     * yang beratnya belum didata otomatis jatuh ke nilai bawaan.
     */
    private function kemasan_pilihan()
    {
        $kode = trim((string) $this->input->post('kode_kemasan'));

        if ($kode === '') {
            return null;
        }

        // Jumlah tiap komponen dikirim layar sebagai id_komponen => jumlah.
        // Hanya jumlahnya yang dipercaya; beratnya tetap dicari di sini supaya
        // angka kemasan tidak bisa dikarang dari browser.
        $jumlah_kiriman = $this->input->post('jumlah_kemasan');
        $jumlah_kiriman = is_array($jumlah_kiriman) ? $jumlah_kiriman : array();

        foreach ($this->timbangan_fcd->get_kemasan() as $kemasan) {
            if ($kemasan['kode'] !== $kode) {
                continue;
            }

            // Ada komponen yang beratnya belum ditimbang: lebih baik kembali
            // ke nilai bawaan daripada menghitung dengan angka yang bolong.
            if (empty($kemasan['lengkap'])) {
                return null;
            }

            $total = 0;

            foreach ($kemasan['komponen'] as $komponen) {
                $jumlah = 1;

                if ($komponen['tanya_jumlah']) {
                    $dikirim = isset($jumlah_kiriman[$komponen['id']]) ? (int) $jumlah_kiriman[$komponen['id']] : 0;
                    $jumlah = max(0, min(self::MAKS_JUMLAH_KEMASAN, $dikirim));
                }

                $total += $komponen['berat'] * $jumlah;
            }

            return $total > 0 ? $total : null;
        }

        return null;
    }

    private function satuan_terpakai($setting)
    {
        $satuan = strtolower(trim((string) $this->input->post('satuan_indikator')));

        return in_array($satuan, array('g', 'kg'), TRUE) ? $satuan : $setting['SATUAN_INDIKATOR'];
    }

    private function to_gram($nilai, $satuan)
    {
        return $satuan == 'kg' ? (float) $nilai * 1000 : (float) $nilai;
    }

    private function format_gram($gram)
    {
        return number_format((float) $gram, 0, ',', '.') . ' g';
    }

    /**
     * Penanda tahap untuk resi campuran PLCA. Resi biasa ditandai '-' supaya
     * kolomnya tidak menimbulkan pertanyaan.
     */
    private function label_tahap($tahap)
    {
        if ($tahap == self::TAHAP_TERPISAH) {
            return '<span class="label label-primary">1 &middot; PLCA</span>';
        }

        if ($tahap == self::TAHAP_SISANYA) {
            return '<span class="label label-info">2 &middot; Lainnya</span>';
        }

        return '<span class="text-muted">-</span>';
    }

    private function badge_status($status)
    {
        $kelas = array(
            'ACCEPT' => 'label-success',
            'UNDER' => 'label-warning',
            'OVER' => 'label-danger',
        );

        return '<span class="label ' . ($kelas[$status] ?? 'label-default') . '">' . $status . '</span>';
    }

    private function parse_reportrange($reportrange)
    {
        if (empty($reportrange) || strpos($reportrange, ' - ') === FALSE) {
            return array(null, null);
        }

        $parts = explode(' - ', $reportrange);

        return array(
            date('Y-m-d H:i:s', strtotime($parts[0])),
            date('Y-m-d H:i:s', strtotime($parts[1])),
        );
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picker_performance extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->model('picking_fcd');
        $this->load->model('kpi_fcd');
        
        // Cek akses - hanya admin dan webmaster
        $this->check_access();
    }

    private function check_access()
    {
        $user = $this->session->userdata('user');
        
        if (!$user || !isset($user['id_user'])) {
            $this->session->set_flashdata('message', 'Access denied. Please login first.');
            redirect('welcome/restricted');
        }
        
        // Cek hakakses - hanya admin (hakakses = 1) dan webmaster/manager (hakakses = 2)
        if (!isset($user['hakakses']) || !in_array($user['hakakses'], [1, 2])) {
            $this->session->set_flashdata('message', 'Access denied. Only admin and webmaster can access Picker Performance.');
            redirect('welcome/restricted');
        }
    }

    public function index()
    {
        // Load menu helper
        $this->load->helper('menu_helper');
        
        $data['message'] = $this->session->flashdata('message');

        // Set default date range (today)
        $data['reportrange'] = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d 23:59:59');
        if ($this->input->method() == 'post') {
            $data['reportrange'] = $this->input->post('reportrange');
        }

        // Parse date range
        $dates = explode(' - ', $data['reportrange']);
        $start_date = $dates[0];
        $end_date = $dates[1];

        // Get picker performance data
        $data['performance_data'] = $this->get_performance_data($start_date, $end_date);
        
        // Check if this is AJAX request (from menu click)
        if ($this->input->is_ajax_request() || $this->input->get('ajax')) {
            // Return JSON for AJAX (like other menu pages)
            echo json_encode(array(
                'view' => $this->load->view('picker_performance_dashboard', $data, TRUE),
                'message' => empty($data['message']) ? null : $data['message'],
            ));
        } else {
            // Return full HTML for direct access
            $this->data['user'] = $this->session->userdata('user');
            $this->data['nama_pk'] = $this->session->userdata('nama_pk');
            $this->data['status_performa'] = $this->session->userdata('status_performa');
            $this->data['html_menu_tree'] = $this->session->userdata('html_menu_tree');
            $this->data['content'] = $this->load->view('picker_performance_dashboard', $data, TRUE);
            
            // Load main template
            $this->load->view('main', $this->data);
        }
    }

    private function get_performance_data($start_date, $end_date)
    {
        $data = array();

        try {
            // Get TIM INTI (registered pickers)
            $tim_inti_query = $this->db->query("
                SELECT 
                    u.id_user,
                    u.username,
                    peg.kode_pegawai,
                    peg.nama_pegawai,
                    'TIM INTI' as tim_kategori,
                    
                    -- Jam Kerja
                    MIN(rab.tanggal_resiambilbarang) as jam_in,
                    MAX(rab.tanggal_resiambilbarang) as jam_out,
                    TIMESTAMPDIFF(HOUR, MIN(rab.tanggal_resiambilbarang), MAX(rab.tanggal_resiambilbarang)) as total_jam,
                    
                    -- Target & Achievement
                    400 as target_paket, -- Default target
                    COUNT(DISTINCT rab.id_resi) as jumlah_paket,
                    (COUNT(DISTINCT rab.id_resi) - 400) as selisih,
                    ROUND((COUNT(DISTINCT rab.id_resi) / 400) * 100, 2) as persentase_capai,
                    
                    -- Produktivitas
                    ROUND(COUNT(DISTINCT rab.id_resi) / GREATEST(TIMESTAMPDIFF(HOUR, MIN(rab.tanggal_resiambilbarang), MAX(rab.tanggal_resiambilbarang)), 1), 2) as rata_per_jam,
                    
                    -- Paket dengan SKU > 50
                    COUNT(DISTINCT CASE 
                        WHEN (SELECT COUNT(*) FROM tbldetailprintresi WHERE id_resi = rab.id_resi) > 50 
                        THEN rab.id_resi 
                    END) as paket_50_sku,
                    
                    -- Status
                    CASE 
                        WHEN COUNT(DISTINCT rab.id_resi) >= 400 THEN 'GOOD'
                        WHEN COUNT(DISTINCT rab.id_resi) >= 300 THEN 'FAIR'
                        ELSE 'POOR'
                    END as status_performa
                    
                FROM tblnamaambilbarang nab
                INNER JOIN tblpegawai peg ON peg.kode_pegawai = nab.id_pegawai
                LEFT JOIN tbluser u ON u.id_pegawai = peg.kode_pegawai
                LEFT JOIN tblresiambilbarang rab ON rab.yangambil_pegawai = peg.kode_pegawai 
                    AND rab.tanggal_resiambilbarang BETWEEN ? AND ?
                WHERE nab.status_aktif = 'AKTIF'
                AND peg.status_aktif = 'AKTIF'
                GROUP BY u.id_user, u.username, peg.kode_pegawai, peg.nama_pegawai
                ORDER BY jumlah_paket DESC
            ", array($start_date, $end_date));
            
            $data['tim_inti'] = $tim_inti_query->result_array();

            // Get TIM OTHERS (non-registered pickers who did picking)
            $tim_others_query = $this->db->query("
                SELECT 
                    u.id_user,
                    u.username,
                    peg.kode_pegawai,
                    peg.nama_pegawai,
                    'TIM OTHERS' as tim_kategori,
                    
                    -- Jam Kerja
                    MIN(rab.tanggal_resiambilbarang) as jam_in,
                    MAX(rab.tanggal_resiambilbarang) as jam_out,
                    TIMESTAMPDIFF(HOUR, MIN(rab.tanggal_resiambilbarang), MAX(rab.tanggal_resiambilbarang)) as total_jam,
                    
                    -- Target & Achievement
                    400 as target_paket,
                    COUNT(DISTINCT rab.id_resi) as jumlah_paket,
                    (COUNT(DISTINCT rab.id_resi) - 400) as selisih,
                    ROUND((COUNT(DISTINCT rab.id_resi) / 400) * 100, 2) as persentase_capai,
                    
                    -- Produktivitas
                    ROUND(COUNT(DISTINCT rab.id_resi) / GREATEST(TIMESTAMPDIFF(HOUR, MIN(rab.tanggal_resiambilbarang), MAX(rab.tanggal_resiambilbarang)), 1), 2) as rata_per_jam,
                    
                    -- Paket dengan SKU > 50
                    COUNT(DISTINCT CASE 
                        WHEN (SELECT COUNT(*) FROM tbldetailprintresi WHERE id_resi = rab.id_resi) > 50 
                        THEN rab.id_resi 
                    END) as paket_50_sku,
                    
                    -- Status
                    CASE 
                        WHEN COUNT(DISTINCT rab.id_resi) >= 400 THEN 'GOOD'
                        WHEN COUNT(DISTINCT rab.id_resi) >= 300 THEN 'FAIR'
                        ELSE 'POOR'
                    END as status_performa
                    
                FROM tblresiambilbarang rab
                INNER JOIN tblpegawai peg ON peg.kode_pegawai = rab.yangambil_pegawai
                LEFT JOIN tbluser u ON u.id_pegawai = peg.kode_pegawai
                LEFT JOIN tblnamaambilbarang nab ON nab.id_pegawai = peg.kode_pegawai AND nab.status_aktif = 'AKTIF'
                WHERE rab.tanggal_resiambilbarang BETWEEN ? AND ?
                AND nab.id_namaambilbarang IS NULL -- Yang tidak terdaftar sebagai picker tetap
                GROUP BY u.id_user, u.username, peg.kode_pegawai, peg.nama_pegawai
                ORDER BY jumlah_paket DESC
            ", array($start_date, $end_date));
            
            $data['tim_others'] = $tim_others_query->result_array();

            // Summary statistics
            $all_pickers = array_merge($data['tim_inti'], $data['tim_others']);
            
            $total_picker = count($all_pickers);
            $total_paket = array_sum(array_column($all_pickers, 'jumlah_paket'));
            $total_jam = array_sum(array_column($all_pickers, 'total_jam'));
            $total_target = $total_picker * 400;
            
            $data['summary'] = array(
                'tanggal' => date('Y-m-d', strtotime($start_date)),
                'hari' => $this->get_indonesian_day(date('Y-m-d', strtotime($start_date))),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_picker' => $total_picker,
                'total_tim_inti' => count($data['tim_inti']),
                'total_tim_others' => count($data['tim_others']),
                'total_paket' => $total_paket,
                'total_target' => $total_target,
                'selisih' => $total_paket - $total_target,
                'persentase_capai' => $total_target > 0 ? round(($total_paket / $total_target) * 100, 2) : 0,
                'total_jam' => $total_jam,
                'rata_paket_per_orang' => $total_picker > 0 ? round($total_paket / $total_picker, 2) : 0,
                'rata_paket_per_jam' => $total_jam > 0 ? round($total_paket / $total_jam, 2) : 0,
                'rata_jam_per_orang' => $total_picker > 0 ? round($total_jam / $total_picker, 2) : 0,
            );

        } catch (Exception $e) {
            log_message('error', 'Error getting picker performance data: ' . $e->getMessage());
            $data['tim_inti'] = array();
            $data['tim_others'] = array();
            $data['summary'] = array();
        }

        return $data;
    }

    private function get_indonesian_day($tanggal)
    {
        $days = array(
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        );
        
        $english_day = date('l', strtotime($tanggal));
        return isset($days[$english_day]) ? $days[$english_day] : $english_day;
    }

    public function export_excel()
    {
        ini_set('memory_limit', '-1');
        
        $reportrange = $this->input->post('reportrange');
        if (empty($reportrange)) {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d 23:59:59');
        }

        // Parse date range
        $dates = explode(' - ', $reportrange);
        $start_date = $dates[0];
        $end_date = $dates[1];

        $data['tanggal'] = date('Y-m-d', strtotime($start_date));
        $data['reportrange'] = $reportrange;
        $data['performance_data'] = $this->get_performance_data($start_date, $end_date);

        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Picker_" . $tanggal . ".xls");

        $this->load->view('template_report/picker_performance_excel', $data);
    }
}


<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kpi_reports extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->model('receipt_fcd');
        $this->load->model('kpi_fcd');
        
        // Cek akses - hanya admin dan webmaster
        $this->check_kpi_access();
    }

    private function check_kpi_access()
    {
        $user = $this->session->userdata('user');
        
        if (!$user || !isset($user['id_user'])) {
            $this->session->set_flashdata('message', 'Access denied. Please login first.');
            redirect('welcome/restricted');
        }
        
        // Cek hakakses - hanya admin (hakakses = 1) dan webmaster/manager (hakakses = 2)
        if (!isset($user['hakakses']) || !in_array($user['hakakses'], [1, 2])) {
            $this->session->set_flashdata('message', 'Access denied. Only admin and webmaster can access KPI Reports.');
            redirect('welcome/restricted');
        }
    }

    public function index()
    {
        $data['message'] = $this->session->flashdata('message');

        // Set default date range untuk KPI Reports
        $data['reportrange'] = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $data['reportrange'] = $this->input->post('reportrange');
        }

        // Set data untuk main view - ikuti pola Welcome controller
        $this->data['user'] = $this->session->userdata('user');
        $this->data['nama_pk'] = $this->session->userdata('nama_pk');
        $this->data['status_performa'] = $this->session->userdata('status_performa');
        $this->data['html_menu_tree'] = $this->session->userdata('html_menu_tree');
        $this->data['content'] = $this->load->view('kpi_reports_index', $data, TRUE);
        
        $this->load->view('main', $this->data);
    }

    public function export()
    {
        $data['message'] = $this->session->flashdata('message');

        // Set default date range untuk export
        $data['reportrange'] = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $data['reportrange'] = $this->input->post('reportrange');
            // Jika ada data POST, langsung export
            $this->export_to_excel();
        } else {
            // Jika tidak ada data POST, tampilkan halaman export
            $this->data['user'] = $this->session->userdata('user');
            $this->data['nama_pk'] = $this->session->userdata('nama_pk');
            $this->data['status_performa'] = $this->session->userdata('status_performa');
            $this->data['html_menu_tree'] = $this->session->userdata('html_menu_tree');
            $this->data['content'] = $this->load->view('kpi_reports_export', $data, TRUE);
            $this->load->view('main', $this->data);
        }
    }

    public function get_kpi_data()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        try {
            // Ambil data KPI langsung dari tabel tblresi
            $data = array();

            // KPI Summary Cards - dari tabel tblprintresi
            $summary_query = $this->db->query("
                SELECT 
                    COUNT(DISTINCT pr.id_printresi) as total_resi,
                    COUNT(DISTINCT pr.created_by) as total_users_scan,
                    COUNT(DISTINCT rab.yangambil_pegawai) as total_users_picker,
                    COUNT(DISTINCT p.packer_pegawai) as total_users_packer,
                    COUNT(DISTINCT rk.id_pegawai) as total_users_ho
                FROM tblprintresi pr
                LEFT JOIN tblresiambilbarang rab ON rab.id_resi = pr.id_printresi
                LEFT JOIN tblpacking p ON p.id_resi = pr.id_printresi
                LEFT JOIN tblresikeluar rk ON rk.id_resi = pr.id_printresi
                WHERE pr.tanggal_printresi BETWEEN ? AND ?
            ", array($start_date, $end_date));
            
            $summary = $summary_query->row_array();
            $total_users = ($summary['total_users_scan'] ?? 0) + 
                          ($summary['total_users_picker'] ?? 0) + 
                          ($summary['total_users_packer'] ?? 0) + 
                          ($summary['total_users_ho'] ?? 0);
            
            $data['kpi_summary'] = array(
                'total_status' => 4, // Scan, Picker, Packer, HO
                'total_users' => $total_users,
                'total_transactions' => $summary['total_resi'] ?? 0,
                'rata_rata_capai' => 85 // Placeholder
            );

            // Status Performa Cards - berdasarkan proses (Scan, Picker, Packer, HO)
            $status_query = $this->db->query("
                SELECT 
                    'SCAN' as kode_status,
                    'Resi Scan' as nama_status,
                    COUNT(DISTINCT pr.id_printresi) as total_transaksi,
                    COUNT(DISTINCT pr.created_by) as total_user,
                    'GOOD' as status_performa,
                    85 as rata_rata_capai
                FROM tblprintresi pr
                WHERE pr.tanggal_printresi BETWEEN ? AND ?
                
                UNION ALL
                
                SELECT 
                    'PICKER' as kode_status,
                    'Picking' as nama_status,
                    COUNT(DISTINCT rab.id_resi) as total_transaksi,
                    COUNT(DISTINCT rab.yangambil_pegawai) as total_user,
                    'GOOD' as status_performa,
                    80 as rata_rata_capai
                FROM tblresiambilbarang rab
                WHERE rab.tanggal_resiambilbarang BETWEEN ? AND ?
                
                UNION ALL
                
                SELECT 
                    'PACKER' as kode_status,
                    'Packing' as nama_status,
                    COUNT(DISTINCT p.id_resi) as total_transaksi,
                    COUNT(DISTINCT p.packer_pegawai) as total_user,
                    'GOOD' as status_performa,
                    75 as rata_rata_capai
                FROM tblpacking p
                WHERE p.tanggal_packing BETWEEN ? AND ?
                
                UNION ALL
                
                SELECT 
                    'HO' as kode_status,
                    'Hand Over' as nama_status,
                    COUNT(DISTINCT rk.id_resi) as total_transaksi,
                    COUNT(DISTINCT rk.id_pegawai) as total_user,
                    'GOOD' as status_performa,
                    90 as rata_rata_capai
                FROM tblresikeluar rk
                WHERE rk.tanggal_resikeluar BETWEEN ? AND ?
            ", array($start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date));
            $data['status_performa'] = $status_query->result_array();

            // Top Performers - top 10 user berdasarkan transaksi (Picker + Packer)
            $top_query = $this->db->query("
                SELECT 
                    u.username as nama_user,
                    peg.nama_pegawai,
                    'PICKER' as nama_status,
                    COUNT(rab.id_resi) as total_transaksi,
                    COUNT(DISTINCT DATE(rab.tanggal_resiambilbarang)) as hari_aktif,
                    ROUND(COUNT(rab.id_resi) / NULLIF(COUNT(DISTINCT DATE(rab.tanggal_resiambilbarang)), 0), 2) as rata_rata_harian
                FROM tblresiambilbarang rab
                LEFT JOIN tblpegawai peg ON peg.kode_pegawai = rab.yangambil_pegawai
                LEFT JOIN tbluser u ON u.id_pegawai = peg.kode_pegawai
                WHERE rab.tanggal_resiambilbarang BETWEEN ? AND ?
                AND rab.yangambil_pegawai IS NOT NULL
                GROUP BY u.username, peg.nama_pegawai
                
                UNION ALL
                
                SELECT 
                    u.username as nama_user,
                    peg.nama_pegawai,
                    'PACKER' as nama_status,
                    COUNT(p.id_resi) as total_transaksi,
                    COUNT(DISTINCT DATE(p.tanggal_packing)) as hari_aktif,
                    ROUND(COUNT(p.id_resi) / NULLIF(COUNT(DISTINCT DATE(p.tanggal_packing)), 0), 2) as rata_rata_harian
                FROM tblpacking p
                LEFT JOIN tblpegawai peg ON peg.kode_pegawai = p.packer_pegawai
                LEFT JOIN tbluser u ON u.id_pegawai = peg.kode_pegawai
                WHERE p.tanggal_packing BETWEEN ? AND ?
                AND p.packer_pegawai IS NOT NULL
                GROUP BY u.username, peg.nama_pegawai
                
                ORDER BY total_transaksi DESC
                LIMIT 10
            ", array($start_date, $end_date, $start_date, $end_date));
            $data['top_performers'] = $top_query->result_array();

            // Daily Performance Chart - transaksi per hari per proses
            $daily_query = $this->db->query("
                SELECT 
                    DATE(pr.tanggal_printresi) as tanggal,
                    'SCAN' as kode_status,
                    COUNT(pr.id_printresi) as total_transaksi
                FROM tblprintresi pr
                WHERE pr.tanggal_printresi BETWEEN ? AND ?
                GROUP BY DATE(pr.tanggal_printresi)
                
                UNION ALL
                
                SELECT 
                    DATE(rab.tanggal_resiambilbarang) as tanggal,
                    'PICKER' as kode_status,
                    COUNT(rab.id_resi) as total_transaksi
                FROM tblresiambilbarang rab
                WHERE rab.tanggal_resiambilbarang BETWEEN ? AND ?
                GROUP BY DATE(rab.tanggal_resiambilbarang)
                
                UNION ALL
                
                SELECT 
                    DATE(p.tanggal_packing) as tanggal,
                    'PACKER' as kode_status,
                    COUNT(p.id_resi) as total_transaksi
                FROM tblpacking p
                WHERE p.tanggal_packing BETWEEN ? AND ?
                GROUP BY DATE(p.tanggal_packing)
                
                UNION ALL
                
                SELECT 
                    DATE(rk.tanggal_resikeluar) as tanggal,
                    'HO' as kode_status,
                    COUNT(rk.id_resi) as total_transaksi
                FROM tblresikeluar rk
                WHERE rk.tanggal_resikeluar BETWEEN ? AND ?
                GROUP BY DATE(rk.tanggal_resikeluar)
                
                ORDER BY tanggal ASC, kode_status ASC
            ", array($start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date));
            $data['daily_chart'] = $daily_query->result_array();

            // Status Performance Distribution
            $dist_query = $this->db->query("
                SELECT 'Resi Scan' as nama_status, COUNT(*) as total_transaksi FROM tblprintresi WHERE tanggal_printresi BETWEEN ? AND ?
                UNION ALL
                SELECT 'Picking' as nama_status, COUNT(*) as total_transaksi FROM tblresiambilbarang WHERE tanggal_resiambilbarang BETWEEN ? AND ?
                UNION ALL
                SELECT 'Packing' as nama_status, COUNT(*) as total_transaksi FROM tblpacking WHERE tanggal_packing BETWEEN ? AND ?
                UNION ALL
                SELECT 'Hand Over' as nama_status, COUNT(*) as total_transaksi FROM tblresikeluar WHERE tanggal_resikeluar BETWEEN ? AND ?
            ", array($start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date));
            $data['status_distribution'] = $dist_query->result_array();

            // Metrics untuk KPI table
            $total_resi = $summary['total_resi'] ?? 0;
            $data['total_receipts'] = $total_resi;
            $data['shipped_receipts'] = round($total_resi * 0.85);
            $data['pending_receipts'] = round($total_resi * 0.10);
            $data['retur_receipts'] = round($total_resi * 0.05);
            $data['completion_rate'] = $total_resi > 0 ? round(($data['shipped_receipts'] / $total_resi) * 100, 2) : 0;
            $data['retur_rate'] = $total_resi > 0 ? round(($data['retur_receipts'] / $total_resi) * 100, 2) : 0;
            $data['avg_processing_time'] = 18;
            $data['picker_productivity'] = 65;
            $data['packer_productivity'] = 70;

            echo json_encode(array('success' => true, 'data' => $data));
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    private function update_kpi_range($start_date, $end_date)
    {
        // Update KPI untuk setiap hari dalam range
        $current_date = $start_date;
        while (strtotime($current_date) <= strtotime($end_date)) {
            $this->kpi_fcd->update_kpi_harian($current_date);
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
    }

    public function export_to_excel()
    {
        ini_set('memory_limit', '-1');
        
        // Handle different export ranges
        $range = $this->input->get('range');
        if ($range) {
            switch ($range) {
                case 'yesterday':
                    $reportrange = date('Y-m-d 00:00:00', strtotime('-1 day')) . ' - ' . date('Y-m-d 23:59:59', strtotime('-1 day'));
                    break;
                case 'this_week':
                    $reportrange = date('Y-m-d 00:00:00', strtotime('monday this week')) . ' - ' . date('Y-m-d H:i:s');
                    break;
                case 'this_month':
                    $reportrange = date('Y-m-01 00:00:00') . ' - ' . date('Y-m-d H:i:s');
                    break;
                default:
                    $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
            }
        } else {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
            if ($this->input->method() == 'post') {
                $reportrange = $this->input->post('reportrange');
            }
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['kpi_data'] = $this->get_kpi_data_for_export($start_date, $end_date);

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=KPI_Reports_" . date('Y-m-d') . ".xls");

        $this->load->view('template_report/kpi_reports', $data);
    }

    private function get_kpi_data_for_export($start_date, $end_date)
    {
        // Ambil data KPI untuk export
        $data = array();

        $total_receipts = $this->receipt_fcd->get_total_receipts_processed($start_date, $end_date);
        $shipped_receipts = $this->receipt_fcd->get_total_shipped_receipts($start_date, $end_date);
        $pending_receipts = $this->receipt_fcd->get_total_pending_receipts($start_date, $end_date);
        $retur_receipts = $this->receipt_fcd->get_total_retur_receipts($start_date, $end_date);

        $completion_rate = $total_receipts > 0 ? ($shipped_receipts / $total_receipts) * 100 : 0;
        $retur_rate = $total_receipts > 0 ? ($retur_receipts / $total_receipts) * 100 : 0;

        $data['total_receipts'] = $total_receipts;
        $data['shipped_receipts'] = $shipped_receipts;
        $data['pending_receipts'] = $pending_receipts;
        $data['retur_receipts'] = $retur_receipts;
        $data['completion_rate'] = round($completion_rate, 2);
        $data['retur_rate'] = round($retur_rate, 2);
        $data['avg_processing_time'] = $this->receipt_fcd->get_avg_processing_time($start_date, $end_date);
        $data['picker_productivity'] = $this->receipt_fcd->get_picker_productivity($start_date, $end_date);
        $data['packer_productivity'] = $this->receipt_fcd->get_packer_productivity($start_date, $end_date);
        $data['daily_performance'] = $this->receipt_fcd->get_daily_performance($start_date, $end_date);

        return $data;
    }
}
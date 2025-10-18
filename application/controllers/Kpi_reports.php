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
            // Ambil data KPI dari view yang sudah dibuat
            $data = array();

            // KPI Summary Cards - dari view vw_kpi_dashboard
            $summary_query = $this->db->query("
                SELECT 
                    COUNT(DISTINCT kode_status) as total_status,
                    SUM(total_user) as total_users,
                    SUM(total_transaksi) as total_transactions,
                    AVG(persentase_capai) as avg_achievement
                FROM vw_kpi_dashboard
                WHERE tanggal BETWEEN ? AND ?
            ", array($start_date, $end_date));
            
            $summary = $summary_query->row_array();
            $data['kpi_summary'] = array(
                'total_status' => $summary['total_status'] ?? 0,
                'total_users' => $summary['total_users'] ?? 0,
                'total_transactions' => $summary['total_transactions'] ?? 0,
                'avg_achievement' => round($summary['avg_achievement'] ?? 0, 2)
            );

            // Status Performa Cards - top 4 status
            $status_query = $this->db->query("
                SELECT kode_status, nama_status, SUM(total_transaksi) as total, AVG(persentase_capai) as achievement
                FROM vw_kpi_dashboard
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY kode_status, nama_status
                ORDER BY total DESC
                LIMIT 4
            ", array($start_date, $end_date));
            $data['status_performa'] = $status_query->result_array();

            // KPI Dashboard Data - detailed
            $dashboard_query = $this->db->query("
                SELECT *
                FROM vw_kpi_dashboard
                WHERE tanggal BETWEEN ? AND ?
                ORDER BY tanggal DESC, total_transaksi DESC
            ", array($start_date, $end_date));
            $data['kpi_dashboard'] = $dashboard_query->result_array();

            // Top Performers
            $top_query = $this->db->query("
                SELECT *
                FROM vw_top_performers
                WHERE tanggal BETWEEN ? AND ?
                ORDER BY total_transaksi DESC
                LIMIT 10
            ", array($start_date, $end_date));
            $data['top_performers'] = $top_query->result_array();

            // Daily Performance Chart
            $daily_query = $this->db->query("
                SELECT tanggal, SUM(total_transaksi) as total, AVG(persentase_capai) as achievement
                FROM vw_kpi_dashboard
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY tanggal
                ORDER BY tanggal ASC
            ", array($start_date, $end_date));
            $data['daily_chart'] = $daily_query->result_array();

            // Status Performance Distribution
            $dist_query = $this->db->query("
                SELECT kode_status as status, SUM(total_transaksi) as total
                FROM vw_kpi_dashboard
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY kode_status
                ORDER BY total DESC
            ", array($start_date, $end_date));
            $data['status_distribution'] = $dist_query->result_array();

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
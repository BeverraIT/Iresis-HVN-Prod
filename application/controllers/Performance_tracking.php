<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Performance_tracking extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
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
            $this->session->set_flashdata('message', 'Access denied. Only admin and webmaster can access Performance Tracking.');
            redirect('welcome/restricted');
        }
    }

    // Method untuk log status performa saat login
    public function log_login_status()
    {
        $user = $this->session->userdata('user');
        $status_id = $this->input->post('status_id');
        
        if (!$user || !$status_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }
        
        $result = $this->kpi_fcd->log_status_performa($user['id_user'], $status_id);
        
        if ($result) {
            // Update session dengan status performa
            $user_status = $this->kpi_fcd->get_user_status_performa($user['id_user']);
            if ($user_status) {
                $this->session->set_userdata('user_status_performa', $user_status);
            }
            echo json_encode(['success' => true, 'message' => 'Status performa berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal update status performa']);
        }
    }

    // Method untuk log transaksi (packing/picking)
    public function log_transaction()
    {
        $user = $this->session->userdata('user');
        $tipe_transaksi = $this->input->post('tipe_transaksi');
        $jumlah_resi = $this->input->post('jumlah_resi', 1);
        
        if (!$user || !$tipe_transaksi) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }
        
        // Ambil status performa user dari session
        $user_status = $this->session->userdata('user_status_performa');
        
        if (!$user_status) {
            // Jika tidak ada di session, ambil dari database
            $user_status = $this->kpi_fcd->get_user_status_performa($user['id_user']);
        }

        if ($user_status) {
            $result = $this->kpi_fcd->log_transaksi_harian(
                $user['id_user'], 
                $user_status->id_statusperforma, 
                $tipe_transaksi, 
                $jumlah_resi
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dicatat']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mencatat transaksi']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Status performa tidak ditemukan']);
        }
    }

    // Method untuk mendapatkan performa user hari ini
    public function get_user_performance()
    {
        $user = $this->session->userdata('user');
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }

        $user_status = $this->kpi_fcd->get_user_status_performa($user['id_user'], $tanggal);
        $transactions = $this->kpi_fcd->get_transaksi_harian($user['id_user'], $tanggal);

        $performance = array(
            'user_status' => $user_status,
            'transactions' => $transactions,
            'total_packing' => 0,
            'total_picking' => 0,
            'total_transaksi' => 0
        );

        foreach ($transactions as $transaction) {
            if ($transaction->tipe_transaksi == 'PACKING') {
                $performance['total_packing'] += $transaction->jumlah_resi;
            } else if ($transaction->tipe_transaksi == 'PICKING') {
                $performance['total_picking'] += $transaction->jumlah_resi;
            }
        }

        $performance['total_transaksi'] = $performance['total_packing'] + $performance['total_picking'];

        echo json_encode(['success' => true, 'data' => $performance]);
    }

    // Method untuk mendapatkan ranking performa
    public function get_performance_ranking()
    {
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
        $limit = $this->input->get('limit') ?: 10;

        $result = $this->kpi_fcd->get_top_performers($tanggal, $tanggal, $limit);
        
        echo json_encode(['success' => true, 'data' => $result->result()]);
    }

    // Method untuk mendapatkan data real-time
    public function get_realtime_data()
    {
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
        
        $result = $this->kpi_fcd->get_realtime_performance($tanggal);
        
        echo json_encode(['success' => true, 'data' => $result->result()]);
    }

    // Method untuk update KPI harian (bisa dipanggil via cron job)
    public function update_daily_kpi()
    {
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        
        $result = $this->kpi_fcd->update_kpi_harian($tanggal);
        
        if ($result) {
            log_message('info', "KPI harian updated for date: $tanggal");
            echo json_encode(['success' => true, 'message' => "KPI harian berhasil diupdate untuk tanggal $tanggal"]);
        } else {
            log_message('error', "Failed to update KPI harian for date: $tanggal");
            echo json_encode(['success' => false, 'message' => "Gagal update KPI harian untuk tanggal $tanggal"]);
        }
    }

    // Method untuk mendapatkan status performa yang tersedia
    public function get_available_status()
    {
        $result = $this->kpi_fcd->get_status_performa();
        
        echo json_encode(['success' => true, 'data' => $result->result()]);
    }

    // Method untuk export performa harian
    public function export_daily_performance()
    {
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

        // Update KPI harian terlebih dahulu
        $this->kpi_fcd->update_kpi_harian($tanggal);

        // Ambil data performa
        $kpi_data = $this->kpi_fcd->get_realtime_performance($tanggal)->result();
        $top_performers = $this->kpi_fcd->get_top_performers($tanggal, $tanggal, 50)->result();

        $data = array(
            'tanggal' => $tanggal,
            'kpi_data' => $kpi_data,
            'top_performers' => $top_performers
        );

        // Set header untuk download Excel
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Daily_Performance_$tanggal.xls");

        $this->load->view('template_report/daily_performance_export', $data);
    }
}
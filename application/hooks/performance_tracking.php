<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Performance_tracking_hook
{
    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function track_login()
    {
        // Cek apakah user sudah login
        $user = $this->CI->session->userdata('user');
        
        if ($user && isset($user['id_user'])) {
            // Load model KPI
            $this->CI->load->model('kpi_fcd');
            
            // Ambil status performa default (NORMAL) jika belum ada
            $status_performa = $this->CI->session->userdata('user_status_performa');
            
            if (!$status_performa) {
                // Set default status performa ke NORMAL
                $normal_status = $this->CI->kpi_fcd->get_status_performa()->where('kode_status', 'NORMAL')->get()->row();
                
                if ($normal_status) {
                    // Log status performa
                    $this->CI->kpi_fcd->log_status_performa($user['id_user'], $normal_status->id_statusperforma);
                    
                    // Set ke session
                    $this->CI->session->set_userdata('user_status_performa', $normal_status);
                }
            }
        }
    }

    public function track_transaction()
    {
        // Method ini bisa dipanggil dari controller lain untuk tracking transaksi
        // Contoh: saat packing atau picking
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Target_kpi extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('target_kpi_fcd');
        
        // Check access - only admin and webmaster
        $this->check_access();
    }

    private function check_access()
    {
        $user = $this->session->userdata('user');
        
        if (!$user || !isset($user['id_user'])) {
            $this->session->set_flashdata('message', 'Access denied. Please login first.');
            redirect('welcome/restricted');
        }
        
        // Only admin (hakakses = 1) and webmaster (hakakses = 2)
        if (!isset($user['hakakses']) || !in_array($user['hakakses'], [1, 2])) {
            $this->session->set_flashdata('message', 'Access denied. Only admin and webmaster can manage KPI targets.');
            redirect('welcome/restricted');
        }
    }

    public function index()
    {
        // Load menu helper
        $this->load->helper('menu_helper');
        
        $data['message'] = $this->session->flashdata('message');
        $user = $this->session->userdata('user');

        // Support both single date (legacy) and date range
        $reportrange = $this->input->get('reportrange');
        $tanggal = $this->input->get('tanggal');
        
        // Determine if using range or single date
        if ($reportrange && strpos($reportrange, ' - ') !== false) {
            // Using date range
            $dates = explode(' - ', $reportrange);
            $start_date = date('Y-m-d', strtotime($dates[0]));
            $end_date = date('Y-m-d', strtotime($dates[1]));
            $data['reportrange'] = $reportrange;
            $data['tanggal'] = $start_date; // For compatibility
            $data['is_range'] = true;
        } else {
            // Using single date (legacy or default)
            $single_date = $tanggal ?: date('Y-m-d');
            $start_date = $single_date;
            $end_date = $single_date;
            $data['tanggal'] = $single_date;
            $data['reportrange'] = $single_date . ' 00:00:00 - ' . $single_date . ' 23:59:59';
            $data['is_range'] = false;
        }
        
        $data['role'] = $this->input->get('role') ?: 'PICKER';

        // Get targets for date range and role
        $data['targets'] = $this->target_kpi_fcd->get_target_by_date_range($start_date, $end_date, $data['role'])->result_array();
        
        // Get target summary for the range
        $data['summary'] = $this->target_kpi_fcd->get_target_summary_range($start_date, $end_date, $data['role']);

        // Get available users (ALL users, not filtered by role)
        $data['available_users'] = $this->target_kpi_fcd->get_available_users()->result_array();

        // Check if this is AJAX request (from menu click OR form submission)
        if ($this->input->is_ajax_request() || $this->input->get('ajax')) {
            // Clear any output buffer
            if (ob_get_length()) ob_clean();
            
            header('Content-Type: application/json');
            $response = array(
                'view' => $this->load->view('target_kpi_index', $data, TRUE),
                'message' => empty($data['message']) ? null : $data['message'],
            );
            echo json_encode($response);
            exit;
        } else {
            // Return full HTML
            $this->data['user'] = $user;
            $this->data['nama_pk'] = $this->session->userdata('nama_pk');
            $this->data['status_performa'] = $this->session->userdata('status_performa');
            $this->data['html_menu_tree'] = $this->session->userdata('html_menu_tree');
            $this->data['content'] = $this->load->view('target_kpi_index', $data, TRUE);
            
            $this->load->view('main', $this->data);
        }
    }

    public function save_targets()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, 'Invalid request method');
        }

        $user = $this->session->userdata('user');
        $tanggal = $this->input->post('tanggal');
        $role = $this->input->post('role');
        $users = $this->input->post('users'); // array of id_user
        $targets = $this->input->post('targets'); // array of target_resi
        $keterangan = $this->input->post('keterangan');

        // Validate
        if (empty($tanggal) || empty($role) || empty($users) || empty($targets)) {
            $this->make_ajax_response(400, 'Data tidak lengkap');
        }

        // Prepare batch data
        $batch_data = [];
        foreach ($users as $index => $id_user) {
            if (!empty($targets[$index]) && $targets[$index] > 0) {
                $batch_data[] = [
                    'id_user' => $id_user,
                    'tanggal' => $tanggal,
                    'role' => $role,
                    'target_resi' => $targets[$index],
                    'keterangan' => $keterangan,
                    'createdby' => $user['id_user']
                ];
            }
        }

        if (empty($batch_data)) {
            $this->make_ajax_response(400, 'Tidak ada target yang valid untuk disimpan');
        }

        // Save batch
        $result = $this->target_kpi_fcd->save_targets_batch($batch_data, $user['id_user']);

        if ($result['success'] > 0) {
            $message = "Target berhasil disimpan untuk {$result['success']} user!";
            if ($result['error'] > 0) {
                $message .= " ({$result['error']} gagal)";
            }
            
            // Return JSON response
            echo json_encode([
                'code' => 200,
                'message' => $message,
                'data' => $result
            ]);
            exit();
        } else {
            echo json_encode([
                'code' => 400,
                'message' => 'Gagal menyimpan target. Tidak ada data yang tersimpan.'
            ]);
            exit();
        }
    }

    public function update_target()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, 'Invalid request method');
        }

        $user = $this->session->userdata('user');
        $id_user = $this->input->post('id_user');
        $tanggal = $this->input->post('tanggal');
        $role = $this->input->post('role');
        $target_resi = $this->input->post('target_resi');
        $keterangan = $this->input->post('keterangan');

        // Validate
        if (empty($id_user) || empty($tanggal) || empty($role) || $target_resi === '') {
            $this->make_ajax_response(400, 'Data tidak lengkap');
        }

        if ($target_resi <= 0) {
            $this->make_ajax_response(400, 'Target harus lebih dari 0');
        }

        // Save
        $data = [
            'id_user' => $id_user,
            'tanggal' => $tanggal,
            'role' => $role,
            'target_resi' => $target_resi,
            'keterangan' => $keterangan,
            'createdby' => $user['id_user']
        ];

        $result = $this->target_kpi_fcd->save_target($data);

        if ($result['affected_rows'] > 0) {
            echo json_encode([
                'code' => 200,
                'message' => 'Target berhasil diupdate!'
            ]);
        } else {
            echo json_encode([
                'code' => 400,
                'message' => 'Gagal mengupdate target'
            ]);
        }
        exit();
    }

    public function delete_target()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, 'Invalid request method');
        }

        $id_target = $this->input->post('id_target');

        if (empty($id_target)) {
            $this->make_ajax_response(400, 'ID target tidak valid');
        }

        $result = $this->target_kpi_fcd->delete_target($id_target);

        if ($result['affected_rows'] > 0) {
            echo json_encode([
                'code' => 200,
                'message' => 'Target berhasil dihapus!'
            ]);
        } else {
            echo json_encode([
                'code' => 400,
                'message' => 'Gagal menghapus target'
            ]);
        }
        exit();
    }

    public function copy_targets()
    {
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, 'Invalid request method');
        }

        $user = $this->session->userdata('user');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $role = $this->input->post('role');

        if (empty($from_date) || empty($to_date) || empty($role)) {
            $this->make_ajax_response(400, 'Data tidak lengkap');
        }

        $result = $this->target_kpi_fcd->copy_targets_from_date($from_date, $to_date, $role, $user['id_user']);

        if ($result['success'] > 0) {
            echo json_encode([
                'code' => 200,
                'message' => "Target berhasil dicopy untuk {$result['success']} user!",
                'data' => $result
            ]);
        } else {
            echo json_encode([
                'code' => 400,
                'message' => $result['message'] ?? 'Gagal copy target'
            ]);
        }
        exit();
    }
}


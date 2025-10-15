<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->model('login_fcd');

        if ($this->session->userdata('user')) {
            redirect('welcome');
        }
    }

    public function index()
    {
        $this->load->model('status_performa_fcd');

        $list_status_performa_raw = $this->status_performa_fcd->get_status_performa()->result_array();
        $grouped_status_performa = [];
        foreach ($list_status_performa_raw as $status) {
            $grouped_status_performa[$status['role']][] = $status;
        }

        $data = array(
            'message' => $this->session->flashdata('message'),
            'machine_name' => $this->input->get('machine_name'),
            'list_pk' => $this->login_fcd->get_pk()->result_array(),
            'list_status_performa' => $grouped_status_performa
        );

        $using_scanner = $this->input->get('using_scanner');

        if ($using_scanner) {
            $this->load->view('login_scan', $data);
        }

        $this->load->view('login', $data);
    }

    public function auth()
    {
        $this->load->model('login_fcd');

        $username = $this->input->post('username');
        $password = md5($this->input->post('password'));
        //$nama_komputer = $this->input->post('nama_komputer');
        //$using_scanner = $this->input->post('using_scanner');
        $nama_pk = $this->input->post('nama_pk');

        if (empty($nama_pk)) {
            $this->session->set_flashdata('message', EMPTY_PK_NAME);
            redirect('login');
            return;
        }

        $user = $this->login_fcd->auth($username, $password)->row_array();

        if (!empty($user) && ($user['password'] == $password || $user['bypass'])) {
            $this->load->model('access_fcd');
            $this->load->helper('menu_helper');

            $this->login_fcd->update_last_login_and_nama_komputer($user['id_user'], $nama_pk);

            $list_menu = $this->access_fcd->get_access_menu($user['hakakses'])->result_array();

            $list_menu_tree = menu_to_tree($list_menu, $list_menu[0]);
            $html_menu_tree = tree_to_html_menu($list_menu_tree['child']);

            // Simpan status performa ke database untuk tracking KPI
            $status_performa_name = $this->input->post('status_performa');
            if (!empty($status_performa_name)) {
                $this->load->model('kpi_fcd');
                
                // Ambil ID status performa berdasarkan nama
                $status_id = $this->kpi_fcd->get_status_id_by_name($status_performa_name);
                
                if ($status_id) {
                    // Log status performa ke database
                    $this->kpi_fcd->log_status_performa($user['id_user'], $status_id);
                    
                    // Ambil detail status performa untuk disimpan di session
                    $status_detail = $this->kpi_fcd->get_user_status_performa($user['id_user']);
                    $sess_data['user_status_performa'] = $status_detail;
                }
            }

            $sess_data['user'] = $user;
            $sess_data['list_menu'] = $list_menu;
            $sess_data['list_menu_tree'] = $list_menu_tree;
            $sess_data['html_menu_tree'] = $html_menu_tree;
            $sess_data['nama_pk'] = $nama_pk;
            $sess_data['status_performa'] = $status_performa_name;

            $this->session->set_userdata($sess_data);

            redirect('/');
        } else {
            $this->session->set_flashdata('message', LOGIN_FAILED);
            redirect('login');
        }
    }
}

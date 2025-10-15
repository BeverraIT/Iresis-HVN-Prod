<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Status_performa extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('kpi_fcd');
    }

    public function index()
    {
        $data['message'] = $this->session->flashdata('message');
        $data['list_status'] = $this->kpi_fcd->get_status_performa()->result_array();

        $this->show($data);
    }

    public function edit($id = null)
    {
        $data['action'] = 'Add Status Performa';
        $data['list_role'] = ['PACKER', 'PICKER'];

        if (!empty($id)) {
            $data['action'] = 'Edit Status Performa';
            $data['status'] = $this->kpi_fcd->get_status_performa($id)->row_array();
        }

        $this->show($data);
    }

    public function save()
    {
        if ($this->input->method() == 'get') {
            redirect('404_override');
        }

        $status['id'] = $this->input->post('id');
        $status['kode_status'] = strtoupper($this->input->post('kode_status'));
        $status['role'] = $this->input->post('role');
        $status['status_name'] = $this->input->post('status_name');
        $status['deskripsi'] = $this->input->post('deskripsi');
        $status['target_harian'] = $this->input->post('target_harian');
        $status['isactive'] = $this->input->post('isactive') ? 1 : 0;

        $save = $this->kpi_fcd->save_status_performa($status, $this->data['user']['id_user']);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', 'Data berhasil disimpan', 'information');
        } else {
            $this->set_message('Warning', 'Tidak ada perubahan data', 'warning');
        }

        $this->show_index();
    }

    public function delete($id)
    {
        $status['id'] = $id;
        $status['isactive'] = 0;

        $save = $this->kpi_fcd->save_status_performa($status, $this->data['user']['id_user']);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', 'Data berhasil dihapus', 'information');
        } else {
            $this->set_message('Warning', 'Tidak ada perubahan data', 'warning');
        }

        $this->show_index();
    }
}


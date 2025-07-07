<?php

class Picker extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('picking_fcd');
        $this->load->model('employee_fcd');
    }

    public function scan_picker()
    {
        $data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();
        $data['total_scan'] = $this->picking_fcd->get_total_scan_user($this->data['user']['id_user'])->row()->total_scan;

        $this->show($data);
    }

    public function save_scan_picker()
    {
        if ($this->input->method() == 'get') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $picking['noresi'] = $this->input->post('noresi');
        $picking['yangambil_pegawai'] = $this->input->post('id_pegawaipicker');
        $picking['pending'] = '';

        $save = $this->picking_fcd->save($picking, $this->data['user']);

        if (isset($save['error'])) {
            $this->make_ajax_response($save['code'], $save['message']);
        }

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }

    public function search_picker()
    {
        $this->show();
    }

    public function get_search_picker_data()
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
            1 => 't2.noresi',
            2 => 't3.nama_pegawai',
            3 => 't.tanggal_resiambilbarang',
            4 => 't.tanggal_resiambilbarang',
            5 => 't4.username',
            6 => null,
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->picking_fcd->get_data($data);

        $total = $this->picking_fcd->get_total_data($data);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                $row->packer,
                date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
                date('H:i:s', strtotime($row->tanggal_resiambilbarang)),
                $row->admin,
                $row->nama_komputer,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }

    public function master_picker()
    {
        $data['message'] = $this->session->flashdata('message');
        $data['list_employee'] = $this->employee_fcd->get_employee()->result_array();
        $data['list_status'] = ['AKTIF', 'TIDAK AKTIF'];
        $data['list_picker'] = $this->picking_fcd->get_picker()->result_array();

        $this->show($data);
    }

    public function save_master_picker()
    {
        if ($this->input->method() == 'get') {
            redirect('404_override');
        }

        $picker['id_pegawai'] =  $this->input->post('id_pegawai');
        $picker['status_aktif'] =  $this->input->post('status_aktif');

        $save = $this->picking_fcd->save_picker($picker);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
        } else {
            $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
        }

        $this->show_index();
    }

    public function delete_master_picker($id_namaambilbarang)
    {
        $save = $this->picking_fcd->destroy_picker($id_namaambilbarang);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
        } else {
            $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
        }

        $this->show_index();
    }

    public function pending_picker()
    {
        $data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();

        $this->show($data);
    }

    public function save_pending_picker()
    {
        if ($this->input->method() == 'get') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $picking['noresi'] = $this->input->post('noresi');
        $picking['yangambil_pegawai'] = $this->input->post('id_pegawaipicker');
        $picking['pending'] = 'ya';

        $save = $this->picking_fcd->save($picking, $this->data['user']);

        if (isset($save['error'])) {
            $this->make_ajax_response($save['code'], $save['message']);
        }

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }

    public function update_picker()
    {
        $data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();

        $this->show($data);
    }

    public function save_update_picker()
    {
        if ($this->input->method() == 'get') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $picking['noresi'] = $this->input->post('noresi');
        $picking['yangambil_pegawai'] = $this->input->post('id_pegawaipicker');

        $save = $this->picking_fcd->save($picking, $this->data['user'], PICKING_UPDATE_PACKER);

        if (isset($save['error'])) {
            $this->make_ajax_response($save['code'], $save['message']);
        }

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }
}
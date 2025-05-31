<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('retur_fcd');
		$this->load->model('marketplace_fcd');
		$this->load->model('courrier_fcd');
	}

	public function scan_retur()
	{
		$data['list_marketplace'] = $this->marketplace_fcd->get_marketplace()->result_array();
		$data['list_courrier'] = $this->courrier_fcd->get_courrier()->result_array();

		$this->show($data);
	}

	// request by ajax
	public function save_retur()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$retur['id_marketplace'] = $this->input->post('id_marketplace');
		$retur['id_kurir'] = $this->input->post('id_kurir');
		$retur['noresi'] = $this->input->post('noresi');

		$save = $this->retur_fcd->save($retur, $this->data['user']['id_user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}

    public function search_retur()
    {
        $data['message'] = $this->session->flashdata('message');

        $this->show($data);
    }

    public function get_data_retur()
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
            2 => 't.tanggal_resiretur',
            3 => 't.tanggal_resiretur',
            4 => 't2.nama_marketplace',
            5 => 't3.nama_kurir',
            6 => 't4.username',
            7 => null,
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->retur_fcd->get_data($data);

        $total = $this->retur_fcd->get_total_data($data);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                date('Y-m-d', strtotime($row->tanggal_resiretur)),
                date('H:i:s', strtotime($row->tanggal_resiretur)),
                $row->nama_marketplace,
                $row->nama_kurir,
                $row->username,
                '<a href="retur_search/delete/' . $row->id_resiretur . '" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>',
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

    public function delete_retur($id_resiretur)
    {
        $save = $this->retur_fcd->destroy($id_resiretur, $this->data['user']['id_user']);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
        } else {
            $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
        }

        $this->show_index();
    }
}

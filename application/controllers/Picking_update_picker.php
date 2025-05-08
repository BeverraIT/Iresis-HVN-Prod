<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picking_update_picker extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('picking_fcd');
	}

	public function index()
	{
		$data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();

		$this->show($data);
	}

	// request by ajax
	public function save()
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

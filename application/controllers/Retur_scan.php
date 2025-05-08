<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_scan extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('retur_fcd');
		$this->load->model('marketplace_fcd');
		$this->load->model('courrier_fcd');
	}

	public function index()
	{
		$data['list_marketplace'] = $this->marketplace_fcd->get_marketplace()->result_array();
		$data['list_courrier'] = $this->courrier_fcd->get_courrier()->result_array();

		$this->show($data);
	}

	// request by ajax
	public function save()
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
}

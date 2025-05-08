<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_delete extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('receipt_fcd');
		$this->load->model('marketplace_fcd');
		$this->load->model('courrier_fcd');
	}

	public function index()
	{
		$data['list_marketplace'] = $this->marketplace_fcd->get_marketplace()->result_array();
		$data['list_courrier'] = $this->courrier_fcd->get_courrier()->result_array();
		$data['total_scan'] = $this->receipt_fcd->get_total_scan_user($this->data['user']['id_user'])->row()->total_scan;

		$this->show($data);
	}

	// request by ajax
	public function delete()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$noresi = $this->input->post('noresi');

		$save = $this->receipt_fcd->destroy_by_noresi($noresi, $this->data['user']['id_user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(400, NOTHING_TO_SAVE);
	}
}

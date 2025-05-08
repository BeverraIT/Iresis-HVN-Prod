<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_scan extends MY_Controller
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
	public function save()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$receipt['id_marketplace'] = $this->input->post('id_marketplace');
		$receipt['id_kurir'] = $this->input->post('id_kurir');
		$receipt['nomorpicklist'] = $this->input->post('nomorpicklist');
		$receipt['noresi'] = $this->input->post('noresi');

		$save = $this->receipt_fcd->save($receipt, $this->data['user']['id_user']);

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}
}

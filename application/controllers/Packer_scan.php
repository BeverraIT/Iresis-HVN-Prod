<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packer_scan extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('employee_fcd');
		$this->load->model('packer_fcd');
	}

	public function index()
	{
		$data['packer'] = $this->employee_fcd->get_employee($this->data['user']['id_pegawai'])->row_array();
		$data['nama_komputer'] = $this->data['user']['nama_komputer'];
		$data['total_scan'] = $this->packer_fcd->get_total_scan_user($this->data['user']['id_pegawai'])->row()->total_scan;

		$this->show($data);
	}

	// request by ajax
	public function save()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$packer['noresi'] = $this->input->post('noresi');

		$save = $this->packer_fcd->save($packer, $this->data['user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}
}

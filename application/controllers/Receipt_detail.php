<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_detail extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('receipt_fcd');
	}

	public function index()
	{
		$data = [];

		if ($this->input->method() == 'post') {
			$noresi = $this->input->post('noresi');

			$data['noresi'] = $noresi;
			$data['receipt'] = $this->receipt_fcd->get_detail($noresi)->row_array();
		}

		$this->show($data);
	}
}

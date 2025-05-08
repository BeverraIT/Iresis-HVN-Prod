<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('receipt_fcd');
	}

	public function index()
	{
		$this->load->helper('menu_helper');

		$data['header_daily_report'] = $this->receipt_fcd->get_header_daily_report(date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'))->row_array();

		$this->data['user'] = $this->session->userdata('user');
		$this->data['html_menu_tree'] = $this->session->userdata('html_menu_tree');
		$this->data['content'] = $this->load->view('welcome', $data, TRUE);

		$this->load->view('main', $this->data);
	}

	public function page_notfound()
	{
		$this->load->view('page_notfound', null);
	}

	public function restricted()
	{
		$this->load->view('restricted', null);
	}

	public function logout()
	{
		$nama_komputer = $this->session->userdata('user')['nama_komputer'];
		$this->session->unset_userdata('user');

		redirect('login?machine_name=' . $nama_komputer);
	}
}

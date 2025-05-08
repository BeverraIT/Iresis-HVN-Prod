<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Marketplace extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('marketplace_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['list_marketplace'] = $this->marketplace_fcd->get_marketplace()->result_array();

		$this->show($data);
	}

	public function edit($id_marketplace = null)
	{
		$this->load->model('role_fcd');

		$data['action'] = 'Tambah marketplace baru';

		if (!empty($id_marketplace)) {
			$data['action'] = 'Edit marketplace';
			$data['marketplace'] = $this->marketplace_fcd->get_marketplace($id_marketplace)->row_array();
		}

		$this->show($data);
	}

	public function save()
	{
		if ($this->input->method() == 'get') {
			redirect('404_override');
		}

		$marketplace['id_marketplace'] =  $this->input->post('id_marketplace');
		$marketplace['nama_marketplace'] =  $this->input->post('nama_marketplace');

		$save = $this->marketplace_fcd->save($marketplace);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}

	public function delete($id_marketplace)
	{
		$marketplace['id_marketplace'] =  $id_marketplace;

		$save = $this->marketplace_fcd->destroy($marketplace);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

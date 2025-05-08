<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Courrier extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('courrier_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['list_courrier'] = $this->courrier_fcd->get_courrier()->result_array();

		$this->show($data);
	}

	public function edit($id_kurir = null)
	{
		$this->load->model('role_fcd');

		$data['action'] = 'Tambah kurir baru';

		if (!empty($id_kurir)) {
			$data['action'] = 'Edit kurir';
			$data['courrier'] = $this->courrier_fcd->get_courrier($id_kurir)->row_array();
		}

		$this->show($data);
	}

	public function save()
	{
		if ($this->input->method() == 'get') {
			redirect('404_override');
		}

		$courrier['id_kurir'] =  $this->input->post('id_kurir');
		$courrier['nama_kurir'] =  $this->input->post('nama_kurir');

		$save = $this->courrier_fcd->save($courrier);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}

	public function delete($id_kurir)
	{
		$courrier['id_kurir'] =  $id_kurir;

		$save = $this->courrier_fcd->destroy($courrier);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

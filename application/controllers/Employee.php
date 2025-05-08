<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('employee_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['list_employee'] = $this->employee_fcd->get_employee()->result_array();

		$this->show($data);
	}

	public function edit($kode_pegawai = null)
	{
		$this->load->model('role_fcd');

		$data['action'] = 'Tambah pegawai baru';
		$data['list_status'] = array("AKTIF", "NON_AKTIF");

		if (!empty($kode_pegawai)) {
			$data['action'] = 'Edit pegawai';
			$data['employee'] = $this->employee_fcd->get_employee($kode_pegawai)->row_array();
		}

		$this->show($data);
	}

	public function save()
	{
		if ($this->input->method() == 'get') {
			redirect('404_override');
		}

		$employee['kode_pegawai'] =  $this->input->post('kode_pegawai');
		$employee['nama_pegawai'] =  $this->input->post('nama_pegawai');
		$employee['status_aktif'] =  $this->input->post('status_aktif');

		$save = $this->employee_fcd->save($employee);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}

	public function delete($kode_pegawai)
	{
		$employee['kode_pegawai'] =  $kode_pegawai;

		$save = $this->employee_fcd->destroy($employee);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

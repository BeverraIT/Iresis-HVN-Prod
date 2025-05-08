<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picking_picker extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('picking_fcd');
		$this->load->model('employee_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['list_employee'] = $this->employee_fcd->get_employee()->result_array();
		$data['list_status'] = ['AKTIF', 'TIDAK AKTIF'];
		$data['list_picker'] = $this->picking_fcd->get_picker()->result_array();

		$this->show($data);
	}

	public function save()
	{
		if ($this->input->method() == 'get') {
			redirect('404_override');
		}

		$picker['id_pegawai'] =  $this->input->post('id_pegawai');
		$picker['status_aktif'] =  $this->input->post('status_aktif');

		$save = $this->picking_fcd->save_picker($picker);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}

	public function delete($id_namaambilbarang)
	{
		$save = $this->picking_fcd->destroy_picker($id_namaambilbarang);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

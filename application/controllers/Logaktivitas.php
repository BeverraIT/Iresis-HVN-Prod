<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logaktivitas extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('logaktivitas_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['list_logaktivitas'] = $this->logaktivitas_fcd->get_data()->result_array();

		$this->show($data);
	}

	public function edit($id = null)
	{
		$this->load->model('role_fcd');

		$data['action'] = 'Tambah log aktivitas baru';
		$data['nama_anda'] = $this->data['user']['username'];

		if (!empty($id)) {
			$data['action'] = 'Edit log aktivitas';
			$data['logaktivitas'] = $this->logaktivitas_fcd->get_data($id)->row_array();
		}

		$this->show($data);
	}

	public function save()
	{
		if ($this->input->method() == 'get') {
			redirect('404_override');
		}

		$data['id'] =  $this->input->post('id');
        $data['activitytype'] = "TURUNKAN_CEK_KAN";
        $data['data'] = json_encode(["qty"=>$this->input->post('qty')]);

		$save = $this->logaktivitas_fcd->save($data,$this->data['user']['id_user']);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}

	public function delete($id)
	{
		$data['id'] =  $id;

		$save = $this->logaktivitas_fcd->destroy($data);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

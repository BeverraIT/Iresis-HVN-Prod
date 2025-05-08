<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Access extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('access_fcd');
		$this->load->model('role_fcd');
		$this->load->model('menu_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['list_role'] = $this->role_fcd->get_role()->result_array();

		$this->show($data);
	}

	public function edit($role_id)
	{
		$this->load->helper('menu');

		$data['action'] = 'Edit role access';
		$data['role'] = $this->role_fcd->get_role($role_id)->row_array();

		$list_menu = $this->menu_fcd->get_menu(null, $role_id)->result_array();

		$list_menu_tree = menu_to_tree($list_menu, $list_menu[0]);
		$data['menu_tree'] = tree_to_html($list_menu_tree);

		$this->show($data);
	}

	public function save()
	{
		if ($this->input->method() == 'get') {
			redirect('404_override');
		}

		$access['list'] = $this->input->post('access');
		$access['roleid'] = $this->input->post('roleid');

		$save = $this->access_fcd->update($access, $this->data['user']['id']);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

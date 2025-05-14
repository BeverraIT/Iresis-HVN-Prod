<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends MY_Controller {

	function __construct()
    {
        parent::__construct();

        $this->load->model('menu_fcd');
    }

	public function index()
	{
		$this->load->helper('menu');

		$data['message'] = $this->session->flashdata('message');
		$data['list_menu'] = $this->menu_fcd->get_menu()->result_array();

        $list_menu_tree = menu_to_tree($data['list_menu'], $data['list_menu'][0]);
        $data['menu_tree'] = tree_to_html($list_menu_tree, FALSE);

		$this->show($data);
	}

	public function edit_menu($menu_id = null)
	{
		$data['action'] = 'Add new menu';
		$data['list_parent'] = $this->menu_fcd->get_parent()->result_array();

		if (!empty($menu_id)) {
			$data['action'] = 'Edit menu';
			$data['menu'] = $this->menu_fcd->get_menu($menu_id)->row_array();
		}

		$this->show($data);
	}

	public function save_menu()
	{
        if ($this->input->method() == 'get') {
            redirect('404_override');
		}
		
		$menu['id'] =  $this->input->post('id');
		$menu['parentid'] =  $this->input->post('parentid');
		$menu['name'] =  $this->input->post('name');
		$menu['uri'] =  empty(trim($this->input->post('uri'))) ? null : $this->input->post('uri');
		$menu['icon'] =  $this->input->post('icon');
		$menu['sortorder'] =  $this->input->post('sortorder');
		$menu['description'] =  $this->input->post('description');

		$save = $this->menu_fcd->save($menu, $this->data['user']['id']);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

        $this->show_index();
	}

	public function delete_menu($menu_id)
	{
		$menu['id'] =  $menu_id;
		$menu['isactive'] = FALSE;

		$save = $this->menu_fcd->save($menu, $this->data['user']['id']);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

        $this->show_index();
	}
}

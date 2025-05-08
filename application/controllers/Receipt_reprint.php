<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_reprint extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('receipt_reprint_fcd');
		$this->load->model('param_fcd');
	}

	public function index()
	{
		$data['list_reason'] = $this->param_fcd->get_param_by_group('REPRINT_RECEIPT_REASON')->result_array();

		$this->show($data);
	}

	// request by ajax
	public function save()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$receipt['alasan'] = $this->input->post('alasan');
		$receipt['keterangan'] = $this->input->post('keterangan');
		$receipt['noresi'] = $this->input->post('noresi');

		if (!empty($_FILES['images']['name'][0])) {
			if (count($_FILES['images']['name']) > 5) {
				$this->set_message('Warning', '5 image max to upload', 'warning');
				$this->show_index();
			}

			$images = $this->upload_files($_FILES['images']);

			if (empty($images)) {
				$this->set_message('Error', $this->upload->display_errors(), 'danger');
				$this->show_index();
			}

			$receipt['image'] = implode(',', $images);
		}

		$save = $this->receipt_reprint_fcd->save($receipt, $this->data['user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}

	private function upload_files($files)
	{
		$config = array(
			'upload_path' => 'uploads/transaction/',
			'allowed_types' => 'jpg|gif|png',
			'overwrite' => 1,
		);

		$this->load->library('upload', $config);

		$images = array();

		$i = 1;
		$time = time();
		foreach ($files['name'] as $key => $image) {
			$_FILES['images[]']['name'] = $files['name'][$key];
			$_FILES['images[]']['type'] = $files['type'][$key];
			$_FILES['images[]']['tmp_name'] = $files['tmp_name'][$key];
			$_FILES['images[]']['error'] = $files['error'][$key];
			$_FILES['images[]']['size'] = $files['size'][$key];

			$fileExt = pathinfo($_FILES['images[]']['name'], PATHINFO_EXTENSION);
			$fileName = '_' . $time . '_' . $i++ . '.' . $fileExt;

			$images[] = $fileName;

			$config['file_name'] = $fileName;

			$this->upload->initialize($config);

			if ($this->upload->do_upload('images[]')) {
				$this->upload->data();
			} else {
				return null;
			}
		}

		return $images;
	}
}

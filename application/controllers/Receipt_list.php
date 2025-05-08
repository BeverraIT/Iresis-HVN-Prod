<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_list extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('receipt_fcd');
	}

	public function index()
	{
		$data['message'] = $this->session->flashdata('message');

		$this->show($data);
	}

	public function get_data()
	{
		$draw = intval($this->input->post('draw'));
		$order = $this->input->post('order');

		$data['start'] = intval($this->input->post('start'));
		$data['length'] = intval($this->input->post('length'));
		$data['search'] = $this->input->post('search')['value'];

		$col = 0;
		$dir = '';
		if (!empty($order)) {
			foreach ($order as $o) {
				$col = $o['column'];
				$dir = $o['dir'];
			}
		}

		$data['dir'] = $dir;

		$data['valid_columns'] = array(
			0 => null,
			1 => 't.noresi',
			2 => 't.tanggal_printresi',
			3 => 't.tanggal_printresi',
			4 => 't2.nama_marketplace',
			5 => 't.nomorpicklist',
			6 => 't.batal',
			7 => 't.keterangan',
			8 => 't3.nama_kurir',
			9 => 't4.username',
		);

		$data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

		$list_resi = $this->receipt_fcd->get_data($data);

		$total = $this->receipt_fcd->get_total_data($data);

		$i = $data['start'] + 1;
		$data = array();
		foreach ($list_resi->result() as $row) {
			$data[] = array(
				$i++ . '.',
				$row->noresi,
				date('Y-m-d', strtotime($row->tanggal_printresi)),
				date('H:i:s', strtotime($row->tanggal_printresi)),
				$row->nama_marketplace,
				$row->nomorpicklist,
				$row->batal,
				$row->keterangan,
				$row->nama_kurir,
				$row->username,
				// '<a href="receipt_list/detail/' . $row->id_printresi . '" class="btn btn-default link"><i class="fa fa-eye"></i> </a> ' .
					'<a href="receipt_list/delete/' . $row->id_printresi . '" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>',
			);
		}

		$output = array(
			"draw" => $draw,
			"recordsTotal" => $total,
			"recordsFiltered" => $total,
			"data" => $data
		);
		echo json_encode($output);
		exit();
	}

	public function delete($id_printresi)
	{
		$save = $this->receipt_fcd->destroy($id_printresi, $this->data['user']['id_user']);

		if ($save['affected_rows'] > 0) {
			$this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
		} else {
			$this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
		}

		$this->show_index();
	}
}

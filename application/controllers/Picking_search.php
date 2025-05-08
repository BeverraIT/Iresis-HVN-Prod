<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picking_search extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('picking_fcd');
	}

	public function index()
	{
		$this->show();
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
			1 => 't2.noresi',
			2 => 't3.nama_pegawai',
			3 => 't.tanggal_resiambilbarang',
			4 => 't.tanggal_resiambilbarang',
			5 => 't4.username',
			6 => null,
		);

		$data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

		$list_resi = $this->picking_fcd->get_data($data);

		$total = $this->picking_fcd->get_total_data($data);

		$i = $data['start'] + 1;
		$data = array();
		foreach ($list_resi->result() as $row) {
			$data[] = array(
				$i++ . '.',
				$row->noresi,
				$row->packer,
				date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
				date('H:i:s', strtotime($row->tanggal_resiambilbarang)),
				$row->admin,
				$row->nama_komputer,
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
}

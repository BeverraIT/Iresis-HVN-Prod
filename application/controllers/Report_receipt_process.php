<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_receipt_process extends MY_Controller
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

	public function get_data_receipt_process_tab0()
	{
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');

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
			1 => 't3.nama_marketplace',
			2 => 't.tanggal_printresi',
			3 => 't.tanggal_printresi',
			4 => 't.noresi',
			5 => 't4.nama_kurir',
			6 => 't.nomorpicklist',
		);

		$data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

		$list_resi = $this->receipt_fcd->get_data_receipt_process_tab0($data, $start_date, $end_date);

		$total = $this->receipt_fcd->get_total_data_receipt_process_tab0($data, $start_date, $end_date);

		$i = $data['start'] + 1;
		$data = array();
		foreach ($list_resi->result() as $row) {
			$data[] = array(
				$i++ . '.',
				$row->nama_marketplace,
				date('Y-m-d', strtotime($row->tanggal_printresi)),
				date('H:i:s', strtotime($row->tanggal_printresi)),
				$row->noresi,
				$row->nama_kurir,
				$row->nomorpicklist,
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

	public function get_data_receipt_process_tab1()
	{
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');

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
			1 => 't3.nama_marketplace',
			2 => 't2.tanggal_printresi',
			3 => 't2.tanggal_printresi',
			4 => 't2.noresi',
			5 => 't4.nama_kurir',
			6 => 't2.nomorpicklist',
			7 => 't.tanggal_resiambilbarang',
			8 => 't.tanggal_resiambilbarang',
			9 => 't5.nama_pegawai',
		);

		$data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

		$list_resi = $this->receipt_fcd->get_data_receipt_process_tab1($data, $start_date, $end_date);

		$total = $this->receipt_fcd->get_total_data_receipt_process_tab1($data, $start_date, $end_date);

		$i = $data['start'] + 1;
		$data = array();
		foreach ($list_resi->result() as $row) {
			$data[] = array(
				$i++ . '.',
				$row->nama_marketplace,
				empty($row->tanggal_printresi) ? null : date('Y-m-d', strtotime($row->tanggal_printresi)),
				empty($row->tanggal_printresi) ? null : date('H:i:s', strtotime($row->tanggal_printresi)),
				$row->noresi,
				$row->nama_kurir,
				$row->nomorpicklist,
				empty($row->tanggal_resiambilbarang) ? null : date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
				empty($row->tanggal_resiambilbarang) ? null : date('H:i:s', strtotime($row->tanggal_resiambilbarang)),
				$row->picker
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

	public function get_data_receipt_process_tab2()
	{
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');

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
			1 => 't3.nama_marketplace',
			2 => 't2.tanggal_printresi',
			3 => 't2.tanggal_printresi',
			4 => 't2.noresi',
			5 => 't4.nama_kurir',
			6 => 't2.nomorpicklist',
			7 => 't.tanggal_resiambilbarang',
			8 => 't.tanggal_resiambilbarang',
			9 => 't5.nama_pegawai',
			10 => 't7.nama_pegawai',
		);

		$data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

		$list_resi = $this->receipt_fcd->get_data_receipt_process_tab2($data, $start_date, $end_date);

		$total = $this->receipt_fcd->get_total_data_receipt_process_tab2($data, $start_date, $end_date);

		$i = $data['start'] + 1;
		$data = array();
		foreach ($list_resi->result() as $row) {
			$data[] = array(
				$i++ . '.',
				$row->nama_marketplace,
				empty($row->tanggal_printresi) ? null : date('Y-m-d', strtotime($row->tanggal_printresi)),
				empty($row->tanggal_printresi) ? null : date('H:i:s', strtotime($row->tanggal_printresi)),
				$row->noresi,
				$row->nama_kurir,
				$row->nomorpicklist,
				empty($row->tanggal_resiambilbarang) ? null : date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
				empty($row->tanggal_resiambilbarang) ? null : date('H:i:s', strtotime($row->tanggal_resiambilbarang)),
				$row->picker,
				$row->packer
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

	public function export_to_excel_tab0()
	{
		ini_set('memory_limit', '-1');
		$reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
		if ($this->input->method() == 'post') {
			$reportrange = $this->input->post('reportrange');
		}

		$start_date = explode(" - ", $reportrange)[0];
		$end_date = explode(" - ", $reportrange)[1];

		$data['reportrange'] = $reportrange;
		$data['list_data'] = $this->receipt_fcd->get_data_receipt_process_tab0([], $start_date, $end_date)->result_array();

		header("Content-type: application/vnd-ms-excel");
		header("Content-Disposition: attachment; filename=Laporan_Resi_Belum_Pick.xls");

		$this->load->view('report_receipt_process_export_to_excel_tab0', $data);
	}

	public function export_to_excel_tab1()
	{
		ini_set('memory_limit', '-1');
		$reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
		if ($this->input->method() == 'post') {
			$reportrange = $this->input->post('reportrange');
		}

		$start_date = explode(" - ", $reportrange)[0];
		$end_date = explode(" - ", $reportrange)[1];

		$data['reportrange'] = $reportrange;
		$data['list_data'] = $this->receipt_fcd->get_data_receipt_process_tab1([], $start_date, $end_date)->result_array();

		header("Content-type: application/vnd-ms-excel");
		header("Content-Disposition: attachment; filename=Laporan_Resi_Belum_Pick.xls");

		$this->load->view('report_receipt_process_export_to_excel_tab1', $data);
	}

	public function export_to_excel_tab2()
	{
		ini_set('memory_limit', '-1');
		$reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
		if ($this->input->method() == 'post') {
			$reportrange = $this->input->post('reportrange');
		}

		$start_date = explode(" - ", $reportrange)[0];
		$end_date = explode(" - ", $reportrange)[1];

		$data['reportrange'] = $reportrange;
		$data['list_data'] = $this->receipt_fcd->get_data_receipt_process_tab2([], $start_date, $end_date)->result_array();

		header("Content-type: application/vnd-ms-excel");
		header("Content-Disposition: attachment; filename=Laporan_Resi_Belum_Pick.xls");

		$this->load->view('report_receipt_process_export_to_excel_tab2', $data);
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

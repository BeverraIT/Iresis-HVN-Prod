<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('receipt_fcd');
	}

	public function receipt_in_process_report()
	{
		$data['message'] = $this->session->flashdata('message');

		$this->show($data);
	}

	public function get_receipt_in_process_data_tab0()
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
			// Tanggal dan jam scan resi diambil dari tblprintresi.tanggal_printresi
			$data[] = array(
				$i++ . '.',
				$row->nama_marketplace,
				($row->tanggal_printresi && strtotime($row->tanggal_printresi)) ? date('Y-m-d', strtotime($row->tanggal_printresi)) : '-',
				($row->tanggal_printresi && strtotime($row->tanggal_printresi)) ? date('H:i:s', strtotime($row->tanggal_printresi)) : '-',
				$row->noresi,
				$row->nama_kurir,
				$row->nomorpicklist,
			);
		}

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "grandTotal" => $total, // Grand total = total records untuk resi proses
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }

	public function get_receipt_in_process_data_tab1()
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
            "grandTotal" => $total, // Grand total = total records untuk resi proses
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }

	public function get_receipt_in_process_data_tab2()
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
			1 => 't4.nama_marketplace',
			2 => 't2.tanggal_printresi',
			3 => 't2.tanggal_printresi',
			4 => 't2.noresi',
			5 => 't5.nama_kurir',
			6 => 't2.nomorpicklist',
			7 => 't3.tanggal_resiambilbarang',
			8 => 't3.tanggal_resiambilbarang',
			9 => 't6.nama_pegawai',
			10 => 't7.name',
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
			"grandTotal" => $total, // Grand total = total records untuk resi proses
			"data" => $data
		);
		echo json_encode($output);
		exit();
    }

	public function export_to_excel_receipt_in_process_tab0()
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

		$this->load->view('template_report/receipt_in_process_report_tab0', $data);
	}

	public function export_to_excel_receipt_in_process_tab1()
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

		$this->load->view('template_report/receipt_in_process_report_tab1', $data);
	}

	public function export_to_excel_receipt_in_process_tab2()
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

		$this->load->view('template_report/receipt_in_process_report_tab2', $data);
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

    public function daily_receipt_report()
    {
        $data['message'] = $this->session->flashdata('message');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['header'] = $this->receipt_fcd->get_header_daily_report($start_date, $end_date)->row_array();
        

        $this->show($data);
    }

    public function get_daily_receipt_report_data()
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
            1 => 'f.nama_marketplace',
            2 => 'e.nama_kurir',
            3 => 'a.noresi',
            4 => 'a.nomorpicklist',
            5 => 'a.tanggal_printresi',
            6 => 'a.tanggal_printresi',
            7 => 't1.nama_pegawai',
            8 => 'b.tanggal_resiambilbarang',
            9 => 'b.tanggal_resiambilbarang',
            10 => 't2.nama_pegawai',
            11 => null, // sp2.status_name - tidak bisa di-search karena subquery
            12 => 'c.tanggal_packing',
            13 => 'c.tanggal_packing',
            14 => 't3.name',
            15 => null, // sp3.status_name - tidak bisa di-search karena subquery
            16 => 'd.tanggal_resikeluar',
            17 => 'd.tanggal_resikeluar',
            18 => 't4.nama_pegawai',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->receipt_fcd->get_data_daily_report($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_daily_report($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->nama_marketplace,
                $row->nama_kurir,
                $row->noresi,
                $row->nomorpicklist,
                empty($row->tanggal_printresi) ? null : date('Y-m-d', strtotime($row->tanggal_printresi)),
                empty($row->tanggal_printresi) ? null : date('H:i:s', strtotime($row->tanggal_printresi)),
                $row->admin_scan,
                empty($row->tanggal_resiambilbarang) ? null: date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
                empty($row->tanggal_resiambilbarang) ? null: date('H:i:s', strtotime($row->tanggal_resiambilbarang)),
                $row->admin_picker,
                !empty($row->picker_status) ? $row->picker_status : '-',
                empty($row->tanggal_packing) ? null : date('Y-m-d', strtotime($row->tanggal_packing)),
                empty($row->tanggal_packing) ? null : date('H:i:s', strtotime($row->tanggal_packing)),
                $row->admin_packer,
                !empty($row->packer_status) ? $row->packer_status : '-',
                empty($row->tanggal_resikeluar) ? null : date('Y-m-d', strtotime($row->tanggal_resikeluar)),
                empty($row->tanggal_resikeluar) ? null : date('H:i:s', strtotime($row->tanggal_resikeluar)),
                $row->admin_ho,
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

    public function export_to_excel_daily_receipt_report()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['header'] = $this->receipt_fcd->get_header_daily_report($start_date, $end_date)->row_array();
        $data['list_data'] = $this->receipt_fcd->get_data_daily_report([], $start_date, $end_date)->result_array();
        

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Resi_Harian.xls");

        $this->load->view('template_report/daily_receipt_report', $data);
    }

    public function per_day_receipt_report()
    {
        $data['message'] = $this->session->flashdata('message');

        if ($this->input->method() == 'post') {
            $data['reportrange'] = $this->input->post('reportrange');
        }

        $this->show($data);
    }

    public function get_per_day_receipt_report_data()
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
            1 => 't.noresi',
            2 => 't5.nama_marketplace',
            3 => 't6.nama_kurir',
            4 => 't.nomorpicklist',
            5 => 't.tanggal_printresi',
            6 => 't.tanggal_printresi',
            7 => 't2.tanggal_resiambilbarang',
            8 => 't2.tanggal_resiambilbarang',
            9 => 't3.tanggal_packing',
            10 => 't3.tanggal_packing',
            11 => 't4.tanggal_resikeluar',
            12 => 't4.tanggal_resikeluar',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->receipt_fcd->get_data_per_day_report($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_per_day_report($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                $row->nama_marketplace,
                $row->nama_kurir,
                $row->nomorpicklist,
                empty($row->tanggal_printresi) ? null : date('Y-m-d', strtotime($row->tanggal_printresi)),
                empty($row->tanggal_printresi) ? null : date('H:i', strtotime($row->tanggal_printresi)),
                empty($row->tanggal_resiambilbarang) ? null : date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
                empty($row->tanggal_resiambilbarang) ? null : date('H:i', strtotime($row->tanggal_resiambilbarang)),
                empty($row->tanggal_packing) ? null : date('Y-m-d', strtotime($row->tanggal_packing)),
                empty($row->tanggal_packing) ? null : date('H:i', strtotime($row->tanggal_packing)),
                empty($row->tanggal_resikeluar) ? null : date('Y-m-d', strtotime($row->tanggal_resikeluar)),
                empty($row->tanggal_resikeluar) ? null : date('H:i', strtotime($row->tanggal_resikeluar)),
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

    public function export_to_excel_per_day_receipt_report()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->receipt_fcd->get_data_per_day_report([], $start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Resi_Per_Hari.xls");

        $this->load->view('template_report/per_day_receipt_report', $data);
    }

    public function receipt_report()
    {
        $data['message'] = $this->session->flashdata('message');

        $this->show($data);
    }

    public function get_receipt_report_data_tab0()
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
            1 => 't.tanggal_printresi',
            2 => 't.tanggal_printresi',
            3 => 't3.nama_marketplace',
            4 => 't.nomorpicklist',
            5 => 't4.nama_kurir',
            6 => 't.noresi',
            7 => 't6.tanggal_resikeluar',
            8 => 't6.tanggal_resikeluar',
            9 => 't7.nama_pegawai',
            10 => 't8.nama_pegawai',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->receipt_fcd->get_data_receipt_tab0($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_receipt_tab0($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                empty($row->tanggal_printresi) ? null : date('Y-m-d', strtotime($row->tanggal_printresi)),
                empty($row->tanggal_printresi) ? null : date('H:i:s', strtotime($row->tanggal_printresi)),
                $row->nama_marketplace,
                $row->nomorpicklist,
                $row->nama_kurir,
                $row->noresi,
                empty($row->tanggal_resikeluar) ? null : date('Y-m-d', strtotime($row->tanggal_resikeluar)),
                empty($row->tanggal_resikeluar) ? null : date('H:i:s', strtotime($row->tanggal_resikeluar)),
                $row->picker,
                $row->packer,
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

    public function get_receipt_report_data_tab1()
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
            1 => 'date(tanggal_printresi)',
            2 => 'nomorpicklist',
            3 => 'total',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->receipt_fcd->get_data_receipt_tab1($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_receipt_tab1($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                date('Y-m-d', strtotime($row->tanggal_printresi)),
                $row->nomorpicklist,
                $row->total,
            );
        }

        $grandTotal = $this->receipt_fcd->get_grand_total_data_receipt_tab1($data, $start_date, $end_date);

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data,
            "grandTotal" => $grandTotal,
        );
        echo json_encode($output);
        exit();
    }

    public function export_to_excel_receipt_report_tab0()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->receipt_fcd->get_data_receipt_tab0([], $start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Total_Resi.xls");

        $this->load->view('template_report/receipt_report_tab0', $data);
    }

    public function export_to_excel_receipt_report_tab1()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->receipt_fcd->get_data_receipt_tab1([], $start_date, $end_date)->result_array();
        $data['grand_total'] = $this->receipt_fcd->get_grand_total_data_receipt_tab1($data, $start_date, $end_date);

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Total_Picklist.xls");

        $this->load->view('template_report/receipt_report_tab1', $data);
    }

    public function shipped_receipt_report()
    {
        $data['message'] = $this->session->flashdata('message');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $data['reportrange'] = $reportrange;

        $this->show($data);
    }

    public function get_shipped_receipt_report_data()
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
            1 => 't2.tanggal_resikeluar',
            2 => 't2.tanggal_resikeluar',
            3 => 't.noresi',
            3 => 't3.nama_kurir',
            4 => 't2.tanggal_cetak',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->receipt_fcd->get_data_shipped_report($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_shipped_report($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                empty($row->tanggal_resikeluar) ? null : date('Y-m-d', strtotime($row->tanggal_resikeluar)),
                empty($row->tanggal_resikeluar) ? null : date('H:i', strtotime($row->tanggal_resikeluar)),
                $row->noresi,
                $row->nama_kurir,
                $row->tanggal_cetak,
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

    public function export_to_excel_shipped_receipt_report()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->receipt_fcd->get_data_shipped_report([], $start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Resi_Dikirim.xls");

        $this->load->view('template_report/shipped_receipt_report', $data);
    }

    public function shipping_report()
    {
        $data['message'] = $this->session->flashdata('message');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');

            $start_date = explode(" - ", $reportrange)[0];
            $end_date = explode(" - ", $reportrange)[1];

            $data['list_data'] = $this->receipt_fcd->get_data_shipping_report($start_date, $end_date)->result_array();
            $data['grand_total'] = $this->receipt_fcd->get_grand_total_data_shipping_report($start_date, $end_date);
        }

        $data['reportrange'] = $reportrange;

        $this->show($data);
    }

    public function export_to_excel_shipping_report()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->receipt_fcd->get_data_shipping_report($start_date, $end_date)->result_array();
        $data['grand_total'] = $this->receipt_fcd->get_grand_total_data_shipping_report($start_date, $end_date);

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Total_Pengiriman_Paket.xls");

        $this->load->view('template_report/shipping_report', $data);
    }

    public function retur_receipt_report()
    {
        $data['message'] = $this->session->flashdata('message');

        if ($this->input->method() == 'post') {
            $data['reportrange'] = $this->input->post('reportrange');
        }

        $this->show($data);
    }

    public function get_retur_receipt_report_data()
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
            1 => 't2.noresi',
            2 => 't3.nama_marketplace',
            3 => 't4.nama_kurir',
            4 => 't2.nomorpicklist',
            5 => 't2.tanggal_printresi',
            6 => 't2.tanggal_printresi',
            7 => 't.tanggal_resiretur',
            8 => 't.tanggal_resiretur',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->receipt_fcd->get_data_retur_receipt_report($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_retur_receipt_report($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                $row->nama_marketplace,
                $row->nama_kurir,
                $row->nomorpicklist,
                empty($row->tanggal_printresi) ? null : date('Y-m-d', strtotime($row->tanggal_printresi)),
                empty($row->tanggal_printresi) ? null : date('H:i', strtotime($row->tanggal_printresi)),
                empty($row->tanggal_resiretur) ? null : date('Y-m-d', strtotime($row->tanggal_resiretur)),
                empty($row->tanggal_resiretur) ? null : date('H:i', strtotime($row->tanggal_resiretur))
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

    public function export_to_excel_retur_receipt_report()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->receipt_fcd->get_data_retur_receipt_report([], $start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Total_Resi_Retur.xls");

        $this->load->view('template_report/retur_receipt_report', $data);
    }

    // ==================== NEW RETUR REPORT METHODS ====================
    
    /**
     * Get data for Terima Retur tab (with SKU details)
     */
    public function get_terima_retur_report_data()
    {
        $this->load->model('retur_fcd');
        
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
            0 => 'dp.no_pesanan',
            1 => 'pr.noresi',
            2 => 'mp.nama_marketplace',
            3 => 'kr.nama_kurir',
            4 => 'tr.tanggal_resiretur',
            5 => 'tr.tanggal_resiretur',
            6 => 'dp.sku',
            7 => 'dp.jumlah',
            8 => 'tr.status_retur',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->retur_fcd->get_terima_retur_with_details($data, $start_date, $end_date);
        $total = $this->retur_fcd->get_total_terima_retur_with_details($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $result = array();
        foreach ($list_resi->result() as $row) {
            $result[] = array(
                'no_pesanan' => $row->no_pesanan ?? '-',
                'noresi' => $row->noresi,
                'marketplace' => $row->nama_marketplace ?? '-',
                'kurir' => $row->nama_kurir ?? '-',
                'tanggal_terima' => empty($row->tanggal_resiretur) ? '-' : date('Y-m-d', strtotime($row->tanggal_resiretur)),
                'jam_terima' => empty($row->tanggal_resiretur) ? '-' : date('H:i:s', strtotime($row->tanggal_resiretur)),
                'sku' => $row->sku ?? '-',
                'quantity' => $row->jumlah ?? 0,
                'status_detail' => $row->status_retur ?? '-',
                'action' => '<button class="btn btn-danger btn-xs btn-delete-retur" data-url="report/delete-terima-retur/' . $row->id_resiretur . '" data-type="terima"><i class="fa fa-trash"></i></button>'
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $result
        );
        echo json_encode($output);
        exit();
    }

    /**
     * Get data for Buka Retur tab (with SKU details)
     */
    public function get_buka_retur_report_data()
    {
        $this->load->model('retur_fcd');
        
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
            0 => 'br.resi_buka',
            1 => 'br.resi_buka',
            2 => 'mp.nama_marketplace',
            3 => 'kr.nama_kurir',
            4 => 'br.tanggal_buka_retur',
            5 => 'br.tanggal_buka_retur',
            6 => 'dp.sku',
            7 => 'dp.jumlah',
            8 => 'br.status_detail_buka',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->retur_fcd->get_buka_retur_with_details($data, $start_date, $end_date);
        $total = $this->retur_fcd->get_total_buka_retur_with_details($data, $start_date, $end_date);

        $i = $data['start'] + 1;
        $result = array();
        foreach ($list_resi->result() as $row) {
            $result[] = array(
                'no_pesanan' => $row->no_pesanan ?? '-',
                'noresi' => $row->resi_buka ?? '-',
                'marketplace' => $row->nama_marketplace ?? '-',
                'kurir' => $row->nama_kurir ?? '-',
                'tanggal_buka' => empty($row->tanggal_buka_retur) ? '-' : date('Y-m-d', strtotime($row->tanggal_buka_retur)),
                'jam_buka' => empty($row->tanggal_buka_retur) ? '-' : date('H:i:s', strtotime($row->tanggal_buka_retur)),
                'sku' => $row->sku ?? '-',
                'quantity' => $row->jumlah ?? 0,
                'status_detail' => $row->status_detail_buka ?? '-',
                'action' => '<button class="btn btn-danger btn-xs btn-delete-retur" data-url="report/delete-buka-retur/' . $row->id_bukaretur . '" data-type="buka"><i class="fa fa-trash"></i></button>'
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $result
        );
        echo json_encode($output);
        exit();
    }

    /**
     * Export Excel for Terima Retur
     */
    public function export_to_excel_terima_retur_report()
    {
        $this->load->model('retur_fcd');
        ini_set('memory_limit', '-1');
        
        $reportrange = $this->input->get('reportrange');
        if (empty($reportrange)) {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->retur_fcd->get_terima_retur_with_details([], $start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Terima_Retur_" . date('YmdHis') . ".xls");

        $this->load->view('template_report/terima_retur_report', $data);
    }

    /**
     * Export Excel for Buka Retur
     */
    public function export_to_excel_buka_retur_report()
    {
        $this->load->model('retur_fcd');
        ini_set('memory_limit', '-1');
        
        $reportrange = $this->input->get('reportrange');
        if (empty($reportrange)) {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = $this->retur_fcd->get_buka_retur_with_details([], $start_date, $end_date)->result_array();

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Buka_Retur_" . date('YmdHis') . ".xls");

        $this->load->view('template_report/buka_retur_report', $data);
    }

    /**
     * Delete Terima Retur record
     */
    public function delete_terima_retur($id_resiretur)
    {
        $this->load->model('retur_fcd');
        
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, 'Invalid request method');
        }

        $result = $this->retur_fcd->destroy($id_resiretur);

        if ($result['affected_rows'] > 0) {
            $this->make_ajax_response(200, 'Data berhasil dihapus');
        } else {
            $this->make_ajax_response(400, 'Gagal menghapus data');
        }
    }

    /**
     * Delete Buka Retur record
     */
    public function delete_buka_retur($id_bukaretur)
    {
        $this->load->model('retur_fcd');
        
        if ($this->input->method() != 'post') {
            $this->make_ajax_response(400, 'Invalid request method');
        }

        $result = $this->retur_fcd->destroy_buka_retur($id_bukaretur);

        if ($result['affected_rows'] > 0) {
            $this->make_ajax_response(200, 'Data berhasil dihapus');
        } else {
            $this->make_ajax_response(400, 'Gagal menghapus data');
        }
    }

    public function production_team_report()
    {
        $data['message'] = $this->session->flashdata('message');

        $this->show($data);
    }

    public function get_production_team_report_data_tab0()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $data['valid_columns'] = array(
            0 => ['searchable' => true, 'col' => 't2.nama_pegawai',],
            1 => ['searchable' => true, 'col' => 'date(b.tanggal_resiambilbarang)',],
            2 => ['searchable' => false, 'col' => 'count(1)',],
        );

        $data['order'] = $order;

        $list_resi = $this->receipt_fcd->get_data_production_team_tab0($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_production_team_tab0($data, $start_date, $end_date);

        $data = array();
        $grand_total = 0;
        
        // Data DIPISAH per picker per hari PER STATUS
        foreach ($list_resi->result() as $row) {
            $grand_total += $row->total; // Hitung grand total dari jumlah per row
            
            $data[] = array(
                $row->pegawai . ' - ' . $row->total,
                $row->waktu_scan_picker ?: $row->tanggal_resiambilbarang,
                $row->total
            );
            
            // Tampilkan status performa di baris terpisah
            $data[] = array(
                '&emsp;&emsp;&emsp;&emsp;&emsp;' . ($row->status_performa ?: 'Tanpa Status'),
                '',
                ''
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "grandTotal" => $grand_total,
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }

    public function get_production_team_report_data_tab1()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $data['valid_columns'] = array(
            0 => ['searchable' => true, 'col' => 't3.name',],
            1 => ['searchable' => true, 'col' => 'date(c.tanggal_packing)',],
            2 => ['searchable' => false, 'col' => 'count(1)',],
        );

        $data['order'] = $order;

        $list_resi = $this->receipt_fcd->get_data_production_team_tab1($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_production_team_tab1($data, $start_date, $end_date);

        $data = array();
        $grand_total = 0;
        
        // Data sudah ter-group by status, tinggal tampilkan langsung
        foreach ($list_resi->result() as $row) {
            $grand_total += $row->total; // Hitung grand total dari jumlah per row
            
            $data[] = array(
                $row->pegawai . ' - ' . $row->total,
                $row->waktu_scan_packer ?: $row->tanggal_packing,
                $row->total
            );
            
            // Tambahkan baris status performa
            if (!empty($row->status_performa)) {
                $data[] = array(
                    '&emsp;&emsp;&emsp;&emsp;&emsp;' . $row->status_performa,
                    '',
                    ''
                );
            }
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "grandTotal" => $grand_total,
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }

    public function export_to_excel_production_team_report_tab0()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = [];

        $grand_total = 0;
        $pickers = $this->receipt_fcd->get_data_production_team_tab0([], $start_date, $end_date)->result_array();
        
        // Data sudah dipisah per picker per hari per status performa
        foreach ($pickers as $picker) {
            $pegawai = $picker['pegawai'];
            $tanggal = $picker['tanggal_resiambilbarang'];
            $waktu_scan = $picker['waktu_scan_picker'];
            $total = $picker['total'];
            $status_performa = $picker['status_performa'] ?? '';
            
            $data['list_data'][$pegawai][] = [
                'tanggal' => $waktu_scan ?: $tanggal,
                'total' => $total,
                'status_performa' => $status_performa
            ];
            
            $grand_total += $total;
        }
        
        $data['grand_total'] = $grand_total;

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Produksi_Picker.xls");

        $this->load->view('template_report/production_team_report_tab0', $data);
    }

    public function export_to_excel_production_team_report_tab1()
    {
        ini_set('memory_limit', '-1');
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $start_date = explode(" - ", $reportrange)[0];
        $end_date = explode(" - ", $reportrange)[1];

        $data['reportrange'] = $reportrange;
        $data['list_data'] = [];

        $grand_total = 0;
        $grouped_data = [];
        $packers = $this->receipt_fcd->get_data_production_team_tab1([], $start_date, $end_date)->result_array();
        
        // Data sudah ter-group by status dari database, tinggal format untuk Excel
        foreach ($packers as $packer) {
            $pegawai = $packer['pegawai'];
            $tanggal = $packer['tanggal_packing'];
            $waktu_scan = $packer['waktu_scan_packer'];
            $status = $packer['status_performa'] ?: '';
            $total = $packer['total'];
            
            $key = $pegawai . '|' . $tanggal . '|' . $status;
            
            $grouped_data[$key] = [
                'pegawai' => $pegawai,
                'tanggal' => $waktu_scan ?: $tanggal,
                'waktu_scan' => $waktu_scan,
                'total' => $total,
                'status_performa' => $status
            ];
            $grand_total += $total;
        }
        
        // Konversi ke format yang dibutuhkan template
        foreach ($grouped_data as $item) {
            $data['list_data'][$item['pegawai']][] = [
                'tanggal' => $item['waktu_scan'] ?: $item['tanggal'], // Tampilkan waktu scan atau tanggal
                'total' => $item['total'],
                'status_performa' => $item['status_performa']
            ];
        }
        
        $data['grand_total'] = $grand_total;

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Produksi_Packer.xls");

        $this->load->view('template_report/production_team_report_tab1', $data);
    }
    
    private function get_picker_status($user_id, $tanggal_scan, $picker_status_map) {
        if (empty($user_id) || empty($tanggal_scan)) {
            return '';
        }
        
        $tanggal_only = date('Y-m-d', strtotime($tanggal_scan));
        $map_key = $user_id . '_' . $tanggal_only;
        $scan_time = strtotime($tanggal_scan);
        
        if (isset($picker_status_map[$map_key]) && is_array($picker_status_map[$map_key])) {
            // Cari status yang created <= waktu scan resi (paling terakhir)
            $last_status = null;
            $last_created = null;
            foreach ($picker_status_map[$map_key] as $status_data) {
                $created_time = strtotime($status_data['created']);
                if ($created_time <= $scan_time) {
                    if ($last_created === null || $created_time > $last_created) {
                        $last_status = $status_data['status'];
                        $last_created = $created_time;
                    }
                }
            }
            // Jika tidak ada status yang created <= waktu scan, return kosong
            // (bukan 'Normal', karena user belum punya status performa di waktu itu)
            return $last_status !== null ? $last_status : '';
        }
        
        return '';
    }
    
    private function get_packer_status($user_id, $tanggal_packing, $packer_status_map) {
        if (empty($user_id) || empty($tanggal_packing)) {
            return '';
        }
        
        $tanggal_only = date('Y-m-d', strtotime($tanggal_packing));
        $map_key = $user_id . '_' . $tanggal_only;
        $packing_time = strtotime($tanggal_packing);
        
        if (isset($packer_status_map[$map_key]) && is_array($packer_status_map[$map_key])) {
            // Cari status yang created <= waktu packing (paling terakhir)
            $last_status = null;
            $last_created = null;
            foreach ($packer_status_map[$map_key] as $status_data) {
                $created_time = strtotime($status_data['created']);
                if ($created_time <= $packing_time) {
                    if ($last_created === null || $created_time > $last_created) {
                        $last_status = $status_data['status'];
                        $last_created = $created_time;
                    }
                }
            }
            // Jika tidak ada status yang created <= waktu packing, return kosong
            return $last_status !== null ? $last_status : '';
        }
        
        return '';
    }
}

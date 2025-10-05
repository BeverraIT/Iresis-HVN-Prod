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
            11 => 'c.tanggal_packing',
            12 => 'c.tanggal_packing',
            13 => 't3.nama_pegawai',
            14 => 'd.tanggal_resikeluar',
            15 => 'd.tanggal_resikeluar',
            16 => 't4.nama_pegawai',
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
                empty($row->tanggal_packing) ? null : date('Y-m-d', strtotime($row->tanggal_packing)),
                empty($row->tanggal_packing) ? null : date('H:i:s', strtotime($row->tanggal_packing)),
                $row->admin_packer,
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
            0 => ['searchable' => true, 'col' => 'CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)',],
            1 => ['searchable' => true, 'col' => 'date(a.tanggal_resiambilbarang)',],
            2 => ['searchable' => false, 'col' => 'count(1)',],
        );

        $data['order'] = $order;

        $list_resi = $this->receipt_fcd->get_data_production_team_tab0($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_production_team_tab0($data, $start_date, $end_date);

        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $row->pegawai,
                '&emsp;&emsp;&emsp;' . $row->tanggal_resiambilbarang,
                $row->total,
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
            0 => ['searchable' => true, 'col' => 'CONCAT(b.name, \' - \', b.id_user)',],
            1 => ['searchable' => true, 'col' => 'date(a.tanggal_packing)',],
            2 => ['searchable' => false, 'col' => 'count(1)',],
        );

        $data['order'] = $order;

        $list_resi = $this->receipt_fcd->get_data_production_team_tab1($data, $start_date, $end_date);

        $total = $this->receipt_fcd->get_total_data_production_team_tab1($data, $start_date, $end_date);

        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $row->pegawai,
                '&emsp;&emsp;&emsp;' . $row->tanggal_packing,
                $row->total,
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
        foreach ($pickers as $picker) {
            $data['list_data'][$picker['pegawai']][] = ['tanggal' => $picker['tanggal_resiambilbarang'], 'total' => $picker['total']];
            $grand_total += $picker['total'];
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
        $pickers = $this->receipt_fcd->get_data_production_team_tab1([], $start_date, $end_date)->result_array();
        foreach ($pickers as $picker) {
            $data['list_data'][$picker['pegawai']][] = ['tanggal' => $picker['tanggal_packing'], 'total' => $picker['total']];
            $grand_total += $picker['total'];
        }
        $data['grand_total'] = $grand_total;

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Produksi_Packer.xls");

        $this->load->view('template_report/production_team_report_tab1', $data);
    }
}

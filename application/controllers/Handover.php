<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Handover extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('handover_fcd');
		$this->load->model('courrier_fcd');
	}

	public function scan_handover()
	{
		$data['nama_komputer'] = $this->data['user']['nama_komputer'];
		$data['total_scan'] = $this->handover_fcd->get_total_scan_user($this->data['user']['id_pegawai'])->row()->total_scan;

		$this->show($data);
	}

	// request by ajax
	public function save_handover()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$handover['noresi'] = $this->input->post('noresi');

		$save = $this->handover_fcd->save($handover, $this->data['user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}

    public function search_handover()
    {
        $this->show();
    }

    public function get_data_handover()
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
            3 => 't.tanggal_resikeluar',
            4 => 't.tanggal_resikeluar',
            5 => 't.sudah_cetak',
            5 => 't.tanggal_cetak',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->handover_fcd->get_data($data);

        $total = $this->handover_fcd->get_total_data($data);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                $row->pegawai,
                date('Y-m-d', strtotime($row->tanggal_resikeluar)),
                date('H:i:s', strtotime($row->tanggal_resikeluar)),
                $row->sudah_cetak,
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

	public function print()
	{
		$data['list_courrier'] = $this->courrier_fcd->get_courrier()->result_array();

		$this->show($data);
	}

	public function print_preview()
	{
		ini_set('memory_limit', '-1');

		$reportrange = $this->input->post('reportrange');

		$id_kurir = $this->input->post('id_kurir');
		$start_date = explode(' - ', $reportrange)[0];
		$end_date = explode(' - ', $reportrange)[1];

		$start_date_parse = date('Y-m-d H:i:s', strtotime($start_date));
		$end_date_parse = date('Y-m-d H:i:s', strtotime($end_date));

		$data['list_receipt'] = $this->handover_fcd->get_data_print($id_kurir, $start_date_parse, $end_date_parse)->result_array();

		$this->load->model('courrier_fcd');

		$courrier = $this->courrier_fcd->get_courrier($id_kurir)->row_array();

		$data['nama_kurir'] = $courrier['nama_kurir'];
		$data['start_date'] = $start_date;
		$data['end_date'] = $end_date;

		header("Content-type: application/vnd-ms-excel");
		header("Content-Disposition: attachment; filename=Laporan_Resi_Keluar_Cetak_Serah_Terima-" . $data['nama_kurir'] . ".xls");

		$this->load->view('template_report/handover_print', $data);
	}
}

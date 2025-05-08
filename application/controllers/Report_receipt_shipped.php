<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_receipt_shipped extends MY_Controller
{

  function __construct()
  {
    parent::__construct();

    $this->load->model('receipt_fcd');
  }

  public function index()
  {
    $data['message'] = $this->session->flashdata('message');

    $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
    if ($this->input->method() == 'post') {
      $reportrange = $this->input->post('reportrange');
    }

    $data['reportrange'] = $reportrange;

    $this->show($data);
  }

  public function get_data()
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

  public function export_to_excel()
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

    $this->load->view('report_receipt_shipped_export_to_excel', $data);
  }
}

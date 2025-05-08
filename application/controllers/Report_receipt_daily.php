<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_receipt_daily extends MY_Controller
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

    $start_date = explode(" - ", $reportrange)[0];
    $end_date = explode(" - ", $reportrange)[1];

    $data['reportrange'] = $reportrange;
    $data['header'] = $this->receipt_fcd->get_header_daily_report($start_date, $end_date)->row_array();

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
        empty($row->tanggal_printresi) ? null : date('H:i', strtotime($row->tanggal_printresi)),
        $row->admin_scan,
        empty($row->tanggal_resiambilbarang) ? null: date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
        empty($row->tanggal_resiambilbarang) ? null: date('H:i', strtotime($row->tanggal_resiambilbarang)),
        $row->admin_picker,
        empty($row->tanggal_packing) ? null : date('Y-m-d', strtotime($row->tanggal_packing)),
        empty($row->tanggal_packing) ? null : date('H:i', strtotime($row->tanggal_packing)),
        $row->admin_packer,
        empty($row->tanggal_resikeluar) ? null : date('Y-m-d', strtotime($row->tanggal_resikeluar)),
        empty($row->tanggal_resikeluar) ? null : date('H:i', strtotime($row->tanggal_resikeluar)),
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
    $data['header'] = $this->receipt_fcd->get_header_daily_report($start_date, $end_date)->row_array();
    $data['list_data'] = $this->receipt_fcd->get_data_daily_report([], $start_date, $end_date)->result_array();

    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Resi_Harian.xls");

    $this->load->view('report_receipt_daily_export_to_excel', $data);
  }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_shipping extends MY_Controller
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

      $start_date = explode(" - ", $reportrange)[0];
      $end_date = explode(" - ", $reportrange)[1];

      $data['list_data'] = $this->receipt_fcd->get_data_shipping_report($start_date, $end_date)->result_array();
      $data['grand_total'] = $this->receipt_fcd->get_grand_total_data_shipping_report($start_date, $end_date);
    }

    $data['reportrange'] = $reportrange;

    $this->show($data);
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
    $data['list_data'] = $this->receipt_fcd->get_data_shipping_report($start_date, $end_date)->result_array();
    $data['grand_total'] = $this->receipt_fcd->get_grand_total_data_shipping_report($start_date, $end_date);

    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Total_Pengiriman_Paket.xls");

    $this->load->view('report_shipping_export_to_excel', $data);
  }
}

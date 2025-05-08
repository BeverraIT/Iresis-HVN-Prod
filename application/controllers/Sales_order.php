<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order extends MY_Controller
{

  function __construct()
  {
    parent::__construct();

    $this->load->model('sales_order_fcd');
  }

  public function index()
  {
    $data['message'] = $this->session->flashdata('message');

    $this->show($data);
  }

  public function edit($id)
  {
    if (!empty($id)) {
      $data['action'] = 'Ubah pesanan';
      $data['sales_order'] = $this->sales_order_fcd->get_sales_order($id)->row_array();

      if (!empty($data['sales_order'])) {
        $data['sales_order']['list_sales_order_item'] = $this->sales_order_fcd->get_list_sales_order_item($id)->result_array();

      } else {
        redirect('404_override');
      }
    }

    $this->show($data);
  }

  public function save()
  {
    if ($this->input->method() == 'get') {
      redirect('404_override');
    }

    $order['id'] =  $this->input->post('id');

    $save = $this->sales_order_fcd->save($order, $this->data['user']['id']);

    if ($save['affected_rows'] > 0) {
      $this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
    } else {
      $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
    }

    $this->show_index();
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
      1 => 'salesorder.salesorder_no',
      2 => 'salesorder.transaction_date',
      3 => 'salesorder.tn_created_date',
      4 => 'salesorder.mp_timestamp',
      5 => 'salesorder.courier',
      6 => 'salesorder.status',
      7 => 'salesorder.picklist_no',
      8 => 'salesorder.store',
      9 => 'salesorder.source',
      10 => 'salesorder.tracking_no',
      11 => 'salesorder.total_amount_mp',
      12 => 'salesorder.total_disc',
      13 => 'salesorder.add_fee',
      14 => 'salesorder.escrow_amount',
      15 => 'salesorder.sub_total',
      16 => 'salesorder.grand_total',
    );

    $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

    $list_order = $this->sales_order_fcd->get_data($data);

    $total = $this->sales_order_fcd->get_total_data($data);

    $i = $data['start'] + 1;
    $data = array();
    foreach ($list_order->result() as $row) {
      $data[] = array(
        $i++ . '.',
        $row->salesorder_no,
        $row->transaction_date,
        $row->tn_created_date,
        $row->mp_timestamp,
        $row->courier . ' ' . $row->is_cod . ' ' . $row->is_instant_courier,
        $row->status,
        $row->picklist_no,
        $row->store,
        $row->source,
        $row->tracking_no,
        $row->total_amount_mp,
        $row->total_disc,
        $row->add_fee,
        $row->escrow_amount,
        $row->sub_total,
        $row->grand_total,
        '<a href="order/edit/' . $row->id . '" class="btn btn-primary link"><i class="fa fa-laptop"></i> </a>',
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

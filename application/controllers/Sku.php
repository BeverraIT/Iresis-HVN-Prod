<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sku extends MY_Controller
{

  function __construct()
  {
    parent::__construct();

    $this->load->model('sku_fcd');
    $this->load->model('param_fcd');
  }

  public function index()
  {
    $data['message'] = $this->session->flashdata('message');
    $data['list_location'] = $this->param_fcd->get_param_by_group(PARAMGROUP_LOCATION)->result_array();

    $this->show($data);
  }

  public function edit($sku_id = null)
  {
    $data['action'] = 'Tambah sku baru';

    if (!empty($sku_id)) {
      $data['action'] = 'Ubah sku';
      $data['sku'] = $this->sku_fcd->get_sku($sku_id)->row_array();
    }

    $this->show($data);
  }

  public function get_sku_location_stock($sku_id)
  {
    $sku = $this->sku_fcd->get_sku($sku_id)->row_array();
    $list_skulocationstock = $this->sku_fcd->get_sku_location_stock($sku['item_id'])->result_array();

    foreach ($list_skulocationstock as $skulocationstock) {
      $sku['list_skulocationstock'][$skulocationstock['location_id'] . '_on_hand'] = $skulocationstock['on_hand'];
      $sku['list_skulocationstock'][$skulocationstock['location_id'] . '_on_order'] = $skulocationstock['on_order'];
      $sku['list_skulocationstock'][$skulocationstock['location_id'] . '_reserved'] = $skulocationstock['reserved'];
      $sku['list_skulocationstock'][$skulocationstock['location_id'] . '_available'] = $skulocationstock['available'];
    }

    $this->make_ajax_response(HTTP_STATUS_OK, null, $sku);
  }

  public function save_sku_location_stock()
  {
    if ($this->input->method() == 'get') {
      redirect('404_override');
    }

    $sku =  $this->sku_fcd->get_sku($this->input->post('id'))->row_array(0);
    $list_skulocationstock = $this->sku_fcd->get_sku_location_stock($sku['item_id'])->result_array();

    foreach ($list_skulocationstock as $key => $value) {
      $list_skulocationstock[$key]['on_hand'] = $this->input->post($value['location_id'] . '_on_hand');
      $list_skulocationstock[$key]['on_order'] = $this->input->post($value['location_id'] . '_on_order');
      $list_skulocationstock[$key]['reserved'] = $this->input->post($value['location_id'] . '_reserved');
      $list_skulocationstock[$key]['available'] = $this->input->post($value['location_id'] . '_available');
    }

    $sku['location_stocks'] = $list_skulocationstock;

    $save = $this->sku_fcd->save($sku, $this->data['user']['id'], true);

    if ($save) {
      $this->make_ajax_response(HTTP_STATUS_OK, SUCCESS_SAVE_DATA, null);
    } else {
      $this->make_ajax_response(HTTP_STATUS_INTERNAL_SERVER_ERROR, NOTHING_TO_SAVE, null);
    }
  }

  public function resync()
  {
    $data['message'] = $this->session->flashdata('message');
    $data['resync_status'] = $this->param_fcd->get_param_by_group(PARAMGROUP_STATUS_RESYNC_SKU)->row_array();

    $this->show($data);
  }

  public function do_resync()
  {
    $resync_status = $this->param_fcd->get_param_by_group(PARAMGROUP_STATUS_RESYNC_SKU)->row_array();

    if ($resync_status['paramvalue1'] == '1') {
      $this->set_message('Success', MESSAGE_RESYNC_STATUS_RUNNING, 'warning');

      $this->show_index();
    }

    $jubelio_api_token = $this->param_fcd->get_param_by_group(PARAMGROUP_JUBELIO_API_TOKEN)->row_array();

    if (empty($jubelio_api_token)) {
      $this->set_message('Warning', JUBELIO_API_TOKEN_NOT_FOUND, 'warning');

      $this->show_index();
    }

    $this->load->library('bgprocess');

    $param['jubelio_api_token'] = $jubelio_api_token['paramvalue1'];
    $param['sync_stock'] = $this->input->post('sync_stock') ?? 0;

    $this->bgprocess->do_async(base_url() . 'bgprocess/download_from_jubelio', $param);

    $this->set_message('Success', MESSAGE_RESYNC_STATUS_START, 'information');

    $this->show_index();
  }

  public function delete($sku_id)
  {
    $deleted = $this->sku_fcd->delete_by_criteria(['id' => $sku_id,]);

    if ($deleted > 0) {
      $this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
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
      1 => 'sku.item_code',
      2 => 'sku.item_name',
      3 => 'sku.average_cost',
    );

    $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

    $list_location = $this->param_fcd->get_param_by_group(PARAMGROUP_LOCATION)->result_array();

    $list_sku = $this->sku_fcd->get_data($data, $list_location);

    $total = $this->sku_fcd->get_total_data($data);

    $i = $data['start'] + 1;
    $data = array();
    foreach ($list_sku->result() as $row) {
      $sku = array(
        $i++ . '.',
        $row->item_code,
        $row->item_name,
        $row->average_cost,
      );

      foreach ($list_location as $location) {
        array_push($sku, $row->{$location['paramvalue2'] . '_on_hand'});
        array_push($sku, $row->{$location['paramvalue2'] . '_on_order'});
        array_push($sku, $row->{$location['paramvalue2'] . '_reserved'});
        array_push($sku, $row->{$location['paramvalue2'] . '_available'});
      }

      array_push($sku, '<button class="btn btn-success btn-edit-sku" data-id="' . $row->id . '"><i class="fa fa-edit"></i> </button>');

      $data[] = $sku;
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

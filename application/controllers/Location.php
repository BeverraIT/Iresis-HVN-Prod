<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Location extends MY_Controller
{

  function __construct()
  {
    parent::__construct();

    $this->load->model('param_fcd');
  }

  public function index()
  {
    $data['message'] = $this->session->flashdata('message');
    $data['list_location'] = $this->param_fcd->get_param_by_group(PARAMGROUP_LOCATION, [['field' => 'paramvalue3', 'dir' => 'desc']])->result_array();

    $this->show($data);
  }

  public function edit($location_id = null)
  {
    $data['action'] = 'Tambah lokasi baru';

    if (!empty($location_id)) {
      $data['action'] = 'Ubah lokasi';
      $data['location'] = $this->param_fcd->get_param($location_id)->row_array();
    }

    $this->show($data);
  }

  public function save()
  {
    if ($this->input->method() == 'get') {
      redirect('404_override');
    }

    $location['id'] =  $this->input->post('id');
    $location['paramvalue1'] = $this->input->post('paramvalue1'); // location name
    $location['paramvalue2'] = $this->input->post('paramvalue2'); // id jubelio
    $location['paramvalue3'] = $this->input->post('paramvalue3'); // status
    $location['paramvalue4'] = $this->input->post('paramvalue4'); // address
    $location['paramgroup'] = PARAMGROUP_LOCATION;

    $save = $this->param_fcd->save($location, $this->data['user']['id']);

    if ($save['affected_rows'] > 0) {
      $this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
    } else {
      $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
    }

    $this->show_index();
  }

  public function do_resync()
  {
    $jubelio_api_token = $this->param_fcd->get_param_by_group(PARAMGROUP_JUBELIO_API_TOKEN)->row_array();

    if (empty($jubelio_api_token)) {
      $this->set_message('Warning', JUBELIO_API_TOKEN_NOT_FOUND, 'warning');

      $this->show_index();
    }

    // init Guzzle client
    $client = new GuzzleHttp\Client();

    // request list of location to Jubelio
    $response = $client->request('GET', JUBELIO_BASE_API . 'inventory/?page=9999&pageSize=200', [
      'headers' =>
      [
        'Authorization' => "Bearer " . $jubelio_api_token['paramvalue1']
      ]
    ]);

    if ($response->getStatusCode() == HTTP_STATUS_OK) {
      // delete first
      $this->param_fcd->delete_by_criteria(['paramgroup' => PARAMGROUP_LOCATION, 'paramvalue3' => 'jubelio',]);

      $body_contents = json_decode($response->getBody()->getContents(), true);

      $list_jubelio_location = [];
      foreach ($body_contents['locations'] as $content) :
        $list_jubelio_location[$content['location_id']] = [
          'location_name' => $content['location_name'],
          'address' => $content['address'],
        ];
      endforeach;

      // reinsert
      foreach ($list_jubelio_location as $key => $value) :
        $location['paramvalue1'] = $value['location_name'];
        $location['paramvalue2'] = $key;
        $location['paramvalue3'] = 'jubelio';
        $location['paramvalue4'] = $value['address'];
        $location['paramgroup'] = PARAMGROUP_LOCATION;

        $this->param_fcd->save($location, $this->data['user']['id']);
      endforeach;
    }

    // TODO set message the result of location synchronization
    $this->set_message('Success', SUCCESS_SAVE_DATA, 'information');

    $this->show_index();
  }

  public function delete($location_id)
  {
    $deleted = $this->param_fcd->delete_by_criteria(['id' => $location_id,]);

    if ($deleted > 0) {
      $this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
    } else {
      $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
    }

    $this->show_index();
  }
}

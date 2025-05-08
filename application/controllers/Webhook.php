<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Webhook extends CI_Controller
{

  function __construct()
  {
    parent::__construct();

    $this->load->model('order_fcd');
  }

  public function update_sales_order()
  {
    $content = $this->input->raw_input_stream;
    $sign_header = $this->input->request_headers()['sign'];

    $sign = hash_hmac('sha256', $content . JUBELIO_WEBHOOK_SIGNATURE, JUBELIO_WEBHOOK_SIGNATURE, false);

    // verify signature
    if ($sign != $sign_header) {
      $response = json_encode([
        'statusCode' => HTTP_STATUS_BAD_REQUEST,
        'error' => 'Bad Request',
        'message' => 'Invalid signature',
      ]);

      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(HTTP_STATUS_BAD_REQUEST)
        ->set_output($response);
    }

    $save = $this->order_fcd->save(json_decode($content, true), JUBELIO_WEBHOOK_USER_ID, ORDER_SYNC_SOURCE_WEBHOOK);

    if (!$save) {
      $response = json_encode([
        'statusCode' => HTTP_STATUS_INTERNAL_SERVER_ERROR,
        'error' => 'Internal Server Error',
        'message' => 'Error when saving order',
      ]);

      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(HTTP_STATUS_INTERNAL_SERVER_ERROR)
        ->set_output($response);
    }

    return $this->output
      ->set_content_type('application/json')
      ->set_status_header(HTTP_STATUS_OK)
      ->set_output(json_encode(['status' => 'ok']));
  }
}

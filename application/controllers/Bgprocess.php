<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bgprocess extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function send_email_blast()
    {
        try {
            $email_blast = json_decode($this->input->post('email_blast'), true);

            if (empty($email_blast)) {
                die(DATA_NOT_FOUND);
            }

            $list_recipients = json_decode($this->input->post('list_recipients'), true);

            // load notif lib
            $this->load->library('notif');

            // load model email_blast_fcd
            $this->load->model('email_blast_fcd');

            foreach ($list_recipients as $recipients) :
                if ($recipients['status'])
                    continue;

                $name = ucwords($recipients['firstname'] . ' ' . $recipients['lastname']);

                $content = $this->load->view('email_blast_template/tmp_' . $email_blast['file_template'], $recipients, true);

                $recipients['sent_date'] = date('Y-m-d H:i:s');
                $send = $this->notif->send(['email' => $recipients['email'], 'name' => $name], $email_blast['subject'], $content);

                $recipients['status'] = $send;
                $this->email_blast_fcd->update_recipients($recipients);
            endforeach;

            if ($this->email_blast_fcd->is_finished($email_blast)) {
                $email_blast['status'] = EMAIL_BLAST_STATUS['DONE'];

                $this->email_blast_fcd->save($email_blast);
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public function download_from_jubelio()
    {
        $this->load->model('param_fcd');

        $jubelio_api_token = json_decode($this->input->post('jubelio_api_token'), true);
        $sync_stock = json_decode($this->input->post('sync_stock'), true);
        $resync_status = $this->param_fcd->get_param_by_group(PARAMGROUP_STATUS_RESYNC_SKU)->row_array();

        if (empty($jubelio_api_token)) {
            exit();
        }

        try {
            // update status resync stock to start
            $resync_status['paramvalue1'] = 1; // set to running
            $this->param_fcd->save($resync_status, 0);

            $this->load->model('sku_fcd');

            // delete first
            $this->sku_fcd->truncate_sku();
            if ($sync_stock) {
                $this->sku_fcd->truncate_sku_location_stock();
            }

            // init Guzzle client
            $client = new GuzzleHttp\Client();

            $page = 45;
            $sku_data = [];
            do {
                // request list of sku to Jubelio
                $response = $client->request('GET', JUBELIO_BASE_API . 'inventory/?page=' . $page++ . '&pageSize=200', [
                    'headers' =>
                    [
                        'Authorization' => "Bearer " . $jubelio_api_token
                    ]
                ]);

                if ($response->getStatusCode() == HTTP_STATUS_OK) {
                    $body_contents = json_decode($response->getBody()->getContents(), true);

                    $sku_data = $body_contents['data'];

                    // reinsert
                    foreach ($sku_data as $jubelio_sku) :
                        $sku['item_id'] = $jubelio_sku['item_id'];
                        $sku['item_code'] = $jubelio_sku['item_code'];
                        $sku['item_name'] = $jubelio_sku['item_name'];
                        $sku['item_group_id'] = $jubelio_sku['item_group_id'];
                        $sku['is_bundle'] = $jubelio_sku['is_bundle'];
                        $sku['brand_name'] = $jubelio_sku['brand_name'];
                        $sku['average_cost'] = $jubelio_sku['average_cost'];
                        $sku['thumbnail'] = $jubelio_sku['thumbnail'];
                        $sku['location_stocks'] = $jubelio_sku['location_stocks'];

                        $this->sku_fcd->save($sku, 0, $sync_stock);
                    endforeach;

                    sleep(3);
                }
            } while (!empty($sku_data));
        } catch (\Exception $e) {
            die($e->getMessage());
        } finally {
            // update status resync stock to finish
            $resync_status['paramvalue1'] = 0; // set to running
            $this->param_fcd->save($resync_status, 0);
        }
    }
}

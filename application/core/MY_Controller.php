<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    protected $data;

    function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('user')) {
            redirect('login');
        } else {
            $this->validate();

            $this->data['user'] = $this->session->userdata('user');
            $this->data['nama_pk'] = $this->session->userdata('nama_pk');
            $this->data['status_performa'] = $this->session->userdata('status_performa');
        }
    }

    public function validate()
    {
        if ($this->router->class == 'welcome') {
            return true;
        }

        $class = $this->router->class;
        $method = $this->router->method == 'index' ? '' : '/' . $this->router->method; // leave it blank when it is `index` method
    }

    public function show($data_content = null)
    {
        $isi = $this->load->view($this->router->class . '/' . $this->router->method, $data_content, TRUE);

        // Halaman dimuat lewat AJAX oleh menu sidebar (devScript.openPage),
        // yang menunggu JSON berisi potongan HTML.
        //
        // Tetapi alamat halaman juga sering dibuka langsung -- lewat bookmark,
        // tautan yang dibagikan, atau setelah menekan F5. Tanpa penanganan di
        // bawah ini, yang muncul di layar adalah JSON mentah. Maka untuk
        // permintaan biasa, potongan tadi dibungkus rangka halaman penuh.
        if (!$this->input->is_ajax_request()) {
            $this->data['content'] = $isi;
            $this->data['html_menu_tree'] = $this->session->userdata('html_menu_tree');

            $this->load->view('main', $this->data);

            return;
        }

        // Clear any output buffer to prevent HTML/whitespace before JSON
        if (ob_get_length()) ob_clean();

        // Set JSON header
        header('Content-Type: application/json');

        echo json_encode(array(
            'view' => $isi,
            'message' => empty($data_content['message']) ? null : $data_content['message'],
        ));
    }

    public function show_index($override_index = null)
    {
        redirect($this->router->class . '/' . $override_index ?? null);
    }

    public function set_message($title, $message, $type)
    {
        $this->session->set_flashdata('message', array(
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ));
    }

    public function make_ajax_response($status_code, $message, $data = [])
    {
        // Clear any output buffer to prevent HTML/whitespace before JSON
        if (ob_get_length()) ob_clean();
        
        // Set JSON header
        header('Content-Type: application/json');
        set_status_header($status_code);

        $response = array(
            'message' => $message,
        );

        if (!empty($data)) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit();
    }
}

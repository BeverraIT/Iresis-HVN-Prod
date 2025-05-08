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
        }
    }

    public function validate()
    {
        if ($this->router->class == 'welcome') {
            return true;
        }

        if (!array_search($this->router->class, array_column($this->session->userdata('list_menu'), 'uri'))) {
            redirect('404_override');
        }
    }

    public function show($data_content = null)
    {
        echo json_encode(array(
            'view' => $this->load->view($this->router->class . '_' . $this->router->method, $data_content, TRUE),
            'message' => empty($data_content['message']) ? null : $data_content['message'],
        ));
    }

    public function show_index()
    {
        redirect($this->router->class);
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

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

        $class = $this->router->class;
        $method = $this->router->method == 'index' ? '' : '/' . $this->router->method; // leave it blank when it is `index` method
    }

    public function show($data_content = null)
    {
        echo json_encode(array(
            'view' => $this->load->view($this->router->class . '/' . $this->router->method, $data_content, TRUE),
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

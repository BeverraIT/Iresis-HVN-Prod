<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Problemtype_fcd extends CI_Model
{
    function get_list()
    {
        return $this->db->get('tbltypemasalah')->result_array();
    }
}
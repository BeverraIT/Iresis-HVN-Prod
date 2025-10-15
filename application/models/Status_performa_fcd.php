<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Status_performa_fcd extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function get_status_performa() {
        return $this->db->get('tblmasterstatusperforma');
    }
}

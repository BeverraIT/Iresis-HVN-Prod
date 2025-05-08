<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_fcd extends CI_Model {

    function auth($username, $password)
    {
        $criterias['tbluser.username'] = $username;
        $criterias['tbluser.isactive'] = TRUE;

        $this->db->select('tbluser.*, tblhakakses.akses');
        
        $this->db->join('tblhakakses', 'tblhakakses.id_hakakses = tbluser.hakakses');

        $this->db->where($criterias);
        
        return $this->db->get('tbluser');
    }

    function update_last_login_and_nama_komputer($user_id, $nama_komputer)
    {
        $this->db->where('id_user', $user_id);
        
        $this->db->update('tbluser', array('lastlogin' => date('Y-m-d H:i:s'), 'nama_komputer' => $nama_komputer));
    }
}

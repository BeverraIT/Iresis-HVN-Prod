<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_fcd extends CI_Model
{

    function get_user($id_user = null)
    {
        $criterias['tbluser.isactive'] = TRUE;

        if (!empty($id_user)) {
            $criterias['tbluser.id_user'] = $id_user;
        }

        $this->db->select('tbluser.*, tblhakakses.akses akses');

        $this->db->join('tblhakakses', 'tblhakakses.id_hakakses = tbluser.hakakses', 'left');

        $this->db->where($criterias);

        return $this->db->get('tbluser');
    }

    function save($user, $id_user)
    {
        if (empty($user['id_user'])) {
            // start transactional
            $this->db->trans_start();
            
            // save employee
            $this->db->insert('tblpegawai', $user['employee']);

            $employee['kode_pegawai'] = $this->db->insert_id();
            $employee['affected_rows'] = 1;

            // set kode_pegawai as id_pegawai in tbluser
            $user['id_pegawai'] = $employee['kode_pegawai'];

            // unset employee
            unset($user['employee']);

            // set user
            $user['isactive'] = TRUE;
            $user['createdby'] = $id_user;
            $user['created'] = date('Y-m-d H:i:s');

            $this->db->insert('tbluser', $user);

            $user['id_user'] = $this->db->insert_id();
            $user['affected_rows'] = 1;

            // end transactional
            $this->db->trans_complete();
        } else {
            unset($user['password']); // admin can not update user password

            $user['updatedby'] = $id_user;
            $user['updated'] = date('Y-m-d H:i:s');

            $this->db->where(array('id_user' => $user['id_user']));

            $this->db->update('tbluser', $user);

            $user['affected_rows'] = $this->db->affected_rows();
        }

        return $user;
    }

    function update_password($user, $id_user)
    {
        $user['updatedby'] = $id_user;
        $user['updated'] = date('Y-m-d H:i:s');

        $this->db->where(array('id_user' => $user['id_user']));

        $this->db->update('tbluser', $user);

        $user['affected_rows'] = $this->db->affected_rows();

        return $user;
    }
}

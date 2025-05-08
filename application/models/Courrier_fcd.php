<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Courrier_fcd extends CI_Model
{

    function get_courrier($id_kurir = null)
    {
        if (!empty($id_kurir)) {
            $this->db->where(['id_kurir' => $id_kurir]);
        }

        return $this->db->get('tblkurir');
    }

    function save($courrier)
    {
        if (empty($courrier['id_kurir'])) {
            $this->db->insert('tblkurir', $courrier);

            $courrier['id_kurir'] = $this->db->insert_id();
            $courrier['affected_rows'] = 1;
        } else {
            $this->db->where(array('id_kurir' => $courrier['id_kurir']));

            $this->db->update('tblkurir', $courrier);

            $courrier['affected_rows'] = $this->db->affected_rows();
        }

        return $courrier;
    }

    function destroy($courrier)
    {
        $this->db->where(array('id_kurir' => $courrier['id_kurir']));

        $this->db->delete('tblkurir');

        $courrier['affected_rows'] = $this->db->affected_rows();

        return $courrier;
    }
}

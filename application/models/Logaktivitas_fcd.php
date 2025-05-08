<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logaktivitas_fcd extends CI_Model
{

    function get_data($id = null)
    {
        if (!empty($id)) {
            $this->db->where(['id' => $id]);
        }
        $this->db->join('tbluser as c', 'logaktivitas.createdby = c.id_user', 'left');
        $this->db->join('tbluser as u', 'logaktivitas.updatedby = u.id_user', 'left');
        $this->db->select('logaktivitas.*, c.username, u.username as updatedbyusername');

        return $this->db->get('logaktivitas');
    }

    function save($data,$user_id)
    {
        if (empty($data['id'])) {
            $data['isactive'] = TRUE;
            $data['createdby'] = $user_id;
            $data['created'] = date('Y-m-d H:i:s');

            $this->db->insert('logaktivitas', $data);

            $data['id'] = $this->db->insert_id();
            $data['affected_rows'] = 1;
        } else {
            $data['updatedby'] = $user_id;
            $data['updated'] = date('Y-m-d H:i:s');

            $this->db->where(array('id' => $data['id']));

            $this->db->update('logaktivitas', $data);

            $data['affected_rows'] = $this->db->affected_rows();
        }
        return $data;
    }

    function destroy($data)
    {
        $this->db->where(array('id' => $data['id']));

        $this->db->delete('logaktivitas');

        $marketplace['affected_rows'] = $this->db->affected_rows();

        return $marketplace;
    }
}

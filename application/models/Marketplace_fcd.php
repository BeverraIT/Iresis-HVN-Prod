<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Marketplace_fcd extends CI_Model
{

    function get_marketplace($id_marketplace = null)
    {
        if (!empty($id_marketplace)) {
            $this->db->where(['id_marketplace' => $id_marketplace]);
        }

        return $this->db->get('tblmarketplace');
    }

    function save($marketplace)
    {
        if (empty($marketplace['id_marketplace'])) {
            $this->db->insert('tblmarketplace', $marketplace);

            $marketplace['id_marketplace'] = $this->db->insert_id();
            $marketplace['affected_rows'] = 1;
        } else {
            $this->db->where(array('id_marketplace' => $marketplace['id_marketplace']));

            $this->db->update('tblmarketplace', $marketplace);

            $marketplace['affected_rows'] = $this->db->affected_rows();
        }

        return $marketplace;
    }

    function destroy($marketplace)
    {
        $this->db->where(array('id_marketplace' => $marketplace['id_marketplace']));

        $this->db->delete('tblmarketplace');

        $marketplace['affected_rows'] = $this->db->affected_rows();

        return $marketplace;
    }
}

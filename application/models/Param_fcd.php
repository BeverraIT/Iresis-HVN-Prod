<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Param_fcd extends CI_Model
{

  function get_param($param_id = null, $orders = [])
  {
    $criterias['param.isactive'] = TRUE;

    if (!empty($param_id)) {
      $criterias['param.id'] = $param_id;
    }

    foreach ($orders as $order) :
      $this->db->order_by($order['field'], $order['dir']);
    endforeach;

    $this->db->where($criterias);

    return $this->db->get('param');
  }

  function get_param_by_group($param_group, $orders = [])
  {
    $criterias['param.isactive'] = TRUE;
    $criterias['param.paramgroup'] = $param_group;

    if (empty($orders)) {
      $this->db->order_by('id', 'asc');
    } else {
      foreach ($orders as $order) :
        $this->db->order_by($order['field'], $order['dir']);
      endforeach;
    }

    $this->db->where($criterias);

    return $this->db->get('param');
  }

  function save($param, $user_id)
  {
    if (empty($param['id'])) {
      $param['isactive'] = TRUE;
      $param['createdby'] = $user_id;
      $param['created'] = date('Y-m-d H:i:s');

      $this->db->insert('param', $param);

      $param['id'] = $this->db->insert_id();
      $param['affected_rows'] = 1;
    } else {
      $param['updatedby'] = $user_id;
      $param['updated'] = date('Y-m-d H:i:s');

      $this->db->update('param', $param, array('id' => $param['id']));

      $param['affected_rows'] = $this->db->affected_rows();
    }

    return $param;
  }

  function delete_by_criteria($criterias)
  {
    $this->db->delete('param', $criterias);

    return $this->db->affected_rows();
  }
}

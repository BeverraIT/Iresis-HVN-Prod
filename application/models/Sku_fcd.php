<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sku_fcd extends CI_Model
{

  function get_sku($sku_id = null)
  {
    $criterias['sku.isactive'] = TRUE;

    if (!empty($sku_id)) {
      $criterias['sku.id'] = $sku_id;
    }

    $this->db->where($criterias);

    return $this->db->get('sku');
  }

  function get_sku_location_stock($item_id)
  {
    return $this->db->get_where('skulocationstock', array('skulocationstock.item_id' => $item_id, 'skulocationstock.isactive' => TRUE));
  }

  function save($sku, $user_id, $sync_stock = false)
  {
    $timestamp = date('Y-m-d H:i:s');
    /**
     * 1. initiate list_skulocationstock
     * 2. unset location_stocks from sku
     * 3. begin transactional
     * 4. replace sku
     * 5. replace skulocationstock
     * 6. end transactional
     * 7. handling transactional
     * 8. return the result
     */

    // initiate list_skulocationstock
    $list_skulocationstock = $sku['location_stocks'];

    // unset location_stocks from sku
    unset($sku['location_stocks']);

    // begin transactional
    $this->db->trans_start();

    // replace sku
    $sku['updatedby'] = $user_id;
    $sku['updated'] = $timestamp;
    $this->db->update('sku', $sku, array('item_id' => $sku['item_id']));
    if ($this->db->affected_rows() == 0) {
      $sku['isactive'] = TRUE;
      $sku['createdby'] = $user_id;
      $sku['created'] = $timestamp;

      $this->db->insert('sku', $sku);
    }

    if ($sync_stock) {
      // replace skulocationstock
      foreach ($list_skulocationstock as $skulocationstock) :
        unset($skulocationstock['location_code']);

        $skulocationstock['updatedby'] = $user_id;
        $skulocationstock['updated'] = $timestamp;
        $this->db->update('skulocationstock', $skulocationstock, array('item_id' => $skulocationstock['item_id'], 'location_id' => $skulocationstock['location_id']));
        if ($this->db->affected_rows() == 0) {
          $skulocationstock['isactive'] = TRUE;
          $skulocationstock['createdby'] = $user_id;
          $skulocationstock['created'] = $timestamp;

          $this->db->insert('skulocationstock', $skulocationstock);
        }
      endforeach;
    }

    // end transactional
    $this->db->trans_complete();

    // handling transactional
    if ($this->db->trans_status() === FALSE) {
      return false;
    }

    // return the result
    return true;
  }

  function truncate_sku()
  {
    $this->db->truncate('sku');
  }

  function truncate_sku_location_stock()
  {
    $this->db->truncate('skulocationstock');
  }

  function get_data($data, $list_location)
  {
    if ($data['order'] != null) {
      $this->db->order_by($data['order'], $data['dir'], FALSE);
    }

    if (!empty($data['search'])) {
      $x = 0;

      $this->db->group_start();

      foreach ($data['valid_columns'] as $sterm) {
        if (empty($sterm)) continue;

        if ($x == 0) {
          $this->db->like($sterm, $data['search']);
        } else {
          $this->db->or_like($sterm, $data['search']);
        }

        $x++;
      }

      $this->db->group_end();
    }

    $this->db->select('sku.id, sku.item_id, sku.item_code, sku.item_name, sku.average_cost');

    foreach ($list_location as $location) {
      $this->db->select('sum(case when skulocationstock.location_id = ' . $location['paramvalue2'] . ' THEN on_hand END) `' . $location['paramvalue2'] . '_on_hand`');
      $this->db->select('sum(case when skulocationstock.location_id = ' . $location['paramvalue2'] . ' THEN on_order END) `' . $location['paramvalue2'] . '_on_order`');
      $this->db->select('sum(case when skulocationstock.location_id = ' . $location['paramvalue2'] . ' THEN reserved END) `' . $location['paramvalue2'] . '_reserved`');
      $this->db->select('sum(case when skulocationstock.location_id = ' . $location['paramvalue2'] . ' THEN available END) `' . $location['paramvalue2'] . '_available`');
    }

    $this->db->join('skulocationstock', 'skulocationstock.item_id = sku.item_id', 'left');

    $this->db->group_by('sku.id, sku.item_id, sku.item_code, sku.item_name, sku.average_cost');

    $this->db->limit($data['length'], $data['start']);

    return $this->db->get('sku');
  }

  function get_total_data($data)
  {
    if (!empty($data['search'])) {
      $x = 0;

      $this->db->group_start();

      foreach ($data['valid_columns'] as $sterm) {
        if (empty($sterm)) continue;

        if ($x == 0) {
          $this->db->like($sterm, $data['search']);
        } else {
          $this->db->or_like($sterm, $data['search']);
        }

        $x++;
      }

      $this->db->group_end();
    }

    $query = $this->db->select("count(1) as num")->get("sku");
    $result = $query->row();

    return isset($result) ? $result->num : 0;
  }
}

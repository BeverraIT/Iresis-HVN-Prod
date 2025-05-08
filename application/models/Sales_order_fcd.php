<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order_fcd extends CI_Model
{

  function save($sales_order, $user_id, $sync_source = null)
  {
    $timestamp = date('Y-m-d H:i:s');
    /**i
     * 1. set sales_order sync source
     * 2. initiate list_sales_order_item
     * 3. unset items from sales_order
     * 4. begin transactional
     * 5. replace sales_order
     * 6. replace sales_order_item
     * 7. end transactional
     * 8. handling transactional
     * 9. return the result
     */

    // set sales_order sync source
    $sales_order['syncsource'] = $sync_source;

    // initiate list_sales_order_item
    $list_sales_order_item = $sales_order['items'];

    // unset items for sales_order
    unset($sales_order['items']);

    // begin transactional
    $this->db->trans_start();

    // replace sales_order
    $sales_order['updatedby'] = $user_id;
    $sales_order['updated'] = $timestamp;
    $this->db->update('salesorder', $sales_order, array('salesorder_id' => $sales_order['salesorder_id']));
    if ($this->db->affected_rows() == 0) {
      $sales_order['isactive'] = TRUE;
      $sales_order['createdby'] = $user_id;
      $sales_order['created'] = $timestamp;

      $this->db->insert('salesorder', $sales_order);
    }

    // replace sales_order_item
    foreach ($list_sales_order_item as $sales_order_item) :
      $sales_order_item['updatedby'] = $user_id;
      $sales_order_item['updated'] = $timestamp;
      $this->db->update('salesorderitem', $sales_order_item, array('salesorder_detail_id' => $sales_order_item['salesorder_detail_id']));
      if ($this->db->affected_rows() == 0) {
        $sales_order_item['isactive'] = TRUE;
        $sales_order_item['createdby'] = $user_id;
        $sales_order_item['created'] = $timestamp;

        $this->db->insert('salesorderitem', $sales_order_item);
      }
    endforeach;

    // end transactional
    $this->db->trans_complete();

    // handling transactional
    if ($this->db->trans_status() === FALSE) {
      return false;
    }

    // return the result
    return true;
  }

  function get_data($data)
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

    $this->db->select('
      salesorder.id,
      salesorder.salesorder_no,
      salesorder.transaction_date,
      salesorder.tn_created_date,
      salesorder.mp_timestamp,
      salesorder.courier,
      salesorder.is_cod,
      salesorder.is_instant_courier,
      salesorder.status,
      salesorder.picklist_no,
      salesorder.store,
      salesorder.source,
      salesorder.tracking_no,
      salesorder.total_amount_mp,
      salesorder.total_disc,
      salesorder.add_fee,
      salesorder.escrow_amount,
      salesorder.sub_total,
      salesorder.grand_total
    ');

    $this->db->limit($data['length'], $data['start']);

    return $this->db->get('salesorder');
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

    $query = $this->db->select("count(1) as num")->get("salesorder");
    $result = $query->row();

    return isset($result) ? $result->num : 0;
  }

  function get_sales_order($id)
  {
    $this->db->where([
      'salesorder.isactive' => TRUE,
      'salesorder.id' => $id,
    ]);

    return $this->db->get('salesorder');
  }

  function get_list_sales_order_item($id)
  {

  }
}

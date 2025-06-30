<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_reprint_fcd extends CI_Model
{

    function save($receipt, $user)
    {
        $receipt = $this->db->get_where('tblprintresi', ['noresi' => $receipt['noresi']])->result_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        $id_resi_list = array_column($receipt, 'id_printresi');

        $this->db->where_in('id_resi', $id_resi_list);
        $receipt_reprint_exist = $this->db->get('tblresiambilbarang')->result_array();
        if (count($receipt_reprint_exist) > 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah diambil. Silakan Cek data'];
        }

        foreach ($receipt as $row) {
            $insert_batch_data[] = [
                'id_resi' => $row['id_printresi'],
                'tanggal_resiprintulang' => date('Y-m-d H:i:s'),
                'admin_pegawai' => $user['id_user'],
                'nama_komputer' => $user['nama_komputer']
            ];
        }

        $this->db->insert_batch('tblresiprintulang', $insert_batch_data);

        $receipt_reprint['id_resiprintulang'] = $this->db->insert_id();
        $receipt_reprint['affected_rows'] = count($insert_batch_data);

        return $receipt_reprint;
    }
}

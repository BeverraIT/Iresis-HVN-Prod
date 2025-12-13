<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_reprint_fcd extends CI_Model
{

    function save($receipt, $user)
    {
        $receipt_data = $this->db->get_where('tblprintresi', ['noresi' => $receipt['noresi']])->row_array();
        if (empty($receipt_data)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        $this->db->where('id_resi', $receipt_data['id_printresi']);
        $receipt_reprint_exist = $this->db->get('tblresiambilbarang')->row_array();
        if (!empty($receipt_reprint_exist)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah diambil. Silakan Cek data'];
        }

        // Note: nama_komputer is now synced with database from login fix
        $insert_data = [
            'id_resi' => $receipt_data['id_printresi'],
            'tanggal_resiprintulang' => date('Y-m-d H:i:s'),
            'admin_pegawai' => $user['id_user'],
            'nama_komputer' => $user['nama_komputer'] // Synced with database from login
        ];

        $this->db->insert('tblresiprintulang', $insert_data);

        $receipt_reprint['id_resiprintulang'] = $this->db->insert_id();
        $receipt_reprint['affected_rows'] = 1;

        return $receipt_reprint;
    }
}

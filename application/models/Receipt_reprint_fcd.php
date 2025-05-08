<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_reprint_fcd extends CI_Model
{

    function save($receipt, $user)
    {
        $receipt = $this->db->get_where('tblprintresi', ['noresi' => $receipt['noresi']])->row_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        $receipt_reprint_exist = $this->db->get_where('tblresiambilbarang', ['id_resi' => $receipt['id_printresi']]);

        if ($receipt_reprint_exist->num_rows() > 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah diambil. Silakan Cek data'];
        }

        $receipt_reprint['tanggal_resiprintulang'] = date('Y-m-d H:i:s');
        $receipt_reprint['id_resi'] = $receipt['id_printresi'];
        $receipt_reprint['admin_pegawai'] = $user['id_user'];
        $receipt_reprint['nama_komputer'] = $user['nama_komputer'];

        $this->db->insert('tblresiprintulang', $receipt_reprint);

        $receipt_reprint['id_resiprintulang'] = $this->db->insert_id();
        $receipt_reprint['affected_rows'] = 1;

        return $receipt_reprint;
    }
}

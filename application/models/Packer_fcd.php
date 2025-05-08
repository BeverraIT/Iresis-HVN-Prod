<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packer_fcd extends CI_Model
{

    function save($packer, $user)
    {
        /**
         * 1. check does noresi exist in tblprintresi
         * 1. throw if doest not exist
         * 2. check does id_resi exist in tblresiamblibarang
         * 3. throw if exist
         * 4. save into tblresiambilbarang
         */
        $receipt = $this->db->get_where('tblprintresi', ['noresi' => $packer['noresi']])->row_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        $picking_exist = $this->db->get_where('tblresiambilbarang', ['id_resi' => $receipt['id_printresi']]);
        if ($picking_exist->num_rows() == 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
        }

        $packer_exist = $this->db->get_where('tblpacking', ['id_resi' => $receipt['id_printresi']])->num_rows();
        if ($packer_exist > 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah di-packing. Silakan Cek data'];
        }

        unset($packer['noresi']);

        $packer['id_resi'] = $receipt['id_printresi'];
        $packer['tanggal_packing'] = date('Y-m-d H:i:s');
        $packer['packer_pegawai'] = $user['id_pegawai'];
        $packer['keterangan'] = $user['nama_komputer'];

        $this->db->insert('tblpacking', $packer);

        $packer['id_packing'] = $this->db->insert_id();
        $packer['affected_rows'] = 1;

        // update tblresiambilbarang to reset pending to ''
        $this->db->where(array('id_resiambilbarang' => $picking_exist->row_array()['id_resiambilbarang']));
        $this->db->update('tblresiambilbarang', ['pending' => '']);

        return $packer;
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
            t.id_packing id,
            t2.noresi,
            t3.nama_pegawai packer,
            t.tanggal_packing,
            t.keterangan
        ');

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi');

        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.packer_pegawai', 'left');

        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tblpacking t');
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

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi');

        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.packer_pegawai', 'left');

        $query = $this->db->select("count(1) as num")->get("tblpacking t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_total_scan_user($id_pegawai)
    {
        $this->db->select('count(1) as total_scan');

        $criterias = [
            'tanggal_packing >= ' => date('Y-m-d'),
            'packer_pegawai' => $id_pegawai,
        ];

        $this->db->where($criterias);

        return $this->db->get_where('tblpacking');
    }
}

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
        $receipt = $this->db
            ->select('id_printresi')
            ->get_where('tblprintresi', ['noresi' => $packer['noresi']])
            ->result_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        unset($packer['noresi']);

        $id_resi_list = array_column($receipt, 'id_printresi');

        $this->db->where_in('id_resi', $id_resi_list);
        $picking_exist = $this->db->get('tblresiambilbarang')->result_array();
        if (count($picking_exist) == 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
        }

        $this->db->where_in('id_resi', $id_resi_list);
        $packer_exist = $this->db->get('tblpacking')->result_array();
        if (count($packer_exist) > 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah di-packing. Silakan Cek data'];
        }

        foreach ($receipt as $row) {
            $insert_batch_data[] = [
                'id_resi' => $row['id_printresi'],
                'tanggal_packing' => date('Y-m-d H:i:s'),
                'packer_pegawai' => $user['id_user'],
                'keterangan' => $user['nama_komputer']
            ];
        }

        $this->db->insert_batch('tblpacking', $insert_batch_data);

        // update tblresiambilbarang to reset pending to ''
        foreach ($picking_exist as $row) {
            $update_batch_data[] = [
                'id_resiambilbarang' => $row['id_resiambilbarang'],
                'pending' => ''
            ];
        }
        $this->db->update_batch('tblresiambilbarang', $update_batch_data, 'id_resiambilbarang');

        $packer['affected_rows'] = count($insert_batch_data);

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
        //$this->db->group_by('t2.noresi');
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

    function get_picker_detail_for_packer($noresi) {
        $this->db->select('t.nama_komputer, t3.nama_pegawai');
        $this->db->from('tblresiambilbarang t');
        $this->db->join('tblprintresi t2', 't.id_resi = t2.id_printresi');
        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.admin_pegawai');
        $this->db->where('t2.noresi', $noresi);

        $query = $this->db->get();
        return $query->row();
    }
}

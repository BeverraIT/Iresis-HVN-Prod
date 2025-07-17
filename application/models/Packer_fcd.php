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
            pr.noresi,
            u.name AS nama_pegawai,
            p.tanggal_packing,
            p.keterangan
        ');

        $this->db->from('tblprintresi pr');

        // Subquery to pick one packing row per id_resi
        $this->db->join('(
            SELECT MIN(id_packing) AS id_packing, id_resi
            FROM tblpacking
            GROUP BY id_resi
        ) p_min', 'p_min.id_resi = pr.id_printresi');

        // Join to the selected packing row
        $this->db->join('tblpacking p', 'p.id_packing = p_min.id_packing');

        // Join to user table
        $this->db->join('tbluser u', 'u.id_user = p.packer_pegawai', 'left');

        // Group to ensure uniqueness   
        $this->db->group_by([
            'pr.noresi',
            'u.name',
            'p.tanggal_packing',
            'p.keterangan'
        ]);

        $this->db->limit($data['length'], $data['start']);

        return $this->db->get();
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

        $this->db->select('
            t2.noresi,
            t3.name AS nama_pegawai,
            t.tanggal_packing,
            t.keterangan
        ');

        $this->db->from('tblpacking t');

        $this->db->join('(
            SELECT id_printresi, MIN(noresi) AS noresi
            FROM tblprintresi
            GROUP BY id_printresi
        ) t2', 't2.id_printresi = t.id_resi');
        
        $this->db->join('tbluser t3', 't3.id_user = t.packer_pegawai', 'left');

        $this->db->group_by([
            't2.noresi',
            't3.name',
            't.tanggal_packing',
            't.keterangan'
        ]);

        $query = $this->db->get();
        return $query->num_rows();
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
        $this->db->select('
            t.nama_komputer,
            t3.name nama_pegawai
        ');
        $this->db->select('t.nama_komputer, t3.name');
        $this->db->from('tblresiambilbarang t');
        $this->db->join('tblprintresi t2', 't.id_resi = t2.id_printresi');
        $this->db->join('tbluser t3', 't3.id_user = t.admin_pegawai');
        $this->db->where('t2.noresi', $noresi);

        $query = $this->db->get();
        return $query->row();
    }
}

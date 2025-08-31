<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packer_fcd extends CI_Model
{

    function save($packer, $user)
    {
        /**
         * 1. check does noresi exist in tblprintresi
         * 2. throw if does not exist
         * 3. check does id_resi exist in tblresiambilbarang
         * 4. throw if does not exist
         * 5. check does id_resi exist in tblpacking
         * 6. throw if exists
         * 7. save into tblpacking
         */
        $receipt = $this->db
            ->select('id_printresi')
            ->get_where('tblprintresi', ['noresi' => $packer['noresi']])
            ->row();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        unset($packer['noresi']);

        // Check if this receipt has been picked
        $picking_exist = $this->db->get_where('tblresiambilbarang', ['id_resi' => $receipt->id_printresi])->row();
        if (!$picking_exist) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
        }

        // Check if this receipt has already been packed
        $packer_exist = $this->db->get_where('tblpacking', ['id_resi' => $receipt->id_printresi])->row();
        if ($packer_exist) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah di-packing. Silakan Cek data'];
        }

        // Insert packing record
        $insert_data = [
            'id_resi' => $receipt->id_printresi,
            'tanggal_packing' => date('Y-m-d H:i:s'),
            'packer_pegawai' => $user['id_user'],
            'keterangan' => $user['nama_komputer']
        ];

        $this->db->insert('tblpacking', $insert_data);

        // Update tblresiambilbarang to reset pending to ''
        $this->db->where('id_resiambilbarang', $picking_exist->id_resiambilbarang);
        $this->db->update('tblresiambilbarang', ['pending' => '']);

        $packer['affected_rows'] = $this->db->affected_rows();

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

        $this->db->from('tblpacking p');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = p.id_resi');
        $this->db->join('tbluser u', 'u.id_user = p.packer_pegawai', 'left');

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

        $this->db->from('tblpacking p');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = p.id_resi');
        $this->db->join('tbluser u', 'u.id_user = p.packer_pegawai', 'left');

        return $this->db->count_all_results();
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
            t3.name as nama_pegawai
        ');
        $this->db->from('tblresiambilbarang t');
        $this->db->join('tblprintresi t2', 't.id_resi = t2.id_printresi');
        $this->db->join('tbluser t3', 't3.id_user = t.admin_pegawai');
        $this->db->where('t2.noresi', $noresi);

        $query = $this->db->get();
        return $query->row();
    }
}

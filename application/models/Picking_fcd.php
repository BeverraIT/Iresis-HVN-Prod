<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picking_fcd extends CI_Model
{

    function get_picker($picker_status_aktif = null)
    {
        if (!empty($picker_status_aktif)) {
            $criterias['t.status_aktif'] = $picker_status_aktif;
        }

        $criterias['t1.status_aktif'] = 'AKTIF';

        $this->db->select('t.*, t1.nama_pegawai');

        $this->db->join('tblpegawai t1', 't1.kode_pegawai = t.id_pegawai');

        $this->db->where($criterias);

        $this->db->order_by('t1.nama_pegawai');

        return $this->db->get('tblnamaambilbarang t');
    }

    function save($picking, $user, $mode = PICKING_INSERT_PACKER)
    {
        // 1. Check if noresi exists
        $receipt = $this->db
            ->select('id_printresi')
            ->get_where('tblprintresi', ['noresi' => $picking['noresi']])
            ->result_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        unset($picking['noresi']);

        $id_resi_list = array_column($receipt, 'id_printresi');

        // 2. Check if id_resi exists in tblresiambilbarang
        $this->db->where_in('id_resi', $id_resi_list);
        $picking_exist = $this->db->get('tblresiambilbarang')->result_array();

        if ($mode == PICKING_INSERT_PACKER) {
            if (count($picking_exist) > 0) {
                return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah diambil. Silakan Cek data'];
            }

            foreach ($receipt as $row) {
                $insert_batch_data[] = [
                    'id_resi' => $row['id_printresi'],
                    'tanggal_resiambilbarang' => date('Y-m-d H:i:s'),
                    'admin_pegawai' => $user['id_user'],
                    'yangambil_pegawai' => $picking['yangambil_pegawai'],
                    'nama_komputer' => $user['nama_komputer'],
                    'pending' => $picking['pending'],
                ];
            }

            $this->db->insert_batch('tblresiambilbarang', $insert_batch_data);
            $picking['affected_rows'] = count($insert_batch_data);
        } else if ($mode == PICKING_UPDATE_PACKER) {
            if (count($picking_exist) == 0) {
                return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
            }

            foreach ($picking_exist as $row) {
                $update_batch_data[] = [
                    'id_resiambilbarang' => $row['id_resiambilbarang'],
                    'tanggal_resiambilbarang' => date('Y-m-d H:i:s'),
                    'admin_pegawai' => $user['id_user'],
                    'yangambil_pegawai' => $picking['yangambil_pegawai'],
                    'nama_komputer' => $user['nama_komputer']
                ];
            }

            $this->db->update_batch('tblresiambilbarang', $update_batch_data, 'id_resiambilbarang');
            $picking['affected_rows'] = count($update_batch_data);
        }

        return $picking;
    }

    function save_picker($picker)
    {
        $existing_picker = $this->db->get_where('tblnamaambilbarang', ['id_pegawai' => $picker['id_pegawai']])->row_array();

        if (!empty($existing_picker)) {
            $picker['id_namaambilbarang'] = $existing_picker['id_namaambilbarang'];
        }

        $this->db->replace('tblnamaambilbarang', $picker);

        $picker['affected_rows'] = $this->db->affected_rows();

        return $picker;
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
            t.id_resiambilbarang id,
            t2.noresi,
            t3.nama_pegawai packer,
            t.tanggal_resiambilbarang,
            t4.name admin,
            t.nama_komputer
        ');

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi');
        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.yangambil_pegawai', 'left');
        $this->db->join('tbluser t4', 't4.id_user = t.admin_pegawai', 'left');
        //$this->db->group_by('t2.noresi');
        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tblresiambilbarang t');
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

        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.yangambil_pegawai', 'left');

        $this->db->join('tbluser t4', 't4.id_user = t.admin_pegawai', 'left');

        $query = $this->db->select("count(1) as num")->get("tblresiambilbarang t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function destroy_picker($id_namaambilbarang)
    {
        $this->db->delete('tblnamaambilbarang', ['id_namaambilbarang' => $id_namaambilbarang]);

        $receipt['affected_rows'] = $this->db->affected_rows();

        return $receipt;
    }

    function get_total_scan_user($id_user)
    {
        $this->db->select('count(1) as total_scan');

        $criterias = [
            'tanggal_resiambilbarang >= ' => date('Y-m-d'),
            'admin_pegawai' => $id_user,
        ];

        $this->db->where($criterias);

        return $this->db->get_where('tblresiambilbarang');
    }
}

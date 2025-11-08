<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_fcd extends CI_Model
{

    function save($retur, $user)
    {
        $receipt = $this->db
            ->select('id_printresi, id_kurir, id_marketplace, noresi')
            ->get_where('tblprintresi', ['noresi' => $retur['noresi']])
            ->row_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        $id_resi = $receipt['id_printresi'];

        $this->db->where('id_resi', $id_resi);
        $retur_exist = $this->db->get('tblresiretur')->row_array();
        if (!empty($retur_exist)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi sudah ada. Silakan Cek data'];
        }

        $insert_data = [
            'id_resi' => $receipt['id_printresi'],
            'tanggal_resiretur' => date('Y-m-d H:i:s'),
            'sudah_cetak' => '',
            'id_kurir' => $receipt['id_kurir'],
            'id_pegawai' => $user['id_user'],
            'id_marketplace' => $receipt['id_marketplace'],
            'noresi' => $receipt['noresi']
        ];

        $this->db->insert('tblresiretur', $insert_data);

        $retur['id_resiretur'] = $this->db->insert_id();
        $retur['affected_rows'] = 1;

        return $retur;
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
            t.id_resiretur,
            t.noresi,
            t.tanggal_resiretur,
            t2.nama_marketplace,
            t3.nama_kurir,
            t4.username
        ');

        $this->db->join('tblmarketplace t2', 't2.id_marketplace = t.id_marketplace', 'left');

        $this->db->join('tblkurir t3', 't3.id_kurir = t.id_kurir', 'left');

        $this->db->join('tbluser t4', 't4.id_user = t.id_pegawai', 'left');

        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tblresiretur t');
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

        $this->db->join('tblmarketplace t2', 't2.id_marketplace = t.id_marketplace', 'left');

        $this->db->join('tblkurir t3', 't3.id_kurir = t.id_kurir', 'left');

        $this->db->join('tbluser t4', 't4.id_user = t.id_pegawai', 'left');

        $query = $this->db->select("count(1) as num")->get("tblresiretur t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function destroy($id_resiretur)
    {
        $this->db->delete('tblresiretur', ['id_resiretur' => $id_resiretur]);

        $retur['affected_rows'] = $this->db->affected_rows();

        return $retur;
    }
}

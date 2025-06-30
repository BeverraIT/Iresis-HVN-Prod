<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Handover_fcd extends CI_Model
{

    function save($handover, $user)
    {
        /**
         * 1. check does noresi exist in tblprintresi
         * 2. throw if doest not exist
         * 3. check does id_resi exist in tblresikeluar
         * 4. throw if exist
         * 5. check does id_resi exist in tblresiambilbarang
         * 6. throw if not exist
         * 7. check does id_resi exist in tblpacking
         * 8. throw if not exist
         * 9. save into tblresikeluar
         */
        $receipt = $this->db->get_where('tblprintresi', ['noresi' => $handover['noresi']])->result_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 404, 'message' => 'Nomor resi tidak ditemukan', 'data' => ['EXCEPTION_CODE' => 'NOT_FOUND']];
        }

        unset($handover['noresi']);

        $id_resi_list = array_column($receipt, 'id_printresi');

        $this->db->where_in('id_resi', $id_resi_list);
        $picking_exist = $this->db->get('tblresiambilbarang')->result_array();
        if (count($picking_exist) == 0) {
            return ['error' => TRUE, 'code' => 402, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
        }

        $this->db->where_in('id_resi', $id_resi_list);
        $packer_exist = $this->db->get('tblpacking')->result_array();
        if (count($packer_exist) == 0) {
            return ['error' => TRUE, 'code' => 402, 'message' => 'Nomor resi belum di-packing. Silakan Cek data'];
        }

        $this->db->where_in('id_resi', $id_resi_list);
        $handover_exist = $this->db->get('tblresikeluar')->result_array();
        if (count($handover_exist) > 0) {
            return ['error' => TRUE, 'code' => 402, 'message' => 'Nomor resi sudah dikirim. Silakan cek data'];
        }

        foreach ($receipt as $row) {
            $insert_batch_data[] = [
                'id_resi' => $row['id_printresi'],
                'tanggal_resikeluar' => date('Y-m-d H:i:s'),
                'sudah_cetak' => '-',
                'tanggal_cetak' => '',
                'id_pegawai' => $user['id_user']
            ];
        }

        //$handover['id_pegawai'] = $user['id_pegawai'];

        $this->db->insert_batch('tblresikeluar', $insert_batch_data);

        $handover['id_resikeluar'] = $this->db->insert_id();
        $handover['affected_rows'] = count($insert_batch_data);

        return $handover;
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
            t.id_resikeluar id,
            t2.noresi,
            t3.nama_pegawai pegawai,
            t.tanggal_resikeluar,
            t.sudah_cetak,
            t.tanggal_cetak
        ');

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi');
        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.id_pegawai', 'left');
        $this->db->group_by('t2.noresi');
        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tblresikeluar t');
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

        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.id_pegawai', 'left');

        $query = $this->db->select("count(1) as num")->get("tblresikeluar t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_print($id_kurir, $start_date, $end_date)
    {
        $this->db->select('t2.noresi');

        $this->db->where([
            't2.id_kurir' => $id_kurir,
            't.tanggal_resikeluar >=' => $start_date,
            't.tanggal_resikeluar <' => $end_date,
        ]);

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi');

        $this->db->order_by('t2.noresi');

        return $this->db->get('tblresikeluar t');
    }

    function get_total_scan_user($id_pegawai)
    {
        $this->db->select('count(1) as total_scan');

        $criterias = [
            'tanggal_resikeluar >= ' => date('Y-m-d'),
            'id_pegawai' => $id_pegawai,
        ];

        $this->db->where($criterias);

        return $this->db->get_where('tblresikeluar');
    }
}

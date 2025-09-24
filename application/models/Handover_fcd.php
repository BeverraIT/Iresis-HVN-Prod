<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Handover_fcd extends CI_Model
{

    function save($handover, $user)
    {
        /**
         * 1. check does noresi exist in tblprintresi
         * 2. throw if does not exist
         * 3. check does id_resi exist in tblresikeluar
         * 4. throw if exist
         * 5. check does id_resi exist in tblresiambilbarang
         * 6. throw if not exist
         * 7. check does id_resi exist in tblpacking
         * 8. throw if not exist
         * 9. save into tblresikeluar
         */
        $receipt = $this->db
            ->select('id_printresi, status_pesanan')
            ->get_where('tblprintresi', ['noresi' => $handover['noresi']])
            ->row();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 404, 'message' => 'Nomor resi tidak ditemukan', 'data' => ['EXCEPTION_CODE' => 'NOT_FOUND']];
        }

        // Check if status_pesanan is COMPLETED or CANCELED
        if (in_array($receipt->status_pesanan, ['COMPLETED', 'CANCELED'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak dapat diproses karena status pesanan sudah ' . $receipt->status_pesanan];
        }

        unset($handover['noresi']);

        // Check if this receipt has been picked
        $picking_exist = $this->db->get_where('tblresiambilbarang', ['id_resi' => $receipt->id_printresi])->row();
        if (!$picking_exist) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
        }

        // Check if this receipt has been packed
        $packer_exist = $this->db->get_where('tblpacking', ['id_resi' => $receipt->id_printresi])->row();
        if (!$packer_exist) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi belum di-packing. Silakan Cek data'];
        }

        // Check if this receipt has already been handed over
        $handover_exist = $this->db->get_where('tblresikeluar', ['id_resi' => $receipt->id_printresi])->row();
        if ($handover_exist) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah dikirim. Silakan Cek data'];
        }

        $insert_data = [
            'id_resi' => $receipt->id_printresi,
            'tanggal_resikeluar' => date('Y-m-d H:i:s'),
            'sudah_cetak' => '-',
            'tanggal_cetak' => '',
            'id_pegawai' => $user['id_user']
        ];

        $this->db->insert('tblresikeluar', $insert_data);

        $handover['affected_rows'] = $this->db->affected_rows();

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
        //$this->db->group_by('t2.noresi');
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

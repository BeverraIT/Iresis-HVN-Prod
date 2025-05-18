<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_fcd extends CI_Model
{
    function get_data($data = null) {

        $this->db->join('tblmarketplace t2', 't.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t3', 't.id_kurir = t3.id_kurir', 'left');
        //$this->db->join('tbluser t4', 't.admin_pegawai = t4.id_user');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        $query = $this->db->get_compiled_select('tblprintresi t');
        log_message('error', 'Query yang dijalankan: ' . $query);
        return $this->db->query($query);

        // return $this->db->get('tblprintresi t');
    }

    function get_total_data($data = null) {
        return $this->db->count_all('tblprintresi');
    }

    function save($receipt, $id_user)
    {
        $receipt['tanggal_printresi'] = date('Y-m-d H:i:s');
        $receipt['admin_pegawai'] = $id_user;

        $this->db->insert('tblprintresi', $receipt);

        $receipt['id_printresi'] = $this->db->insert_id();
        $receipt['affected_rows'] = 1;

        return $receipt;
    }

    function save_reprint($receipt, $id_user)
    {
        $receipt['tanggal_printresi'] = date('Y-m-d H:i:s');
        $receipt['admin_pegawai'] = $id_user;

        $this->db->insert('tblprintulangresi', $receipt);

        $receipt['id_printresi'] = $this->db->insert_id();
        $receipt['affected_rows'] = 1;

        return $receipt;
    }

    function get_detail($noresi)
    {
        $this->db->select('t.noresi, t.tanggal_printresi, t2.nama_marketplace, t3.tanggal_resiambilbarang
            , t4.nama_pegawai picker, t5.tanggal_packing, t6.nama_pegawai packer
            , t5.keterangan komputer_packer_no, t7.nama_kurir, t8.tanggal_cetak
            , t8.tanggal_resikeluar');

        $this->db->join('tblmarketplace t2', 't2.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblresiambilbarang t3', 't3.id_resi = t.id_printresi', 'left');
        $this->db->join('tblpegawai t4', 't4.kode_pegawai = t3.yangambil_pegawai', 'left');
        $this->db->join('tblpacking t5', 't5.id_resi = t.id_printresi', 'left');
        $this->db->join('tblpegawai t6', 't6.kode_pegawai = t5.packer_pegawai', 'left');
        $this->db->join('tblkurir t7', 't7.id_kurir = t.id_kurir', 'left');
        $this->db->join('tblresikeluar t8', 't8.id_resi = t.id_printresi', 'left');

        $this->db->where(['t.noresi' => $noresi]);

        return $this->db->get('tblprintresi t');
    }

    function get_total_scan_user($id_user)
    {
        $this->db->select('count(1) as total_scan');

        $criterias = [
            'tanggal_printresi >= ' => date('Y-m-d'),
            'admin_pegawai' => $id_user,
        ];

        $this->db->where($criterias);

        return $this->db->get_where('tblprintresi');
    }

    function get_data_receipt_process_tab0($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            t3.nama_marketplace,
            t.tanggal_printresi,
            t.noresi,
            t4.nama_kurir,
            t.nomorpicklist
        ');

        $this->db->join('tblresiambilbarang t2', 't2.id_resi = t.id_printresi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t.id_kurir', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->where('t2.id_resiambilbarang is null');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblprintresi t');
    }

    function get_total_data_receipt_process_tab0($data, $start_date, $end_date)
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

        $this->db->join('tblresiambilbarang t2', 't2.id_resi = t.id_printresi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t.id_kurir', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->where('t2.id_resiambilbarang is null');

        $query = $this->db->select("count(1) as num")->get("tblprintresi t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_receipt_process_tab1($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            t3.nama_marketplace,
            t2.tanggal_printresi,
            t2.noresi,
            t4.nama_kurir,
            t2.nomorpicklist,
            t.tanggal_resiambilbarang,
            t5.nama_pegawai picker
        ');

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t2.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t2.id_kurir', 'left');

        $this->db->join('tblpegawai t5', 't5.kode_pegawai = t.yangambil_pegawai', 'left');

        $this->db->join('tblpacking t6', 't6.id_resi = t.id_resi', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);
        $this->db->where('t6.id_packing is null');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblresiambilbarang t');
    }

    function get_total_data_receipt_process_tab1($data, $start_date, $end_date)
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

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t2.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t2.id_kurir', 'left');

        $this->db->join('tblpegawai t5', 't5.kode_pegawai = t.yangambil_pegawai', 'left');

        $this->db->join('tblpacking t6', 't6.id_resi = t.id_resi', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);
        $this->db->where('t6.id_packing is null');

        $query = $this->db->select("count(1) as num")->get("tblresiambilbarang t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_receipt_process_tab2($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            t3.nama_marketplace,
            t2.tanggal_printresi,
            t2.noresi,
            t4.nama_kurir,
            t2.nomorpicklist,
            t.tanggal_resiambilbarang,
            t5.nama_pegawai picker,
            t7.nama_pegawai packer
        ');

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t2.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t2.id_kurir', 'left');

        $this->db->join('tblpegawai t5', 't5.kode_pegawai = t.yangambil_pegawai', 'left');

        $this->db->join('tblpacking t6', 't6.id_resi = t.id_resi', 'left');

        $this->db->join('tblpegawai t7', 't7.kode_pegawai = t6.packer_pegawai', 'left');

        $this->db->join('tblresikeluar t8', 't8.id_resi = t.id_resi', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);
        $this->db->where('t8.id_resikeluar is null');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblresiambilbarang t');
    }

    function get_total_data_receipt_process_tab2($data, $start_date, $end_date)
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

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t2.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t2.id_kurir', 'left');

        $this->db->join('tblpegawai t5', 't5.kode_pegawai = t.yangambil_pegawai', 'left');

        $this->db->join('tblpacking t6', 't6.id_resi = t.id_resi', 'left');

        $this->db->join('tblpegawai t7', 't7.kode_pegawai = t6.packer_pegawai', 'left');

        $this->db->join('tblresikeluar t8', 't8.id_resi = t.id_resi', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);
        $this->db->where('t8.id_resikeluar is null');

        $query = $this->db->select("count(1) as num")->get("tblresiambilbarang t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_daily_report($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            f.nama_marketplace
            , e.nama_kurir
            , a.noresi
            , a.nomorpicklist
            , a.tanggal_printresi
            , t1.nama_pegawai admin_scan 
            , b.tanggal_resiambilbarang
            , t2.nama_pegawai admin_picker
            , c.tanggal_packing
            , t3.nama_pegawai admin_packer
            , d.tanggal_resikeluar
            , t4.nama_pegawai admin_ho
        ');

        $this->db->join('tblresiambilbarang b', 'a.id_printresi = b.id_resi', 'left');
        $this->db->join('tblpacking c', 'a.id_printresi = c.id_resi', 'left');
        $this->db->join('tblresikeluar d', 'a.id_printresi = d.id_resi', 'left');
        $this->db->join('tblkurir e', 'e.id_kurir = a.id_kurir', 'left');
        $this->db->join('tblmarketplace f', 'f.id_marketplace = a.id_marketplace', 'left');
        $this->db->join('tblpegawai t1', 't1.kode_pegawai = a.admin_pegawai ', 'left');
        $this->db->join('tblpegawai t2', 't2.kode_pegawai = b.yangambil_pegawai ', 'left');
        $this->db->join('tblpegawai t3', 't3.kode_pegawai = c.packer_pegawai ', 'left');
        $this->db->join('tblpegawai t4', 't4.kode_pegawai = d.id_pegawai', 'left');

        $this->db->where('a.tanggal_printresi >=', $start_date);
        $this->db->where('a.tanggal_printresi <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblprintresi a');
    }

    function get_total_data_daily_report($data, $start_date, $end_date)
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

        $this->db->join('tblresiambilbarang b', 'a.id_printresi = b.id_resi', 'left');
        $this->db->join('tblpacking c', 'a.id_printresi = c.id_resi', 'left');
        $this->db->join('tblresikeluar d', 'a.id_printresi = d.id_resi', 'left');
        $this->db->join('tblkurir e', 'e.id_kurir = a.id_kurir', 'left');
        $this->db->join('tblmarketplace f', 'f.id_marketplace = a.id_marketplace', 'left');
        $this->db->join('tblpegawai t1', 't1.kode_pegawai = a.admin_pegawai ', 'left');
        $this->db->join('tblpegawai t2', 't2.kode_pegawai = b.yangambil_pegawai ', 'left');
        $this->db->join('tblpegawai t3', 't3.kode_pegawai = c.packer_pegawai ', 'left');
        $this->db->join('tblpegawai t4', 't4.kode_pegawai = d.id_pegawai', 'left');

        $this->db->where('a.tanggal_printresi >=', $start_date);
        $this->db->where('a.tanggal_printresi <=', $end_date);

        $query = $this->db->select("count(1) as num")->get("tblprintresi a");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_header_daily_report($start_date, $end_date)
    {
        $this->db->select('
            count(case when a.tanggal_printresi is not null THEN 1 END) total_scan_resi
            , count(case when b.tanggal_resiambilbarang is not null THEN 1 END) total_pick_resi
            , count(case when c.tanggal_packing is not null THEN 1 END) total_pack_resi
            , count(case when d.tanggal_resikeluar is not null THEN 1 END) total_ho_resi
        ');

        $this->db->join('tblresiambilbarang b', 'a.id_printresi = b.id_resi', 'left');
        $this->db->join('tblpacking c', 'a.id_printresi = c.id_resi', 'left');
        $this->db->join('tblresikeluar d', 'a.id_printresi = d.id_resi', 'left');

        $this->db->where('a.tanggal_printresi >=', $start_date);
        $this->db->where('a.tanggal_printresi <=', $end_date);

        return $this->db->get('tblprintresi a');
    }

    function destroy($id_printresi, $id_user)
    {
        /**
         * 1. get resi from tblprintresi
         * 2. insert resi from #1 into tblprintresihapus with added field (`admin_pegawai_hapus`, `tanggal_printresi_hapus`)
         * 3. delete resi from tblprintresi
         */

        $receipt = $this->db->get_where('tblprintresi', ['id_printresi' => $id_printresi])->row_array();

        if (empty($receipt)) {
            $receipt['affected_rows'] = 0;

            return $receipt;
        }

        $receipt['admin_pegawai_hapus'] = $id_user;
        $receipt['tanggal_printresi_hapus'] = date('Y-m-d H:i:s');

        $this->db->insert('tblprintresihapus', $receipt);

        $this->db->delete('tblprintresi', ['id_printresi' => $id_printresi]);

        $receipt['affected_rows'] = $this->db->affected_rows();

        return $receipt;
    }

    function destroy_by_noresi($noresi, $user)
    {
        $receipt = $this->db->get_where('tblprintresi', ['noresi' => $noresi])->row_array();
        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        return $this->destroy($receipt['id_printresi'], $user['id_user']);
    }

    function get_data_receipt_tab0($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            t.tanggal_printresi,
            t3.nama_marketplace,
            t.nomorpicklist,
            t4.nama_kurir,
            t.noresi,
            t6.tanggal_resikeluar,
            t7.nama_pegawai as picker,
            t8.nama_pegawai as packer
        ');

        $this->db->join('tblresiambilbarang t2', 't2.id_resi = t.id_printresi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t.id_kurir', 'left');

        $this->db->join('tblpacking t5', 't5.id_resi = t.id_printresi', 'left');

        $this->db->join('tblresikeluar t6', 't6.id_resi = t.id_printresi', 'left');

        $this->db->join('tblpegawai t7', 't7.kode_pegawai = t2.yangambil_pegawai', 'left');

        $this->db->join('tblpegawai t8', 't8.kode_pegawai = t6.id_pegawai', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblprintresi t');
    }

    function get_total_data_receipt_tab0($data, $start_date, $end_date)
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

        $this->db->join('tblresiambilbarang t2', 't2.id_resi = t.id_printresi', 'left');

        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t.id_marketplace', 'left');

        $this->db->join('tblkurir t4', 't4.id_kurir = t.id_kurir', 'left');

        $this->db->join('tblpacking t5', 't5.id_resi = t.id_printresi', 'left');

        $this->db->join('tblresikeluar t6', 't6.id_resi = t.id_printresi', 'left');

        $this->db->join('tblpegawai t7', 't7.kode_pegawai = t2.yangambil_pegawai', 'left');

        $this->db->join('tblpegawai t8', 't8.kode_pegawai = t6.id_pegawai', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        $query = $this->db->select("count(1) as num")->get("tblprintresi t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_receipt_tab1($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            date(t.tanggal_printresi) as tanggal_printresi,
            t.nomorpicklist,
            COUNT(1) as total
        ');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        $this->db->group_by('date(t.tanggal_printresi), t.nomorpicklist');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblprintresi t');
    }

    function get_total_data_receipt_tab1($data, $start_date, $end_date)
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

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        $this->db->group_by('date(t.tanggal_printresi), t.nomorpicklist');

        $query = $this->db->select("count(1) as num")->get("tblprintresi t");
        $result = $query->num_rows();

        return isset($result) ? $result : 0;
    }

    function get_grand_total_data_receipt_tab1($data, $start_date, $end_date)
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

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        $query = $this->db->select("count(1) as num")->get("tblprintresi t");
        $result = $query->row()->num;

        return isset($result) ? $result : 0;
    }

    function get_data_shipped_report($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            t2.tanggal_resikeluar
            , t2.tanggal_resikeluar
            , t.noresi
            , t3.nama_kurir
            , t2.tanggal_cetak
        ');

        $this->db->join('tblresikeluar t2', 't2.id_resi = t.id_printresi');

        $this->db->join('tblkurir t3', 't3.id_kurir = t.id_kurir', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblprintresi t');
    }

    function get_total_data_shipped_report($data, $start_date, $end_date)
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

        $this->db->join('tblresikeluar t2', 't2.id_resi = t.id_printresi');

        $this->db->join('tblkurir t3', 't3.id_kurir = t.id_kurir', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);

        $query = $this->db->select("count(1) as num")->get("tblprintresi t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_shipping_report($start_date, $end_date)
    {
        $this->db->select('
            coalesce(t3.nama_kurir, \'- Tidak diketahui -\') nama_kurir
            , count(1) total
        ');

        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi');

        $this->db->join('tblkurir t3', 't3.id_kurir = t2.id_kurir', 'left');

        $this->db->where('t.tanggal_resikeluar >=', $start_date);
        $this->db->where('t.tanggal_resikeluar <=', $end_date);

        $this->db->group_by('t3.nama_kurir');

        $this->db->order_by('t3.nama_kurir');

        return $this->db->get('tblresikeluar t');
    }

    function get_grand_total_data_shipping_report($start_date, $end_date)
    {
        $this->db->where('t.tanggal_resikeluar >=', $start_date);
        $this->db->where('t.tanggal_resikeluar <=', $end_date);

        $query = $this->db->select("count(1) as num")->get("tblresikeluar t");
        $result = $query->row()->num;

        return isset($result) ? $result : 0;
    }

    function get_data_per_day_report($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
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
            t.noresi
            , t5.nama_marketplace
            , t6.nama_kurir
            , t.nomorpicklist
            , t.tanggal_printresi
            , t2.tanggal_resiambilbarang
            , t3.tanggal_packing
            , t4.tanggal_resikeluar
        ');

        $this->db->join('tblresiambilbarang t2', 't.id_printresi = t2.id_resi', 'left');
        $this->db->join('tblpacking t3', 't.id_printresi = t3.id_resi', 'left');
        $this->db->join('tblresikeluar t4', 't.id_printresi = t4.id_resi', 'left');
        $this->db->join('tblmarketplace t5', 't5.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblkurir t6', 't6.id_kurir = t.id_kurir', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->group_start()
            ->where('t2.id_resiambilbarang', null)
            ->or_where('t3.id_packing', null)
            ->or_where('t4.id_resikeluar', null);
        $this->db->group_end();

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblprintresi t');
    }

    function get_total_data_per_day_report($data, $start_date, $end_date)
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

        $this->db->join('tblresiambilbarang t2', 't.id_printresi = t2.id_resi', 'left');
        $this->db->join('tblpacking t3', 't.id_printresi = t3.id_resi', 'left');
        $this->db->join('tblresikeluar t4', 't.id_printresi = t4.id_resi', 'left');
        $this->db->join('tblmarketplace t5', 't5.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblkurir t6', 't6.id_kurir = t.id_kurir', 'left');

        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->group_start()
            ->where('t2.id_resiambilbarang', null)
            ->or_where('t3.id_packing', null)
            ->or_where('t4.id_resikeluar', null);
        $this->db->group_end();

        $query = $this->db->select("count(1) as num")->get("tblprintresi t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function insert_receipt(array $receiptData, ?string $user_id = null) {
        if (empty($receiptData)) return;

        $batch_size = 500;
        $batch_data = [];
        $total_success_insert = 0;
        $total_skip_insert = 0;

        // Lookup maps for faster ID resolution
        $marketplace_map = [];
        foreach ($this->db->get('tblmarketplace')->result() as $mp) {
            $marketplace_map[strtolower($mp->nama_marketplace)] = $mp->id_marketplace;
        }

        $kurir_map = [];
        foreach ($this->db->get('tblkurir')->result() as $kr) {
            $kurir_map[strtolower($kr->nama_kurir)] = $kr->id_kurir;
        }

        // Cache existing data
        $existing_data = $this->get_existing_receipt_data($receiptData);

        // Convert Excel serial date to PHP date string
        $excelDateToPhpDate = function ($excelDate) {
            if (is_numeric($excelDate)) {
                $unixDate = ($excelDate - 25569) * 86400;
                return gmdate("d/m/Y", $unixDate);
            }
            return $excelDate; // assume it's already in proper format
        };

        // Convert Excel fractional time to H:i:s
        $excelTimeToPhpTime = function ($excelTime) {
            if (is_numeric($excelTime)) {
                $totalSeconds = (int) round($excelTime * 86400); // 86400 = seconds per day
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = $totalSeconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
            return $excelTime; // Assume already formatted
        };

        // Helper to combine date and time
        $combineDateTime = function ($date, $time) {
            if (!$date || !$time) return null;
            $dt = DateTime::createFromFormat('d/m/Y H:i:s', "$date $time");
            return $dt ? $dt->format('Y-m-d H:i:s') : null;
        };

        foreach ($receiptData as $row) {
            $no_pesanan = $row['A'] ?? null;
            $sku = $row['P'] ?? null;

            if ($no_pesanan === 'NO_PESANAN' || !$no_pesanan || !$sku) continue;

            if (isset($existing_data["$no_pesanan-$sku"])) {
                $total_skip_insert++;
                continue;
            }

            $marketplace = strtolower($row['N'] ?? '');
            $id_marketplace = $marketplace_map[$marketplace] ?? 99;

            $kurirRaw = $row['S'] ?? '';
            $kurir = strtolower($kurirRaw);

            if (stripos($kurirRaw, 'SPX') !== false) {
                $kurir = 'shopee';
            }
            $id_kurir = $kurir_map[$kurir] ?? 99;

            $batch_data[] = [
                'noresi' => $row['B'] ?? null,
                'no_pesanan' => $no_pesanan,
                'id_marketplace' => $id_marketplace,
                'id_kurir' => $id_kurir,
                'admin_pegawai' => $user_id,
                'sku' => $sku,
                'nama_barang' => $row['W'] ?? null,
                'jumlah' => $row['Q'] ?? null,
                'no_rak' => $row['R'] ?? null,
                'tanggal_printresi' => $combineDateTime($excelDateToPhpDate($row['F'] ?? null), $excelTimeToPhpTime($row['G'] ?? null)),
                'tanggal_pesan' => $combineDateTime($excelDateToPhpDate($row['D'] ?? null), $excelTimeToPhpTime($row['E'] ?? null)),
                'tanggal_bataskirim' => $combineDateTime($excelDateToPhpDate($row['H'] ?? null), $excelTimeToPhpTime($row['I'] ?? null)),
                'tanggal_pengiriman' => $combineDateTime($excelDateToPhpDate($row['J'] ?? null), $excelTimeToPhpTime($row['K'] ?? null)),
                'tanggal_selesai' => $combineDateTime($excelDateToPhpDate($row['L'] ?? null), $excelTimeToPhpTime($row['M'] ?? null)),
                'tanggal_retur' => $combineDateTime($excelDateToPhpDate($row['U'] ?? null), $excelTimeToPhpTime($row['V'] ?? null)),
                'status_pesanan' => $row['T'] ?? null,
                'batal' => null,
                'keterangan' => null,
                'nomorpicklist' => $row['C'] ?? null
            ];

            if (count($batch_data) >= $batch_size) {
                $this->db->insert_batch('tblprintresi', $batch_data);
                $total_success_insert += count($batch_data);
                $batch_data = [];
            }
        }

        // Insert remaining batch
        if (!empty($batch_data)) {
            $this->db->insert_batch('tblprintresi', $batch_data);
            $total_success_insert += count($batch_data);
        }

        log_message('error', "Total Data Dimasukkan: $total_success_insert | Data Dilewati: $total_skip_insert");
    }

    private function get_existing_receipt_data(array $dataResi) {
        $no_pesanan_list = array_unique(array_column($dataResi, 'A'));
        if (empty($no_pesanan_list)) {
            return [];
        }

        // Ambil data yang sudah ada dalam 1 query
        $this->db->select('no_pesanan, sku');
        $this->db->where_in('no_pesanan', $no_pesanan_list);
        $query = $this->db->get('tblprintresi');
        $existing_data = [];

        foreach ($query->result() as $row) {
            $existing_data["{$row->no_pesanan}-{$row->sku}"] = true;
        }

        return $existing_data;
    }
}

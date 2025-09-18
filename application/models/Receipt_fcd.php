<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_fcd extends CI_Model
{
    function get_data($data = null) {

        $this->db->select('t.noresi, t.tanggal_printresi, t3.nama_kurir, t2.nama_marketplace, t.nomorpicklist, t.status_pesanan, t4.name, t.created_at, t.id_printresi');
        $this->db->from('tblprintresi t');
        $this->db->join('tblmarketplace t2', 't.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t3', 't.id_kurir = t3.id_kurir', 'left');
        $this->db->join('tbluser t4', 't.created_by = t4.id_user', 'left');
        $this->db->order_by('t.created_at', 'DESC');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $column) {
                if (!empty($column)) {
                    $this->db->or_like($column, $data['search']);
                }
            }
            $this->db->group_end();
        }

        $query = $this->db->get();
        log_message('error', 'Query yang dijalankan: ' . $this->db->last_query());
        return $query;
    }

    public function get_total_data($data = null)
    {
        $this->db->select('t.noresi, t.tanggal_printresi, t3.nama_kurir, t2.nama_marketplace, t.nomorpicklist, t.status_pesanan, t4.name, t.created_at, t.id_printresi');
        $this->db->from('tblprintresi t');
        $this->db->join('tblmarketplace t2', 't.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t3', 't.id_kurir = t3.id_kurir', 'left');
        $this->db->join('tbluser t4', 't.created_by = t4.id_user', 'left');
        $this->db->order_by('t.created_at', 'DESC');

        // Apply search filter if available
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $column) {
                if (!empty($column)) {
                    $this->db->or_like($column, $data['search']);
                }
            }
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    function get_detail_receipt($noresi) {
        $this->db->select('
            pr.id_printresi,
            pr.noresi,
            dr.sku,
            dr.jumlah,
            dr.no_rak
        ');
        $this->db->from('tblprintresi pr');
        $this->db->join('tbldetailprintresi dr', 'dr.id_resi = pr.id_printresi');
        $this->db->where('pr.noresi', $noresi);
        return $this->db->get();
    }

    function get_receipt_for_packer($data, $noresi) {
        $this->db->select('
            pr.noresi,
            pr.id_printresi,
            dr.sku,
            dr.jumlah,
            dr.no_rak,
            s.nama_sku,
            s.link_foto,
            rab.yangambil_pegawai,
            u.name
        ');
        $this->db->from('tblprintresi pr');
        $this->db->join('tbldetailprintresi dr', 'dr.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblsku s', 's.id_sku = dr.sku', 'left');
        $this->db->join('tblresiambilbarang rab', 'rab.id_resi = pr.id_printresi', 'left');
        $this->db->join('tbluser u', 'u.id_pegawai = rab.yangambil_pegawai', 'left');
        $this->db->join('tblpacking p', 'p.id_resi = pr.id_printresi', 'left');

        $this->db->where('pr.noresi', $noresi);
        //$this->db->where('p.id_resi IS NULL'); // Only unpacked items

        // Optional: pagination
        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        // Order by latest
        $this->db->order_by('pr.created_at', 'DESC');

        $query = $this->db->get();
        log_message('error', 'Query get_receipt_for_packer: ' . $this->db->last_query());

        return $query->result();
    }

    function get_total_receipt_for_packer($noresi) {
        $this->db->from('tblprintresi pr');
        $this->db->join('tbldetailprintresi dr', 'dr.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblpacking p', 'p.id_resi = pr.id_printresi', 'left');
        $this->db->where('pr.noresi', $noresi);
        $this->db->where('p.id_resi IS NULL'); // Only unpacked items

        return $this->db->count_all_results();
    }

    function save($receipt, $id_user)
    {
        $existing_data = $this->get_existing_receipt_data_scan($receipt);
        if ($existing_data) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah completed'];
        }

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
            , t4.nama_pegawai picker, t5.tanggal_packing, t6.name packer
            , t5.keterangan komputer_packer_no, t7.nama_kurir, t8.tanggal_cetak
            , t8.tanggal_resikeluar, t.status_pesanan, t.tanggal_retur, t.tanggal_bataskirim'
        );

        $this->db->join('tblmarketplace t2', 't2.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblresiambilbarang t3', 't3.id_resi = t.id_printresi', 'left');
        $this->db->join('tblpegawai t4', 't4.kode_pegawai = t3.yangambil_pegawai', 'left');
        $this->db->join('tblpacking t5', 't5.id_resi = t.id_printresi', 'left');
        $this->db->join('tbluser t6', 't6.id_user = t5.packer_pegawai', 'left');
        $this->db->join('tblkurir t7', 't7.id_kurir = t.id_kurir', 'left');
        $this->db->join('tblresikeluar t8', 't8.id_resi = t.id_printresi', 'left');

        $this->db->where(['t.noresi' => $noresi]);
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit(1);

        return $this->db->get('tblprintresi t');
    }

    function get_detail_items($noresi)
    {
        $this->db->select('t9.sku, t9.jumlah, t9.no_pesanan');
        $this->db->join('tbldetailprintresi t9', 't9.id_resi = t.id_printresi', 'inner');
        $this->db->where(['t.noresi' => $noresi]);
        $this->db->order_by('t9.id_detail_resi', 'ASC');

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

        $this->db->distinct();
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

        $query = $this->db->select("COUNT(DISTINCT t.noresi) AS num")->get("tblprintresi t");
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

        $this->db->distinct();
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

        $query = $this->db->select("COUNT(DISTINCT t2.noresi) AS num")->get("tblresiambilbarang t");
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
            t4.nama_marketplace,
            t2.tanggal_printresi,
            t2.noresi,
            t5.nama_kurir,
            t2.nomorpicklist,
            t3.tanggal_resiambilbarang,
            t6.nama_pegawai picker,
            t7.name packer
        ');

        $this->db->distinct();
        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'left');
        $this->db->join('tblresiambilbarang t3', 't3.id_resi = t2.id_printresi', 'left');
        $this->db->join('tblmarketplace t4', 't4.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t5', 't5.id_kurir = t2.id_kurir', 'left');
        $this->db->join('tblpegawai t6', 't6.kode_pegawai = t3.yangambil_pegawai', 'left');
        $this->db->join('tbluser t7', 't7.id_user = t.packer_pegawai', 'left');
        $this->db->join('tblresikeluar t8', 't8.id_resi = t2.id_printresi', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);
        $this->db->where('t8.id_resikeluar is null');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblpacking t');
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
        $this->db->join('tblresiambilbarang t3', 't3.id_resi = t2.id_printresi', 'left');
        $this->db->join('tblmarketplace t4', 't4.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t5', 't5.id_kurir = t2.id_kurir', 'left');
        $this->db->join('tblpegawai t6', 't6.kode_pegawai = t3.yangambil_pegawai', 'left');
        $this->db->join('tbluser t7', 't7.id_user = t.packer_pegawai', 'left');
        $this->db->join('tblresikeluar t8', 't8.id_resi = t2.id_printresi', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);
        $this->db->where('t8.id_resikeluar is null');

        $query = $this->db->select("COUNT(DISTINCT t2.noresi) AS num")->get("tblpacking t");
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

        $this->db->distinct();
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

        $query = $this->db->select("COUNT(DISTINCT a.noresi) AS num")->get("tblprintresi a");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_header_daily_report($start_date, $end_date)
    {
        $this->db->select('
            count(DISTINCT case when a.tanggal_printresi is not null THEN a.noresi END) total_scan_resi
            , count(DISTINCT case when b.tanggal_resiambilbarang is not null THEN a.noresi END) total_pick_resi
            , count(DISTINCT case when c.tanggal_packing is not null THEN a.noresi END) total_pack_resi
            , count(DISTINCT case when d.tanggal_resikeluar is not null THEN a.noresi END) total_ho_resi
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
         * 3. delete related records from tbldetailprintresi
         * 4. delete resi from tblprintresi
         */

        $receipt = $this->db->get_where('tblprintresi', ['id_printresi' => $id_printresi])->row_array();

        if (empty($receipt)) {
            $receipt['affected_rows'] = 0;

            return $receipt;
        }

        //$receipt['admin_pegawai_hapus'] = $id_user;
        //$receipt['tanggal_printresi_hapus'] = date('Y-m-d H:i:s');

        $data = [
            'id_printresi' => $receipt['id_printresi'],
            'tanggal_printresi' => $receipt['tanggal_printresi'],
            'id_marketplace' => $receipt['id_marketplace'],
            'noresi' => $receipt['noresi'],
            'nomorpicklist' => $receipt['nomorpicklist'],
            'batal' => $receipt['batal'],
            'keterangan' => $receipt['keterangan'],
            'id_kurir' => $receipt['id_kurir'],
            'admin_pegawai' => $receipt['admin_pegawai'],
            'admin_pegawai_hapus' => $id_user,
            'tanggal_printresi_hapus' => date('Y-m-d H:i:s')
        ];

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $data[$key] = '';
            }
        }

        $this->db->insert('tblprintresihapus', $data);

        // Delete related records from tbldetailprintresi first
        $this->db->delete('tbldetailprintresi', ['id_resi' => $id_printresi]);

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

        return $this->destroy($receipt['id_printresi'], $user);
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

        $this->db->distinct();
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

        $query = $this->db->select("COUNT(DISTINCT t.noresi) AS num")->get("tblprintresi t");
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

        $this->db->distinct();
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

        $query = $this->db->select("COUNT(DISTINCT t.noresi) AS num")->get("tblprintresi t");
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

        $this->db->distinct();
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

        $query = $this->db->select("COUNT(DISTINCT t.noresi) as num")->get("tblprintresi t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function insert_receipt(array $receiptData, ?string $user_id = null) {
        if (empty($receiptData)) return "No data provided";

        $batch_size = 500;
        $total_success_insert = 0;
        $total_skip_insert = 0;
        $total_duplicate_skip = 0;
        $total_skip_update_same_status = 0; // Track skipped updates

        // Start transaction
        $this->db->trans_start();

        try {
            // Lookup caches
            $marketplace_map = [];
            foreach ($this->db->get('tblmarketplace')->result() as $mp) {
                $marketplace_map[strtolower($mp->nama_marketplace)] = $mp->id_marketplace;
            }
            $kurir_map = [];
            foreach ($this->db->get('tblkurir')->result() as $kr) {
                $kurir_map[strtolower($kr->nama_kurir)] = $kr->id_kurir;
            }

            // Helpers
            $excelDateToPhpDate = function ($excelDate) {
                if (is_numeric($excelDate)) {
                    $unixDate = ($excelDate - 25569) * 86400;
                    return gmdate("d/m/Y", $unixDate);
                }
                return $excelDate;
            };
            $excelTimeToPhpTime = function ($excelTime) {
                if (is_numeric($excelTime)) {
                    $totalSeconds = (int) round($excelTime * 86400);
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                    $seconds = $totalSeconds % 60;
                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                }
                return $excelTime;
            };
            $combineDateTime = function ($date, $time) {
                if (!$date || !$time) return null;
                $dt = DateTime::createFromFormat('d/m/Y H:i:s', "$date $time");
                return $dt ? $dt->format('Y-m-d H:i:s') : null;
            };

            $courier_aliases = [
                'anteraja'          => ['anteraja'],
                'baraka'            => ['baraka'],
                'central cargo'     => ['kargo', 'central cargo'],
                'goto'              => ['goto'],
                'id express'        => ['id express'],
                'instant/sameday'   => ['instant', 'gosend', 'grab'],
                'jemput sendiri'    => ['jemput sendiri'],
                'jne'               => ['jne'],
                'jnt'               => ['j&t', 'jnt'],
                'lazada'            => ['lazada', 'lex id'],
                'ninja'             => ['ninja'],
                'rex'               => ['rex'],
                'sicepat - rekom'   => ['sicepat rekom', 'rekomendasi'],
                'sicepat'           => ['sicepat'],
                'shopee'            => ['spx', 'shopee'],
                'spax'              => ['spax'],
                'wahana'            => ['wahana'],
            ];
            $detectCourier = function(string $raw) use ($courier_aliases) {
                $rawLower = strtolower($raw);
                $section = $rawLower;
                if (stripos($rawLower, 'diantar oleh:') !== false) {
                    $parts = explode('diantar oleh:', $rawLower);
                    $section = trim($parts[1]);
                } elseif (stripos($rawLower, 'delivery:') !== false) {
                    $parts = explode('delivery:', $rawLower);
                    $section = trim($parts[1]);
                }
                foreach ([$section, $rawLower] as $text) {
                    foreach ($courier_aliases as $norm => $alts) {
                        foreach ($alts as $needle) {
                            if (stripos($text, $needle) !== false) return $norm;
                        }
                    }
                }
                return '';
            };

            // Check for existing records with status-based logic
            $existing_noresi = [];
            $update_candidates = [];
            $existing_status_map = [];
            $noresi_list = array_filter(array_unique(array_column($receiptData, 'B')));
            if (!empty($noresi_list)) {
                // Process in smaller chunks to avoid regex compilation errors
                $chunk_size = 1000; // Process 1000 noresi at a time
                $noresi_chunks = array_chunk($noresi_list, $chunk_size);

                foreach ($noresi_chunks as $chunk) {
                    $this->db->select('noresi, status_pesanan, id_printresi');
                    $this->db->from('tblprintresi');
                    $this->db->where_in('noresi', $chunk);
                    $existing_result = $this->db->get()->result();

                    foreach ($existing_result as $row) {
                        $status = strtoupper(trim($row->status_pesanan ?? ''));
                        $existing_status_map[$row->noresi] = $status;
                        if ($status === 'COMPLETED') {
                            // Skip - status is COMPLETED, do not update
                            $existing_noresi[$row->noresi] = 'completed_skip';
                            $total_duplicate_skip++;
                        } else {
                            // Can be updated - status is null, empty string, or anything except COMPLETED
                            $update_candidates[$row->noresi] = [
                                'id_printresi' => $row->id_printresi,
                                'current_status' => $row->status_pesanan
                            ];
                        }
                    }
                }
            }

            $batch_header_map = [];
            $batch_detail_map = [];
            $batch_update_map = [];
            $total_updated = 0;

            foreach ($receiptData as $row) {
                $noresi             = $row['B'] ?? '';
                $no_pesanan         = $row['A'] ?? null;
                $sku                = $row['P'] ?? '';
                $status_pesanan     = $row['T'] ?? null;
                $new_status_upper   = strtoupper(trim($status_pesanan ?? ''));

                // Skip header row or invalid data
                if ($no_pesanan === 'NO_PESANAN' || !$no_pesanan || !$sku || !$noresi) {
                    $total_skip_insert++;
                    continue;
                }

                // Skip if already exists and is COMPLETED
                if (isset($existing_noresi[$noresi])) {
                    continue; // This will be counted in total_duplicate_skip already
                }

                // Check if this noresi needs to be updated instead of inserted
                if (isset($update_candidates[$noresi])) {
                    $current_status = strtoupper(trim($existing_status_map[$noresi] ?? ''));
                    // Only update if status_pesanan is different (case-insensitive, trim)
                    if ($current_status !== $new_status_upper) {
                        // Normalize marketplace
                        $marketplaceRaw = strtolower($row['N'] ?? '');
                        if (stripos($marketplaceRaw, 'tokopedia') !== false) {
                            $marketplace = 'tokopedia';
                        }
                        elseif (stripos($marketplaceRaw, 'internal') !== false) {
                            $marketplace = 'reseller';
                        }
                        else $marketplace = $marketplaceRaw;
                        $id_marketplace = $marketplace_map[$marketplace] ?? 99;

                        // Normalize courier
                        $kurirRaw = $row['S'] ?? '';
                        $kurir = $detectCourier($kurirRaw);
                        $id_kurir = $kurir_map[$kurir] ?? 99;

                        $batch_update_map[$noresi] = [
                            'id_printresi'        => $update_candidates[$noresi]['id_printresi'],
                            'id_marketplace'      => $id_marketplace,
                            'id_kurir'            => $id_kurir,
                            'tanggal_pesan'       => $combineDateTime($excelDateToPhpDate($row['D'] ?? null), $excelTimeToPhpTime($row['E'] ?? null)),
                            'tanggal_bataskirim'  => $combineDateTime($excelDateToPhpDate($row['H'] ?? null), $excelTimeToPhpTime($row['I'] ?? null)),
                            'tanggal_pengiriman'  => $combineDateTime($excelDateToPhpDate($row['J'] ?? null), $excelTimeToPhpTime($row['K'] ?? null)),
                            'tanggal_selesai'     => $combineDateTime($excelDateToPhpDate($row['L'] ?? null), $excelTimeToPhpTime($row['M'] ?? null)),
                            'tanggal_retur'       => $combineDateTime($excelDateToPhpDate($row['U'] ?? null), $excelTimeToPhpTime($row['V'] ?? null)),
                            'status_pesanan'      => $row['T'] ?? null,
                            'nomorpicklist'       => $row['C'] ?? '',
                            'modified_at'         => date('Y-m-d H:i:s'),
                            'modified_by'         => $user_id
                        ];
                    } else {
                        $total_skip_update_same_status++;
                    }
                    continue;
                }

                // Normalize marketplace
                $marketplaceRaw = strtolower($row['N'] ?? '');
                if (stripos($marketplaceRaw, 'tokopedia') !== false) {
                    $marketplace = 'tokopedia';
                }
                elseif (stripos($marketplaceRaw, 'internal') !== false) {
                    $marketplace = 'reseller';
                }
                else $marketplace = $marketplaceRaw;
                $id_marketplace = $marketplace_map[$marketplace] ?? 99;

                // Normalize courier
                $kurirRaw = $row['S'] ?? '';
                $kurir = $detectCourier($kurirRaw);
                $id_kurir = $kurir_map[$kurir] ?? 99;

                // Always set detail_key
                $detail_key = "$noresi-$no_pesanan-$sku";

                // Header: one per noresi
                if (!isset($batch_header_map[$noresi])) {
                    $batch_header_map[$noresi] = [
                        'noresi'              => $noresi,
                        'id_marketplace'      => $id_marketplace,
                        'id_kurir'            => $id_kurir,
                        'admin_pegawai'       => $user_id,
                        'tanggal_printresi'   => $combineDateTime($excelDateToPhpDate($row['F'] ?? ''), $excelTimeToPhpTime($row['G'] ?? '')),
                        'tanggal_pesan'       => $combineDateTime($excelDateToPhpDate($row['D'] ?? null), $excelTimeToPhpTime($row['E'] ?? null)),
                        'tanggal_bataskirim'  => $combineDateTime($excelDateToPhpDate($row['H'] ?? null), $excelTimeToPhpTime($row['I'] ?? null)),
                        'tanggal_pengiriman'  => $combineDateTime($excelDateToPhpDate($row['J'] ?? null), $excelTimeToPhpTime($row['K'] ?? null)),
                        'tanggal_selesai'     => $combineDateTime($excelDateToPhpDate($row['L'] ?? null), $excelTimeToPhpTime($row['M'] ?? null)),
                        'tanggal_retur'       => $combineDateTime($excelDateToPhpDate($row['U'] ?? null), $excelTimeToPhpTime($row['V'] ?? null)),
                        'status_pesanan'      => $row['T'] ?? null,
                        'batal'               => '',
                        'keterangan'          => '',
                        'nomorpicklist'       => $row['C'] ?? '',
                        'created_at'          => date('Y-m-d H:i:s'),
                        'created_by'          => $user_id
                    ];
                }

                // Detail: sum per noresi + no_pesanan + sku
                if (!isset($batch_detail_map[$detail_key])) {
                    $batch_detail_map[$detail_key] = [
                        'noresi'     => $noresi,
                        'no_pesanan' => $no_pesanan,
                        'sku'        => $sku,
                        'no_rak'     => $row['R'] ?? '',
                        'jumlah'     => (int)($row['Q'] ?? 0)
                    ];
                } else {
                    $batch_detail_map[$detail_key]['jumlah'] += (int)($row['Q'] ?? 0);
                }
            }

            // Insert Headers in batches
            if (!empty($batch_header_map)) {
                $header_chunks = array_chunk(array_values($batch_header_map), $batch_size);
                foreach ($header_chunks as $chunk) {
                    $this->db->insert_batch('tblprintresi', $chunk);
                }
                $total_success_insert += count($batch_header_map);
            }

            // Get inserted header IDs for detail insertion - handle large datasets properly
            $noresi_list = array_keys($batch_header_map);
            $id_resi_map = [];
            if (!empty($noresi_list)) {
                // Process in smaller chunks to avoid regex compilation errors
                $chunk_size = 1000; // Process 1000 noresi at a time
                $noresi_chunks = array_chunk($noresi_list, $chunk_size);

                foreach ($noresi_chunks as $chunk) {
                    $this->db->select('id_printresi, noresi');
                    $this->db->from('tblprintresi');
                    $this->db->where_in('noresi', $chunk);
                    $headers = $this->db->get()->result();

                    foreach ($headers as $h) {
                        $id_resi_map[$h->noresi] = $h->id_printresi;
                    }
                }

                // Prepare Detail Rows
                $detail_rows = [];
                foreach ($batch_detail_map as $detail) {
                    $id_resi = $id_resi_map[$detail['noresi']] ?? null;
                    if (!$id_resi) continue;

                    $detail_rows[] = [
                        'id_resi'    => $id_resi,
                        'no_pesanan' => $detail['no_pesanan'],
                        'sku'        => $detail['sku'],
                        'no_rak'     => $detail['no_rak'],
                        'jumlah'     => $detail['jumlah']
                    ];
                }

                // Insert Details in batches
                if (!empty($detail_rows)) {
                    $detail_chunks = array_chunk($detail_rows, $batch_size);
                    foreach ($detail_chunks as $chunk) {
                        $this->db->insert_batch('tbldetailprintresi', $chunk);
                    }
                }
            }

            // Update records that were marked for update
            if (!empty($batch_update_map)) {
                foreach ($batch_update_map as $noresi => $update_data) {
                    $this->db->where('id_printresi', $update_data['id_printresi']);
                    $this->db->update('tblprintresi', $update_data);
                    $total_updated++;
                }
            }

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $message = "Total Data Terinput: $total_success_insert | Dilewati: $total_skip_insert | Duplikat: $total_duplicate_skip | Diupdate: $total_updated | Data Tidak Berubah: $total_skip_update_same_status";
            log_message('info', $message);

            return $message;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $error_message = "Error inserting receipt data: " . $e->getMessage();
            log_message('error', $error_message);
            return $error_message;
        }
    }

    /**
     * Helper to insert batch safely
     */
    private function _flush_batch(&$batch_data_map, &$batch_detail_map, &$total_success_insert) {
        $this->db->insert_batch('tblprintresi', array_values($batch_data_map));
        $inserted_rows = count($batch_data_map);
        $last_id = $this->db->insert_id();

        $detail_batch = [];
        $i = 0;
        foreach ($batch_data_map as $k => $header) {
            $id_resi = $last_id - $inserted_rows + (++$i);
            $detail = $batch_detail_map[$k];
            $detail_batch[] = [
                'id_resi'   => $id_resi,
                'no_pesanan'=> $detail['no_pesanan'],
                'sku'       => $detail['sku'],
                'no_rak'    => $detail['no_rak'],
                'jumlah'    => $detail['jumlah']
            ];
        }

        if (!empty($detail_batch)) {
            $this->db->insert_batch('tbldetailprintresi', $detail_batch);
        }

        $total_success_insert += $inserted_rows;
        $batch_data_map = [];
        $batch_detail_map = [];
    }

    private function get_existing_receipt_data(array $dataResi) {

        // Step 1: Extract no_pesanan values from dataResi
        $no_pesanan_list = array_unique(array_column($dataResi, 'A'));
        $sku_list = array_unique(array_column($dataResi, 'P'));

        if (empty($no_pesanan_list) || empty($sku_list)) {
            return [];
        }

        // Step 2: Create temp table & insert values
        $this->db->trans_start();

        // Create the temp table (if not exists)
        $this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS temp_pesanan (no_pesanan VARCHAR(100) PRIMARY KEY)");
        $this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS temp_sku (sku VARCHAR(100) PRIMARY KEY)");

        // Empty the temp table (optional safety)
        $this->db->truncate('temp_pesanan');
        $this->db->truncate('temp_sku');

        // Prepare batch insert
        $insert_pesanan = array_map(fn($no) => ['no_pesanan' => $no], $no_pesanan_list);
        $insert_sku = array_map(fn($sku) => ['sku' => $sku], $sku_list);

        // Insert into temp_pesanan
        $this->db->insert_batch('temp_pesanan', $insert_pesanan);
        $this->db->insert_batch('temp_sku', $insert_sku);

        // Step 3: Join with tblprintresi
        $this->db->select('pr.no_pesanan, pr.sku, pr.status_pesanan');
        $this->db->from('tblprintresi pr');
        $this->db->join('temp_pesanan tp', 'tp.no_pesanan = pr.no_pesanan');
        $this->db->join('temp_sku ts', 'ts.sku = pr.sku');

        $query = $this->db->get();

        $existing_data = [];
        foreach ($query->result() as $row) {
            $key = "{$row->no_pesanan}-{$row->sku}";
            $existing_data[$key] = strtolower($row->status_pesanan);
        }

        $this->db->trans_complete();

        return $existing_data;
    }

    private function get_existing_receipt_data_scan(array $receipt) {

        $noresi = $receipt['noresi'] ?? null;

        if (empty($noresi)) {
            return [];
        }

        $this->db->select('noresi, status_pesanan');
        $this->db->from('tblprintresi');
        $this->db->where('noresi', $noresi);

        $query = $this->db->get();

        $existing_data = [];
        foreach ($query->result() as $row) {
            if (strtolower($row->status_pesanan) === 'completed') {
                $existing_data[] = $row->noresi;
            }
        }

        return $existing_data;
    }

    function get_data_retur_receipt_report($data, $start_date, $end_date)
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
            t2.noresi
            , t3.nama_marketplace
            , t4.nama_kurir
            , t2.nomorpicklist
            , t2.tanggal_printresi
            , t.tanggal_resiretur
        ');

        $this->db->distinct();
        $this->db->join('tblprintresi t2' , 't2.id_printresi = t.id_resi', 'left');
        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblkurir t4', 't4.id_kurir = t.id_kurir', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get('tblresiretur t');
    }

    function get_total_data_retur_receipt_report($data, $start_date, $end_date)
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

        $this->db->join('tblprintresi t2' , 't2.id_printresi = t.id_resi', 'left');
        $this->db->join('tblmarketplace t3', 't3.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblkurir t4', 't4.id_kurir = t.id_kurir', 'left');

        $this->db->where('t2.tanggal_printresi >=', $start_date);
        $this->db->where('t2.tanggal_printresi <=', $end_date);

        $query = $this->db->select("COUNT(DISTINCT t.id_resi) as num")->get("tblresiretur t");
        $result = $query->row();

        return isset($result) ? $result->num : 0;
    }

    function get_data_production_team_tab0($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
            foreach ($data['order'] as $order) {
                $this->db->order_by($data['valid_columns'][$order['column']]['col'], $order['dir'], FALSE);
            }
        } else {
            $this->db->order_by('CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)', 'asc', FALSE);
            $this->db->order_by('date(a.tanggal_resiambilbarang)', 'asc', FALSE);
        }

        if (!empty($data['search'])) {
            $x = 0;

            $this->db->group_start();

            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm) || !$sterm['searchable']) continue;

                if ($x == 0) {
                    $this->db->like($sterm['col'], $data['search']);
                } else {
                    $this->db->or_like($sterm['col'], $data['search']);
                }

                $x++;
            }

            $this->db->group_end();
        }

        $this->db->select('
            min(a.tanggal_resiambilbarang) tanggal_resiambilbarang,
            CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai) pegawai,
            count(1) as total
        ');

        $this->db->join('tblpegawai b', 'b.kode_pegawai = a.yangambil_pegawai');

        $this->db->where('a.tanggal_resiambilbarang >=', $start_date);
        $this->db->where('a.tanggal_resiambilbarang <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        $this->db->group_by('date(a.tanggal_resiambilbarang), CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)');

        return $this->db->get('tblresiambilbarang a');
    }

    function get_total_data_production_team_tab0($data, $start_date, $end_date)
    {
        if (!empty($data['search'])) {
            $x = 0;

            $this->db->group_start();

            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm) || !$sterm['searchable']) continue;

                if ($x == 0) {
                    $this->db->like($sterm['col'], $data['search']);
                } else {
                    $this->db->or_like($sterm['col'], $data['search']);
                }

                $x++;
            }

            $this->db->group_end();
        }

        $this->db->join('tblpegawai b', 'b.kode_pegawai = a.yangambil_pegawai');

        $this->db->where('a.tanggal_resiambilbarang >=', $start_date);
        $this->db->where('a.tanggal_resiambilbarang <=', $end_date);

        $this->db->group_by('date(a.tanggal_resiambilbarang), CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)');

        $query = $this->db->select("count(1) as num")->get("tblresiambilbarang a");
        $result = $query->num_rows();

        return isset($result) ? $result : 0;
    }

    function get_data_production_team_tab1($data, $start_date, $end_date)
    {
        if (!empty($data) && $data['order'] != null) {
            foreach ($data['order'] as $order) {
                $this->db->order_by($data['valid_columns'][$order['column']]['col'], $order['dir'], FALSE);
            }
        } else {
            $this->db->order_by('CONCAT(b.name, \' - \', b.id_user)', 'asc', FALSE);
            $this->db->order_by('date(a.tanggal_packing)', 'asc', FALSE);
        }

        if (!empty($data['search'])) {
            $x = 0;

            $this->db->group_start();

            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm) || !$sterm['searchable']) continue;

                if ($x == 0) {
                    $this->db->like($sterm['col'], $data['search']);
                } else {
                    $this->db->or_like($sterm['col'], $data['search']);
                }

                $x++;
            }

            $this->db->group_end();
        }

        $this->db->select('
            min(a.tanggal_packing) tanggal_packing,
            CONCAT(b.name, \' - \', b.id_user) pegawai,
            count(1) as total
        ');

        $this->db->join('tbluser b', 'b.id_user = a.packer_pegawai');

        $this->db->where('a.tanggal_packing >=', $start_date);
        $this->db->where('a.tanggal_packing <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        $this->db->group_by('date(a.tanggal_packing), CONCAT(b.name, \' - \', b.id_user)');

        return $this->db->get('tblpacking a');
    }

    function get_total_data_production_team_tab1($data, $start_date, $end_date)
    {
        if (!empty($data['search'])) {
            $x = 0;

            $this->db->group_start();

            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm) || !$sterm['searchable']) continue;

                if ($x == 0) {
                    $this->db->like($sterm['col'], $data['search']);
                } else {
                    $this->db->or_like($sterm['col'], $data['search']);
                }

                $x++;
            }

            $this->db->group_end();
        }

        $this->db->join('tbluser b', 'b.id_user = a.packer_pegawai');

        $this->db->where('a.tanggal_packing >=', $start_date);
        $this->db->where('a.tanggal_packing <=', $end_date);

        $this->db->group_by('date(a.tanggal_packing), CONCAT(b.name, \' - \', b.id_user)');

        $query = $this->db->select("count(1) as num")->get("tblpacking a");
        $result = $query->num_rows();

        return isset($result) ? $result : 0;
    }
}


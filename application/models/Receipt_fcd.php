<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_fcd extends CI_Model
{
    function get_data($data = null) {

        // Step 1: Build subquery for latest created_at per no_pesanan + sku
        $this->db->select('no_pesanan, sku, MAX(created_at) AS latest_created_at');
        $this->db->from('tblprintresi');
        $this->db->group_by(['no_pesanan', 'sku']);
        $subquery = $this->db->get_compiled_select();

        // Step 2: Main query using subquery join
        $this->db->select('t.noresi, t.tanggal_printresi, t.no_pesanan, t.sku, t2.nama_marketplace, t.nomorpicklist, t.status_pesanan, t3.nama_kurir, t4.name, t.created_at, t.id_printresi');
        $this->db->from('tblprintresi t');
        $this->db->join('tblmarketplace t2', 't.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t3', 't.id_kurir = t3.id_kurir', 'left');
        $this->db->join('tbluser t4', 't.created_by = t4.id_user', 'left');
        $this->db->join("($subquery) latest",
            't.no_pesanan = latest.no_pesanan AND t.sku = latest.sku AND t.created_at = latest.latest_created_at',
            'inner');

        $this->db->order_by('t.created_at', 'DESC');

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        $query = $this->db->get();
        log_message('error', 'Query yang dijalankan: ' . $this->db->last_query());
        return $query;
    }

    public function get_total_data($data = null)
    {
        // Step 1: Subquery for latest created_at per no_pesanan + sku
        $this->db->select('no_pesanan, sku, MAX(created_at) AS latest_created_at');
        $this->db->from('tblprintresi');
        $this->db->group_by(['no_pesanan', 'sku']);
        $subquery = $this->db->get_compiled_select();

        // Step 2: Main query for counting total filtered rows
        $this->db->from('tblprintresi t');
        $this->db->join('tblmarketplace t2', 't.id_marketplace = t2.id_marketplace', 'left');
        $this->db->join('tblkurir t3', 't.id_kurir = t3.id_kurir', 'left');
        $this->db->join('tbluser t4', 't.created_by = t4.id_user', 'left');
        $this->db->join("($subquery) latest",
            't.no_pesanan = latest.no_pesanan AND t.sku = latest.sku AND t.created_at = latest.latest_created_at',
            'inner');

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
        $this->db->where(['tblprintresi.noresi' => $noresi]);
        return $this->db->get('tblprintresi');
    }

    function get_receipt_for_packer($data, $noresi) {
        // Step 1: Build subquery to get latest created_at per noresi + sku
        $this->db->select('noresi, sku, MAX(created_at) AS latest_created_at');
        $this->db->from('tblprintresi');
        $this->db->group_by(['noresi', 'sku']);
        $subquery = $this->db->get_compiled_select();

        // Step 2: Main query
        $this->db->select('t.*, t3.link_foto, t4.yangambil_pegawai');
        $this->db->from('tblprintresi t');
        $this->db->join('tblpacking t2', 't.id_printresi = t2.id_resi', 'left');
        $this->db->join("($subquery) latest",
            't.noresi = latest.noresi AND t.sku = latest.sku AND t.created_at = latest.latest_created_at',
            'inner');
        $this->db->join('tblsku t3', 't.sku = t3.id_sku', 'left');
        $this->db->join('tblresiambilbarang t4', 't.id_printresi = t4.id_resi', 'left');

        $this->db->where('t.noresi', $noresi);
        $this->db->where('t2.id_resi IS NULL');

        // Optional: pagination
        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        // Order by latest
        $this->db->order_by('t.created_at', 'DESC');

        // Execute
        $query = $this->db->get();

        // Debug log
        log_message('error', 'Query get_receipt_for_packer: ' . $this->db->last_query());

        return $query->result();
    }

    function get_total_receipt_for_packer($noresi) {
        // Step 1: Build query with joins and filters
        $this->db->from('tblprintresi t');
        $this->db->join('tblprintresifinance t1', 't.id_printresi = t1.id_printresi', 'inner');
        $this->db->join('tblpacking t2', 't.id_printresi = t2.id_resi', 'left'); // Only include if not packed
        $this->db->where('t.noresi', $noresi);
        $this->db->where('t2.id_resi IS NULL'); // Only those not yet packed

        // Optional: log query before counting
        log_message('error', 'Query get_total_resi_for_packer: ' . $this->db->get_compiled_select());

        // Step 2: Return count of matched rows
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
            , t4.nama_pegawai picker, t5.tanggal_packing, t6.nama_pegawai packer
            , t5.keterangan komputer_packer_no, t7.nama_kurir, t8.tanggal_cetak
            , t8.tanggal_resikeluar, t.status_pesanan, t.sku, t.tanggal_retur, t.tanggal_bataskirim
            , t.jumlah, t.no_pesanan'
        );

        $this->db->join('tblmarketplace t2', 't2.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblresiambilbarang t3', 't3.id_resi = t.id_printresi', 'left');
        $this->db->join('tblpegawai t4', 't4.kode_pegawai = t3.yangambil_pegawai', 'left');
        $this->db->join('tblpacking t5', 't5.id_resi = t.id_printresi', 'left');
        $this->db->join('tblpegawai t6', 't6.kode_pegawai = t5.packer_pegawai', 'left');
        $this->db->join('tblkurir t7', 't7.id_kurir = t.id_kurir', 'left');
        $this->db->join('tblresikeluar t8', 't8.id_resi = t.id_printresi', 'left');

        $this->db->where(['t.noresi' => $noresi]);
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit(1);

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
         * 3. delete resi from tblprintresi
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

        $batch_data_map = [];

        foreach ($receiptData as $row) {
            $no_pesanan = $row['A'] ?? null;
            $sku = $row['P'] ?? null;
            $status_pesanan = strtolower($row['T']); // Optional usage

            $key = "$no_pesanan-$sku";

            if ($no_pesanan === 'NO_PESANAN' || !$no_pesanan || !$sku) continue;

            if (isset($existing_data[$key])) {
                $db_status = strtolower($existing_data[$key]);

                if ($db_status === 'completed' || $db_status === 'canceled') {
                    $total_skip_insert++;
                    continue; // skip if DB already has completed/canceled
                }
            }

            $marketplace = '';
            $marketplaceRaw = strtolower($row['N'] ?? '');
            if (stripos($marketplaceRaw, 'tokopedia') !== false) {
                $marketplace = 'tokopedia';
            }
            elseif (stripos($marketplaceRaw, 'internal') !== false) {
                $marketplace = 'reseller';
            }
            else $marketplace = $marketplaceRaw;
            $id_marketplace = $marketplace_map[$marketplace] ?? 99;

            $kurir = '';
            $kurirRaw = $row['S'] ?? '';
            $kurirDiantarOleh = '';
            // Try to extract courier from "Diantar oleh:" first
            if (stripos($kurirRaw, 'Diantar oleh:') !== false) {
                $parts = explode('Diantar oleh:', $kurirRaw);
                $kurirDiantarOleh = strtolower(trim($parts[1]));
            }
            // If not found, try "Delivery:"
            elseif (stripos($kurirRaw, 'Delivery:') !== false) {
                $parts = explode('Delivery:', $kurirRaw);
                $kurirDiantarOleh = strtolower(trim($parts[1]));
            }
            // Check courier from extracted part first
            if ($kurirDiantarOleh !== '') {
                if (stripos($kurirDiantarOleh, 'jne') !== false) {
                    $kurir = 'jne';
                } elseif (stripos($kurirDiantarOleh, 'j&t') !== false || stripos($kurirDiantarOleh, 'jnt') !== false) {
                    $kurir = 'jnt';
                } elseif (stripos($kurirDiantarOleh, 'ninja') !== false) {
                    $kurir = 'ninja';
                } elseif (stripos($kurirDiantarOleh, 'sicepat') !== false) {
                    $kurir = 'sicepat';
                } elseif (stripos($kurirDiantarOleh, 'spx') !== false) {
                    $kurir = 'shopee';
                } elseif (stripos($kurirDiantarOleh, 'lex id') !== false) {
                    $kurir = 'lazada';
                }
            }
            // If not found in extracted part, search the whole raw string
            if ($kurir === '') {
                if (stripos($kurirRaw, 'anteraja') !== false) {
                    $kurir = 'anteraja';
                } elseif (stripos($kurirRaw, 'baraka') !== false) {
                    $kurir = 'baraka';
                } elseif (stripos($kurirRaw, 'goto') !== false) {
                    $kurir = 'goto';
                } elseif (stripos($kurirRaw, 'id express') !== false) {
                    $kurir = 'id express';
                } elseif (stripos($kurirRaw, 'instant') !== false || stripos($kurirRaw, 'gosend') !== false || stripos($kurirRaw, 'grab') !== false) {
                    $kurir = 'instant/sameday';
                } elseif (stripos($kurirRaw, 'jemput sendiri') !== false) {
                    $kurir = 'jemput sendiri';
                } elseif (stripos($kurirRaw, 'jne') !== false) {
                    $kurir = 'jne';
                } elseif (stripos($kurirRaw, 'j&t') !== false || stripos($kurirRaw, 'jnt') !== false) {
                    $kurir = 'jnt';
                } elseif (stripos($kurirRaw, 'kargo') !== false) {
                    $kurir = 'central cargo';
                } elseif (stripos($kurirRaw, 'lazada') !== false || stripos($kurirRaw, 'lex id') !== false) {
                    $kurir = 'lazada';
                } elseif (stripos($kurirRaw, 'ninja') !== false) {
                    $kurir = 'ninja';
                } elseif (stripos($kurirRaw, 'rex') !== false) {
                    $kurir = 'rex';
                } elseif (stripos($kurirRaw, 'sicepat') !== false) {
                    $kurir = 'sicepat';
                } elseif (stripos($kurirRaw, 'sicepat rekom') !== false || stripos($kurirRaw, 'rekomendasi') !== false) {
                    $kurir = 'sicepat - rekom';
                } elseif (stripos($kurirRaw, 'spx') !== false || stripos($kurirRaw, 'shopee') !== false) {
                    $kurir = 'shopee';
                } elseif (stripos($kurirRaw, 'spax') !== false) {
                    $kurir = 'spax';
                } elseif (stripos($kurirRaw, 'wahana') !== false) {
                    $kurir = 'wahana';
                }
            }
            // Default fallback
            $id_kurir = $kurir_map[$kurir] ?? 99;

            if (!isset($batch_data_map[$key])) {
                $batch_data_map[$key] = [
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
                    'nomorpicklist' => $row['C'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $user_id
                ];
            } else {
                // Sum the quantity
                $batch_data_map[$key]['jumlah'] += (int) ($row['Q'] ?? 0);
            }

            if (count($batch_data_map) >= $batch_size) {
                $this->db->insert_batch('tblprintresi', array_values($batch_data_map));
                $total_success_insert += count($batch_data_map);
                $batch_data_map = [];
            }
        }

        // Insert remaining batch
        if (!empty($batch_data_map)) {
            $this->db->insert_batch('tblprintresi', array_values($batch_data_map));
            $total_success_insert += count($batch_data_map);
        }

        $message = "Total Data Terinput: $total_success_insert | Dilewati: $total_skip_insert";
        log_message('error', $message);

        return $message;
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
            $this->db->order_by('CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)', 'asc', FALSE);
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
            CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai) pegawai,
            count(1) as total
        ');

        $this->db->join('tblpegawai b', 'b.kode_pegawai = a.packer_pegawai');

        $this->db->where('a.tanggal_packing >=', $start_date);
        $this->db->where('a.tanggal_packing <=', $end_date);

        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        $this->db->group_by('date(a.tanggal_packing), CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)');

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

        $this->db->join('tblpegawai b', 'b.kode_pegawai = a.packer_pegawai');

        $this->db->where('a.tanggal_packing >=', $start_date);
        $this->db->where('a.tanggal_packing <=', $end_date);

        $this->db->group_by('date(a.tanggal_packing), CONCAT(b.nama_pegawai, \' - \', b.kode_pegawai)');

        $query = $this->db->select("count(1) as num")->get("tblpacking a");
        $result = $query->num_rows();

        return isset($result) ? $result : 0;
    }
}
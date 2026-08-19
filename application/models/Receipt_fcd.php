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
            , COALESCE(t5.keterangan, t6.nama_komputer) komputer_packer_no, t7.nama_kurir, t8.tanggal_cetak
            , t8.tanggal_resikeluar, t.status_pesanan, t.tanggal_retur, t.tanggal_bataskirim
            , (SELECT sp.status_name FROM tblkpi k LEFT JOIN tblmasterstatusperforma sp ON sp.id_statusperforma = k.id_statusperforma WHERE k.id_user = t3.admin_pegawai AND DATE(k.tanggal) = DATE(t3.tanggal_resiambilbarang) AND k.tipe_transaksi = "PICKER" AND k.created <= t3.tanggal_resiambilbarang ORDER BY ABS(TIMESTAMPDIFF(SECOND, k.created, t3.tanggal_resiambilbarang)) ASC LIMIT 1) as picker_status
            , (SELECT sp2.status_name FROM tblkpi k2 LEFT JOIN tblmasterstatusperforma sp2 ON sp2.id_statusperforma = k2.id_statusperforma WHERE k2.id_user = t5.packer_pegawai AND DATE(k2.tanggal) = DATE(t5.tanggal_packing) AND k2.tipe_transaksi = "PACKER" AND k2.created <= t5.tanggal_packing ORDER BY ABS(TIMESTAMPDIFF(SECOND, k2.created, t5.tanggal_packing)) ASC LIMIT 1) as packer_status'
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

        // Select tanggal_printresi dari tblprintresi untuk tanggal dan jam scan resi
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
        // Build search WHERE clause
        $search_where = '';
        if (!empty($data['search'])) {
            $search = $this->db->escape_like_str($data['search']);
            $search_conditions = [
                "f.nama_marketplace LIKE '%{$search}%'",
                "e.nama_kurir LIKE '%{$search}%'",
                "a.noresi LIKE '%{$search}%'",
                "a.nomorpicklist LIKE '%{$search}%'",
                "t1.nama_pegawai LIKE '%{$search}%'",
                "t2.nama_pegawai LIKE '%{$search}%'",
                "t3.name LIKE '%{$search}%'",
                "t4.nama_pegawai LIKE '%{$search}%'"
            ];
            $search_where = " AND (" . implode(" OR ", $search_conditions) . ")";
        }

        // Build ORDER BY clause
        $order_by = '';
        if (!empty($data) && !empty($data['order'])) {
            // Map column names to SELECT aliases for ORDER BY compatibility
            $column_mapping = [
                'f.nama_marketplace' => 'nama_marketplace',
                'e.nama_kurir' => 'nama_kurir',
                'a.noresi' => 'a.noresi',
                'a.nomorpicklist' => 'nomorpicklist',
                'a.tanggal_printresi' => 'tanggal_printresi',
                't1.nama_pegawai' => 'admin_scan',
                'b.tanggal_resiambilbarang' => 'tanggal_resiambilbarang',
                't2.nama_pegawai' => 'admin_picker',
                'c.tanggal_packing' => 'tanggal_packing',
                't3.name' => 'admin_packer',
                'd.tanggal_resikeluar' => 'tanggal_resikeluar',
                't4.nama_pegawai' => 'admin_ho',
            ];
            
            $order_column = isset($column_mapping[$data['order']]) ? $column_mapping[$data['order']] : 'tanggal_printresi';
            $order_by = " ORDER BY " . $order_column . " " . strtoupper($data['dir']);
        } else {
            // Default: Urutkan berdasarkan tanggal print resi (lebih cepat dari GREATEST)
            $order_by = " ORDER BY a.tanggal_printresi DESC";
        }

        // Build LIMIT clause
        $limit_clause = '';
        if (!empty($data['length'])) {
            $limit_clause = " LIMIT " . intval($data['length']) . " OFFSET " . intval($data['start']);
        }

        // OPTIMIZED V4: Simplified query - status diambil dari kolom status_performa_id yang sudah ada
        // Gunakan STRAIGHT_JOIN untuk force MySQL mengikuti urutan JOIN yang optimal
        $sql = "
        SELECT STRAIGHT_JOIN
            f.nama_marketplace,
            e.nama_kurir,
            a.noresi,
            a.nomorpicklist,
            a.tanggal_printresi,
            t1.nama_pegawai as admin_scan,
            b.tanggal_resiambilbarang,
            b.admin_pegawai as picker_user_id,
            t2.nama_pegawai as admin_picker,
            COALESCE(sp_picker.status_name, '') as picker_status,
            c.tanggal_packing,
            c.packer_pegawai,
            t3.name as admin_packer,
            COALESCE(sp_packer.status_name, '') as packer_status,
            d.tanggal_resikeluar,
            t4.nama_pegawai as admin_ho
        FROM tblprintresi a USE INDEX (idx_printresi_tanggal)
        LEFT JOIN tblresiambilbarang b ON a.id_printresi = b.id_resi
        LEFT JOIN tblpacking c ON a.id_printresi = c.id_resi
        LEFT JOIN tblresikeluar d ON a.id_printresi = d.id_resi
        LEFT JOIN tblkurir e ON e.id_kurir = a.id_kurir
        LEFT JOIN tblmarketplace f ON f.id_marketplace = a.id_marketplace
        LEFT JOIN tblpegawai t1 ON t1.kode_pegawai = a.admin_pegawai
        LEFT JOIN tblpegawai t2 ON t2.kode_pegawai = b.yangambil_pegawai
        LEFT JOIN tbluser t3 ON t3.id_user = c.packer_pegawai
        LEFT JOIN tblpegawai t4 ON t4.kode_pegawai = d.id_pegawai
        LEFT JOIN tblmasterstatusperforma sp_picker ON sp_picker.id_statusperforma = b.status_performa_id
        LEFT JOIN tblmasterstatusperforma sp_packer ON sp_packer.id_statusperforma = c.status_performa_id
        WHERE a.tanggal_printresi >= " . $this->db->escape($start_date) . "
        AND a.tanggal_printresi <= " . $this->db->escape($end_date) . "
        {$search_where}
        {$order_by}
        {$limit_clause}
        ";

        return $this->db->query($sql);
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
        $this->db->join('tbluser t3', 't3.id_user = c.packer_pegawai ', 'left');
        $this->db->join('tblpegawai t4', 't4.kode_pegawai = d.id_pegawai', 'left');

        // Filter berdasarkan tanggal print resi
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

        $this->db->where('t2.tanggal_resikeluar >=', $start_date);
        $this->db->where('t2.tanggal_resikeluar <=', $end_date);

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

        $this->db->where('t2.tanggal_resikeluar >=', $start_date);
        $this->db->where('t2.tanggal_resikeluar <=', $end_date);

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

        // Rincian baris yang dilewati, supaya kehilangan data tidak lagi senyap
        $total_skip_no_resi  = 0; // pesanan belum punya nomor resi (wajar, akan masuk di upload berikutnya)
        $total_skip_invalid  = 0; // sudah punya resi tapi no_pesanan/sku kosong (anomali, perlu dicek)
        $total_detail_resync = 0; // resi lama yang detailnya disegarkan ulang

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
                $nama_kurir_lower = strtolower($kr->nama_kurir);
                $kurir_map[$nama_kurir_lower] = $kr->id_kurir;

                // Pisahkan mapping untuk JNT biasa dan JNT Cargo (ini berbeda kurir)
                $is_jnt = stripos($nama_kurir_lower, 'jnt') !== false
                       || stripos($nama_kurir_lower, 'j&t') !== false;

                if (!$is_jnt) {
                    continue;
                }

                // Master kurir memakai ejaan Indonesia ("JNT KARGO") sementara file export
                // Jubelio memakai ejaan Inggris ("J&T Cargo"). Kalau hanya 'cargo' yang dicek,
                // baris "JNT KARGO" lolos ke cabang JNT biasa lalu menimpa mapping kurir JNT
                // yang sebenarnya, sehingga J&T Express ikut tercatat sebagai kargo.
                $is_cargo = stripos($nama_kurir_lower, 'cargo') !== false
                         || stripos($nama_kurir_lower, 'kargo') !== false;

                if ($is_cargo) {
                    $kurir_map['jnt cargo'] = $kr->id_kurir;
                    $kurir_map['j&t cargo'] = $kr->id_kurir;
                } else {
                    $kurir_map['jnt'] = $kr->id_kurir;
                    $kurir_map['j&t'] = $kr->id_kurir;
                }
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
                // Ejaan Inggris maupun Indonesia sama-sama muncul di file export
                'jnt cargo'         => ['j&t cargo', 'jnt cargo', 'j&t kargo', 'jnt kargo'],
                'jnt'               => ['j&t', 'jnt'], // JNT biasa / J&T biasa (bukan cargo)
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
                
                // Urutan pemeriksaan menentukan hasil, karena alias saling bertumpang tindih.
                //
                // 'jnt cargo' harus lebih dulu dari 'central cargo': Central Cargo memakai alias
                // telanjang 'kargo', sehingga "J&T KARGO" akan tertangkap sebagai Central Cargo
                // kalau urutannya terbalik.
                //
                // Semua varian cargo lalu diperiksa sebelum kurir biasa, supaya "J&T Cargo"
                // tidak keburu cocok dengan alias 'j&t' milik JNT biasa.
                $ordered_aliases = [];
                if (isset($courier_aliases['jnt cargo'])) {
                    $ordered_aliases['jnt cargo'] = $courier_aliases['jnt cargo'];
                }
                foreach ($courier_aliases as $norm => $alts) {
                    if (!isset($ordered_aliases[$norm]) && stripos($norm, 'cargo') !== false) {
                        $ordered_aliases[$norm] = $alts;
                    }
                }
                foreach ($courier_aliases as $norm => $alts) {
                    if (!isset($ordered_aliases[$norm])) {
                        $ordered_aliases[$norm] = $alts;
                    }
                }
                
                foreach ([$section, $rawLower] as $text) {
                    foreach ($ordered_aliases as $norm => $alts) {
                        foreach ($alts as $needle) {
                            if (stripos($text, $needle) !== false) return $norm;
                        }
                    }
                }
                return '';
            };

            // Penentuan marketplace & kurir dipakai di dua tempat (insert dan update), dan
            // hasilnya juga perlu dibandingkan dengan data lama untuk memutuskan apakah header
            // perlu diperbarui. Dijadikan closure supaya logikanya tidak bercabang dua.
            $resolveMarketplace = function ($row) use ($marketplace_map) {
                $marketplaceRaw = strtolower($row['N'] ?? '');
                if (stripos($marketplaceRaw, 'tokopedia') !== false) {
                    $marketplace = 'tokopedia';
                } elseif (stripos($marketplaceRaw, 'internal') !== false) {
                    $marketplace = 'reseller';
                } else {
                    $marketplace = $marketplaceRaw;
                }

                return [$marketplace, $marketplace_map[$marketplace] ?? 99];
            };

            $resolveKurir = function ($row, $marketplace) use ($kurir_map, $detectCourier) {
                $kurir = $detectCourier($row['S'] ?? '');

                // Lazada memakai kurir sendiri walau namanya tertulis JNE/Ninja
                if ($marketplace === 'lazada' && in_array($kurir, ['jne', 'ninja'], true)) {
                    $kurir = 'lazada';
                }

                $id_kurir = $kurir_map[$kurir] ?? null;
                if (!$id_kurir) {
                    if ($kurir === 'jnt cargo') {
                        $id_kurir = $kurir_map['jnt cargo'] ?? $kurir_map['j&t cargo'] ?? null;
                    } elseif ($kurir === 'jnt') {
                        $id_kurir = $kurir_map['jnt'] ?? $kurir_map['j&t'] ?? null;
                    }
                }

                return $id_kurir ?? 99;
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
                    $this->db->select('noresi, status_pesanan, id_printresi, id_kurir, id_marketplace');
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
                                'current_status' => $row->status_pesanan,
                                'id_kurir' => $row->id_kurir,
                                'id_marketplace' => $row->id_marketplace
                            ];
                        }
                    }
                }
            }

            $batch_header_map = [];
            $batch_detail_map = [];
            $batch_update_map = [];
            $update_detail_map = []; // detail milik resi yang sudah ada, untuk disinkronkan ulang
            $seen_same_status = [];  // supaya "data tidak berubah" dihitung per resi, bukan per baris
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

                    if ($no_pesanan !== 'NO_PESANAN') {
                        if (!$noresi) {
                            // Pesanan belum dapat nomor resi dari marketplace. Wajar, tidak perlu alarm.
                            $total_skip_no_resi++;
                        } else {
                            // Sudah ada resi tapi SKU/no_pesanan kosong: ini item yang benar-benar hilang.
                            $total_skip_invalid++;
                            log_message('warning', "insert_receipt: baris dilewati, resi $noresi punya no_pesanan/sku kosong");
                        }
                    }
                    continue;
                }

                // Skip if already exists and is COMPLETED
                if (isset($existing_noresi[$noresi])) {
                    continue; // This will be counted in total_duplicate_skip already
                }

                // Check if this noresi needs to be updated instead of inserted
                if (isset($update_candidates[$noresi])) {
                    // Detail tetap dikumpulkan walaupun status pesanan tidak berubah, karena qty
                    // di Jubelio bisa berubah tanpa status ikut berubah. Perbandingan dengan data
                    // lama dilakukan belakangan supaya resi yang isinya sama tidak ditulis ulang.
                    $id_resi_lama = $update_candidates[$noresi]['id_printresi'];
                    $update_key   = $id_resi_lama . '|' . $no_pesanan . '|' . $sku;

                    if (!isset($update_detail_map[$update_key])) {
                        $update_detail_map[$update_key] = [
                            'id_resi'    => $id_resi_lama,
                            'no_pesanan' => $no_pesanan,
                            'sku'        => $sku,
                            'no_rak'     => $row['R'] ?? '',
                            'jumlah'     => (int)($row['Q'] ?? 0)
                        ];
                    } else {
                        $update_detail_map[$update_key]['jumlah'] += (int)($row['Q'] ?? 0);
                    }

                    $current_status = strtoupper(trim($existing_status_map[$noresi] ?? ''));

                    list($marketplace, $id_marketplace) = $resolveMarketplace($row);
                    $id_kurir = $resolveKurir($row, $marketplace);

                    // Header diperbarui bukan hanya saat status berubah. Kurir dan marketplace
                    // ikut dibandingkan, karena hasil pemetaannya bisa berubah setelah perbaikan
                    // kode - dan resi yang statusnya kebetulan tetap sama tidak boleh terkunci
                    // dengan kurir yang salah selamanya.
                    $perlu_update = $current_status !== $new_status_upper
                        || (int) $update_candidates[$noresi]['id_kurir'] !== (int) $id_kurir
                        || (int) $update_candidates[$noresi]['id_marketplace'] !== (int) $id_marketplace;

                    if ($perlu_update) {
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
                    } elseif (!isset($seen_same_status[$noresi])) {
                        $seen_same_status[$noresi] = true;
                        $total_skip_update_same_status++;
                    }
                    continue;
                }

                list($marketplace, $id_marketplace) = $resolveMarketplace($row);
                $id_kurir = $resolveKurir($row, $marketplace);

                // Always set detail_key
                $detail_key = "$noresi-$no_pesanan-$sku";

                // Header: one per noresi
                if (!isset($batch_header_map[$noresi])) {
                    // Gunakan created_at yang sama untuk tanggal_printresi agar konsisten
                    $created_at = date('Y-m-d H:i:s');
                    $batch_header_map[$noresi] = [
                        'noresi'              => $noresi,
                        'id_marketplace'      => $id_marketplace,
                        'id_kurir'            => $id_kurir,
                        'admin_pegawai'       => $user_id,
                        'tanggal_printresi'   => $created_at, // Sama dengan created_at
                        'tanggal_pesan'       => $combineDateTime($excelDateToPhpDate($row['D'] ?? null), $excelTimeToPhpTime($row['E'] ?? null)),
                        'tanggal_bataskirim'  => $combineDateTime($excelDateToPhpDate($row['H'] ?? null), $excelTimeToPhpTime($row['I'] ?? null)),
                        'tanggal_pengiriman'  => $combineDateTime($excelDateToPhpDate($row['J'] ?? null), $excelTimeToPhpTime($row['K'] ?? null)),
                        'tanggal_selesai'     => $combineDateTime($excelDateToPhpDate($row['L'] ?? null), $excelTimeToPhpTime($row['M'] ?? null)),
                        'tanggal_retur'       => $combineDateTime($excelDateToPhpDate($row['U'] ?? null), $excelTimeToPhpTime($row['V'] ?? null)),
                        'status_pesanan'      => $row['T'] ?? null,
                        'batal'               => '',
                        'keterangan'          => '',
                        'nomorpicklist'       => $row['C'] ?? '',
                        'created_at'          => $created_at,
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

            // Sinkronkan detail milik resi yang sudah ada di database.
            //
            // Sebelumnya blok update hanya menyentuh tblprintresi, sehingga tbldetailprintresi
            // tidak pernah ikut diperbaiki saat file diupload ulang. Akibatnya qty yang salah
            // menetap selamanya dan berat standar di modul timbangan ikut salah.
            //
            // Yang dijaga di sini: progres picker/CS (status_kurangan, qty_kurang,
            // tanggal_scan_kurangan) tidak boleh hilang, dan id_detail_resi dipertahankan
            // untuk baris yang masih ada supaya halaman yang sedang terbuka tidak kehilangan acuan.
            if (!empty($update_detail_map)) {
                $total_detail_resync += $this->_sync_detail_receipt($update_detail_map, $batch_size);
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

            $message = "Total Data Terinput: $total_success_insert | Dilewati: $total_skip_insert | Duplikat: $total_duplicate_skip | Diupdate: $total_updated | Data Tidak Berubah: $total_skip_update_same_status | Detail Disinkronkan: $total_detail_resync";

            if ($total_skip_invalid > 0) {
                $message .= " | PERHATIAN: $total_skip_invalid baris punya nomor resi tapi SKU/no_pesanan kosong, cek log";
            }

            log_message('info', $message . " | Dilewati krn belum ada resi: $total_skip_no_resi");

            return $message;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $error_message = "Error inserting receipt data: " . $e->getMessage();
            log_message('error', $error_message);
            return $error_message;
        }
    }

    /**
     * Menyegarkan tbldetailprintresi untuk resi yang sudah ada di database.
     *
     * Hanya resi yang isinya benar-benar berubah yang ditulis, supaya upload ulang
     * file besar tidak menyentuh ratusan ribu baris tanpa perlu.
     *
     * Aturan yang dipakai per baris detail:
     *   - masih ada di file & nilainya berubah  -> UPDATE (id_detail_resi dipertahankan)
     *   - baru muncul di file                   -> INSERT
     *   - tidak ada lagi di file                -> DELETE
     *
     * Progres picker/CS ikut dipertahankan. Kalau qty pesanan turun di bawah qty_kurang
     * yang pernah dicatat picker, qty_kurang dipotong mengikuti qty baru supaya tidak
     * ada kurangan yang melebihi jumlah pesanan.
     *
     * @param array $update_detail_map hasil agregasi file, key = "id_resi|no_pesanan|sku"
     * @param int   $batch_size
     * @return int jumlah resi yang detailnya berubah
     */
    private function _sync_detail_receipt(array $update_detail_map, $batch_size = 500)
    {
        // Kelompokkan per id_resi
        $per_resi = [];
        foreach ($update_detail_map as $detail) {
            $per_resi[$detail['id_resi']][$detail['no_pesanan'] . '|' . $detail['sku']] = $detail;
        }

        $resi_berubah = 0;

        foreach (array_chunk(array_keys($per_resi), 500) as $chunk_id_resi) {
            $this->db->select('id_detail_resi, id_resi, no_pesanan, sku, no_rak, jumlah, qty_manual, status_kurangan, qty_kurang, tanggal_scan_kurangan');
            $this->db->from('tbldetailprintresi');
            $this->db->where_in('id_resi', $chunk_id_resi);
            $existing_rows = $this->db->get()->result_array();

            $existing = [];
            foreach ($existing_rows as $row) {
                $existing[$row['id_resi']][$row['no_pesanan'] . '|' . $row['sku']] = $row;
            }

            $rows_to_insert = [];
            $ids_to_delete  = [];

            foreach ($chunk_id_resi as $id_resi) {
                $baru = $per_resi[$id_resi];
                $lama = $existing[$id_resi] ?? [];

                $ada_perubahan = false;

                // Baris yang hilang dari file
                foreach ($lama as $key => $row_lama) {
                    if (!isset($baru[$key])) {
                        $ids_to_delete[] = $row_lama['id_detail_resi'];
                        $ada_perubahan = true;
                    }
                }

                foreach ($baru as $key => $row_baru) {
                    if (!isset($lama[$key])) {
                        // Item baru yang sebelumnya belum tercatat
                        $rows_to_insert[] = [
                            'id_resi'    => $id_resi,
                            'no_pesanan' => $row_baru['no_pesanan'],
                            'sku'        => $row_baru['sku'],
                            'no_rak'     => $row_baru['no_rak'],
                            'jumlah'     => $row_baru['jumlah']
                        ];
                        $ada_perubahan = true;
                        continue;
                    }

                    $row_lama = $lama[$key];

                    // Qty yang sudah dikoreksi manual tidak boleh ditimpa oleh file. Selama
                    // export Jubelio masih melebur qty Tokopedia, isi file justru lebih buruk
                    // daripada angka hasil koreksi yang sudah dicocokkan ke layar Jubelio.
                    $qty_dikunci = (int) $row_lama['qty_manual'] === 1;

                    $jumlah_berubah = !$qty_dikunci
                        && (int) $row_lama['jumlah'] !== (int) $row_baru['jumlah'];
                    $rak_berubah = (string) $row_lama['no_rak'] !== (string) $row_baru['no_rak'];

                    if (!$jumlah_berubah && !$rak_berubah) {
                        continue;
                    }

                    $update_data = ['no_rak' => $row_baru['no_rak']];

                    if ($jumlah_berubah) {
                        $update_data['jumlah'] = $row_baru['jumlah'];

                        // Kurangan tidak boleh melebihi jumlah pesanan yang baru
                        if ((int) $row_lama['qty_kurang'] > (int) $row_baru['jumlah']) {
                            $update_data['qty_kurang'] = $row_baru['jumlah'];
                        }
                    }

                    $this->db->where('id_detail_resi', $row_lama['id_detail_resi']);
                    $this->db->update('tbldetailprintresi', $update_data);
                    $ada_perubahan = true;
                }

                if ($ada_perubahan) {
                    $resi_berubah++;
                }
            }

            if (!empty($ids_to_delete)) {
                foreach (array_chunk($ids_to_delete, $batch_size) as $chunk_delete) {
                    $this->db->where_in('id_detail_resi', $chunk_delete);
                    $this->db->delete('tbldetailprintresi');
                }
            }

            if (!empty($rows_to_insert)) {
                foreach (array_chunk($rows_to_insert, $batch_size) as $chunk_insert) {
                    $this->db->insert_batch('tbldetailprintresi', $chunk_insert);
                }
            }
        }

        return $resi_berubah;
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
        // Build search conditions for the subquery
        $search_where = '';
        if (!empty($data['search'])) {
            $search = $this->db->escape_like_str($data['search']);
            $search_where = " AND (t2.nama_pegawai LIKE '%{$search}%' OR DATE(b.tanggal_resiambilbarang) LIKE '%{$search}%')";
        }
        
        // OPTIMIZED V9: Group hanya per tanggal & picker, ambil status dari scan PERTAMA
        $sql = "
        SELECT 
            DATE(b.tanggal_resiambilbarang) as tanggal_resiambilbarang,
            MIN(b.admin_pegawai) as admin_pegawai,
            MAX(t2.nama_pegawai) as pegawai,
            MAX(COALESCE(first_status.status_name, 'Tanpa Status')) as status_performa,
            COUNT(DISTINCT a.id_printresi) as total,
            MIN(b.tanggal_resiambilbarang) as waktu_scan_picker
        FROM tblprintresi a
        INNER JOIN tblresiambilbarang b ON a.id_printresi = b.id_resi
        LEFT JOIN tblpegawai t2 ON t2.kode_pegawai = b.yangambil_pegawai
        -- JOIN ke subquery untuk ambil status PERTAMA per picker per hari
        LEFT JOIN (
            SELECT 
                b3.yangambil_pegawai,
                DATE(b3.tanggal_resiambilbarang) as tanggal_date,
                sp3.status_name
            FROM tblresiambilbarang b3
            LEFT JOIN tblmasterstatusperforma sp3 ON sp3.id_statusperforma = b3.status_performa_id
            INNER JOIN (
                SELECT 
                    yangambil_pegawai,
                    DATE(tanggal_resiambilbarang) as tanggal_date,
                    MIN(tanggal_resiambilbarang) as first_scan_time
                FROM tblresiambilbarang
                WHERE tanggal_resiambilbarang >= " . $this->db->escape($start_date) . "
                AND tanggal_resiambilbarang <= " . $this->db->escape($end_date) . "
                GROUP BY yangambil_pegawai, DATE(tanggal_resiambilbarang)
            ) first_scan ON b3.yangambil_pegawai = first_scan.yangambil_pegawai
                AND DATE(b3.tanggal_resiambilbarang) = first_scan.tanggal_date
                AND b3.tanggal_resiambilbarang = first_scan.first_scan_time
        ) first_status ON first_status.yangambil_pegawai = b.yangambil_pegawai
            AND first_status.tanggal_date = DATE(b.tanggal_resiambilbarang)
        WHERE b.tanggal_resiambilbarang >= " . $this->db->escape($start_date) . "
        AND b.tanggal_resiambilbarang <= " . $this->db->escape($end_date) . "
        {$search_where}
        GROUP BY DATE(b.tanggal_resiambilbarang), b.yangambil_pegawai
        ";
        
        // Add ORDER BY
        if (!empty($data) && !empty($data['order'])) {
            $order_col = ['pegawai', 'tanggal_resiambilbarang', 'total'];
            $order_clauses = [];
            foreach ($data['order'] as $order) {
                if (isset($order_col[$order['column']])) {
                    $order_clauses[] = $order_col[$order['column']] . ' ' . strtoupper($order['dir']);
                }
            }
            if (!empty($order_clauses)) {
                $sql .= " ORDER BY " . implode(', ', $order_clauses);
            }
        } else {
            $sql .= " ORDER BY pegawai ASC, tanggal_resiambilbarang ASC, status_performa ASC";
        }
        
        // Add LIMIT/OFFSET
        if (!empty($data['length'])) {
            $sql .= " LIMIT " . intval($data['length']) . " OFFSET " . intval($data['start']);
        }

        return $this->db->query($sql);
    }

    function get_total_data_production_team_tab0($data, $start_date, $end_date)
    {
        // Build search conditions
        $search_where = '';
        if (!empty($data['search'])) {
            $search = $this->db->escape_like_str($data['search']);
            $search_where = " AND (t2.nama_pegawai LIKE '%{$search}%' OR DATE(b.tanggal_resiambilbarang) LIKE '%{$search}%')";
        }
        
        // OPTIMIZED V9: Count rows dengan grouping baru (hanya per tanggal & picker)
        $sql = "
        SELECT COUNT(*) as total
        FROM (
            SELECT 
                DATE(b.tanggal_resiambilbarang) as tanggal_resiambilbarang,
                b.yangambil_pegawai
            FROM tblprintresi a
            INNER JOIN tblresiambilbarang b ON a.id_printresi = b.id_resi
            LEFT JOIN tblpegawai t2 ON t2.kode_pegawai = b.yangambil_pegawai
            WHERE b.tanggal_resiambilbarang >= " . $this->db->escape($start_date) . "
            AND b.tanggal_resiambilbarang <= " . $this->db->escape($end_date) . "
            {$search_where}
            GROUP BY DATE(b.tanggal_resiambilbarang), b.yangambil_pegawai
        ) as grouped_data";
        
        $result = $this->db->query($sql)->row();
        return $result ? $result->total : 0;
    }

    function get_data_production_team_tab1($data, $start_date, $end_date)
    {
        // Build search conditions for the subquery
        $search_where = '';
        if (!empty($data['search'])) {
            $search = $this->db->escape_like_str($data['search']);
            $search_where = " AND (t3.name LIKE '%{$search}%' OR DATE(c.tanggal_packing) LIKE '%{$search}%')";
        }
        
        // OPTIMIZED V9: Group hanya per tanggal & packer, ambil status dari scan PERTAMA
        $sql = "
        SELECT 
            DATE(c.tanggal_packing) as tanggal_packing,
            c.packer_pegawai,
            MAX(t3.name) as pegawai,
            MAX(COALESCE(first_status.status_name, 'Tanpa Status')) as status_performa,
            COUNT(DISTINCT a.id_printresi) as total,
            MIN(c.tanggal_packing) as waktu_scan_packer
        FROM tblprintresi a
        INNER JOIN tblpacking c ON a.id_printresi = c.id_resi
        LEFT JOIN tbluser t3 ON t3.id_user = c.packer_pegawai
        -- JOIN ke subquery untuk ambil status PERTAMA per packer per hari
        LEFT JOIN (
            SELECT 
                c3.packer_pegawai,
                DATE(c3.tanggal_packing) as tanggal_date,
                sp3.status_name
            FROM tblpacking c3
            LEFT JOIN tblmasterstatusperforma sp3 ON sp3.id_statusperforma = c3.status_performa_id
            INNER JOIN (
                SELECT 
                    packer_pegawai,
                    DATE(tanggal_packing) as tanggal_date,
                    MIN(tanggal_packing) as first_scan_time
                FROM tblpacking
                WHERE tanggal_packing >= " . $this->db->escape($start_date) . "
                AND tanggal_packing <= " . $this->db->escape($end_date) . "
                GROUP BY packer_pegawai, DATE(tanggal_packing)
            ) first_scan ON c3.packer_pegawai = first_scan.packer_pegawai
                AND DATE(c3.tanggal_packing) = first_scan.tanggal_date
                AND c3.tanggal_packing = first_scan.first_scan_time
        ) first_status ON first_status.packer_pegawai = c.packer_pegawai
            AND first_status.tanggal_date = DATE(c.tanggal_packing)
        WHERE c.tanggal_packing >= " . $this->db->escape($start_date) . "
        AND c.tanggal_packing <= " . $this->db->escape($end_date) . "
        {$search_where}
        GROUP BY DATE(c.tanggal_packing), c.packer_pegawai
        ";
        
        // Add ORDER BY
        if (!empty($data) && !empty($data['order'])) {
            $order_col = ['pegawai', 'tanggal_packing', 'total'];
            $order_clauses = [];
            foreach ($data['order'] as $order) {
                if (isset($order_col[$order['column']])) {
                    $order_clauses[] = $order_col[$order['column']] . ' ' . strtoupper($order['dir']);
                }
            }
            if (!empty($order_clauses)) {
                $sql .= " ORDER BY " . implode(', ', $order_clauses);
            }
        } else {
            $sql .= " ORDER BY pegawai ASC, tanggal_packing ASC";
        }
        
        // Add LIMIT/OFFSET
        if (!empty($data['length'])) {
            $sql .= " LIMIT " . intval($data['length']) . " OFFSET " . intval($data['start']);
        }

        return $this->db->query($sql);
    }

    function get_total_data_production_team_tab1($data, $start_date, $end_date)
    {
        // Build search conditions
        $search_where = '';
        if (!empty($data['search'])) {
            $search = $this->db->escape_like_str($data['search']);
            $search_where = " AND (t3.name LIKE '%{$search}%' OR DATE(c.tanggal_packing) LIKE '%{$search}%')";
        }
        
        // OPTIMIZED V9: Count rows dengan grouping baru (hanya per tanggal & packer)
        $sql = "
        SELECT COUNT(*) as total
        FROM (
            SELECT 
                DATE(c.tanggal_packing) as tanggal_packing,
                c.packer_pegawai
            FROM tblprintresi a
            INNER JOIN tblpacking c ON a.id_printresi = c.id_resi
            LEFT JOIN tbluser t3 ON t3.id_user = c.packer_pegawai
            WHERE c.tanggal_packing >= " . $this->db->escape($start_date) . "
            AND c.tanggal_packing <= " . $this->db->escape($end_date) . "
            {$search_where}
            GROUP BY DATE(c.tanggal_packing), c.packer_pegawai
        ) as grouped_data";
        
        $result = $this->db->query($sql)->row();
        return $result ? $result->total : 0;
    }

    // KPI Methods
    function get_total_receipts_processed($start_date, $end_date)
    {
        $this->db->where('tanggal_printresi >=', $start_date);
        $this->db->where('tanggal_printresi <=', $end_date);
        return $this->db->count_all_results('tblprintresi');
    }

    function get_total_shipped_receipts($start_date, $end_date)
    {
        $this->db->join('tblresikeluar t2', 't.id_printresi = t2.id_printresi', 'inner');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        return $this->db->count_all_results('tblprintresi t');
    }

    function get_total_pending_receipts($start_date, $end_date)
    {
        $this->db->join('tblresikeluar t2', 't.id_printresi = t2.id_printresi', 'left');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->where('t2.id_printresi IS NULL');
        return $this->db->count_all_results('tblprintresi t');
    }

    function get_total_retur_receipts($start_date, $end_date)
    {
        $this->db->join('tblresiretur t2', 't.id_printresi = t2.id_printresi', 'inner');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        return $this->db->count_all_results('tblprintresi t');
    }

    function get_avg_processing_time($start_date, $end_date)
    {
        $this->db->select('AVG(TIMESTAMPDIFF(HOUR, t.tanggal_printresi, t2.tanggal_resikeluar)) as avg_time');
        $this->db->join('tblresikeluar t2', 't.id_printresi = t2.id_printresi', 'inner');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->where('t2.tanggal_resikeluar IS NOT NULL');
        
        $query = $this->db->get('tblprintresi t');
        $result = $query->row();
        
        return $result ? round($result->avg_time, 2) : 0;
    }

    function get_picker_productivity($start_date, $end_date)
    {
        $this->db->select('COUNT(t.id_printresi) / COUNT(DISTINCT DATE(t.tanggal_resiambilbarang)) as productivity');
        $this->db->join('tblresiambilbarang t2', 't.id_printresi = t2.id_printresi', 'inner');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->where('t2.tanggal_resiambilbarang IS NOT NULL');
        
        $query = $this->db->get('tblprintresi t');
        $result = $query->row();
        
        return $result ? round($result->productivity, 2) : 0;
    }

    function get_packer_productivity($start_date, $end_date)
    {
        $this->db->select('COUNT(t.id_printresi) / COUNT(DISTINCT DATE(t2.tanggal_packing)) as productivity');
        $this->db->join('tblpacking t2', 't.id_printresi = t2.id_printresi', 'inner');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->where('t2.tanggal_packing IS NOT NULL');
        
        $query = $this->db->get('tblprintresi t');
        $result = $query->row();
        
        return $result ? round($result->productivity, 2) : 0;
    }

    function get_daily_performance($start_date, $end_date)
    {
        $this->db->select('
            DATE(t.tanggal_printresi) as date,
            COUNT(t.id_printresi) as receipts,
            COUNT(t2.id_printresi) as completed,
            ROUND((COUNT(t2.id_printresi) / COUNT(t.id_printresi)) * 100, 2) as completion_rate
        ');
        $this->db->join('tblresikeluar t2', 't.id_printresi = t2.id_printresi', 'left');
        $this->db->where('t.tanggal_printresi >=', $start_date);
        $this->db->where('t.tanggal_printresi <=', $end_date);
        $this->db->group_by('DATE(t.tanggal_printresi)');
        $this->db->order_by('DATE(t.tanggal_printresi)', 'ASC');
        
        $query = $this->db->get('tblprintresi t');
        $results = $query->result_array();
        
        $data = array(
            'labels' => array(),
            'values' => array()
        );
        
        foreach ($results as $row) {
            $data['labels'][] = $row['date'];
            $data['values'][] = $row['receipts'];
        }
        
        return $data;
    }


    function get_picker_performance_by_user($start_date, $end_date)
    {
        $this->db->select('
            COALESCE(peg.nama_pegawai, "Unknown User") as nama_pegawai,
            COALESCE(peg.kode_pegawai, "N/A") as kode_pegawai,
            COALESCE(sp.status_name, "Normal") as status_name,
            COALESCE(sp.kode_status, "NORMAL") as kode_status,
            COALESCE(COUNT(rab.id_resiambilbarang), 0) as total_scan,
            COUNT(DISTINCT DATE(rab.tanggal_resiambilbarang)) as hari_aktif,
            ROUND(COALESCE(COUNT(rab.id_resiambilbarang), 0) / NULLIF(COUNT(DISTINCT DATE(rab.tanggal_resiambilbarang)), 0), 2) as rata_rata_harian
        ');
        
        $this->db->from('tblresiambilbarang rab');
        $this->db->join('tblpegawai peg', 'peg.kode_pegawai = rab.yangambil_pegawai', 'left');
        $this->db->join('tblkpi k', 'k.id_user = rab.admin_pegawai AND DATE(k.tanggal) = DATE(rab.tanggal_resiambilbarang) AND k.tipe_transaksi = "PICKER"', 'left');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = k.id_statusperforma', 'left');
        $this->db->where('rab.tanggal_resiambilbarang >=', $start_date);
        $this->db->where('rab.tanggal_resiambilbarang <=', $end_date);
        $this->db->where('peg.kode_pegawai IS NOT NULL');
        $this->db->group_by('peg.kode_pegawai, peg.nama_pegawai, sp.kode_status, sp.status_name');
        $this->db->order_by('total_scan DESC');
        
        return $this->db->get();
    }
}


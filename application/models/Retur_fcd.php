<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_fcd extends CI_Model
{

    function save($retur, $user)
    {
        // Validasi status retur harus diisi
        if (empty($retur['status_retur'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Status harus diisi'];
        }

        // Validasi hasil scan harus diisi
        if (empty($retur['hasil_scan'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Hasil Scan harus diisi'];
        }

        // Parse hasil scan - bisa berisi multiple resi (separated by newline)
        $resi_list = array_filter(array_map('trim', explode("\n", $retur['hasil_scan'])));

        if (empty($resi_list)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Hasil scan tidak valid'];
        }

        $success_count = 0;
        $error_messages = [];

        foreach ($resi_list as $noresi) {
            // Check if receipt exists
            $receipt = $this->db
                ->select('id_printresi, id_kurir, id_marketplace, noresi')
                ->get_where('tblprintresi', ['noresi' => $noresi])
                ->row_array();
            
            if (empty($receipt)) {
                $error_messages[] = "Resi $noresi tidak ditemukan";
                continue;
            }

            // Check if retur already exists
            $this->db->where('id_resi', $receipt['id_printresi']);
            $retur_exist = $this->db->get('tblresiretur')->row_array();
            
            if (!empty($retur_exist)) {
                // Jika input Buka Retur, cek apakah resi sudah ada dengan status Terima Retur
                if ($retur['status_retur'] == 'Buka Retur' && $retur_exist['status_retur'] == 'Terima Retur') {
                    $error_messages[] = "Resi $noresi sudah diinput di Terima Retur, tidak bisa diinput di Buka Retur";
                    continue;
                }
                // Jika resi sudah ada dengan status yang sama atau status lain, tolak
                $error_messages[] = "Resi $noresi sudah ada";
                continue;
            }

            // Insert retur data
            $insert_data = [
                'id_resi' => $receipt['id_printresi'],
                'tanggal_resiretur' => date('Y-m-d H:i:s'),
                'status_retur' => $retur['status_retur'],
                'status_detail' => isset($retur['status_detail']) ? $retur['status_detail'] : null,
                'sudah_cetak' => '',
                'id_kurir' => $receipt['id_kurir'],
                'id_pegawai' => $user,
                'id_marketplace' => $receipt['id_marketplace'],
                'noresi' => $receipt['noresi']
            ];

            $this->db->insert('tblresiretur', $insert_data);
            $success_count++;
        }

        if ($success_count == 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Tidak ada resi yang berhasil diproses. ' . implode(', ', $error_messages)];
        }

        $retur['affected_rows'] = $success_count;
        $retur['success_message'] = "$success_count resi berhasil diproses";
        
        if (!empty($error_messages)) {
            $retur['warning'] = implode(', ', $error_messages);
        }

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

    function save_buka_retur($retur, $user)
    {
        // Validasi status detail harus diisi
        if (empty($retur['status_detail_buka'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Status Detail harus diisi'];
        }

        // Validasi hasil scan harus diisi
        if (empty($retur['hasil_scan_buka'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Hasil Scan harus diisi'];
        }

        // Insert data buka retur
        $insert_data = [
            'status_buka' => $retur['status_buka'],
            'status_detail_buka' => $retur['status_detail_buka'],
            'resi_buka' => $retur['resi_buka'],
            'hasil_scan_buka' => $retur['hasil_scan_buka'],
            'tanggal_buka_retur' => date('Y-m-d H:i:s'),
            'id_pegawai' => $user
        ];

        $this->db->insert('tblbukaretur', $insert_data);

        $retur['id_bukaretur'] = $this->db->insert_id();
        $retur['affected_rows'] = $this->db->affected_rows();

        return $retur;
    }

    function destroy($id_resiretur)
    {
        $this->db->delete('tblresiretur', ['id_resiretur' => $id_resiretur]);

        $retur['affected_rows'] = $this->db->affected_rows();

        return $retur;
    }

    /**
     * Get total scan Terima Retur for today by user
     */
    function get_total_scan_terima_today($user_id)
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('tblresiretur');
        $this->db->where('id_pegawai', $user_id);
        $this->db->not_like('status_retur', 'Buka Retur');
        $this->db->where('DATE(tanggal_resiretur)', date('Y-m-d'));
        
        $result = $this->db->get()->row();
        return $result ? $result->total : 0;
    }

    /**
     * Get total scan Buka Retur for today by user
     */
    function get_total_scan_buka_today($user_id)
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('tblresiretur');
        $this->db->where('id_pegawai', $user_id);
        $this->db->like('status_retur', 'Buka Retur');
        $this->db->where('DATE(tanggal_resiretur)', date('Y-m-d'));
        
        $result = $this->db->get()->row();
        return $result ? $result->total : 0;
    }

    function destroy_buka_retur($id_bukaretur)
    {
        $this->db->delete('tblbukaretur', ['id_bukaretur' => $id_bukaretur]);

        $retur['affected_rows'] = $this->db->affected_rows();

        return $retur;
    }

    /**
     * Get Terima Retur data with SKU details
     */
    function get_terima_retur_with_details($data, $start_date, $end_date)
    {
        // Build order clause
        if (!empty($data) && !empty($data['order'])) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        } else {
            $this->db->order_by('tr.tanggal_resiretur', 'DESC');
        }

        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        // Select with joins to get SKU details
        $this->db->select('
            tr.id_resiretur,
            tr.noresi,
            tr.tanggal_resiretur,
            tr.status_retur,
            mp.nama_marketplace,
            kr.nama_kurir,
            dp.no_pesanan,
            dp.sku,
            dp.jumlah
        ');

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Pagination
        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get();
    }

    // ==================== RETUR COMPLAIN ====================
    public function save_complain($complain, $user_id)
    {
        if (empty($complain['noresi'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi harus diisi'];
        }

        if (empty($complain['complain_type'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Jenis complain harus diisi'];
        }

        $complain['noresi'] = strtoupper(trim($complain['noresi']));

        $this->db->where('noresi', $complain['noresi']);
        if ($complain['complain_type'] === 'refund') {
            $this->db->where('complain_type', 'refund');
        } elseif ($complain['complain_type'] === 'replacement') {
            $this->db->where('complain_type', 'replacement');
        }

        $existing = $this->db->get('tblreturcomplain')->row_array();

        $complain['updated_by'] = $user_id;
        $complain['updated_at'] = date('Y-m-d H:i:s');

        if (!empty($existing)) {
            $this->db->where('id', $existing['id']);
            $this->db->update('tblreturcomplain', $complain);
            $complain['id'] = $existing['id'];
        } else {
            $complain['created_by'] = $user_id;
            $complain['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tblreturcomplain', $complain);
            $complain['id'] = $this->db->insert_id();
        }

        return $complain;
    }

    public function get_complain_list($params)
    {
        $this->_build_complain_query($params);

        if (!empty($params['length'])) {
            $this->db->limit($params['length'], $params['start']);
        }

        return $this->db->get();
    }

    public function get_total_complain_list($params)
    {
        $this->_build_complain_query($params, true);
        return $this->db->count_all_results();
    }

    private function _build_complain_query($params, $count_only = false)
    {
        if (!$count_only) {
            $this->db->select('rc.*, pr.id_marketplace, mp.nama_marketplace as marketplace_name');
        }

        $this->db->from('tblreturcomplain rc');
        $this->db->join('tblprintresi pr', 'pr.noresi = rc.noresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = pr.id_marketplace', 'left');

        if (!empty($params['status_filter']) && $params['status_filter'] !== 'ALL') {
            $this->db->where('rc.status', $params['status_filter']);
        }

        if (!empty($params['reportrange'])) {
            $dates = explode(' - ', $params['reportrange']);
            if (count($dates) === 2) {
                $start_date = trim($dates[0]);
                $end_date = trim($dates[1]);
                $this->db->where('rc.created_at >=', $start_date);
                $this->db->where('rc.created_at <=', $end_date);
            }
        }

        if (!empty($params['search'])) {
            $this->db->group_start();
            $this->db->like('rc.noresi', $params['search']);
            $this->db->or_like('rc.customer_name', $params['search']);
            $this->db->or_like('rc.marketplace', $params['search']);
            $this->db->or_like('rc.notes', $params['search']);
            $this->db->group_end();
        }

        if (!$count_only) {
            if (!empty($params['order'])) {
                $this->db->order_by($params['order'], $params['dir']);
            } else {
                $this->db->order_by('rc.created_at', 'DESC');
            }
        }
    }

    public function get_complain_report_list($params)
    {
        $this->_build_complain_report_query($params);

        if (!empty($params['length'])) {
            $this->db->limit($params['length'], $params['start']);
        }

        return $this->db->get();
    }

    public function get_total_complain_report_list($params)
    {
        $this->_build_complain_report_query($params, true);
        return $this->db->count_all_results();
    }

    private function _build_complain_report_query($params, $count_only = false)
    {
        if (!$count_only) {
            $this->db->select('rc.*, pr.id_marketplace, mp.nama_marketplace as marketplace_name');
        }

        $this->db->from('tblreturcomplain rc');
        $this->db->join('tblprintresi pr', 'pr.noresi = rc.noresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = pr.id_marketplace', 'left');

        // Filter: hanya data yang sudah diubah statusnya (bukan TO_DO)
        $this->db->where('rc.status !=', 'TO_DO');

        if (!empty($params['status_filter']) && $params['status_filter'] !== 'ALL') {
            $this->db->where('rc.status', $params['status_filter']);
        }

        if (!empty($params['reportrange'])) {
            $dates = explode(' - ', $params['reportrange']);
            if (count($dates) === 2) {
                $start_date = trim($dates[0]);
                $end_date = trim($dates[1]);
                // Filter berdasarkan updated_at karena ini laporan perubahan status
                $this->db->where('rc.updated_at >=', $start_date);
                $this->db->where('rc.updated_at <=', $end_date);
            }
        }

        if (!empty($params['search'])) {
            $this->db->group_start();
            $this->db->like('rc.noresi', $params['search']);
            $this->db->or_like('rc.customer_name', $params['search']);
            $this->db->or_like('rc.marketplace', $params['search']);
            $this->db->or_like('rc.notes', $params['search']);
            $this->db->group_end();
        }

        if (!$count_only) {
            if (!empty($params['order'])) {
                $this->db->order_by($params['order'], $params['dir']);
            } else {
                $this->db->order_by('rc.updated_at', 'DESC');
            }
        }
    }

    public function update_complain_status($id, $status, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblreturcomplain', array(
            'status' => $status,
            'updated_by' => $user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

        if ($this->db->affected_rows() === 0) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Data complain tidak ditemukan atau status sama'];
        }

        return ['success' => TRUE];
    }

    /**
     * Get total count of Terima Retur with details
     */
    function get_total_terima_retur_with_details($data, $start_date, $end_date)
    {
        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get Buka Retur data with SKU details
     */
    function get_buka_retur_with_details($data, $start_date, $end_date)
    {
        // Build order clause
        if (!empty($data) && !empty($data['order'])) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        } else {
            $this->db->order_by('br.tanggal_buka_retur', 'DESC');
        }

        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        // Select with joins to get SKU details
        // Note: resi_buka might be noresi, so we join based on that
        $this->db->select('
            br.id_bukaretur,
            br.resi_buka,
            br.tanggal_buka_retur,
            br.status_detail_buka,
            mp.nama_marketplace,
            kr.nama_kurir,
            dp.no_pesanan,
            dp.sku,
            dp.jumlah
        ');

        $this->db->from('tblbukaretur br');
        $this->db->join('tblprintresi pr', 'pr.noresi = br.resi_buka', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = pr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = pr.id_kurir', 'left');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('br.tanggal_buka_retur >=', $start_date);
            $this->db->where('br.tanggal_buka_retur <=', $end_date);
        }

        // Pagination
        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get();
    }

    /**
     * Get total count of Buka Retur with details
     */
    function get_total_buka_retur_with_details($data, $start_date, $end_date)
    {
        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        $this->db->from('tblbukaretur br');
        $this->db->join('tblprintresi pr', 'pr.noresi = br.resi_buka', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = pr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = pr.id_kurir', 'left');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('br.tanggal_buka_retur >=', $start_date);
            $this->db->where('br.tanggal_buka_retur <=', $end_date);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get Laporan Terima Retur (with pagination)
     */
    function get_laporan_terima_retur($data, $start_date, $end_date, $id_kurir = null)
    {
        // Build order clause
        if (!empty($data['order'])) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        } else {
            $this->db->order_by('tr.tanggal_resiretur', 'DESC');
        }

        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        // Select with joins
        $this->db->select('
            tr.id_resiretur,
            tr.noresi,
            tr.tanggal_resiretur,
            tr.status_retur,
            tr.status_detail,
            mp.nama_marketplace,
            kr.nama_kurir,
            dp.no_pesanan,
            dp.sku,
            dp.jumlah
        ');

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by Terima Retur only - Use WHERE for better performance (can use index)
        $this->db->where('tr.status_retur', 'Terima Retur');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Filter by kurir
        if (!empty($id_kurir)) {
            $this->db->where('tr.id_kurir', $id_kurir);
        }

        // Pagination
        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get();
    }

    /**
     * Get total count Laporan Terima Retur
     */
    function get_total_laporan_terima_retur($data, $start_date, $end_date, $id_kurir = null)
    {
        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by Terima Retur only - Use WHERE for better performance (can use index)
        $this->db->where('tr.status_retur', 'Terima Retur');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Filter by kurir
        if (!empty($id_kurir)) {
            $this->db->where('tr.id_kurir', $id_kurir);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get Laporan Buka Retur (with pagination)
     */
    function get_laporan_buka_retur($data, $start_date, $end_date, $id_kurir = null)
    {
        // Build order clause
        if (!empty($data['order'])) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        } else {
            $this->db->order_by('tr.tanggal_resiretur', 'DESC');
        }

        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        // Select with joins
        $this->db->select('
            tr.id_resiretur,
            tr.noresi,
            tr.tanggal_resiretur,
            tr.status_retur,
            tr.status_detail,
            mp.nama_marketplace,
            kr.nama_kurir,
            dp.no_pesanan,
            dp.sku,
            dp.jumlah
        ');

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by Buka Retur only - Use WHERE for better performance (can use index)
        $this->db->where('tr.status_retur', 'Buka Retur');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Filter by kurir
        if (!empty($id_kurir)) {
            $this->db->where('tr.id_kurir', $id_kurir);
        }

        // Pagination
        if (!empty($data['length'])) {
            $this->db->limit($data['length'], $data['start']);
        }

        return $this->db->get();
    }

    /**
     * Get total count Laporan Buka Retur
     */
    function get_total_laporan_buka_retur($data, $start_date, $end_date, $id_kurir = null)
    {
        // Build search clause
        if (!empty($data['search'])) {
            $this->db->group_start();
            foreach ($data['valid_columns'] as $sterm) {
                if (empty($sterm)) continue;
                $this->db->or_like($sterm, $data['search']);
            }
            $this->db->group_end();
        }

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by Buka Retur only - Use WHERE for better performance (can use index)
        $this->db->where('tr.status_retur', 'Buka Retur');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Filter by kurir
        if (!empty($id_kurir)) {
            $this->db->where('tr.id_kurir', $id_kurir);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get ALL Laporan Terima Retur (for Excel export, no pagination)
     */
    function get_laporan_terima_retur_all($start_date, $end_date, $id_kurir = null)
    {
        $this->db->select('
            tr.id_resiretur,
            tr.noresi,
            tr.tanggal_resiretur,
            tr.status_retur,
            tr.status_detail,
            mp.nama_marketplace,
            kr.nama_kurir,
            dp.no_pesanan,
            dp.sku,
            dp.jumlah
        ');

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by Terima Retur only - Use WHERE for better performance (can use index)
        $this->db->where('tr.status_retur', 'Terima Retur');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Filter by kurir
        if (!empty($id_kurir)) {
            $this->db->where('tr.id_kurir', $id_kurir);
        }

        $this->db->order_by('tr.tanggal_resiretur', 'DESC');

        return $this->db->get();
    }

    /**
     * Get ALL Laporan Buka Retur (for Excel export, no pagination)
     */
    function get_laporan_buka_retur_all($start_date, $end_date, $id_kurir = null)
    {
        $this->db->select('
            tr.id_resiretur,
            tr.noresi,
            tr.tanggal_resiretur,
            tr.status_retur,
            tr.status_detail,
            mp.nama_marketplace,
            kr.nama_kurir,
            dp.no_pesanan,
            dp.sku,
            dp.jumlah
        ');

        $this->db->from('tblresiretur tr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = tr.id_resi', 'left');
        $this->db->join('tbldetailprintresi dp', 'dp.id_resi = pr.id_printresi', 'left');
        $this->db->join('tblmarketplace mp', 'mp.id_marketplace = tr.id_marketplace', 'left');
        $this->db->join('tblkurir kr', 'kr.id_kurir = tr.id_kurir', 'left');

        // Filter by Buka Retur only - Use WHERE for better performance (can use index)
        $this->db->where('tr.status_retur', 'Buka Retur');

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('tr.tanggal_resiretur >=', $start_date);
            $this->db->where('tr.tanggal_resiretur <=', $end_date);
        }

        // Filter by kurir
        if (!empty($id_kurir)) {
            $this->db->where('tr.id_kurir', $id_kurir);
        }

        $this->db->order_by('tr.tanggal_resiretur', 'DESC');

        return $this->db->get();
    }
}

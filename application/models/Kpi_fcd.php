<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kpi_fcd extends CI_Model
{
    // ========== STATUS PERFORMA METHODS ==========
    
    function get_status_performa($id = null)
    {
        if (!empty($id)) {
            $this->db->where('id_statusperforma', $id);
        }
        
        $this->db->where('isactive', 1);
        $this->db->order_by('kode_status', 'ASC');
        
        return $this->db->get('tblmasterstatusperforma');
    }
    
    function get_status_performa_by_kategori($kategori)
    {
        $this->db->where('role', $kategori);
        $this->db->where('isactive', 1);
        $this->db->order_by('kode_status', 'ASC');
        
        return $this->db->get('tblmasterstatusperforma');
    }
    
    function save_status_performa($status, $user_id)
    {
        $timestamp = date('Y-m-d H:i:s');
        
        if (!empty($status['id_statusperforma'])) {
            // Update existing
            $status['updatedby'] = $user_id;
            $status['updated'] = $timestamp;
            $this->db->where('id_statusperforma', $status['id_statusperforma']);
            $this->db->update('tblmasterstatusperforma', $status);
        } else {
            // Insert new
            unset($status['id_statusperforma']);
            $status['createdby'] = $user_id;
            $status['created'] = $timestamp;
            $this->db->insert('tblmasterstatusperforma', $status);
        }
        
        return array('affected_rows' => $this->db->affected_rows());
    }
    
    // ========== LOG STATUS PERFORMA METHODS ==========
    
    function get_status_id_by_name($status_name)
    {
        // Cari berdasarkan status_name atau kode_status
        $this->db->where('isactive', 1);
        $this->db->group_start();
        $this->db->where('status_name', $status_name);
        $this->db->or_where('kode_status', $status_name);
        $this->db->group_end();
        
        $result = $this->db->get('tblmasterstatusperforma')->row();
        
        return $result ? $result->id_statusperforma : null;
    }
    
    function log_status_performa_with_target($user_id, $status_id, $tanggal, $target_pribadi)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        // Cek apakah sudah ada log untuk user dan tanggal tersebut
        $existing = $this->db->get_where('tblstatusperforma', array(
            'id_user' => $user_id,
            'tanggal' => $tanggal
        ))->row();
        
        if ($existing) {
            // Update existing log
            $data = array(
                'id_statusperforma' => $status_id,
                'target_pribadi' => $target_pribadi,
                'jam_login' => date('H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
                'updatedby' => $user_id
            );
            $this->db->where('id_log', $existing->id_log);
            $this->db->update('tblstatusperforma', $data);
        } else {
            // Insert new log
            $data = array(
                'id_user' => $user_id,
                'id_statusperforma' => $status_id,
                'target_pribadi' => $target_pribadi,
                'tanggal' => $tanggal,
                'jam_login' => date('H:i:s'),
                'isactive' => 1,
                'createdby' => $user_id,
                'created' => date('Y-m-d H:i:s')
            );
            $this->db->insert('tblstatusperforma', $data);
        }
        
        return array('affected_rows' => $this->db->affected_rows());
    }
    
    function log_status_performa($user_id, $status_id, $tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        // Cek apakah sudah ada log untuk user dan tanggal tersebut
        $existing = $this->db->get_where('tblstatusperforma', array(
            'id_user' => $user_id,
            'tanggal' => $tanggal
        ))->row();
        
        if ($existing) {
            // Nonaktifkan semua entry untuk user dan tanggal ini
            $this->db->where('id_user', $user_id);
            $this->db->where('tanggal', $tanggal);
            $this->db->update('tblstatusperforma', array('isactive' => 0));
            
            // Update existing log dan aktifkan
            $data = array(
                'id_statusperforma' => $status_id,
                'jam_login' => date('H:i:s'),
                'isactive' => 1,
                'updated' => date('Y-m-d H:i:s'),
                'updatedby' => $user_id
            );
            
            $this->db->where('id_log', $existing->id_log);
            return $this->db->update('tblstatusperforma', $data);
        } else {
            // Insert new log
            $data = array(
                'id_user' => $user_id,
                'id_statusperforma' => $status_id,
                'tanggal' => $tanggal,
                'jam_login' => date('H:i:s'),
                'isactive' => 1,
                'createdby' => $user_id,
                'created' => date('Y-m-d H:i:s')
            );
            
            return $this->db->insert('tblstatusperforma', $data);
        }
    }
    
    function get_user_status_performa($user_id, $tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->select('lsp.*, sp.kode_status, sp.status_name, sp.role');
        $this->db->from('tblstatusperforma lsp');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = lsp.id_statusperforma');
        $this->db->where('lsp.id_user', $user_id);
        $this->db->where('lsp.tanggal', $tanggal);
        $this->db->where('lsp.isactive', 1);
        
        return $this->db->get()->row();
    }
    
    // ========== LOG TRANSAKSI HARIAN METHODS ==========
    
    function log_transaksi_harian($user_id, $status_id, $tipe_transaksi, $jumlah_resi, $tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        // Cek apakah sudah ada log untuk user, tanggal, dan tipe transaksi
        $existing = $this->db->get_where('tblkpi', array(
            'id_user' => $user_id,
            'tanggal' => $tanggal,
            'tipe_transaksi' => $tipe_transaksi
        ))->row();
        
        if ($existing) {
            // Update existing log
            $data = array(
                'id_statusperforma' => $status_id,
                'jumlah_resi' => $existing->jumlah_resi + $jumlah_resi,
                'updated' => date('Y-m-d H:i:s'),
                'updatedby' => $user_id
            );
            
            $this->db->where('id_log', $existing->id_log);
            return $this->db->update('tblkpi', $data);
        } else {
            // Insert new log
            $data = array(
                'id_user' => $user_id,
                'id_statusperforma' => $status_id,
                'tanggal' => $tanggal,
                'tipe_transaksi' => $tipe_transaksi,
                'jumlah_resi' => $jumlah_resi,
                'createdby' => $user_id,
                'created' => date('Y-m-d H:i:s')
            );
            
            return $this->db->insert('tblkpi', $data);
        }
    }
    
    function get_transaksi_harian($user_id, $tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->select('lth.*, sp.kode_status, sp.status_name, sp.role');
        $this->db->from('tblkpi lth');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = lth.id_statusperforma');
        $this->db->where('lth.id_user', $user_id);
        $this->db->where('lth.tanggal', $tanggal);
        
        return $this->db->get()->result();
    }
    
    // ========== KPI DASHBOARD METHODS ==========
    
    function get_kpi_dashboard($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            t.tanggal,
            COUNT(DISTINCT t.id_user) as total_user_aktif,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('t.tanggal');
        $this->db->order_by('t.tanggal DESC, total_transaksi DESC');
        
        return $this->db->get();
    }
    
    function get_kpi_by_status($start_date, $end_date, $status_id = null)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            t.tanggal,
            sp.id_statusperforma,
            sp.kode_status,
            sp.status_name,
            COUNT(DISTINCT t.id_user) as total_user_aktif,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        
        if (!empty($status_id)) {
            $this->db->where('sp.id_statusperforma', $status_id);
        }
        
        $this->db->group_by('t.tanggal, sp.id_statusperforma, sp.kode_status, sp.status_name');
        $this->db->order_by('t.tanggal DESC, total_transaksi DESC');
        
        return $this->db->get();
    }
    
    function get_kpi_summary($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            COUNT(DISTINCT sp.kode_status) as total_status,
            COUNT(DISTINCT t.id_user) as total_user_aktif,
            COALESCE(SUM(CASE WHEN k.tipe_transaksi = "PACKING" THEN k.jumlah_resi ELSE 0 END), 0) as total_packing,
            COALESCE(SUM(CASE WHEN k.tipe_transaksi = "PICKING" THEN k.jumlah_resi ELSE 0 END), 0) as total_picking,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as rata_rata_capai,
            0 as excellent_count,
            0 as good_count,
            0 as fair_count,
            0 as poor_count
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        
        return $this->db->get()->row();
    }
    
    function get_top_performers($start_date, $end_date, $limit = 10)
    {
        $this->db->select('
            u.name as nama_user,
            sp.kode_status,
            sp.status_name,
            SUM(lth.jumlah_resi) as total_transaksi,
            COUNT(DISTINCT lth.tanggal) as hari_aktif,
            AVG(lth.jumlah_resi) as rata_rata_harian
        ');
        
        $this->db->from('tblkpi lth');
        $this->db->join('tbluser u', 'u.id_user = lth.id_user');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = lth.id_statusperforma');
        $this->db->where('lth.tanggal >=', $start_date);
        $this->db->where('lth.tanggal <=', $end_date);
        $this->db->group_by('lth.id_user, lth.id_statusperforma');
        $this->db->order_by('total_transaksi DESC');
        $this->db->limit($limit);
        
        return $this->db->get();
    }
    
    // ========== UPDATE KPI METHODS ==========
    
    function update_kpi_harian($tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        // Panggil stored procedure untuk update KPI harian
        $this->db->query("CALL sp_update_kpi_harian('$tanggal')");
        
        return true;
    }
    
    function get_daily_performance_chart($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            t.tanggal,
            sp.kode_status,
            sp.status_name,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as persentase_capai
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('t.tanggal, sp.kode_status, sp.status_name');
        $this->db->order_by('t.tanggal ASC, total_transaksi DESC');
        
        return $this->db->get();
    }
    
    function get_status_performance_comparison($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            sp.kode_status,
            sp.status_name,
            sp.role,
            COUNT(DISTINCT t.id_user) as total_user,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as rata_rata_per_user,
            0 as rata_rata_capai,
            0 as excellent_days,
            0 as good_days,
            0 as fair_days,
            0 as poor_days
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('sp.kode_status, sp.status_name, sp.role');
        $this->db->order_by('total_transaksi DESC');
        
        return $this->db->get();
    }
    
    // ========== DASHBOARD SPECIFIC METHODS ==========
    
    function get_kpi_summary_cards($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            COUNT(DISTINCT sp.kode_status) as total_status_aktif,
            COUNT(DISTINCT t.id_user) as total_user_aktif,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as rata_rata_capai
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        
        return $this->db->get()->row();
    }
    
    function get_status_performa_cards($start_date, $end_date, $limit = 4)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            sp.kode_status,
            sp.status_name,
            COUNT(DISTINCT t.id_user) as total_user,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as rata_rata_capai,
            "NORMAL" as status_performa
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('sp.kode_status, sp.status_name');
        $this->db->order_by('total_transaksi DESC');
        $this->db->limit($limit);
        
        return $this->db->get();
    }
    
    function get_realtime_performance($tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            t.tanggal,
            sp.kode_status,
            sp.status_name,
            COUNT(DISTINCT t.id_user) as total_user_aktif,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal', $tanggal);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('t.tanggal, sp.kode_status, sp.status_name');
        $this->db->order_by('total_transaksi DESC');
        
        return $this->db->get();
    }
    
    function get_user_performance_today($user_id, $tanggal = null)
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->select('
            lsp.*,
            sp.kode_status,
            sp.status_name,
            sp.target_harian,
            COALESCE(SUM(CASE WHEN lth.tipe_transaksi = "PACKING" THEN lth.jumlah_resi ELSE 0 END), 0) as total_packing,
            COALESCE(SUM(CASE WHEN lth.tipe_transaksi = "PICKING" THEN lth.jumlah_resi ELSE 0 END), 0) as total_picking,
            COALESCE(SUM(lth.jumlah_resi), 0) as total_transaksi,
            CASE 
                WHEN sp.target_harian > 0 THEN 
                    ROUND((COALESCE(SUM(lth.jumlah_resi), 0) / sp.target_harian) * 100, 2)
                ELSE 0 
            END as persentase_capai
        ');
        
        $this->db->from('tblstatusperforma lsp');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = lsp.id_statusperforma');
        $this->db->join('tblkpi lth', 'lth.id_user = lsp.id_user AND lth.id_statusperforma = lsp.id_statusperforma AND lth.tanggal = lsp.tanggal', 'left');
        $this->db->where('lsp.id_user', $user_id);
        $this->db->where('lsp.tanggal', $tanggal);
        $this->db->where('lsp.isactive', 1);
        $this->db->group_by('lsp.id_user, sp.id_statusperforma');
        
        return $this->db->get()->row();
    }
    
    function get_daily_trend_data($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            t.tanggal,
            sp.kode_status,
            sp.status_name,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as persentase_capai
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('t.tanggal, sp.kode_status, sp.status_name');
        $this->db->order_by('t.tanggal ASC, sp.kode_status ASC');
        
        return $this->db->get();
    }
    
    function get_performance_distribution($start_date, $end_date)
    {
        // Query langsung tanpa view untuk menghindari error
        $this->db->select('
            sp.kode_status,
            sp.status_name,
            COALESCE(SUM(k.jumlah_resi), 0) as total_transaksi,
            0 as rata_rata_capai
        ');
        
        $this->db->from('tblstatusperforma t');
        $this->db->join('tblmasterstatusperforma sp', 'sp.id_statusperforma = t.id_statusperforma', 'left');
        $this->db->join('tblkpi k', 'k.id_user = t.id_user AND k.id_statusperforma = t.id_statusperforma AND k.tanggal = t.tanggal', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.isactive', 1);
        $this->db->group_by('sp.kode_status, sp.status_name');
        $this->db->order_by('total_transaksi DESC');
        
        return $this->db->get();
    }
}

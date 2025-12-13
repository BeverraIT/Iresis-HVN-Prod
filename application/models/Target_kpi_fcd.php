<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Target_kpi_fcd extends CI_Model
{
    /**
     * Get target KPI by date and role
     */
    public function get_target_by_date_role($tanggal, $role)
    {
        $this->db->select('t.*, u.username, u.name, peg.nama_pegawai');
        $this->db->from('tbltargetkpiharian t');
        $this->db->join('tbluser u', 'u.id_user = t.id_user', 'left');
        $this->db->join('tblpegawai peg', 'peg.kode_pegawai = u.id_pegawai', 'left');
        $this->db->where('t.tanggal', $tanggal);
        $this->db->where('t.role', $role);
        $this->db->order_by('u.username', 'ASC');
        
        return $this->db->get();
    }
    
    /**
     * Get target KPI by date range and role
     */
    public function get_target_by_date_range($start_date, $end_date, $role)
    {
        $this->db->select('t.*, u.username, u.name, peg.nama_pegawai');
        $this->db->from('tbltargetkpiharian t');
        $this->db->join('tbluser u', 'u.id_user = t.id_user', 'left');
        $this->db->join('tblpegawai peg', 'peg.kode_pegawai = u.id_pegawai', 'left');
        $this->db->where('t.tanggal >=', $start_date);
        $this->db->where('t.tanggal <=', $end_date);
        $this->db->where('t.role', $role);
        $this->db->order_by('t.tanggal', 'DESC');
        $this->db->order_by('u.username', 'ASC');
        
        return $this->db->get();
    }

    /**
     * Get all users for target setting (SEMUA user - PICKER + PACKER + OTHERS)
     * Changed to show ALL users regardless of role, so webmaster can set target
     * for any user to do any role (e.g. PACKER doing PICKER task)
     */
    public function get_available_users()
    {
        // Get ALL users: PICKER + PACKER (combine both)
        $sql = "
            SELECT 
                id_user,
                username,
                nama_pegawai,
                user_role
            FROM (
                -- Get all pickers from tblnamaambilbarang
                SELECT DISTINCT 
                    u.id_user,
                    u.username,
                    peg.nama_pegawai,
                    'PICKER' as user_role
                FROM tblnamaambilbarang nab
                INNER JOIN tblpegawai peg ON peg.kode_pegawai = nab.id_pegawai
                LEFT JOIN tbluser u ON u.id_pegawai = peg.kode_pegawai
                WHERE peg.status_aktif = 'AKTIF'
                AND u.id_user IS NOT NULL
                
                UNION
                
                -- Get all packers from tblpacking
                SELECT DISTINCT 
                    u.id_user,
                    u.username,
                    COALESCE(peg.nama_pegawai, u.name) as nama_pegawai,
                    'PACKER' as user_role
                FROM tblpacking p
                INNER JOIN tbluser u ON u.id_user = p.packer_pegawai
                LEFT JOIN tblpegawai peg ON peg.kode_pegawai = u.id_pegawai
                WHERE u.id_user IS NOT NULL
            ) combined_users
            GROUP BY id_user, username, nama_pegawai, user_role
            ORDER BY nama_pegawai ASC
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Save or update target
     */
    public function save_target($data)
    {
        // Check if target already exists
        $existing = $this->db->get_where('tbltargetkpiharian', [
            'id_user' => $data['id_user'],
            'tanggal' => $data['tanggal'],
            'role' => $data['role']
        ])->row();

        if ($existing) {
            // Update existing target
            $data['updatedby'] = $data['createdby'];
            $data['updated'] = date('Y-m-d H:i:s');
            unset($data['createdby'], $data['created']);
            
            $this->db->where('id_target', $existing->id_target);
            $this->db->update('tbltargetkpiharian', $data);
            
            return ['affected_rows' => $this->db->affected_rows(), 'action' => 'update'];
        } else {
            // Insert new target
            $data['created'] = date('Y-m-d H:i:s');
            $this->db->insert('tbltargetkpiharian', $data);
            
            return ['affected_rows' => $this->db->affected_rows(), 'action' => 'insert'];
        }
    }

    /**
     * Save multiple targets at once
     */
    public function save_targets_batch($targets, $createdby)
    {
        $success_count = 0;
        $error_count = 0;
        
        foreach ($targets as $target) {
            try {
                $target['createdby'] = $createdby;
                $result = $this->save_target($target);
                
                if ($result['affected_rows'] > 0) {
                    $success_count++;
                }
            } catch (Exception $e) {
                $error_count++;
                log_message('error', 'Error saving target: ' . $e->getMessage());
            }
        }
        
        return [
            'success' => $success_count,
            'error' => $error_count,
            'total' => count($targets)
        ];
    }

    /**
     * Delete target
     */
    public function delete_target($id_target)
    {
        $this->db->where('id_target', $id_target);
        $this->db->delete('tbltargetkpiharian');
        
        return ['affected_rows' => $this->db->affected_rows()];
    }

    /**
     * Get target for specific user and date
     */
    public function get_target_by_user_date($id_user, $tanggal, $role)
    {
        return $this->db->get_where('tbltargetkpiharian', [
            'id_user' => $id_user,
            'tanggal' => $tanggal,
            'role' => $role
        ])->row();
    }

    /**
     * Copy targets from previous date
     */
    public function copy_targets_from_date($from_date, $to_date, $role, $createdby)
    {
        $targets = $this->get_target_by_date_role($from_date, $role)->result_array();
        
        if (empty($targets)) {
            return ['success' => 0, 'error' => 0, 'message' => 'Tidak ada target di tanggal tersebut'];
        }
        
        $new_targets = [];
        foreach ($targets as $target) {
            $new_targets[] = [
                'id_user' => $target['id_user'],
                'tanggal' => $to_date,
                'role' => $target['role'],
                'target_resi' => $target['target_resi'],
                'keterangan' => 'Copy from ' . $from_date,
                'createdby' => $createdby
            ];
        }
        
        return $this->save_targets_batch($new_targets, $createdby);
    }

    /**
     * Get target summary for dashboard
     */
    public function get_target_summary($tanggal, $role)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_users,
                SUM(target_resi) as total_target,
                ROUND(AVG(target_resi), 0) as avg_target,
                MIN(target_resi) as min_target,
                MAX(target_resi) as max_target
            FROM tbltargetkpiharian
            WHERE tanggal = ? AND role = ?
        ";
        
        return $this->db->query($sql, [$tanggal, $role])->row();
    }
    
    /**
     * Get target summary for date range
     */
    public function get_target_summary_range($start_date, $end_date, $role)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT DATE(tanggal)) as total_days,
                COUNT(DISTINCT id_user) as total_users,
                COUNT(*) as total_records,
                SUM(target_resi) as total_target,
                ROUND(AVG(target_resi), 0) as avg_target,
                MIN(target_resi) as min_target,
                MAX(target_resi) as max_target
            FROM tbltargetkpiharian
            WHERE tanggal >= ? AND tanggal <= ? AND role = ?
        ";
        
        return $this->db->query($sql, [$start_date, $end_date, $role])->row();
    }
}


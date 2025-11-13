<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picking_fcd extends CI_Model
{

    function get_picker($picker_status_aktif = null)
    {
        // Optimized query dengan prepared statement dan index
        $sql = "
            SELECT t.*, t1.nama_pegawai 
            FROM tblnamaambilbarang t 
            INNER JOIN tblpegawai t1 ON t1.kode_pegawai = t.id_pegawai 
            WHERE t1.status_aktif = 'AKTIF'
        ";
        
        $params = [];
        
        if (!empty($picker_status_aktif)) {
            $sql .= " AND t.status_aktif = ?";
            $params[] = $picker_status_aktif;
        }
        
        $sql .= " ORDER BY t1.nama_pegawai";
        
        return $this->db->query($sql, $params);
    }

    function save($picking, $user, $mode = PICKING_INSERT_PACKER)
    {
        // Start transaction untuk memastikan data konsisten
        $this->db->trans_start();
        
        try {
            // 1. Check if noresi exists and get status (optimized query)
            $receipt = $this->db
                ->select('id_printresi, status_pesanan')
                ->get_where('tblprintresi', ['noresi' => $picking['noresi']])
                ->row();

            if (empty($receipt)) {
                $this->db->trans_rollback();
                return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
            }

            // 2. Check if status_pesanan is COMPLETED or CANCELED
            if (in_array($receipt->status_pesanan, ['COMPLETED', 'CANCELED'])) {
                $this->db->trans_rollback();
                return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak dapat diproses karena status pesanan sudah ' . $receipt->status_pesanan];
            }

            unset($picking['noresi']);
            $id_resi = $receipt->id_printresi;

            // 3. Check if id_resi exists in tblresiambilbarang (optimized query)
            $picking_exist = $this->db
                ->select('id_resiambilbarang')
                ->get_where('tblresiambilbarang', ['id_resi' => $id_resi])
                ->row();

            if ($mode == PICKING_INSERT_PACKER) {
                if (!empty($picking_exist)) {
                    $this->db->trans_rollback();
                    return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah diambil. Silakan Cek data'];
                }

                // Insert single record
                // Note: nama_komputer is now synced with database from login fix
                $insert_data = [
                    'id_resi' => $id_resi,
                    'tanggal_resiambilbarang' => date('Y-m-d H:i:s'),
                    'admin_pegawai' => $user['id_user'],
                    'yangambil_pegawai' => $picking['yangambil_pegawai'],
                    'nama_komputer' => $user['nama_komputer'], // Synced with database from login
                    'pending' => $picking['pending'],
                    'status_performa_id' => $picking['status_performa_id'] ?? null,
                ];

                $this->db->insert('tblresiambilbarang', $insert_data);
                $picking['affected_rows'] = $this->db->affected_rows();
                
                // Log KPI secara asynchronous (non-blocking) - gunakan admin_pegawai sebagai id_user
                $this->log_kpi_transaksi_async($user['id_user'], $picking['status_performa_id'] ?? null, $id_resi);

            } else if ($mode == PICKING_UPDATE_PACKER) {
                if (empty($picking_exist)) {
                    $this->db->trans_rollback();
                    return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
                }

                // Update single record
                // Note: nama_komputer is now synced with database from login fix
                $update_data = [
                    'tanggal_resiambilbarang' => date('Y-m-d H:i:s'),
                    'admin_pegawai' => $user['id_user'],
                    'yangambil_pegawai' => $picking['yangambil_pegawai'],
                    'nama_komputer' => $user['nama_komputer'] // Synced with database from login
                ];

                $this->db->where('id_resiambilbarang', $picking_exist->id_resiambilbarang);
                $this->db->update('tblresiambilbarang', $update_data);
                $picking['affected_rows'] = $this->db->affected_rows();
            }

            // Commit transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return ['error' => TRUE, 'code' => 500, 'message' => 'Database transaction failed'];
            }

            return $picking;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error in picking save: ' . $e->getMessage());
            return ['error' => TRUE, 'code' => 500, 'message' => 'Internal server error'];
        }
    }

    function save_picker($picker)
    {
        $existing_picker = $this->db->get_where('tblnamaambilbarang', ['id_pegawai' => $picker['id_pegawai']])->row_array();

        if (!empty($existing_picker)) {
            $picker['id_namaambilbarang'] = $existing_picker['id_namaambilbarang'];
        }

        $this->db->replace('tblnamaambilbarang', $picker);

        $picker['affected_rows'] = $this->db->affected_rows();

        return $picker;
    }

    function get_data($data)
    {
        // Use subquery to improve performance by filtering first
        $this->db->select('
            t2.noresi,
            COALESCE(t3.nama_pegawai, "-") as nama_pegawai,
            t.tanggal_resiambilbarang,
            COALESCE(t4.name, "-") as name,
            t.nama_komputer
        ');

        // Add index hints for better performance
        $this->db->from('tblresiambilbarang t');
        $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'inner');
        $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.yangambil_pegawai', 'left');
        $this->db->join('tbluser t4', 't4.id_user = t.admin_pegawai', 'left');

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

        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        } else {
            // Add default ordering for consistent results
            $this->db->order_by('t.tanggal_resiambilbarang', 'DESC');
        }

        $this->db->limit($data['length'], $data['start']);

        return $this->db->get();
    }

    function get_total_data($data)
    {
        // Optimize total count query - avoid unnecessary JOINs when possible
        if (!empty($data['search'])) {
            // Only use JOINs when search is applied
            $this->db->from('tblresiambilbarang t');
            $this->db->join('tblprintresi t2', 't2.id_printresi = t.id_resi', 'inner');
            $this->db->join('tblpegawai t3', 't3.kode_pegawai = t.yangambil_pegawai', 'left');
            $this->db->join('tbluser t4', 't4.id_user = t.admin_pegawai', 'left');

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

            return $this->db->count_all_results();
        } else {
            // When no search, simply count all records from main table
            return $this->db->count_all('tblresiambilbarang');
        }
    }

    function destroy_picker($id_namaambilbarang)
    {
        $this->db->delete('tblnamaambilbarang', ['id_namaambilbarang' => $id_namaambilbarang]);

        $receipt['affected_rows'] = $this->db->affected_rows();

        return $receipt;
    }

    function get_total_scan_user($id_user)
    {
        // Optimized query dengan prepared statement dan index
        $query = $this->db->query("
            SELECT COUNT(1) as total_scan 
            FROM tblresiambilbarang 
            WHERE DATE(tanggal_resiambilbarang) = CURDATE()
            AND admin_pegawai = ?
        ", [$id_user]);
        
        return $query;
    }

    /**
     * Log KPI transaksi secara asynchronous (non-blocking)
     */
    private function log_kpi_transaksi_async($user_id, $status_performa_id = null, $id_resi = null) {
        // Log KPI langsung tanpa queue untuk menghindari duplikasi
        // PENTING: JANGAN update tblstatusperforma di sini karena itu adalah status LOGIN (untuk PACKER)
        // Status picker disimpan langsung ke tblkpi saja
        if ($status_performa_id) {
            $this->log_kpi_transaksi_with_status($user_id, 'PICKER', $status_performa_id, 1, $id_resi);
        } else {
            $this->log_kpi_transaksi($user_id, 'PICKER');
        }
        
        return true;
    }
    
    /**
     * Process KPI queue in background (optimized batch processing)
     */
    public function process_kpi_queue() {
        if (!isset($_SESSION['kpi_queue']) || empty($_SESSION['kpi_queue'])) {
            return;
        }
        
        $queue = $_SESSION['kpi_queue'];
        $_SESSION['kpi_queue'] = []; // Clear queue
        
        // Group by user and status for batch processing
        $grouped_queue = [];
        foreach ($queue as $kpi_data) {
            $key = $kpi_data['user_id'] . '_' . ($kpi_data['status_performa_id'] ?? 'default');
            if (!isset($grouped_queue[$key])) {
                $grouped_queue[$key] = [
                    'user_id' => $kpi_data['user_id'],
                    'status_performa_id' => $kpi_data['status_performa_id'],
                    'tipe_transaksi' => $kpi_data['tipe_transaksi'],
                    'count' => 0
                ];
            }
            $grouped_queue[$key]['count']++;
        }
        
        // Process grouped queue
        foreach ($grouped_queue as $group) {
            try {
                if ($group['status_performa_id']) {
                    // Call once with total count for batch processing
                    $this->log_kpi_transaksi_with_status($group['user_id'], $group['tipe_transaksi'], $group['status_performa_id'], $group['count']);
                } else {
                    $this->log_kpi_transaksi_batch($group['user_id'], $group['tipe_transaksi'], $group['count']);
                }
            } catch (Exception $e) {
                log_message('error', 'Error processing KPI queue: ' . $e->getMessage());
            }
        }
    }

    /**
     * Log transaksi picking ke tblkpi untuk KPI tracking (optimized)
     */
    private function log_kpi_transaksi($user_id, $tipe_transaksi) {
        try {
            $tanggal = date('Y-m-d');
            
            // Ambil id_statusperforma dari log status performa user hari ini (optimized query)
            $status_log = $this->db
                ->select('id_statusperforma')
                ->get_where('tblstatusperforma', [
                    'id_user' => $user_id,
                    'tanggal' => $tanggal,
                    'isactive' => 1
                ])
                ->row();
            
            if (!$status_log) {
                // Jika tidak ada log status performa, skip (tidak log warning untuk performa)
                return false;
            }
            
            // Cek apakah sudah ada log transaksi untuk kombinasi ini
            $existing_log = $this->db
                ->get_where('tblkpi', [
                    'id_user' => $user_id,
                    'id_statusperforma' => $status_log->id_statusperforma,
                    'tanggal' => $tanggal,
                    'tipe_transaksi' => $tipe_transaksi
                ])
                ->row();
            
            if ($existing_log) {
                // Update: increment jumlah_resi, waktu created TETAP (scan pertama kali)
                $this->db->where('id_log', $existing_log->id_log);
                $this->db->set('jumlah_resi', 'jumlah_resi + 1', FALSE);
                $this->db->set('updated', date('Y-m-d H:i:s'));
                $this->db->set('updatedby', $user_id);
                $this->db->update('tblkpi');
            } else {
                // Insert: log transaksi baru (scan pertama kali untuk kombinasi ini)
                $this->db->insert('tblkpi', [
                    'id_user' => $user_id,
                    'id_statusperforma' => $status_log->id_statusperforma,
                    'tanggal' => $tanggal,
                    'tipe_transaksi' => $tipe_transaksi,
                    'jumlah_resi' => 1,
                    'createdby' => $user_id,
                    'created' => date('Y-m-d H:i:s')
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'Error in log_kpi_transaksi: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log transaksi picking ke tblkpi dengan status performa yang dipilih (optimized)
     */
    private function log_kpi_transaksi_with_status($user_id, $tipe_transaksi, $status_performa_id, $count = 1, $id_resi = null) {
        try {
            $tanggal = date('Y-m-d');
            
            log_message('debug', 'Logging KPI: user_id=' . $user_id . ', status_performa_id=' . $status_performa_id . ', tipe=' . $tipe_transaksi . ', tanggal=' . $tanggal . ', count=' . $count . ', id_resi=' . $id_resi);
            
            // Cek apakah sudah ada log transaksi untuk kombinasi ini
            $existing_log = $this->db
                ->get_where('tblkpi', [
                    'id_user' => $user_id,
                    'id_statusperforma' => $status_performa_id,
                    'tanggal' => $tanggal,
                    'tipe_transaksi' => $tipe_transaksi
                ])
                ->row();
            
            if ($existing_log) {
                // Update: increment jumlah_resi, waktu created TETAP (scan pertama kali)
                $this->db->where('id_log', $existing_log->id_log);
                $this->db->set('jumlah_resi', 'jumlah_resi + ' . $count, FALSE);
                $this->db->set('updated', date('Y-m-d H:i:s'));
                $this->db->set('updatedby', $user_id);
                $this->db->update('tblkpi');
                log_message('debug', 'KPI updated: affected_rows=' . $this->db->affected_rows());
            } else {
                // Insert: log transaksi baru (scan pertama kali untuk kombinasi ini)
                $this->db->insert('tblkpi', [
                    'id_user' => $user_id,
                    'id_statusperforma' => $status_performa_id,
                    'tanggal' => $tanggal,
                    'tipe_transaksi' => $tipe_transaksi,
                    'jumlah_resi' => $count,
                    'createdby' => $user_id,
                    'created' => date('Y-m-d H:i:s')
                ]);
                log_message('debug', 'KPI inserted: affected_rows=' . $this->db->affected_rows());
            }
            return true;
        } catch (Exception $e) {
            // Log error tapi jangan stop proses
            log_message('error', 'Error log_kpi_transaksi_with_status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log KPI transaksi dengan batch processing (optimized)
     */
    private function log_kpi_transaksi_batch($user_id, $tipe_transaksi, $count) {
        try {
            $tanggal = date('Y-m-d');
            
            // Ambil id_statusperforma dari log status performa user hari ini
            $status_log = $this->db
                ->select('id_statusperforma')
                ->get_where('tblstatusperforma', [
                    'id_user' => $user_id,
                    'tanggal' => $tanggal,
                    'isactive' => 1
                ])
                ->row();
            
            if (!$status_log) {
                return false;
            }
            
            // Gunakan INSERT ... ON DUPLICATE KEY UPDATE dengan batch count
            $this->db->query("
                INSERT INTO tblkpi (id_user, id_statusperforma, tanggal, tipe_transaksi, jumlah_resi, createdby, created)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                jumlah_resi = jumlah_resi + ?,
                updated = ?,
                updatedby = ?
            ", [
                $user_id, 
                $status_log->id_statusperforma, 
                $tanggal, 
                $tipe_transaksi, 
                $count,
                $user_id, 
                date('Y-m-d H:i:s'),
                $count,
                date('Y-m-d H:i:s'),
                $user_id
            ]);
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'Error in log_kpi_transaksi_batch: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log KPI transaksi dengan status performa dan batch processing (optimized)
     */
    private function log_kpi_transaksi_with_status_batch($user_id, $tipe_transaksi, $status_performa_id, $count) {
        try {
            $tanggal = date('Y-m-d');
            
            // Gunakan INSERT ... ON DUPLICATE KEY UPDATE dengan batch count
            $this->db->query("
                INSERT INTO tblkpi (id_user, id_statusperforma, tanggal, tipe_transaksi, jumlah_resi, createdby, created)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                jumlah_resi = jumlah_resi + ?,
                updated = ?,
                updatedby = ?
            ", [
                $user_id, 
                $status_performa_id, 
                $tanggal, 
                $tipe_transaksi, 
                $count,
                $user_id, 
                date('Y-m-d H:i:s'),
                $count,
                date('Y-m-d H:i:s'),
                $user_id
            ]);
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'Error in log_kpi_transaksi_with_status_batch: ' . $e->getMessage());
            return false;
        }
    }
}

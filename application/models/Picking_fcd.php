<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Picking_fcd extends CI_Model
{

    function get_picker($picker_status_aktif = null)
    {
        if (!empty($picker_status_aktif)) {
            $criterias['t.status_aktif'] = $picker_status_aktif;
        }

        $criterias['t1.status_aktif'] = 'AKTIF';

        $this->db->select('t.*, t1.nama_pegawai');

        $this->db->join('tblpegawai t1', 't1.kode_pegawai = t.id_pegawai');

        $this->db->where($criterias);

        $this->db->order_by('t1.nama_pegawai');

        return $this->db->get('tblnamaambilbarang t');
    }

    function save($picking, $user, $mode = PICKING_INSERT_PACKER)
    {
        // 1. Check if noresi exists and get status (now expecting single record since noresi is unique)
        $receipt = $this->db
            ->select('id_printresi, status_pesanan')
            ->get_where('tblprintresi', ['noresi' => $picking['noresi']])
            ->row(); // Changed from result_array() to row() since noresi is now unique

        if (empty($receipt)) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak ditemukan'];
        }

        // 2. Check if status_pesanan is COMPLETED or CANCELED
        if (in_array($receipt->status_pesanan, ['COMPLETED', 'CANCELED'])) {
            return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi tidak dapat diproses karena status pesanan sudah ' . $receipt->status_pesanan];
        }

        unset($picking['noresi']);

        $id_resi = $receipt->id_printresi; // Single ID instead of array

        // 3. Check if id_resi exists in tblresiambilbarang
        $picking_exist = $this->db
            ->get_where('tblresiambilbarang', ['id_resi' => $id_resi])
            ->row(); // Changed to row() since we're checking single record

        if ($mode == PICKING_INSERT_PACKER) {
            if (!empty($picking_exist)) {
                return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor resi sudah diambil. Silakan Cek data'];
            }

            // Insert single record instead of batch
            $insert_data = [
                'id_resi' => $id_resi,
                'tanggal_resiambilbarang' => date('Y-m-d H:i:s'),
                'admin_pegawai' => $user['id_user'],
                'yangambil_pegawai' => $picking['yangambil_pegawai'],
                'nama_komputer' => $user['nama_komputer'],
                'pending' => $picking['pending'],
            ];

            $this->db->insert('tblresiambilbarang', $insert_data);
            $picking['affected_rows'] = $this->db->affected_rows();
            
            // Log transaksi ke tblkpi untuk KPI tracking
            if (isset($picking['status_performa_id'])) {
                $this->log_kpi_transaksi_with_status($user['id_user'], 'PICKER', $picking['status_performa_id']);
            } else {
                $this->log_kpi_transaksi($user['id_user'], 'PICKER');
            }

        } else if ($mode == PICKING_UPDATE_PACKER) {
            if (empty($picking_exist)) {
                return ['error' => TRUE, 'code' => 400, 'message' => 'Nomor Resi belum di-picker. Silakan Cek data'];
            }

            // Update single record instead of batch
            $update_data = [
                'tanggal_resiambilbarang' => date('Y-m-d H:i:s'),
                'admin_pegawai' => $user['id_user'],
                'yangambil_pegawai' => $picking['yangambil_pegawai'],
                'nama_komputer' => $user['nama_komputer']
            ];

            $this->db->where('id_resiambilbarang', $picking_exist->id_resiambilbarang);
            $this->db->update('tblresiambilbarang', $update_data);
            $picking['affected_rows'] = $this->db->affected_rows();
        }

        return $picking;
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
        $this->db->select('count(1) as total_scan');

        $criterias = [
            'tanggal_resiambilbarang >= ' => date('Y-m-d'),
            'admin_pegawai' => $id_user,
        ];

        $this->db->where($criterias);

        return $this->db->get_where('tblresiambilbarang');
    }

    /**
     * Log transaksi picking ke tblkpi untuk KPI tracking
     */
    private function log_kpi_transaksi($user_id, $tipe_transaksi) {
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
            // Jika tidak ada log status performa, skip
            log_message('warning', "User {$user_id} melakukan {$tipe_transaksi} tanpa status performa");
            return false;
        }
        
        // Cek apakah sudah ada log transaksi untuk user, status, tanggal, dan tipe ini
        $existing_log = $this->db
            ->get_where('tblkpi', [
                'id_user' => $user_id,
                'id_statusperforma' => $status_log->id_statusperforma,
                'tanggal' => $tanggal,
                'tipe_transaksi' => $tipe_transaksi
            ])
            ->row();
        
        if ($existing_log) {
            // Update: increment jumlah_resi
            $this->db->where('id_log', $existing_log->id_log);
            $this->db->set('jumlah_resi', 'jumlah_resi + 1', FALSE);
            $this->db->set('updated', date('Y-m-d H:i:s'));
            $this->db->set('updatedby', $user_id);
            $this->db->update('tblkpi');
        } else {
            // Insert: log transaksi baru
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
    }
    
    /**
     * Log transaksi picking ke tblkpi dengan status performa yang dipilih
     */
    private function log_kpi_transaksi_with_status($user_id, $tipe_transaksi, $status_performa_id) {
        try {
            $tanggal = date('Y-m-d');
            
            // Cek apakah sudah ada log transaksi untuk user, status, tanggal, dan tipe ini
            $this->db->select('id_log, jumlah_resi');
            $this->db->where('id_user', $user_id);
            $this->db->where('id_statusperforma', $status_performa_id);
            $this->db->where('tanggal', $tanggal);
            $this->db->where('tipe_transaksi', $tipe_transaksi);
            $existing_log = $this->db->get('tblkpi')->row();
            
            if ($existing_log) {
                // Update: increment jumlah_resi
                $this->db->where('id_log', $existing_log->id_log);
                $this->db->set('jumlah_resi', 'jumlah_resi + 1', FALSE);
                $this->db->set('updated', date('Y-m-d H:i:s'));
                $this->db->set('updatedby', $user_id);
                $this->db->update('tblkpi');
            } else {
                // Insert: log transaksi baru
                $this->db->insert('tblkpi', [
                    'id_user' => $user_id,
                    'id_statusperforma' => $status_performa_id,
                    'tanggal' => $tanggal,
                    'tipe_transaksi' => $tipe_transaksi,
                    'jumlah_resi' => 1,
                    'createdby' => $user_id,
                    'created' => date('Y-m-d H:i:s')
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            // Log error tapi jangan stop proses
            log_message('error', 'Error log_kpi_transaksi_with_status: ' . $e->getMessage());
            return false;
        }
    }
}

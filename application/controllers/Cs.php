<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cs extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('receipt_fcd');
        $this->load->model('retur_fcd');
        $this->load->model('packer_fcd');
    }

    public function laporan_kurangan_picker()
    {
        $data['message'] = $this->session->flashdata('message');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $data['reportrange'] = $reportrange;

        $this->show($data);
    }

    public function get_laporan_kurangan_picker_data()
    {
        $reportrange = $this->input->post('reportrange');
        
        if (empty($reportrange)) {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        }

        $dates = explode(' - ', $reportrange);
        $start_date = trim($dates[0]);
        $end_date = trim($dates[1]);

        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'] ?? '';

        $col = 0;
        $dir = '';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $data['dir'] = $dir;
        $data['valid_columns'] = [
            0 => null,
            1 => 'dr.sku',
            2 => 'pr.noresi',
            3 => 'm.nama_marketplace',
            4 => 'pr.tanggal_printresi',
            5 => 'pr.tanggal_bataskirim',
            6 => 'dr.qty_kurang',
            7 => null, // checkbox
            8 => null  // action
        ];
        $data['order'] = isset($data['valid_columns'][$col]) ? $data['valid_columns'][$col] : null;

        // Build base query for counting total - per resi detail
        $this->db->select('dr.id_detail_resi');
        $this->db->from('tbldetailprintresi dr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = dr.id_resi', 'inner');
        $this->db->join('tblmarketplace m', 'm.id_marketplace = pr.id_marketplace', 'left');
        $this->db->where('dr.status_kurangan', 'Ya');
        // Filter berdasarkan tanggal scan kurangan, jika tidak ada gunakan tanggal_printresi sebagai fallback
        $this->db->where("(dr.tanggal_scan_kurangan >= '$start_date' AND dr.tanggal_scan_kurangan <= '$end_date') OR (dr.tanggal_scan_kurangan IS NULL AND pr.tanggal_printresi >= '$start_date' AND pr.tanggal_printresi <= '$end_date')", NULL, FALSE);
        
        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('dr.sku', $data['search']);
            $this->db->or_like('pr.noresi', $data['search']);
            $this->db->or_like('m.nama_marketplace', $data['search']);
            $this->db->group_end();
        }
        
        // Get total count
        $total = $this->db->count_all_results();
        
        // Now build query for data - per resi detail (bukan group by)
        $this->db->select('
            dr.id_detail_resi,
            dr.sku,
            dr.qty_kurang,
            dr.tanggal_scan_kurangan,
            pr.noresi,
            pr.id_printresi,
            pr.tanggal_printresi,
            pr.tanggal_bataskirim,
            m.nama_marketplace,
            COALESCE(s.nama_sku, dr.sku) as nama_barang
        ');
        $this->db->from('tbldetailprintresi dr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = dr.id_resi', 'inner');
        $this->db->join('tblmarketplace m', 'm.id_marketplace = pr.id_marketplace', 'left');
        $this->db->join('tblsku s', 's.id_sku = dr.sku', 'left');
        $this->db->where('dr.status_kurangan', 'Ya');
        // Filter berdasarkan tanggal scan kurangan, jika tidak ada gunakan tanggal_printresi sebagai fallback
        $this->db->where("(dr.tanggal_scan_kurangan >= '$start_date' AND dr.tanggal_scan_kurangan <= '$end_date') OR (dr.tanggal_scan_kurangan IS NULL AND pr.tanggal_printresi >= '$start_date' AND pr.tanggal_printresi <= '$end_date')", NULL, FALSE);
        
        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('dr.sku', $data['search']);
            $this->db->or_like('pr.noresi', $data['search']);
            $this->db->or_like('m.nama_marketplace', $data['search']);
            $this->db->group_end();
        }
        
        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir']);
        } else {
            // Order by tanggal scan kurangan jika ada, jika tidak gunakan tanggal print resi
            $this->db->order_by('COALESCE(dr.tanggal_scan_kurangan, pr.tanggal_printresi)', 'DESC');
        }

        // Apply limit
        if ($data['length'] > 0) {
            $this->db->limit($data['length'], $data['start']);
        }

        $query = $this->db->get();
        $items = $query->result_array();

        $table_number = $data['start'] + 1;
        $data_table = [];

        foreach ($items as $item) {
            $id_detail = $item['id_detail_resi'];
            $noresi = $item['noresi'];
            
            $data_table[] = [
                $table_number++ . '.',
                $item['sku'] ?? '-',
                $item['noresi'] ?? '-',
                $item['nama_marketplace'] ?? '-',
                !empty($item['tanggal_printresi']) ? date('d/m/Y', strtotime($item['tanggal_printresi'])) : '-',
                !empty($item['tanggal_bataskirim']) ? date('d/m/Y', strtotime($item['tanggal_bataskirim'])) : '-',
                $item['qty_kurang'] ?? 0,
                '<input type="checkbox" class="row-select" data-id-detail="' . $id_detail . '" data-noresi="' . htmlspecialchars($noresi, ENT_QUOTES, 'UTF-8') . '" />',
                '<button type="button" class="btn btn-sm btn-success btn-submit-resi" data-id-detail="' . $id_detail . '" data-noresi="' . htmlspecialchars($noresi, ENT_QUOTES, 'UTF-8') . '">Submit</button>'
            ];
        }

        $output = [
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data_table
        ];

        header('Content-Type: application/json');
        echo json_encode($output);
        exit();
    }

    public function submit_kurangan_picker()
    {
        if ($this->input->method() == 'get') {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 400, 'message' => INVALID_REQUEST_METHOD));
            exit();
        }

        $selected_items = $this->input->post('selected_items'); // Array of id_detail_resi
        $noresi = $this->input->post('noresi'); // Optional: single noresi for single submit

        if (empty($selected_items) && empty($noresi)) {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 400, 'message' => 'Tidak ada data yang dipilih'));
            exit();
        }

        // Jika submit per resi (single)
        if (!empty($noresi)) {
            // Update status untuk semua item kurangan di resi tersebut
            $this->db->select('dr.id_detail_resi');
            $this->db->from('tbldetailprintresi dr');
            $this->db->join('tblprintresi pr', 'pr.id_printresi = dr.id_resi', 'inner');
            $this->db->where('pr.noresi', $noresi);
            $this->db->where('dr.status_kurangan', 'Ya');
            $query = $this->db->get();
            $items = $query->result_array();
            
            $saved_count = 0;
            foreach ($items as $item) {
                // Update status atau tambahkan flag bahwa sudah di-submit CS
                // Misalnya update status_kurangan menjadi 'Sudah Diproses' atau tambahkan kolom baru
                // Untuk sementara, kita bisa update qty_kurang atau tambahkan timestamp
                $this->db->where('id_detail_resi', $item['id_detail_resi']);
                $this->db->update('tbldetailprintresi', [
                    'status_kurangan' => 'Sudah Diproses' // atau bisa tetap 'Ya' dan tambah kolom baru
                ]);
                if ($this->db->affected_rows() > 0) {
                    $saved_count++;
                }
            }
        } else {
            // Submit multiple items
            $saved_count = 0;
            foreach ($selected_items as $id_detail_resi) {
                $this->db->where('id_detail_resi', $id_detail_resi);
                $this->db->where('status_kurangan', 'Ya');
                $this->db->update('tbldetailprintresi', [
                    'status_kurangan' => 'Sudah Diproses'
                ]);
                if ($this->db->affected_rows() > 0) {
                    $saved_count++;
                }
            }
        }

        header('Content-Type: application/json');
        if ($saved_count > 0) {
            echo json_encode(array('code' => 201, 'message' => 'Data berhasil disubmit (' . $saved_count . ' item)'));
        } else {
            echo json_encode(array('code' => 200, 'message' => 'Tidak ada data yang diupdate'));
        }
        exit();
    }

    public function export_excel_laporan_kurangan_picker()
    {
        ini_set('memory_limit', '-1');
        
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $dates = explode(' - ', $reportrange);
        $start_date = trim($dates[0]);
        $end_date = trim($dates[1]);

        // Query untuk mendapatkan data kurangan picker
        $this->db->select('
            dr.sku,
            COUNT(DISTINCT pr.noresi) as jumlah_resi,
            SUM(dr.qty_kurang) as total_qty_kurang,
            GROUP_CONCAT(DISTINCT m.nama_marketplace ORDER BY m.nama_marketplace SEPARATOR ", ") as marketplace,
            MIN(COALESCE(dr.tanggal_scan_kurangan, pr.tanggal_printresi)) as tgl_cetak,
            MIN(pr.tanggal_bataskirim) as b_akhir_kirim
        ');
        $this->db->from('tbldetailprintresi dr');
        $this->db->join('tblprintresi pr', 'pr.id_printresi = dr.id_resi', 'inner');
        $this->db->join('tblmarketplace m', 'm.id_marketplace = pr.id_marketplace', 'left');
        $this->db->where('dr.status_kurangan', 'Ya');
        // Filter berdasarkan tanggal scan kurangan, jika tidak ada gunakan tanggal_printresi sebagai fallback
        $this->db->group_start();
        $this->db->where('dr.tanggal_scan_kurangan >=', $start_date);
        $this->db->where('dr.tanggal_scan_kurangan <=', $end_date);
        $this->db->or_group_start();
        $this->db->where('dr.tanggal_scan_kurangan IS NULL');
        $this->db->where('pr.tanggal_printresi >=', $start_date);
        $this->db->where('pr.tanggal_printresi <=', $end_date);
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_by('dr.sku');
        $this->db->order_by('jumlah_resi', 'DESC');

        $query = $this->db->get();
        $data['list_data'] = $query->result_array();
        $data['reportrange'] = $reportrange;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Kurangan_Picker_" . date('Y-m-d') . ".xls");

        $this->load->view('template_report/laporan_kurangan_picker', $data);
    }

    public function retur_complain()
    {
        $data['message'] = $this->session->flashdata('message');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $data['reportrange'] = $reportrange;

        $this->show($data);
    }

    public function get_retur_complain_data()
    {
        $reportrange = $this->input->post('reportrange');
        
        if (empty($reportrange)) {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        }

        $dates = explode(' - ', $reportrange);
        $start_date = trim($dates[0]);
        $end_date = trim($dates[1]);

        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $params['start'] = intval($this->input->post('start'));
        $params['length'] = intval($this->input->post('length'));
        $params['search'] = $this->input->post('search')['value'] ?? '';
        $params['status_filter'] = $this->input->post('status_filter');
        $params['reportrange'] = $reportrange;

        $col = 0;
        $dir = 'desc';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $params['dir'] = $dir;
        $params['valid_columns'] = array(
            0 => null,
            1 => 'rc.noresi',
            2 => 'rc.customer_name',
            3 => 'rc.marketplace',
            4 => 'rc.created_at',
            5 => 'rc.updated_at',
            6 => 'rc.complain_type',
            7 => 'rc.status'
        );
        $params['order'] = isset($params['valid_columns'][$col]) ? $params['valid_columns'][$col] : 'rc.updated_at';

        // Get data complain yang sudah diubah statusnya (bukan TO_DO)
        $list = $this->retur_fcd->get_complain_report_list($params);
        $total = $this->retur_fcd->get_total_complain_report_list($params);

        $rows = array();
        $no = $params['start'] + 1;
        foreach ($list->result() as $row) {
            $complain_type_label = ucfirst($row->complain_type);
            if ($row->complain_type === 'refund') {
                $complain_type_label = 'Refund Dana';
            } elseif ($row->complain_type === 'replacement') {
                $complain_type_label = 'Pergantian Barang';
            }

            $status_labels = array(
                'TO_DO' => 'To Do',
                'WAITING_CUSTOMER' => 'Waiting Customer',
                'REFUND_DANA' => 'Refund Dana',
                'PERGANTIAN_BARANG' => 'Pergantian Barang',
                'EXPIRED' => 'Expired'
            );
            $status_label = isset($status_labels[$row->status]) ? $status_labels[$row->status] : $row->status;

            // Use marketplace from table if available, otherwise use marketplace_name from join
            $marketplace_display = !empty($row->marketplace) ? $row->marketplace : ($row->marketplace_name ?: '-');

            $rows[] = array(
                $no++ . '.',
                $row->noresi,
                $row->customer_name ?: '-',
                $marketplace_display,
                date('d/m/Y H:i', strtotime($row->created_at)),
                date('d/m/Y H:i', strtotime($row->updated_at)),
                $complain_type_label,
                $status_label,
                $row->notes ?: '-',
                !empty($row->refund_amount) ? number_format($row->refund_amount, 0, ',', '.') : '-',
                !empty($row->replacement_sku) ? $row->replacement_sku . ' (Qty: ' . $row->replacement_qty . ')' : '-'
            );
        }

        $output = array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows
        );

        header('Content-Type: application/json');
        echo json_encode($output);
        exit();
    }

    public function export_excel_retur_complain()
    {
        ini_set('memory_limit', '-1');
        
        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $dates = explode(' - ', $reportrange);
        $start_date = trim($dates[0]);
        $end_date = trim($dates[1]);

        // Get all complain data yang sudah diubah statusnya
        $params = array(
            'start' => 0,
            'length' => 0, // Get all - no limit
            'search' => '',
            'status_filter' => 'ALL',
            'reportrange' => $reportrange,
            'dir' => 'desc',
            'order' => 'rc.updated_at'
        );

        $query = $this->retur_fcd->get_complain_report_list($params);
        $data['list_data'] = $query->result_array();
        $data['reportrange'] = $reportrange;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Retur_Complain_" . date('Y-m-d') . ".xls");

        $this->load->view('template_report/retur_complain', $data);
    }

    public function masalah_picker()
    {
        $data['message'] = $this->session->flashdata('message');

        $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        if ($this->input->method() == 'post') {
            $reportrange = $this->input->post('reportrange');
        }

        $data['reportrange'] = $reportrange;

        $this->show($data);
    }

    public function get_masalah_picker_data()
    {
        $reportrange = $this->input->post('reportrange');
        
        if (empty($reportrange)) {
            $reportrange = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
        }

        $dates = explode(' - ', $reportrange);
        $start_date = trim($dates[0]);
        $end_date = trim($dates[1]);

        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'] ?? '';

        $col = 0;
        $dir = '';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $data['dir'] = $dir;
        $data['valid_columns'] = [
            0 => null,
            1 => 'mp.noresi',
            2 => 'mp.sku',
            3 => 'mp.qty',
            4 => 'mp.qty_bermasalah',
            5 => 'tm.type_masalah',
            6 => 'mp.created',
            7 => null  // action
        ];
        $data['order'] = isset($data['valid_columns'][$col]) ? $data['valid_columns'][$col] : 'mp.created';

        // Build base query for counting total
        $this->db->select('mp.id_masalahpicker');
        $this->db->from('tblmasalahpicker mp');
        $this->db->join('tbltypemasalah tm', 'tm.id_typemasalah = mp.id_typemasalah', 'left');
        $this->db->where('mp.created >=', $start_date);
        $this->db->where('mp.created <=', $end_date);
        
        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('mp.sku', $data['search']);
            $this->db->or_like('mp.noresi', $data['search']);
            $this->db->or_like('tm.type_masalah', $data['search']);
            $this->db->or_like('mp.sku_salah', $data['search']);
            $this->db->group_end();
        }
        
        // Get total count
        $total = $this->db->count_all_results();
        
        // Now build query for data
        $this->db->select('
            mp.id_masalahpicker,
            mp.noresi,
            mp.sku,
            mp.qty,
            mp.qty_bermasalah,
            mp.sku_salah,
            mp.created,
            tm.type_masalah,
            COALESCE(s.nama_sku, mp.sku) as nama_barang,
            COALESCE(peg.nama_pegawai, u.name) as nama_picker
        ');
        $this->db->from('tblmasalahpicker mp');
        $this->db->join('tbltypemasalah tm', 'tm.id_typemasalah = mp.id_typemasalah', 'left');
        $this->db->join('tblsku s', 's.id_sku = mp.sku', 'left');
        $this->db->join('tbluser u', 'u.id_user = mp.created_by', 'left');
        $this->db->join('tblpegawai peg', 'peg.kode_pegawai = u.id_pegawai', 'left');
        $this->db->where('mp.created >=', $start_date);
        $this->db->where('mp.created <=', $end_date);
        
        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('mp.sku', $data['search']);
            $this->db->or_like('mp.noresi', $data['search']);
            $this->db->or_like('tm.type_masalah', $data['search']);
            $this->db->or_like('mp.sku_salah', $data['search']);
            $this->db->group_end();
        }
        
        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir']);
        } else {
            $this->db->order_by('mp.created', 'DESC');
        }

        // Apply limit
        if ($data['length'] > 0) {
            $this->db->limit($data['length'], $data['start']);
        }

        $query = $this->db->get();
        $items = $query->result_array();

        $table_number = $data['start'] + 1;
        $data_table = [];

        foreach ($items as $item) {
            $data_table[] = [
                $table_number++ . '.',
                $item['noresi'] ?? '-',
                $item['sku'] ?? '-',
                $item['nama_barang'] ?? '-',
                $item['qty'] ?? 0,
                $item['qty_bermasalah'] ?? 0,
                $item['type_masalah'] ?? '-',
                $item['nama_picker'] ?? '-',
                !empty($item['created']) ? date('d/m/Y H:i', strtotime($item['created'])) : '-',
                '<button type="button" class="btn btn-sm btn-info btn-detail-masalah" data-id="' . $item['id_masalahpicker'] . '">Detail</button>'
            ];
        }

        $output = [
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data_table
        ];

        header('Content-Type: application/json');
        echo json_encode($output);
        exit();
    }

    public function get_detail_masalah_picker()
    {
        if ($this->input->method() == 'get') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => INVALID_REQUEST_METHOD]);
            exit();
        }

        $id = $this->input->post('id');

        if (empty($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
            exit();
        }

        $this->db->select('
            mp.id_masalahpicker,
            mp.noresi,
            mp.sku,
            mp.qty,
            mp.qty_bermasalah,
            mp.sku_salah,
            mp.created,
            mp.updated,
            tm.type_masalah,
            COALESCE(s.nama_sku, mp.sku) as nama_barang,
            COALESCE(peg.nama_pegawai, u.name) as nama_picker
        ');
        $this->db->from('tblmasalahpicker mp');
        $this->db->join('tbltypemasalah tm', 'tm.id_typemasalah = mp.id_typemasalah', 'left');
        $this->db->join('tblsku s', 's.id_sku = mp.sku', 'left');
        $this->db->join('tbluser u', 'u.id_user = mp.created_by', 'left');
        $this->db->join('tblpegawai peg', 'peg.kode_pegawai = u.id_pegawai', 'left');
        $this->db->where('mp.id_masalahpicker', $id);
        $query = $this->db->get();
        $result = $query->row_array();

        if ($result) {
            // Format tanggal
            if (!empty($result['created'])) {
                $result['created'] = date('d/m/Y H:i:s', strtotime($result['created']));
            }
            if (!empty($result['updated'])) {
                $result['updated'] = date('d/m/Y H:i:s', strtotime($result['updated']));
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        exit();
    }
}


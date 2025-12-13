<?php

class Picker extends MY_Controller
{
    private static $status_cache = array(); // Simple static cache for status IDs
    
    // Clear cache method for debugging
    public function clear_status_cache() {
        self::$status_cache = array();
        echo "Status cache cleared";
    }
    
    function __construct()
    {
        parent::__construct();

        $this->load->model('picking_fcd');
        $this->load->model('employee_fcd');
    }

    public function scan_picker()
    {
        $this->load->model('kpi_fcd');
        
        // Optimize: Load data in parallel
        $data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();
        
        // Get total scan with fallback
        $total_scan_result = $this->picking_fcd->get_total_scan_user($this->data['user']['id_user'])->row();
        $data['total_scan'] = $total_scan_result ? $total_scan_result->total_scan : 0;
        
        // Ambil data status performa untuk PICKER
        $list_status_performa_raw = $this->kpi_fcd->get_status_performa_by_kategori('PICKER')->result_array();
        $data['list_status_performa'] = $list_status_performa_raw;

        $this->show($data);
    }

    public function save_scan_picker()
    {
        if ($this->input->method() == 'get') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $picking['noresi'] = $this->input->post('noresi');
        $picking['yangambil_pegawai'] = $this->input->post('id_pegawaipicker');
        $picking['pending'] = '';
        
        // Debug: Tampilkan semua data POST
        $post_data = $this->input->post();
        log_message('debug', 'All POST data: ' . json_encode($post_data));
        
        // Ambil status performa yang dipilih (optimized with static cache)
        $status_performa_code = $this->input->post('status_performa');
        log_message('debug', 'Status performa code dari POST: ' . $status_performa_code);
        
        if (!empty($status_performa_code)) {
            // Check static cache first
            if (!isset(self::$status_cache[$status_performa_code])) {
                $this->load->model('kpi_fcd');
                $status_id = $this->kpi_fcd->get_status_id_by_name($status_performa_code);
                self::$status_cache[$status_performa_code] = $status_id;
                log_message('debug', 'Status ID dari database: ' . $status_id . ' untuk kode: ' . $status_performa_code);
            }
            
            $status_id = self::$status_cache[$status_performa_code];
            if ($status_id) {
                $picking['status_performa_id'] = $status_id;
                log_message('debug', 'Status performa ID: ' . $status_id . ' untuk kode: ' . $status_performa_code);
            } else {
                log_message('debug', 'Status performa ID tidak ditemukan untuk kode: ' . $status_performa_code);
            }
        } else {
            // Jika tidak ada status yang dipilih, gunakan NORMAL sebagai default
            $this->load->model('kpi_fcd');
            $normal_status_id = $this->kpi_fcd->get_status_id_by_name('NORMAL_PICKER');
            if ($normal_status_id) {
                $picking['status_performa_id'] = $normal_status_id;
                log_message('debug', 'Menggunakan status NORMAL sebagai default: ' . $normal_status_id);
            }
        }

        // Debug: Tampilkan data yang akan disimpan
        log_message('debug', 'Data picking yang akan disimpan: ' . json_encode($picking));
        log_message('debug', 'User data: ' . json_encode($this->data['user']));

        $save = $this->picking_fcd->save($picking, $this->data['user']);

        if (isset($save['error'])) {
            $this->make_ajax_response($save['code'], $save['message']);
        }

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }

    public function search_picker()
    {
        $this->show();
    }

    public function process_kpi_queue()
    {
        // Process KPI queue in background
        $this->picking_fcd->process_kpi_queue();
        $this->make_ajax_response(200, 'KPI queue processed');
    }

    public function get_search_picker_data()
    {
        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $col = 0;
        $dir = '';
        if (!empty($order)) {
            foreach ($order as $o) {
                $col = $o['column'];
                $dir = $o['dir'];
            }
        }

        $data['dir'] = $dir;

        $data['valid_columns'] = array(
            0 => null,
            1 => 't2.noresi',
            2 => 't3.nama_pegawai',
            3 => 't.tanggal_resiambilbarang',
            4 => 't.tanggal_resiambilbarang',
            5 => 't4.name',
            6 => null,
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->picking_fcd->get_data($data);

        $total = $this->picking_fcd->get_total_data($data);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                $row->nama_pegawai,
                date('Y-m-d', strtotime($row->tanggal_resiambilbarang)),
                date('H:i:s', strtotime($row->tanggal_resiambilbarang)),
                $row->name,
                $row->nama_komputer,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }

    public function master_picker()
    {
        $data['message'] = $this->session->flashdata('message');
        $data['list_employee'] = $this->employee_fcd->get_employee()->result_array();
        $data['list_status'] = ['AKTIF', 'TIDAK AKTIF'];
        $data['list_picker'] = $this->picking_fcd->get_picker()->result_array();

        $this->show($data);
    }

    public function save_master_picker()
    {
        if ($this->input->method() == 'get') {
            redirect('404_override');
        }

        $picker['id_pegawai'] =  $this->input->post('id_pegawai');
        $picker['status_aktif'] =  $this->input->post('status_aktif');

        $save = $this->picking_fcd->save_picker($picker);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', SUCCESS_SAVE_DATA, 'information');
        } else {
            $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
        }

        redirect('picker/master_picker');
    }

    public function delete_master_picker($id_namaambilbarang)
    {
        $save = $this->picking_fcd->destroy_picker($id_namaambilbarang);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
        } else {
            $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
        }

        redirect('picker/master_picker');
    }

    public function pending_picker()
    {
        $data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();

        $this->show($data);
    }

    public function save_pending_picker()
    {
        if ($this->input->method() == 'get') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $picking['noresi'] = $this->input->post('noresi');
        $picking['yangambil_pegawai'] = $this->input->post('id_pegawaipicker');
        $picking['pending'] = 'ya';

        $save = $this->picking_fcd->save($picking, $this->data['user']);

        if (isset($save['error'])) {
            $this->make_ajax_response($save['code'], $save['message']);
        }

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }

    public function update_picker()
    {
        $data['list_picker'] = $this->picking_fcd->get_picker('AKTIF')->result_array();

        $this->show($data);
    }

    public function save_update_picker()
    {
        if ($this->input->method() == 'get') {
            $this->make_ajax_response(400, INVALID_REQUEST_METHOD);
        }

        $picking['noresi'] = $this->input->post('noresi');
        $picking['yangambil_pegawai'] = $this->input->post('id_pegawaipicker');

        $save = $this->picking_fcd->save($picking, $this->data['user'], PICKING_UPDATE_PACKER);

        if (isset($save['error'])) {
            $this->make_ajax_response($save['code'], $save['message']);
        }

        if ($save['affected_rows'] > 0) {
            $this->make_ajax_response(201, SUCCESS_SAVE_DATA);
        }

        $this->make_ajax_response(200, NOTHING_TO_SAVE);
    }

    public function kurangan_picker()
    {
        $this->load->model('receipt_fcd');
        
        $data = [];
        $data['noresi'] = null;

        if ($this->input->method() == 'post') {
            $noresi = $this->input->post('noresi');
            
            if (!empty($noresi)) {
                // Check if receipt exists
                $receipt = $this->db->get_where('tblprintresi', ['noresi' => $noresi])->row_array();
                
                if (!empty($receipt)) {
                    $data['noresi'] = $noresi;
                    $data['id_printresi'] = $receipt['id_printresi'];
                } else {
                    $data['error_message'] = 'Nomor resi tidak ditemukan';
                }
            }
        }

        $this->show($data);
    }

    public function get_kurangan_picker_data($noresi = null)
    {
        // Get noresi from URL parameter or POST
        if (empty($noresi)) {
            $noresi = $this->input->post('noresi');
        }
        
        if (empty($noresi)) {
            $noresi = $this->uri->segment(3);
        }
        
        $noresi = urldecode($noresi);
        
        if (empty($noresi)) {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 400, 'message' => 'Nomor resi tidak boleh kosong'));
            exit();
        }

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
            1 => 's.nama_sku',
            2 => 'dr.sku',
            3 => 'dr.jumlah',
            4 => null
        ];
        $data['order'] = isset($data['valid_columns'][$col]) ? $data['valid_columns'][$col] : null;

        // Build base query for counting total
        $this->db->select('dr.id_detail_resi');
        $this->db->from('tblprintresi pr');
        $this->db->join('tbldetailprintresi dr', 'dr.id_resi = pr.id_printresi', 'inner');
        $this->db->join('tblsku s', 's.id_sku = dr.sku', 'left');
        $this->db->where('pr.noresi', $noresi);
        
        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('dr.sku', $data['search']);
            $this->db->or_like('s.nama_sku', $data['search']);
            $this->db->group_end();
        }
        
        // Get total count
        $total = $this->db->count_all_results();
        
        // Now build query for data with all columns
        $this->db->select('
            dr.id_detail_resi,
            dr.sku,
            dr.jumlah as qty,
            COALESCE(s.nama_sku, dr.sku) as nama_barang,
            pr.noresi,
            pr.id_printresi,
            COALESCE(dr.qty_kurang, 0) as qty_kurang
        ');
        $this->db->from('tblprintresi pr');
        $this->db->join('tbldetailprintresi dr', 'dr.id_resi = pr.id_printresi', 'inner');
        $this->db->join('tblsku s', 's.id_sku = dr.sku', 'left');
        $this->db->where('pr.noresi', $noresi);
        
        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('dr.sku', $data['search']);
            $this->db->or_like('s.nama_sku', $data['search']);
            $this->db->group_end();
        }
        
        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir']);
        } else {
            $this->db->order_by('dr.id_detail_resi', 'ASC');
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
            $qty_kurang = $item['qty_kurang'] ?? 0;
            
            $data_table[] = [
                $table_number++ . '.',
                $item['sku'] ?? '-',
                $item['nama_barang'] ?? '-',
                $item['qty'] ?? 0,
                '<input type="number" class="form-control qty_kurang" data-id-detail="' . $item['id_detail_resi'] . '" value="' . $qty_kurang . '" min="0" step="1" style="width: 100px;" />'
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

    public function save_kurangan_picker()
    {
        if ($this->input->method() == 'get') {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 400, 'message' => INVALID_REQUEST_METHOD));
            exit();
        }

        $noresi = $this->input->post('noresi');
        $items = $this->input->post('items'); // Array of items with status_kurangan and qty_kurang

        if (empty($noresi)) {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 400, 'message' => 'Nomor resi tidak boleh kosong'));
            exit();
        }

        if (empty($items) || !is_array($items)) {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 400, 'message' => 'Data items tidak valid'));
            exit();
        }

        $this->load->model('receipt_fcd');
        
        // Get receipt ID
        $receipt = $this->db->get_where('tblprintresi', ['noresi' => $noresi])->row_array();
        if (empty($receipt)) {
            header('Content-Type: application/json');
            echo json_encode(array('code' => 404, 'message' => 'Nomor resi tidak ditemukan'));
            exit();
        }

        // Save kurangan data for each item
        $saved_count = 0;
        foreach ($items as $item) {
            $id_detail_resi = $item['id_detail_resi'] ?? null;
            $qty_kurang = intval($item['qty_kurang'] ?? 0);

            if (empty($id_detail_resi)) continue;

            // Check if detail record exists
            $existing = $this->db->get_where('tbldetailprintresi', [
                'id_detail_resi' => $id_detail_resi
            ])->row_array();

            if (!empty($existing)) {
                // Update existing record - status kurangan langsung set ke 'Ya'
                $update_data = [
                    'status_kurangan' => 'Ya',
                    'qty_kurang' => $qty_kurang > 0 ? $qty_kurang : 0
                ];
                
                // Simpan tanggal scan kurangan jika belum ada atau status sebelumnya bukan 'Ya'
                if ($existing['status_kurangan'] !== 'Ya' || empty($existing['tanggal_scan_kurangan'])) {
                    $update_data['tanggal_scan_kurangan'] = date('Y-m-d H:i:s');
                }
                
                $this->db->where('id_detail_resi', $id_detail_resi);
                $this->db->update('tbldetailprintresi', $update_data);
                if ($this->db->affected_rows() > 0) {
                    $saved_count++;
                }
            }
        }

        header('Content-Type: application/json');
        if ($saved_count > 0) {
            echo json_encode(array('code' => 201, 'message' => 'Data kurangan berhasil disimpan'));
        } else {
            echo json_encode(array('code' => 200, 'message' => 'Tidak ada data yang diupdate'));
        }
        exit();
    }
}
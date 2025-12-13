<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur extends MY_Controller
{
	private $complain_statuses = array(
		'TO_DO' => 'To Do',
		'WAITING_CUSTOMER' => 'Waiting Customer',
		'REFUND_DANA' => 'Refund Dana',
		'PERGANTIAN_BARANG' => 'Pergantian Barang',
		'EXPIRED' => 'Expired'
	);

	function __construct()
	{
		parent::__construct();

		$this->load->model('retur_fcd');
		$this->load->model('marketplace_fcd');
		$this->load->model('courrier_fcd');
	}

	public function scan_retur()
	{
		// Get today's scan count for Terima Retur
		$data['total_scan_terima'] = $this->retur_fcd->get_total_scan_terima_today($this->data['user']['id_user']);
		
		// Get today's scan count for Buka Retur  
		$data['total_scan_buka'] = $this->retur_fcd->get_total_scan_buka_today($this->data['user']['id_user']);

		$this->show($data);
	}

	// request by ajax - Terima Retur
	public function save_retur()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$retur['status_retur'] = 'Terima Retur';
		$retur['status_detail'] = null; // Status detail tidak digunakan di Terima Retur
		$retur['hasil_scan'] = $this->input->post('hasil_scan');

		$save = $this->retur_fcd->save($retur, $this->data['user']['id_user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$message = $save['success_message'];
			if (isset($save['warning'])) {
				$message .= '. Peringatan: ' . $save['warning'];
			}
			$this->make_ajax_response(201, $message);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}

	// request by ajax - Buka Retur
	public function save_buka_retur()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$status_detail = $this->input->post('status_detail_buka');
		$hasil_scan = $this->input->post('hasil_scan_buka');
		
		$retur['status_retur'] = 'Buka Retur';
		$retur['status_detail'] = $status_detail;
		$retur['hasil_scan'] = $hasil_scan;

		$save = $this->retur_fcd->save($retur, $this->data['user']['id_user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$message = $save['success_message'];
			if (isset($save['warning'])) {
				$message .= '. Peringatan: ' . $save['warning'];
			}
			$this->make_ajax_response(201, $message);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}

    public function search_retur()
    {
        $data['message'] = $this->session->flashdata('message');

        $this->show($data);
    }

    // ==================== LAPORAN RETUR ====================
    public function laporan_retur()
    {
        // Get list of couriers for filter
        $data['list_kurir'] = $this->courrier_fcd->get_courrier()->result();
        
        $this->show($data);
    }

    public function get_data_retur()
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
            1 => 't.noresi',
            2 => 't.tanggal_resiretur',
            3 => 't.tanggal_resiretur',
            4 => 't2.nama_marketplace',
            5 => 't3.nama_kurir',
            6 => 't4.username',
            7 => null,
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->retur_fcd->get_data($data);

        $total = $this->retur_fcd->get_total_data($data);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                date('Y-m-d', strtotime($row->tanggal_resiretur)),
                date('H:i:s', strtotime($row->tanggal_resiretur)),
                $row->nama_marketplace,
                $row->nama_kurir,
                $row->username,
                '<a href="retur_search/delete/' . $row->id_resiretur . '" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>',
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

    public function delete_retur($id_resiretur)
    {
        $save = $this->retur_fcd->destroy($id_resiretur, $this->data['user']['id_user']);

        if ($save['affected_rows'] > 0) {
            $this->set_message('Success', SUCCESS_REMOVE_DATA, 'information');
        } else {
            $this->set_message('Warning', NOTHING_TO_SAVE, 'warning');
        }

        $this->show_index();
    }

    // ==================== LAPORAN TERIMA RETUR ====================
    public function get_data_terima_retur_laporan()
    {
        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $id_kurir = $this->input->post('id_kurir');

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
            0 => 'dp.no_pesanan',
            1 => 'tr.noresi',
            2 => 'mp.nama_marketplace',
            3 => 'kr.nama_kurir',
            4 => 'tr.tanggal_resiretur',
            5 => 'tr.tanggal_resiretur',
            6 => 'dp.sku',
            7 => 'dp.jumlah',
            8 => 'tr.status_detail'
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_retur = $this->retur_fcd->get_laporan_terima_retur($data, $start_date, $end_date, $id_kurir);
        $total = $this->retur_fcd->get_total_laporan_terima_retur($data, $start_date, $end_date, $id_kurir);

        $i = $data['start'] + 1;
        $result_data = array();
        foreach ($list_retur->result() as $row) {
            $result_data[] = array(
                $row->no_pesanan ?: '-',
                $row->noresi,
                $row->nama_marketplace ?: '-',
                $row->nama_kurir ?: '-',
                date('Y-m-d', strtotime($row->tanggal_resiretur)),
                date('H:i:s', strtotime($row->tanggal_resiretur)),
                $row->sku ?: '-',
                $row->jumlah ?: '0',
                $row->status_detail ?: '-'
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $result_data
        );
        echo json_encode($output);
        exit();
    }

    // ==================== LAPORAN BUKA RETUR ====================
    public function get_data_buka_retur_laporan()
    {
        $draw = intval($this->input->post('draw'));
        $order = $this->input->post('order');

        $data['start'] = intval($this->input->post('start'));
        $data['length'] = intval($this->input->post('length'));
        $data['search'] = $this->input->post('search')['value'];

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $id_kurir = $this->input->post('id_kurir');

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
            0 => 'dp.no_pesanan',
            1 => 'tr.noresi',
            2 => 'mp.nama_marketplace',
            3 => 'kr.nama_kurir',
            4 => 'tr.tanggal_resiretur',
            5 => 'tr.tanggal_resiretur',
            6 => 'dp.sku',
            7 => 'dp.jumlah',
            8 => 'tr.status_detail'
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_retur = $this->retur_fcd->get_laporan_buka_retur($data, $start_date, $end_date, $id_kurir);
        $total = $this->retur_fcd->get_total_laporan_buka_retur($data, $start_date, $end_date, $id_kurir);

        $i = $data['start'] + 1;
        $result_data = array();
        foreach ($list_retur->result() as $row) {
            $result_data[] = array(
                $row->no_pesanan ?: '-',
                $row->noresi,
                $row->nama_marketplace ?: '-',
                $row->nama_kurir ?: '-',
                date('Y-m-d', strtotime($row->tanggal_resiretur)),
                date('H:i:s', strtotime($row->tanggal_resiretur)),
                $row->sku ?: '-',
                $row->jumlah ?: '0',
                $row->status_detail ?: '-'
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $result_data
        );
        echo json_encode($output);
        exit();
    }

    // ==================== EXPORT EXCEL TERIMA RETUR ====================
    public function export_excel_terima_retur()
    {
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $id_kurir = $this->input->get('id_kurir');

        $data['list_retur'] = $this->retur_fcd->get_laporan_terima_retur_all($start_date, $end_date, $id_kurir);
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Terima_Retur_" . date('YmdHis') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        $this->load->view('retur/export_terima_retur', $data);
    }

    // ==================== EXPORT EXCEL BUKA RETUR ====================
    public function export_excel_buka_retur()
    {
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $id_kurir = $this->input->get('id_kurir');

        $data['list_retur'] = $this->retur_fcd->get_laporan_buka_retur_all($start_date, $end_date, $id_kurir);
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Buka_Retur_" . date('YmdHis') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        $this->load->view('retur/export_buka_retur', $data);
    }

	// ==================== PROSES COMPLAIN CUSTOMER ====================
	public function complain()
	{
		$data['message'] = $this->session->flashdata('message');
		$data['status_options'] = $this->complain_statuses;
		$data['list_marketplace'] = $this->marketplace_fcd->get_marketplace()->result_array();
		$data['reportrange'] = date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s');
		if ($this->input->method() === 'post' && $this->input->post('reportrange')) {
			$data['reportrange'] = $this->input->post('reportrange');
		}

		$this->show($data);
	}

	public function save_refund_complain()
	{
		// Start output buffering and clear any existing output
		while (ob_get_level()) {
			ob_end_clean();
		}
		ob_start();
		
		// Set JSON header
		header('Content-Type: application/json');
		
		if ($this->input->method() !== 'post') {
			ob_end_clean();
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		try {
			$complain = array(
				'noresi' => strtoupper(trim($this->input->post('noresi'))),
				'customer_name' => $this->input->post('customer_name'),
				'marketplace' => $this->input->post('marketplace'),
				'no_pesanan' => $this->input->post('no_pesanan'),
				'sku' => $this->input->post('sku'),
				'qty' => $this->input->post('qty'),
				'complain_type' => 'refund',
				'status' => 'TO_DO',
				'refund_amount' => $this->input->post('refund_amount'),
				'refund_bank' => $this->input->post('refund_bank'),
				'refund_account' => $this->input->post('refund_account'),
				'notes' => $this->input->post('notes')
			);

			$save = $this->retur_fcd->save_complain($complain, $this->data['user']['id_user']);
			if (isset($save['error'])) {
				ob_end_clean();
				$this->make_ajax_response($save['code'], $save['message']);
			}

			ob_end_clean();
			$this->make_ajax_response(201, 'Complain refund berhasil disimpan');
		} catch (Exception $e) {
			ob_end_clean();
			$this->make_ajax_response(500, 'Terjadi kesalahan: ' . $e->getMessage());
		}
	}

	public function save_replacement_complain()
	{
		// Start output buffering and clear any existing output
		while (ob_get_level()) {
			ob_end_clean();
		}
		ob_start();
		
		// Set JSON header
		header('Content-Type: application/json');
		
		if ($this->input->method() !== 'post') {
			ob_end_clean();
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		try {
			$complain = array(
				'noresi' => strtoupper(trim($this->input->post('noresi'))),
				'customer_name' => $this->input->post('customer_name'),
				'marketplace' => $this->input->post('marketplace'),
				'no_pesanan' => $this->input->post('no_pesanan'),
				'sku' => $this->input->post('sku'),
				'qty' => $this->input->post('qty'),
				'complain_type' => 'replacement',
				'status' => 'TO_DO',
				'replacement_sku' => $this->input->post('replacement_sku'),
				'replacement_qty' => $this->input->post('replacement_qty'),
				'notes' => $this->input->post('notes')
			);

			$save = $this->retur_fcd->save_complain($complain, $this->data['user']['id_user']);
			if (isset($save['error'])) {
				ob_end_clean();
				$this->make_ajax_response($save['code'], $save['message']);
			}

			ob_end_clean();
			$this->make_ajax_response(201, 'Complain pergantian barang berhasil disimpan');
		} catch (Exception $e) {
			ob_end_clean();
			$this->make_ajax_response(500, 'Terjadi kesalahan: ' . $e->getMessage());
		}
	}

	public function get_complain_data()
	{
		$draw = intval($this->input->post('draw'));
		$order = $this->input->post('order');

		$params['start'] = intval($this->input->post('start'));
		$params['length'] = intval($this->input->post('length'));
		$params['search'] = $this->input->post('search')['value'] ?? '';
		$params['status_filter'] = $this->input->post('status_filter');
		$params['reportrange'] = $this->input->post('reportrange');

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
			5 => 'rc.complain_type',
			6 => 'rc.status'
		);
		$params['order'] = isset($params['valid_columns'][$col]) ? $params['valid_columns'][$col] : 'rc.created_at';

		$list = $this->retur_fcd->get_complain_list($params);
		$total = $this->retur_fcd->get_total_complain_list($params);

		$rows = array();
		$no = $params['start'] + 1;
		foreach ($list->result() as $row) {
			$status_select = '<select class="form-control complain-status-select" data-id="' . $row->id . '" data-old-status="' . htmlspecialchars($row->status, ENT_QUOTES, 'UTF-8') . '">';
			foreach ($this->complain_statuses as $key => $label) {
				$selected = $row->status === $key ? 'selected' : '';
				$status_select .= '<option value="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
			}
			$status_select .= '</select>';

			$rows[] = array(
				$no++ . '.',
				$row->noresi,
				$row->customer_name ?: '-',
				$row->marketplace ?: '-',
				date('d/m/Y H:i', strtotime($row->created_at)),
				ucfirst($row->complain_type),
				$status_select,
				$row->notes ?: '-'
			);
		}

		$output = array(
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
			'data' => $rows
		);

		echo json_encode($output);
		exit();
	}

	public function update_complain_status()
	{
		// Start output buffering and clear any existing output
		while (ob_get_level()) {
			ob_end_clean();
		}
		ob_start();
		
		// Set JSON header
		header('Content-Type: application/json');
		
		if ($this->input->method() !== 'post') {
			ob_end_clean();
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$id = intval($this->input->post('id'));
		$status = $this->input->post('status');

		if (empty($id) || empty($status) || !isset($this->complain_statuses[$status])) {
			ob_end_clean();
			$this->make_ajax_response(400, 'Data status tidak valid');
		}

		$update = $this->retur_fcd->update_complain_status($id, $status, $this->data['user']['id_user']);
		if (isset($update['error'])) {
			ob_end_clean();
			$this->make_ajax_response($update['code'], $update['message']);
		}

		ob_end_clean();
		$this->make_ajax_response(200, 'Status complain berhasil diperbarui');
	}

	public function get_receipt_info()
	{
		// Start output buffering and clear any existing output
		while (ob_get_level()) {
			ob_end_clean();
		}
		ob_start();
		
		// Set JSON header
		header('Content-Type: application/json');
		
		if ($this->input->method() !== 'post') {
			ob_end_clean();
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$noresi = strtoupper(trim($this->input->post('noresi')));

		if (empty($noresi)) {
			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode(['success' => false, 'message' => 'Nomor resi tidak boleh kosong']);
			exit();
		}

		// Get receipt info from tblprintresi
		$this->db->select('
			pr.noresi,
			m.nama_marketplace
		');
		$this->db->from('tblprintresi pr');
		$this->db->join('tblmarketplace m', 'm.id_marketplace = pr.id_marketplace', 'left');
		$this->db->where('pr.noresi', $noresi);
		$this->db->limit(1);
		
		$query = $this->db->get();
		$receipt = $query->row_array();

		if (empty($receipt)) {
			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode(['success' => false, 'message' => 'Nomor resi tidak ditemukan']);
			exit();
		}

		// Get no_pesanan from tbldetailprintresi (get unique values, join with comma if multiple)
		$this->db->select('GROUP_CONCAT(DISTINCT dr.no_pesanan ORDER BY dr.no_pesanan SEPARATOR ", ") as no_pesanan');
		$this->db->from('tbldetailprintresi dr');
		$this->db->join('tblprintresi pr2', 'pr2.id_printresi = dr.id_resi', 'inner');
		$this->db->where('pr2.noresi', $noresi);
		$this->db->where('dr.no_pesanan IS NOT NULL');
		$this->db->where('dr.no_pesanan !=', '');
		$pesanan_query = $this->db->get();
		$pesanan = $pesanan_query->row_array();

		// Get SKU and QTY from tbldetailprintresi (get first item or combine if multiple)
		$this->db->select('GROUP_CONCAT(DISTINCT dr.sku ORDER BY dr.id_detail_resi SEPARATOR ", ") as sku, SUM(dr.jumlah) as qty');
		$this->db->from('tbldetailprintresi dr');
		$this->db->join('tblprintresi pr3', 'pr3.id_printresi = dr.id_resi', 'inner');
		$this->db->where('pr3.noresi', $noresi);
		$sku_qty_query = $this->db->get();
		$sku_qty = $sku_qty_query->row_array();

		// Try to get customer_name, no_pesanan, sku, and qty from existing retur complain if available
		// Note: customer_name tidak tersedia di tblprintresi, jadi hanya ambil dari data complain sebelumnya
		$this->db->select('customer_name, no_pesanan, sku, qty');
		$this->db->from('tblreturcomplain');
		$this->db->where('noresi', $noresi);
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit(1);
		$complain_query = $this->db->get();
		$complain = $complain_query->row_array();

		$result = [
			'marketplace' => $receipt['nama_marketplace'] ?? '',
			// Customer name hanya dari data complain sebelumnya (jika ada), jika tidak ada biarkan kosong untuk diinput manual
			'customer_name' => !empty($complain['customer_name']) ? $complain['customer_name'] : '',
			'no_pesanan' => !empty($complain['no_pesanan']) ? $complain['no_pesanan'] : (!empty($pesanan['no_pesanan']) ? $pesanan['no_pesanan'] : ''),
			// SKU dan QTY dari data complain sebelumnya atau dari tbldetailprintresi
			'sku' => !empty($complain['sku']) ? $complain['sku'] : (!empty($sku_qty['sku']) ? $sku_qty['sku'] : ''),
			'qty' => !empty($complain['qty']) ? $complain['qty'] : (!empty($sku_qty['qty']) ? intval($sku_qty['qty']) : '')
		];

		ob_end_clean();
		header('Content-Type: application/json');
		echo json_encode(['success' => true, 'data' => $result]);
		exit();
	}
}

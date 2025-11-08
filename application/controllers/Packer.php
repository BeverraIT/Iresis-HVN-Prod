<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packer extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('employee_fcd');
		$this->load->model('packer_fcd');
        $this->load->model('receipt_fcd');
        $this->load->model('problemtype_fcd');
	}

	public function scan_packer()
	{
        $data = [];

        // Initialize default values to prevent undefined variable errors
        $data['total_scan'] = 0;
        $data['nama_picker'] = '-';
        $data['komputer_picker'] = '-';
        $data['komputer_packer'] = isset($this->data['nama_pk']) ? $this->data['nama_pk'] : (isset($this->data['user']['nama_komputer']) ? $this->data['user']['nama_komputer'] : '-');

        if ($this->input->method() == 'post') {
            $noresi = $this->input->post('noresi');

            $receipts = $this->receipt_fcd->get_detail_receipt($noresi)->result();
            $packer_scan = $this->packer_fcd->get_total_scan_user($this->data['user']['id_user'])->row();
            $picker_detail = $this->packer_fcd->get_picker_detail_for_packer($noresi);

            // Always update total_scan from current user
            if($packer_scan) {
                $data['total_scan'] = $packer_scan->total_scan;
            }

            if(!empty($receipts)) {
                $data['noresi'] = $noresi;
                $data['list_type_masalah'] = $this->problemtype_fcd->get_list();

                // Get the first receipt for basic info
                $first_receipt = $receipts[0];
                $data['id_printresi'] = $first_receipt->id_printresi;

                // Collect all SKUs and quantities - handle null values
                $data['items'] = [];
                $total_qty = 0;
                foreach ($receipts as $receipt) {
                    $data['items'][] = [
                        'sku' => $receipt->sku ?? '-',
                        'jumlah' => $receipt->jumlah ?? 0,
                        'no_rak' => $receipt->no_rak ?? '-'
                    ];
                    $total_qty += ($receipt->jumlah ?? 0);
                }

                // For backward compatibility, set first item as main
                $data['sku'] = $first_receipt->sku ?? '-';
                $data['qty'] = $first_receipt->jumlah ?? 0;
                $data['no_rak'] = $first_receipt->no_rak ?? '-';
                $data['total_qty'] = $total_qty;
                $data['total_items'] = count($receipts);

                // Update picker details if found
                if($picker_detail) {
                    $data['nama_picker'] = $picker_detail->nama_pegawai;
                    $data['komputer_picker'] = $picker_detail->nama_komputer;
                }
            } else {
                // Handle case where no detail records exist
                $data['noresi'] = $noresi;
                $data['error_message'] = 'No detail records found for this receipt number';
            }
        }

        $this->show($data);
	}

    public function get_scan_packer_data($noresi)
    {
        // Decode the noresi parameter in case it contains special characters
        $noresi = urldecode($noresi);

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

        // Define valid columns for ordering
        $data['valid_columns'] = [
            0 => null,
            1 => null,
            2 => 's.nama_barang',
            3 => 'dr.sku',
            4 => 'dr.jumlah',
            5 => null
        ];

        $data['order'] = isset($data['valid_columns'][$col]) ? $data['valid_columns'][$col] : null;

        try {
            // Get receipt data with proper null handling
            $data_resi = $this->receipt_fcd->get_receipt_for_packer($data, $noresi);
            $total_data_resi = $this->receipt_fcd->get_total_receipt_for_packer($noresi);

            $table_number = $data['start'] + 1;
            $data_masalah_picker = array();

            // Check if we have any detail records
            if (!empty($data_resi)) {
                foreach ($data_resi as $row_masalah) {
                    // Handle cases where tbldetailprintresi might be null
                    $sku = $row_masalah->sku ?? '-';
                    $jumlah = $row_masalah->jumlah ?? 0;
                    $no_rak = $row_masalah->no_rak ?? '-';
                    $nama_barang = $row_masalah->nama_sku ?? '-';
                    $link_foto = $row_masalah->link_foto ?? '';
                    $yangambil_pegawai = $row_masalah->yangambil_pegawai ?? '';
                    $picker_name = $row_masalah->name ?? $yangambil_pegawai; // Use picker_name if available, fallback to yangambil_pegawai

                    $data_masalah_picker[] = array(
                        $table_number++ . '.',
                        $link_foto ? '<img src="' . htmlspecialchars($link_foto, ENT_QUOTES, 'UTF-8') . '" style="max-width: 100px; max-height: 100px; cursor: pointer;" class="img-thumbnail foto-preview" data-foto="' . htmlspecialchars($link_foto, ENT_QUOTES, 'UTF-8') . '">' : '<span class="text-muted">No Photo</span>',
                        $nama_barang,
                        $sku,
                        $jumlah,
                        '<div class="text-center">
                            <button 
                                class="btn btn-info saveMasalahPicker" 
                                data-id="' . htmlspecialchars($row_masalah->id_printresi, ENT_QUOTES, 'UTF-8') . '"
                                data-noresi="' . htmlspecialchars($row_masalah->noresi, ENT_QUOTES, 'UTF-8') . '"
                                data-sku="' . htmlspecialchars($sku, ENT_QUOTES, 'UTF-8') . '"
                                data-qty="' . htmlspecialchars($jumlah, ENT_QUOTES, 'UTF-8') . '"
                                data-nama-picker="' . htmlspecialchars($picker_name, ENT_QUOTES, 'UTF-8') . '"
                                data-no-rak="' . htmlspecialchars($no_rak, ENT_QUOTES, 'UTF-8') . '"
                            >Masalah Picker</button><br>
                        </div>'
                    );
                }
            } else {
                // When no detail records exist, return empty data
                // The static table will show "No details" message
                $data_masalah_picker = [];
                $total_data_resi = 0;
            }

            $output = array(
                "draw" => $draw,
                "recordsTotal" => $total_data_resi,
                "recordsFiltered" => $total_data_resi,
                "data" => $data_masalah_picker
            );

            echo json_encode($output);
        } catch (Exception $e) {
            // Return error response for DataTables
            $output = array(
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error: " . $e->getMessage()
            );
            echo json_encode($output);
            log_message('error', 'Error in get_scan_packer_data: ' . $e->getMessage());
        }

        exit();
    }

	// request by ajax
	public function save_packer()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$packer['noresi'] = $this->input->post('noresi');
		
		// Prioritas: 1) Status dari POST, 2) Status dari session user, 3) Default NORMAL
		$status_performa_code = $this->input->post('status_performa');
		
		if (!empty($status_performa_code)) {
			// Jika user memilih status dari dropdown
			$this->load->model('kpi_fcd');
			$status_id = $this->kpi_fcd->get_status_id_by_name($status_performa_code);
			if ($status_id) {
				$packer['status_performa_id'] = $status_id;
			}
		} else {
			// Cek status dari session user (yang di-set saat login)
			$user_status_performa = $this->session->userdata('user_status_performa');
			
			if ($user_status_performa && isset($user_status_performa->id_statusperforma)) {
				// Gunakan status dari session
				$packer['status_performa_id'] = $user_status_performa->id_statusperforma;
			} else {
				// Fallback ke NORMAL jika tidak ada status sama sekali
				$this->load->model('kpi_fcd');
				$normal_status_id = $this->kpi_fcd->get_status_id_by_name('NORMAL_PACKER');
				if ($normal_status_id) {
					$packer['status_performa_id'] = $normal_status_id;
				}
			}
		}

		$save = $this->packer_fcd->save($packer, $this->data['user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}

    public function search_packer()
    {
        $this->show();
    }

    public function get_data_packer()
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
            1 => 'pr.noresi',
            2 => 'u.name',
            3 => 'p.tanggal_packing',
            4 => 'p.tanggal_packing',
            5 => 'p.keterangan',
        );

        $data['order'] = !isset($data['valid_columns'][$col]) ? null : $data['valid_columns'][$col];

        $list_resi = $this->packer_fcd->get_data($data);

        $total = $this->packer_fcd->get_total_data($data);

        $i = $data['start'] + 1;
        $data = array();
        foreach ($list_resi->result() as $row) {
            $data[] = array(
                $i++ . '.',
                $row->noresi,
                $row->nama_pegawai,
                date('Y-m-d', strtotime($row->tanggal_packing)),
                date('H:i:s', strtotime($row->tanggal_packing)),
                $row->keterangan,
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

	// Keepalive endpoint untuk prevent session timeout
	public function keepalive()
	{
		// Simple endpoint to keep session alive
		// Just accessing session data will refresh the session timeout
		if ($this->session->userdata('user')) {
			echo json_encode(['status' => 'alive', 'timestamp' => time()]);
		} else {
			set_status_header(401);
			echo json_encode(['status' => 'expired']);
		}
	}
}

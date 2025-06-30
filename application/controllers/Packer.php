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

        if ($this->input->method() == 'post') {
            $noresi = $this->input->post('noresi');

            $receipt= $this->receipt_fcd->get_detail_receipt($noresi)->row();
            $packer_scan= $this->packer_fcd->get_total_scan_user($this->data['user']['id_user'])->row();
            $picker_detail= $this->packer_fcd->get_picker_detail_for_packer($noresi);

            if(!empty($receipt)) {
                $data['noresi'] = $noresi;
                $data['list_type_masalah'] = $this->problemtype_fcd->get_list();
                $data['sku'] = $receipt->sku;
                $data['qty'] = $receipt->jumlah;
                $data['no_rak'] = $receipt->no_rak;
                $data['id_printresi'] = $receipt->id_printresi;
                $data['total_scan'] = $packer_scan->total_scan;
                $data['nama_picker'] = $picker_detail->nama_pegawai;
                $data['komputer_picker'] = $picker_detail->nama_komputer;
            } else {
                $data['noresi'] = $noresi;
            }
        }

        $this->show($data);
	}

    public function get_scan_packer_data($noresi)
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
        // pastikan valid_columns diset, atau tambahkan ini jika belum
        $data['valid_columns'] = [
            0 => 'noresi',
            1 => 'sku',
            2 => 'qty'
        ];
        $data['order'] = isset($data['valid_columns'][$col]) ? $data['valid_columns'][$col] : null;

        $data_resi = $this->receipt_fcd->get_receipt_for_packer($data, $noresi);
        // log_message('error', 'ini resi return bos : ' . json_encode($data_resi));

        $total_data_resi = $this->receipt_fcd->get_total_receipt_for_packer($noresi);

        $table_number = $data['start'] + 1;
        $data_masalah_picker = array();

        foreach ($data_resi as $row_masalah) {
            // log_message('error', 'masuk looping : ' . json_encode($row_masalah->noresi));

            $data_masalah_picker[] = array(
                $table_number++ . '.',
                $row_masalah->nama_barang,
                $row_masalah->sku,
                $row_masalah->jumlah,
                '<button class="btn btn-info lihat-foto" data-foto="' . htmlspecialchars($row_masalah->link_foto, ENT_QUOTES, 'UTF-8') . '">Lihat Foto</button>',
                '<div class="text-center">
					<button 
						style="margin-bottom: 2px; widht: 50px"
						class="btn btn-info saveMasalahPicker" 
						data-id="' . htmlspecialchars($row_masalah->id_printresi, ENT_QUOTES, 'UTF-8') . '"
						data-noresi="' . htmlspecialchars($row_masalah->noresi, ENT_QUOTES, 'UTF-8') . '"
						data-sku="' . htmlspecialchars($row_masalah->sku, ENT_QUOTES, 'UTF-8') . '"
						data-qty="' . htmlspecialchars($row_masalah->jumlah, ENT_QUOTES, 'UTF-8') . '"
						data-nama-picker="' . htmlspecialchars($row_masalah->yangambil_pegawai, ENT_QUOTES, 'UTF-8') . '"
						data-no-rak="' . htmlspecialchars($row_masalah->no_rak, ENT_QUOTES, 'UTF-8') . '"
					>Masalah Picker</button><br>
				</div>'
            );
        }

        // log_message('error', 'result : ' . json_encode($data_masalah_picker));

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total_data_resi,
            "recordsFiltered" => $total_data_resi,
            "data" => $data_masalah_picker
        );

        echo json_encode($output);
        exit();
    }

	// request by ajax
	public function save_packer()
	{
		if ($this->input->method() == 'get') {
			$this->make_ajax_response(400, INVALID_REQUEST_METHOD);
		}

		$packer['noresi'] = $this->input->post('noresi');

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
            1 => 't2.noresi',
            2 => 't3.nama_pegawai',
            3 => 't.tanggal_packing',
            4 => 't.tanggal_packing',
            5 => 't.keterangan',
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
                $row->packer,
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
}

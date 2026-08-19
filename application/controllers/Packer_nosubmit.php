<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packer_nosubmit extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->model('employee_fcd');
		$this->load->model('packer_nosubmit_fcd');
        $this->load->model('receipt_fcd');
        // $this->load->model('problemtype_fcd'); // Not needed if we don't report problems? Or maybe we do?
        // If "no submit" creates problems, we might need it. But usually "no submit" is for speed.
        // I'll keep it commented out unless I see a need. 
        // Actually, scan_packer view has "Masalah Picker". If we want to keep that functionality available 
        // (e.g. if auto-submit fails or user wants to report), we might need it.
        // But for "1x scan langsung masuk", it's distinct.
        // I will assume simple flow for now.
	}

	public function scan()
	{
        $data = [];

        // Initialize default values
        $data['total_scan'] = 0;
        $data['nama_picker'] = '-';
        $data['komputer_picker'] = '-';
        $data['komputer_packer'] = isset($this->data['nama_pk']) ? $this->data['nama_pk'] : (isset($this->data['user']['nama_komputer']) ? $this->data['user']['nama_komputer'] : '-');

        // Get total scan for current user to display
        $packer_scan = $this->packer_nosubmit_fcd->get_total_scan_user($this->data['user']['id_user'])->row();
        if($packer_scan) {
            $data['total_scan'] = $packer_scan->total_scan;
        }

        // Custom show logic to use specific view
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        echo json_encode(array(
            'view' => $this->load->view('packer/scan_packer_nosubmit', $data, TRUE),
            'message' => empty($data['message']) ? null : $data['message'],
        ));
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
            // Jika user memilih status dari dropdown (jika ada)
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

		$save = $this->packer_nosubmit_fcd->save($packer, $this->data['user']);

		if (isset($save['error'])) {
			$this->make_ajax_response($save['code'], $save['message']);
		}

		if ($save['affected_rows'] > 0) {
            // Success
            // We might want to return the updated total scan count here if we wanted to be fancy, 
            // but the client-side can just increment or we can fetch it.
            // For now, standard response.
			$this->make_ajax_response(201, SUCCESS_SAVE_DATA);
		}

		$this->make_ajax_response(200, NOTHING_TO_SAVE);
	}
}

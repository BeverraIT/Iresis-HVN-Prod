<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee_fcd extends CI_Model
{

    function get_employee($kode_pegawai = null)
    {
        if (!empty($kode_pegawai)) {
            $this->db->where(['kode_pegawai' => $kode_pegawai]);
        } else {
            $this->db->order_by('nama_pegawai asc');
        }

        return $this->db->get('tblpegawai');
    }

    function save($employee)
    {
        if (empty($employee['kode_pegawai'])) {
            $this->db->insert('tblpegawai', $employee);

            $employee['kode_pegawai'] = $this->db->insert_id();
            $employee['affected_rows'] = 1;
        } else {
            $this->db->where(array('kode_pegawai' => $employee['kode_pegawai']));

            $this->db->update('tblpegawai', $employee);

            $employee['affected_rows'] = $this->db->affected_rows();
        }

        return $employee;
    }

    function destroy($employee)
    {
        $this->db->where(array('kode_pegawai' => $employee['kode_pegawai']));

        $this->db->delete('tblpegawai');

        $employee['affected_rows'] = $this->db->affected_rows();

        return $employee;
    }
}

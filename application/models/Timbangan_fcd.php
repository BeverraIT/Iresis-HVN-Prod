<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model modul timbangan (TIM RESI).
 *
 * Semua berat di dalam model ini memakai satuan GRAM. Konversi dari satuan
 * indikator (g / kg) dilakukan di controller berdasarkan param SATUAN_INDIKATOR.
 */
class Timbangan_fcd extends CI_Model
{
    /**
     * Ambil semua param modul timbangan sebagai array asosiatif
     * (paramvalue1 => paramvalue2), lengkap dengan nilai default.
     */
    function get_setting()
    {
        $default = array(
            'TOLERANSI_PERSEN' => '5',
            'BERAT_KEMASAN' => '0',
            'SATUAN_INDIKATOR' => 'g',
            'INTERLOCK' => '1',
            'BAUD_RATE' => '9600',
            'KODE_SUPERVISOR' => '8888',
            'AUTO_SIMPAN' => '1',
            'STABIL_MS' => '800',
            // Alur dua kali scan: hasil baru tersimpan setelah operator
            // men-scan resi yang sama untuk kedua kalinya. Set 0 untuk
            // kembali ke alur lama (simpan otomatis begitu berat diam).
            'SCAN_GANDA' => '1',
            'JEDA_SCAN_MS' => '1500',
            'TIMEOUT_RESI_MENIT' => '15',
            // Pita toleransi hibrida. Lihat hitung_setpoint() di controller
            // untuk cara ketiganya bekerja bersama.
            'TOLERANSI_MIN_GRAM' => '15',
            'BATAS_SKU_PERSEN' => '80',
            'VARIASI_KEMASAN_GRAM' => '8',
        );

        $this->db->where(array('paramgroup' => 'TIMBANGAN', 'isactive' => TRUE));

        foreach ($this->db->get('param')->result() as $row) {
            $default[$row->paramvalue1] = $row->paramvalue2;
        }

        return $default;
    }

    /**
     * Daftar jenis kemasan yang bisa dipilih operator.
     *
     * Disimpan di tabel param (paramgroup = 'KEMASAN') supaya jenis yang
     * beratnya belum ditimbang bisa dilengkapi belakangan tanpa mengubah kode.
     * Berat kosong berarti belum didata -- pilihannya tetap ditampilkan tetapi
     * tidak bisa dipakai.
     *
     * @return array daftar ['kode' => ..., 'nama' => ..., 'berat' => float|null]
     */
    function get_kemasan()
    {
        $this->db->where('isactive', TRUE);
        $this->db->order_by('sortorder', 'ASC');

        $hasil = array();

        foreach ($this->db->get('tbltimbangan_kemasan')->result() as $row) {
            $hasil[$row->kode] = array(
                'kode' => $row->kode,
                'nama' => $row->nama,
                'komponen' => array(),
                'berat' => 0,          // berat bila tiap komponen dipakai 1x
                'perlu_jumlah' => FALSE,
                'lengkap' => TRUE,     // FALSE bila ada komponen yang belum didata
            );
        }

        if (empty($hasil)) {
            return array();
        }

        $this->db->select('k.kode, p.id_komponen, p.nama, p.berat, p.tanya_jumlah');
        $this->db->join('tbltimbangan_kemasan k', 'k.id_kemasan = p.id_kemasan');
        $this->db->order_by('k.sortorder', 'ASC');
        $this->db->order_by('p.sortorder', 'ASC');

        foreach ($this->db->get('tbltimbangan_kemasan_komponen p')->result() as $row) {
            if (!isset($hasil[$row->kode])) {
                continue;
            }

            $berat = $row->berat === null ? null : (float) $row->berat;

            $hasil[$row->kode]['komponen'][] = array(
                'id' => (int) $row->id_komponen,
                'nama' => $row->nama,
                'berat' => $berat,
                'tanya_jumlah' => (bool) $row->tanya_jumlah,
            );

            if ($berat === null) {
                $hasil[$row->kode]['lengkap'] = FALSE;
            } else {
                $hasil[$row->kode]['berat'] += $berat;
            }

            if ($row->tanya_jumlah) {
                $hasil[$row->kode]['perlu_jumlah'] = TRUE;
            }
        }

        return array_values($hasil);
    }

    /**
     * Riwayat perubahan berat master SKU.
     *
     * Diisi otomatis oleh trigger pada tbltimbangan_sku, sehingga ikut
     * menangkap perubahan lewat menu, impor Excel, maupun SQL langsung.
     */
    function get_log_master($start_date = null, $end_date = null)
    {
        $this->db->select('
            t.kode_sku,
            t.berat_lama,
            t.berat_baru,
            t.aksi,
            t.waktu,
            t2.nama_sku,
            t3.name petugas
        ');

        $this->db->join('tblsku t2', 't2.id_sku = t.kode_sku', 'left');
        $this->db->join('tbluser t3', 't3.id_user = t.diubah_oleh', 'left');

        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('t.waktu >=', $start_date);
            $this->db->where('t.waktu <=', $end_date);
        }

        $this->db->order_by('t.waktu', 'DESC');
        $this->db->order_by('t.id_log', 'DESC');

        return $this->db->get('tbltimbangan_sku_log t');
    }

    /**
     * Perbandingan jumlah resi yang diunggah dengan yang sudah ditimbang,
     * per batch unggahan (nomor picklist).
     *
     * Memakai COUNT(DISTINCT ...) di kedua sisi: satu resi yang ditimbang
     * berulang kali -- percobaan ke-2, atau dua tahap pada resi campuran --
     * tetap terhitung satu.
     */
    function get_progres_batch($start_date = null, $end_date = null)
    {
        $this->db->select('
            t.nomorpicklist,
            COUNT(DISTINCT t.noresi) jumlah_resi,
            COUNT(DISTINCT g.noresi) jumlah_ditimbang,
            MIN(t.created_at) waktu_unggah
        ', FALSE);

        $this->db->join('tbltimbangan g', 'g.noresi = t.noresi', 'left');

        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('t.created_at >=', $start_date);
            $this->db->where('t.created_at <=', $end_date);
        }

        $this->db->group_by('t.nomorpicklist');
        $this->db->order_by('waktu_unggah', 'DESC');

        return $this->db->get('tblprintresi t');
    }

    function save_setting($code, $value, $user_id)
    {
        $criterias = array('paramgroup' => 'TIMBANGAN', 'paramvalue1' => $code);

        $this->db->update('param', array(
            'paramvalue2' => $value,
            'updatedby' => $user_id,
            'updated' => date('Y-m-d H:i:s'),
        ), $criterias);

        if ($this->db->affected_rows() == 0) {
            $this->db->insert('param', array(
                'paramvalue1' => $code,
                'paramvalue2' => $value,
                'paramgroup' => 'TIMBANGAN',
                'isactive' => TRUE,
                'createdby' => $user_id,
                'created' => date('Y-m-d H:i:s'),
            ));
        }

        return TRUE;
    }

    // ------------------------------------------------------------------
    // Resi
    // ------------------------------------------------------------------

    function get_resi($noresi)
    {
        $this->db->select('t.id_printresi, t.noresi, t.nomorpicklist, t.status_pesanan, t2.nama_marketplace, t3.nama_kurir');

        $this->db->join('tblmarketplace t2', 't2.id_marketplace = t.id_marketplace', 'left');
        $this->db->join('tblkurir t3', 't3.id_kurir = t.id_kurir', 'left');

        $this->db->where(array('t.noresi' => $noresi));
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit(1);

        return $this->db->get('tblprintresi t');
    }

    /**
     * Item per resi beserta berat standarnya.
     *
     * Berat standar diambil dari tbltimbangan_sku (hasil impor sku.xlsx).
     * Kalau SKU belum ada di sana, sistem jatuh ke tblsku.berat yang
     * satuannya juga gram. Kolom `sumber_berat` menjelaskan asalnya supaya
     * operator tahu angka mana yang perlu diperbarui.
     */
    function get_resi_items($id_resi)
    {
        $this->db->select('
            t.sku,
            t.jumlah,
            t.no_rak,
            t2.nama_sku,
            t3.berat_standar berat_master,
            t3.berat_max_pcs,
            t3.toleransi_persen,
            t2.berat berat_tblsku,
            COALESCE(t3.berat_standar, t2.berat) berat_standar,
            CASE
                WHEN t3.berat_standar IS NOT NULL THEN "MASTER"
                WHEN t2.berat IS NOT NULL AND t2.berat > 0 THEN "TBLSKU"
                ELSE "KOSONG"
            END sumber_berat
        ', FALSE);

        $this->db->join('tblsku t2', 't2.id_sku = t.sku', 'left');
        $this->db->join('tbltimbangan_sku t3', 't3.kode_sku = t.sku and t3.isactive = ' . TRUE, 'left');

        $this->db->where(array('t.id_resi' => $id_resi));
        $this->db->order_by('t.id_detail_resi', 'ASC');

        return $this->db->get('tbldetailprintresi t');
    }

    function get_timbangan_by_noresi($noresi)
    {
        $this->db->where(array('noresi' => $noresi));
        $this->db->order_by('id_timbangan', 'DESC');
        $this->db->limit(1);

        return $this->db->get('tbltimbangan');
    }

    function save_timbangan($timbangan)
    {
        $timbangan['created'] = date('Y-m-d H:i:s');

        $this->db->insert('tbltimbangan', $timbangan);

        $timbangan['id_timbangan'] = $this->db->insert_id();
        $timbangan['affected_rows'] = $this->db->affected_rows();

        return $timbangan;
    }

    function delete_timbangan($id_timbangan)
    {
        $this->db->delete('tbltimbangan', array('id_timbangan' => $id_timbangan));

        return $this->db->affected_rows();
    }

    function get_total_scan_user($id_user)
    {
        $this->db->select('count(1) as total_scan');

        $this->db->where(array(
            'tanggal_timbangan >= ' => date('Y-m-d 00:00:00'),
            'admin_pegawai' => $id_user,
        ));

        return $this->db->get('tbltimbangan');
    }

    // ------------------------------------------------------------------
    // Kunci verifikasi berat
    //
    // Setiap paket yang tidak lolos mengunci layar operator dan dicatat di
    // sini, supaya jelas berapa kali atasan dipanggil dan pada resi mana.
    // ------------------------------------------------------------------

    function save_lock($lock)
    {
        $lock['waktu_lock'] = date('Y-m-d H:i:s');

        $this->db->insert('tbltimbangan_lock', $lock);

        return $this->db->insert_id();
    }

    function close_lock($id_lock, $percobaan_gagal)
    {
        $this->db->update('tbltimbangan_lock', array(
            'waktu_unlock' => date('Y-m-d H:i:s'),
            'percobaan_gagal' => (int) $percobaan_gagal,
        ), array('id_lock' => $id_lock, 'waktu_unlock' => null));

        return $this->db->affected_rows();
    }

    // ------------------------------------------------------------------
    // Master berat SKU
    // ------------------------------------------------------------------

    function get_master($kode_sku = null)
    {
        $criterias['isactive'] = TRUE;

        if (!empty($kode_sku)) {
            $criterias['kode_sku'] = $kode_sku;
        }

        $this->db->where($criterias);

        return $this->db->get('tbltimbangan_sku');
    }

    /**
     * Upsert massal dari file Excel.
     *
     * @param array $rows list of ['kode_sku' => ..., 'berat_standar' => ...]
     * @return array ringkasan jumlah baris baru & diperbarui
     */
    function save_master_bulk($rows, $user_id)
    {
        $timestamp = date('Y-m-d H:i:s');
        $inserted = 0;
        $updated = 0;
        $unchanged = 0;

        $this->db->trans_start();

        foreach ($rows as $row) :
            $this->db->update('tbltimbangan_sku', array(
                'berat_standar' => $row['berat_standar'],
                'isactive' => TRUE,
                'updatedby' => $user_id,
                'updated' => $timestamp,
            ), array('kode_sku' => $row['kode_sku']));

            if ($this->db->affected_rows() > 0) {
                $updated++;
                continue;
            }

            // affected_rows() = 0 bisa berarti belum ada, atau nilainya sama
            // persis. Cek dulu supaya tidak insert duplikat.
            if ($this->db->where('kode_sku', $row['kode_sku'])->count_all_results('tbltimbangan_sku') > 0) {
                $unchanged++;
                continue;
            }

            $this->db->insert('tbltimbangan_sku', array(
                'kode_sku' => $row['kode_sku'],
                'berat_standar' => $row['berat_standar'],
                'isactive' => TRUE,
                'createdby' => $user_id,
                'created' => $timestamp,
            ));

            $inserted++;
        endforeach;

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return FALSE;
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'unchanged' => $unchanged);
    }

    function save_master($master, $user_id)
    {
        $timestamp = date('Y-m-d H:i:s');

        $this->db->update('tbltimbangan_sku', array(
            'berat_standar' => $master['berat_standar'],
            'toleransi_persen' => $master['toleransi_persen'],
            'isactive' => TRUE,
            'updatedby' => $user_id,
            'updated' => $timestamp,
        ), array('kode_sku' => $master['kode_sku']));

        if ($this->db->affected_rows() > 0) {
            return array('affected_rows' => $this->db->affected_rows());
        }

        if ($this->db->where('kode_sku', $master['kode_sku'])->count_all_results('tbltimbangan_sku') > 0) {
            return array('affected_rows' => 0);
        }

        $this->db->insert('tbltimbangan_sku', array(
            'kode_sku' => $master['kode_sku'],
            'berat_standar' => $master['berat_standar'],
            'toleransi_persen' => $master['toleransi_persen'],
            'isactive' => TRUE,
            'createdby' => $user_id,
            'created' => $timestamp,
        ));

        return array('affected_rows' => $this->db->affected_rows());
    }

    function delete_master($kode_sku)
    {
        $this->db->delete('tbltimbangan_sku', array('kode_sku' => $kode_sku));

        return $this->db->affected_rows();
    }

    // ------------------------------------------------------------------
    // Datatable: master berat SKU
    // ------------------------------------------------------------------

    private function apply_search($data)
    {
        if (empty($data['search'])) {
            return;
        }

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

    function get_data_master($data)
    {
        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        }

        $this->apply_search($data);

        $this->db->select('t.kode_sku, t.berat_standar, t.toleransi_persen, t.updated, t2.nama_sku');
        $this->db->join('tblsku t2', 't2.id_sku = t.kode_sku', 'left');
        $this->db->where('t.isactive', TRUE);
        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tbltimbangan_sku t');
    }

    function get_total_data_master($data)
    {
        $this->apply_search($data);

        $this->db->join('tblsku t2', 't2.id_sku = t.kode_sku', 'left');
        $this->db->where('t.isactive', TRUE);

        return $this->db->select('count(1) as num')->get('tbltimbangan_sku t')->row()->num;
    }

    // ------------------------------------------------------------------
    // Datatable: berat resi tidak sesuai
    //
    // Satu baris per resi: yang ditampilkan hanya penimbangan terakhir,
    // supaya percobaan ke-1 dan ke-2 pada resi yang sama tidak muncul
    // sebagai dua pekerjaan berbeda bagi admin.
    // ------------------------------------------------------------------

    private function filter_tidak_sesuai($sudah_ditindaklanjuti = 0)
    {
        $this->db->where('t.status !=', 'ACCEPT');
        $this->db->where('t.tindak_lanjut', $sudah_ditindaklanjuti ? 1 : 0);

        // Baris MELESET terbaru per nomor resi.
        //
        // Anak kueri ini sengaja ikut menyaring status: kalau ia melihat
        // seluruh baris, penimbangan ulang yang berhasil (ACCEPT) menjadi baris
        // terakhir dan catatan melesetnya lenyap dari daftar. Padahal catatan
        // itu harus bertahan sampai admin menekan "Tindak Lanjut" -- hasil
        // timbang ulang yang benar bukan alasan untuk menghapus jejak
        // kesalahannya.
        //
        // Dikelompokkan per nomor resi DAN per tahap: resi campuran PLCA
        // ditimbang dua kali, dan tahap 1 yang meleset tidak boleh tertutup
        // oleh tahap 2 -- keduanya perkara terpisah bagi admin.
        $this->db->where('t.id_timbangan = (SELECT MAX(x.id_timbangan) FROM tbltimbangan x WHERE x.noresi = t.noresi AND x.tahap = t.tahap AND x.status != \'ACCEPT\')', null, FALSE);
    }

    function get_data_tidak_sesuai($data, $sudah_ditindaklanjuti = 0)
    {
        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        } else {
            $this->db->order_by('t.tanggal_timbangan', 'DESC');
        }

        $this->apply_search($data);

        $this->db->select('
            t.id_timbangan,
            t.noresi,
            t.berat_aktual,
            t.berat_standar,
            t.berat_min,
            t.berat_max,
            t.selisih,
            t.status,
            t.percobaan_ke,
            t.tahap,
            t.tindak_lanjut,
            t.tindak_lanjut_waktu,
            t.tindak_lanjut_catatan,
            t.tanggal_timbangan,
            t.nama_komputer,
            t.ip_address,
            t2.name petugas,
            t3.name admin_tindak_lanjut
        ');

        $this->db->join('tbluser t2', 't2.id_user = t.admin_pegawai', 'left');
        $this->db->join('tbluser t3', 't3.id_user = t.tindak_lanjut_oleh', 'left');

        $this->filter_tidak_sesuai($sudah_ditindaklanjuti);

        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tbltimbangan t');
    }

    function get_total_data_tidak_sesuai($data, $sudah_ditindaklanjuti = 0)
    {
        $this->apply_search($data);

        $this->db->join('tbluser t2', 't2.id_user = t.admin_pegawai', 'left');
        $this->db->join('tbluser t3', 't3.id_user = t.tindak_lanjut_oleh', 'left');

        $this->filter_tidak_sesuai($sudah_ditindaklanjuti);

        return $this->db->select('count(1) as num')->get('tbltimbangan t')->row()->num;
    }

    /**
     * Data untuk diekspor ke Excel. Isinya sengaja disamakan dengan yang
     * tampil di layar -- satu baris per resi, penimbangan terakhirnya --
     * supaya angka di berkas dan di daftar tidak pernah berbeda.
     */
    /**
     * Rincian SKU untuk banyak resi sekaligus.
     *
     * Dipakai export supaya tidak menembak satu kueri per baris: 61 resi
     * berarti 61 kueri kalau diambil satu per satu.
     */
    function get_items_beberapa_resi($daftar_id_resi)
    {
        if (empty($daftar_id_resi)) {
            return array();
        }

        $this->db->select('
            t.id_resi,
            t.sku,
            t.jumlah,
            t2.nama_sku,
            COALESCE(t3.berat_standar, t2.berat) berat_standar,
            CASE
                WHEN t3.berat_standar IS NOT NULL THEN "MASTER"
                WHEN t2.berat IS NOT NULL AND t2.berat > 0 THEN "TBLSKU"
                ELSE "KOSONG"
            END sumber_berat
        ', FALSE);

        $this->db->join('tblsku t2', 't2.id_sku = t.sku', 'left');
        $this->db->join('tbltimbangan_sku t3', 't3.kode_sku = t.sku and t3.isactive = ' . TRUE, 'left');

        $this->db->where_in('t.id_resi', $daftar_id_resi);
        $this->db->order_by('t.id_resi', 'ASC');
        $this->db->order_by('t.id_detail_resi', 'ASC');

        return $this->db->get('tbldetailprintresi t')->result_array();
    }

    function get_data_export_tidak_sesuai($sudah_ditindaklanjuti = 0)
    {
        $this->db->select('
            t.id_resi,
            t.raw_data,
            t.noresi,
            t.berat_aktual,
            t.berat_standar,
            t.berat_min,
            t.berat_max,
            t.selisih,
            t.toleransi_persen,
            t.status,
            t.percobaan_ke,
            t.tahap,
            t.sku_tanpa_master,
            t.tindak_lanjut,
            t.tindak_lanjut_waktu,
            t.tindak_lanjut_catatan,
            t.tanggal_timbangan,
            t.nama_komputer,
            t.ip_address,
            t2.name petugas,
            t3.name admin_tindak_lanjut
        ');

        $this->db->join('tbluser t2', 't2.id_user = t.admin_pegawai', 'left');
        $this->db->join('tbluser t3', 't3.id_user = t.tindak_lanjut_oleh', 'left');

        $this->filter_tidak_sesuai($sudah_ditindaklanjuti);

        $this->db->order_by('t.tanggal_timbangan', 'ASC');

        return $this->db->get('tbltimbangan t');
    }

    /**
     * Berapa kali nomor resi ini pernah ditimbang dengan hasil meleset.
     * Dipakai untuk menentukan percobaan ke berapa dan kapan kode atasan
     * mulai diminta.
     */
    function hitung_percobaan_meleset($noresi)
    {
        $this->db->where(array('noresi' => $noresi, 'status !=' => 'ACCEPT'));

        return (int) $this->db->select('count(1) as num')->get('tbltimbangan')->row()->num;
    }

    /**
     * @param int|null $tahap batasi ke tahap tertentu. Resi campuran PLCA
     *        ditimbang dua kali (tahap 1 dan 2), jadi "sudah pernah lolos"
     *        harus dinilai per tahap -- kalau tidak, tahap 2 akan ditolak
     *        karena tahap 1 sudah berstatus ACCEPT.
     */
    function get_timbangan_lolos($noresi, $tahap = null)
    {
        $this->db->where(array('noresi' => $noresi, 'status' => 'ACCEPT'));

        if ($tahap !== null) {
            $this->db->where('tahap', $tahap);
        }

        $this->db->order_by('id_timbangan', 'DESC');
        $this->db->limit(1);

        return $this->db->get('tbltimbangan');
    }

    function get_timbangan_by_id($id_timbangan)
    {
        $this->db->where('id_timbangan', $id_timbangan);

        return $this->db->get('tbltimbangan');
    }

    /**
     * Tandai semua baris milik satu nomor resi sebagai sudah ditindaklanjuti,
     * bukan hanya baris terakhir: percobaan ke-1 dan ke-2 adalah satu perkara
     * yang sama bagi admin.
     */
    function tindak_lanjut_timbangan($noresi, $id_user, $catatan)
    {
        $this->db->where('noresi', $noresi);

        return $this->tandai_tindak_lanjut($id_user, $catatan);
    }

    /**
     * Tandai seluruh resi yang masih menunggu, sekali jalan. Dipakai admin
     * saat daftarnya panjang dan semuanya sudah dicek bersamaan.
     */
    function tindak_lanjut_semua($id_user, $catatan)
    {
        return $this->tandai_tindak_lanjut($id_user, $catatan);
    }

    private function tandai_tindak_lanjut($id_user, $catatan)
    {
        $this->db->update('tbltimbangan', array(
            'tindak_lanjut' => 1,
            'tindak_lanjut_oleh' => $id_user,
            'tindak_lanjut_waktu' => date('Y-m-d H:i:s'),
            'tindak_lanjut_catatan' => $catatan === '' ? null : substr($catatan, 0, 255),
        ), array('status !=' => 'ACCEPT', 'tindak_lanjut' => 0));

        return $this->db->affected_rows();
    }

    /**
     * Berapa nomor resi yang masih menunggu ditindaklanjuti. Dipakai untuk
     * memberi tahu admin persis berapa yang akan tersapu sekali tekan.
     */
    function hitung_menunggu_tindak_lanjut()
    {
        $this->db->distinct();
        $this->db->select('noresi');
        $this->db->where(array('status !=' => 'ACCEPT', 'tindak_lanjut' => 0));

        return $this->db->get('tbltimbangan')->num_rows();
    }

    // ------------------------------------------------------------------
    // Datatable: laporan penimbangan
    // ------------------------------------------------------------------

    function get_data_timbangan($data, $start_date = null, $end_date = null, $status = null)
    {
        if ($data['order'] != null) {
            $this->db->order_by($data['order'], $data['dir'], FALSE);
        }

        $this->apply_search($data);

        $this->db->select('
            t.id_timbangan,
            t.noresi,
            t.berat_aktual,
            t.berat_standar,
            t.berat_min,
            t.berat_max,
            t.selisih,
            t.status,
            t.tanggal_timbangan,
            t.nama_komputer,
            t.ip_address,
            t2.name petugas
        ');

        $this->db->join('tbluser t2', 't2.id_user = t.admin_pegawai', 'left');

        $this->filter_timbangan($start_date, $end_date, $status);

        $this->db->limit($data['length'], $data['start']);

        return $this->db->get('tbltimbangan t');
    }

    function get_total_data_timbangan($data, $start_date = null, $end_date = null, $status = null)
    {
        $this->apply_search($data);

        $this->db->join('tbluser t2', 't2.id_user = t.admin_pegawai', 'left');

        $this->filter_timbangan($start_date, $end_date, $status);

        return $this->db->select('count(1) as num')->get('tbltimbangan t')->row()->num;
    }

    function get_data_export($start_date = null, $end_date = null, $status = null)
    {
        $this->db->select('
            t.noresi,
            t.berat_aktual,
            t.berat_standar,
            t.berat_min,
            t.berat_max,
            t.selisih,
            t.toleransi_persen,
            t.status,
            t.sku_tanpa_master,
            t.tanggal_timbangan,
            t.nama_komputer,
            t.ip_address,
            t2.name petugas
        ');

        $this->db->join('tbluser t2', 't2.id_user = t.admin_pegawai', 'left');

        $this->filter_timbangan($start_date, $end_date, $status);

        $this->db->order_by('t.tanggal_timbangan', 'ASC');

        return $this->db->get('tbltimbangan t');
    }

    private function filter_timbangan($start_date, $end_date, $status)
    {
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('t.tanggal_timbangan >=', $start_date);
            $this->db->where('t.tanggal_timbangan <=', $end_date);
        }

        if (!empty($status)) {
            $this->db->where('t.status', $status);
        }
    }
}

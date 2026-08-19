<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Berat Resi Tidak Sesuai</h2>

<table class="table table-striped table-bordered">
  <tbody>
    <tr>
      <td>Kelompok data</td>
      <td>:</td>
      <td><?= empty($tindak_lanjut) ? 'Belum ditindaklanjuti' : 'Sudah ditindaklanjuti' ?></td>
    </tr>
    <tr>
      <td>Total resi</td>
      <td>:</td>
      <td><?= count($list_data) ?></td>
    </tr>
    <tr>
      <td>Diunduh</td>
      <td>:</td>
      <td><?= date('d-m-Y H:i:s') ?></td>
    </tr>
  </tbody>
</table>

<table class="table table-striped table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>No. Resi</th>
      <th>Berat Standar (g)</th>
      <th>Batas Bawah (g)</th>
      <th>Batas Atas (g)</th>
      <th>Berat Aktual (g)</th>
      <th>Selisih (g)</th>
      <th>Toleransi (%)</th>
      <th>Status</th>
      <th>Tahap</th>
      <th>Percobaan</th>
      <th>SKU Tanpa Master</th>
      <th>Jumlah Baris SKU</th>
      <th>Rincian SKU</th>
      <th>Data Mentah Indikator</th>
      <th>Petugas</th>
      <th>Komputer</th>
      <th>IP Device</th>
      <th>Waktu Timbang</th>
      <th>Tindak Lanjut</th>
      <th>Admin</th>
      <th>Waktu Tindak Lanjut</th>
      <th>Catatan</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= $data['noresi'] ?></td>
        <td><?= $data['berat_standar'] ?></td>
        <td><?= $data['berat_min'] ?></td>
        <td><?= $data['berat_max'] ?></td>
        <td><?= $data['berat_aktual'] ?></td>
        <td><?= $data['selisih'] ?></td>
        <td><?= $data['toleransi_persen'] ?></td>
        <td><?= $data['status'] ?></td>
        <td><?= $data['tahap'] == 0 ? '-' : $data['tahap'] ?></td>
        <td><?= $data['percobaan_ke'] ?></td>
        <td><?= $data['sku_tanpa_master'] ?></td>
        <?php $rincian = isset($rincian_sku[$data['id_resi']]) ? $rincian_sku[$data['id_resi']] : null; ?>
        <td><?= $rincian ? $rincian['jumlah_sku'] : 0 ?></td>
        <td><?= $rincian ? $rincian['teks'] : '-' ?></td>
        <td><?= $data['raw_data'] ?></td>
        <td><?= $data['petugas'] ?></td>
        <td><?= $data['nama_komputer'] ?></td>
        <td><?= $data['ip_address'] ?: '-' ?></td>
        <td><?= $data['tanggal_timbangan'] ?></td>
        <td><?= empty($data['tindak_lanjut']) ? 'Belum' : 'Sudah' ?></td>
        <td><?= $data['admin_tindak_lanjut'] ?></td>
        <td><?= $data['tindak_lanjut_waktu'] ?></td>
        <td><?= $data['tindak_lanjut_catatan'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

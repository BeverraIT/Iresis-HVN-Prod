<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Laporan Timbangan</h2>

<table class="table table-striped table-bordered">
  <tbody>
    <tr>
      <td>Periode</td>
      <td>:</td>
      <td><?= explode(" - ", $reportrange)[0] ?></td>
    </tr>
    <tr>
      <td>Sampai dengan</td>
      <td>:</td>
      <td><?= explode(" - ", $reportrange)[1] ?></td>
    </tr>
    <tr>
      <td>Status</td>
      <td>:</td>
      <td><?= empty($status) ? 'Semua status' : $status ?></td>
    </tr>
    <tr>
      <td>Total data</td>
      <td>:</td>
      <td><?= count($list_data) ?></td>
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
      <th>SKU Tanpa Master</th>
      <th>Petugas</th>
      <th>Komputer</th>
      <th>Waktu</th>
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
        <td><?= $data['sku_tanpa_master'] ?></td>
        <td><?= $data['petugas'] ?></td>
        <td><?= $data['nama_komputer'] ?></td>
        <td><?= $data['tanggal_timbangan'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

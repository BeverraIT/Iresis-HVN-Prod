<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Laporan Total Resi</h2>

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
      <th>Tanggal Scan Resi</th>
      <th>Jam Scan Resi</th>
      <th>Market Place</th>
      <th>Nomor Pick List</th>
      <th>Kurir</th>
      <th>Nomor Resi</th>
      <th>Tanggal Scan Keluar</th>
      <th>Jam Scan Keluar</th>
      <th>Picker</th>
      <th>Packer</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('Y-m-d', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('H:i', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= $data['nama_marketplace'] ?></td>
        <td><?= $data['nomorpicklist'] ?></td>
        <td><?= $data['nama_kurir'] ?></td>
        <td><?= $data['noresi'] ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('Y-m-d', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('H:i', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= $data['picker'] ?></td>
        <td><?= $data['packer'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
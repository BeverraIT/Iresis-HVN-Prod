<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Laporan Resi Picker Belum Scan Packer</h2>

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
  </tbody>
</table>

<table class="table table-striped table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Market Place</th>
      <th>Tanggal Scan Resi</th>
      <th>Jam Scan Resi</th>
      <th>Nomor Resi</th>
      <th>Kurir</th>
      <th>Nomor Pick List</th>
      <th>Tanggal Pick</th>
      <th>Jam Pick</th>
      <th>Picker</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= $data['nama_marketplace'] ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('Y-m-d', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('H:i', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= $data['noresi'] ?></td>
        <td><?= $data['nama_kurir'] ?></td>
        <td><?= $data['nomorpicklist'] ?></td>
        <td><?= empty($data['tanggal_resiambilbarang']) ? null : date('Y-m-d', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td><?= empty($data['tanggal_resiambilbarang']) ? null : date('H:i', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td><?= $data['picker'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
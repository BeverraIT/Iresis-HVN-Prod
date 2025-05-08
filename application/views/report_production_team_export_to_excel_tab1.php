<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Laporan Total Picklist</h2>

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
      <th>Tanggal Scan</th>
      <th>Nomor Picklist</th>
      <th>Jumlah Resi</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('Y-m-d', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= $data['nomorpicklist'] ?></td>
        <td><?= $data['total'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
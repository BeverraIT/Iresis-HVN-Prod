<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Laporan resi dikirim</h2>

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
      <th>Tanggal Resi Keluar</th>
      <th>Jam Resi Keluar</th>
      <th>Nomor Resi</th>
      <th>Kurir</th>
      <th>Tanggal Cetak</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('Y-m-d', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('H:i', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= $data['noresi'] ?></td>
        <td><?= $data['nama_kurir'] ?></td>
        <td><?= $data['tanggal_cetak'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
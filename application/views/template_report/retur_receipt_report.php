<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<table class="table table-striped table-bordered" id="datatable-retur-receipt-report">
  <thead>
    <tr>
      <th>#</th>
      <th>No. Resi</th>
      <th>Marketplace</th>
      <th>Kurir</th>
      <th>Nomor Picklist</th>
      <th>Tanggal Scan Resi</th>
      <th>Jam Scan Resi</th>
      <th>Tanggal Retur</th>
      <th>Jam Retur</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= $data['noresi'] ?></td>
        <td><?= $data['nama_marketplace'] ?></td>
        <td><?= $data['nama_kurir'] ?></td>
        <td><?= $data['nomorpicklist'] ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('Y-m-d', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('H:i', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= empty($data['tanggal_resiretur']) ? null : date('Y-m-d', strtotime($data['tanggal_resiretur'])) ?></td>
        <td><?= empty($data['tanggal_resiretur']) ? null : date('H:i', strtotime($data['tanggal_resiretur'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
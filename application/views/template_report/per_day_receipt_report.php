<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<table class="table table-striped table-bordered" id="datatable-report-receipt-daily">
  <thead>
    <tr>
      <th>#</th>
      <th>No. Resi</th>
      <th>Marketplace</th>
      <th>Kurir</th>
      <th>Nomor Picklist</th>
      <th>Tanggal Scan</th>
      <th>Jam Scan</th>
      <th>Tanggal Pick</th>
      <th>Jam Pick</th>
      <th>Tanggal Packing</th>
      <th>Jam Packing</th>
      <th>Tanggal Resi Keluar</th>
      <th>Jam Resi Keluar</th>
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
        <td><?= empty($data['tanggal_resiambilbarang']) ? null : date('Y-m-d', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td><?= empty($data['tanggal_resiambilbarang']) ? null : date('H:i', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td><?= empty($data['tanggal_packing']) ? null : date('Y-m-d', strtotime($data['tanggal_packing'])) ?></td>
        <td><?= empty($data['tanggal_packing']) ? null : date('H:i', strtotime($data['tanggal_packing'])) ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('Y-m-d', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('H:i', strtotime($data['tanggal_resikeluar'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
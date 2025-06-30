<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<table class="table table-striped table-bordered" id="datatable-report-receipt-daily">
  <thead>
    <tr>
      <?php
      $not_pick_by_receipt_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pick_resi']) / $header['total_scan_resi'] * 100;
      $not_pack_by_receipt_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pack_resi']) / $header['total_scan_resi'] * 100;
      $not_ho_by_receipt_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_ho_resi']) / $header['total_scan_resi'] * 100;
      ?>
      <th class="text-right" colspan="8">Yang belum dikerjakan dari total resi (in paket)</th>
      <th class="text-center"><?= number_format($header['total_scan_resi'] - $header['total_pick_resi']) ?></th>
      <th class="text-center"><?= number_format($not_pick_by_receipt_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
      <th class="text-center"><?= number_format($header['total_scan_resi'] - $header['total_pack_resi']) ?></th>
      <th class="text-center"><?= number_format($not_pack_by_receipt_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
      <th class="text-center"><?= number_format($header['total_scan_resi'] - $header['total_ho_resi']) ?></th>
      <th class="text-center"><?= number_format($not_ho_by_receipt_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
    </tr>
    <tr>
      <?php
      $not_pick_by_dept_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pick_resi']) / $header['total_scan_resi'] * 100;
      $not_pack_by_dept_in_percent = $header['total_pick_resi'] == 0 ? 0 : ($header['total_pick_resi'] - $header['total_pack_resi']) / $header['total_pick_resi'] * 100;
      $not_ho_by_dept_in_percent = $header['total_pack_resi'] == 0 ? 0 : ($header['total_pack_resi'] - $header['total_ho_resi']) / $header['total_pack_resi'] * 100;
      ?>
      <th class="text-right" colspan="8">Yang belum dikerjakan dari masing<sup>2</sup> dept (in paket)</th>
      <th class="text-center"><?= number_format($header['total_scan_resi'] - $header['total_pick_resi']) ?></th>
      <th class="text-center"><?= number_format($not_pick_by_dept_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
      <th class="text-center"><?= number_format($header['total_pick_resi'] - $header['total_pack_resi']) ?></th>
      <th class="text-center"><?= number_format($not_pack_by_dept_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
      <th class="text-center"><?= number_format($header['total_pack_resi'] - $header['total_ho_resi']) ?></th>
      <th class="text-center"><?= number_format($not_ho_by_dept_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
    </tr>
    <tr>
      <?php
      $done_pick_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_pick_resi'] / $header['total_scan_resi'] * 100;
      $done_pack_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_pack_resi'] / $header['total_scan_resi'] * 100;
      $done_ho_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_ho_resi'] / $header['total_scan_resi'] * 100;
      ?>
      <th class="text-right" colspan="7">Total yang sedang / sudah dikerjakan</th>
      <th class="text-center"><?= number_format($header['total_scan_resi']) ?></th>
      <th class="text-center"><?= number_format($header['total_pick_resi']) ?></th>
      <th class="text-center"><?= number_format($done_pick_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
      <th class="text-center"><?= number_format($header['total_pack_resi']) ?></th>
      <th class="text-center"><?= number_format($done_pack_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
      <th class="text-center"><?= number_format($header['total_ho_resi']) ?></th>
      <th class="text-center"><?= number_format($done_ho_in_percent, 2) ?>%</th>
      <th class="text-center">-</th>
    </tr>
    <tr>
      <th rowspan="2">#</th>
      <th rowspan="2">MP</th>
      <th rowspan="2">Kurir</th>
      <th rowspan="2"># Resi</th>
      <th rowspan="2">Pick list</th>
      <th class="text-center" colspan="3">Resi</th>
      <th class="text-center" colspan="3">Picker</th>
      <th class="text-center" colspan="3">Packer</th>
      <th class="text-center" colspan="3">HO</th>
    </tr>
    <tr>
      <th>Tgl scan</th>
      <th>Jam scan</th>
      <th>Nama</th>
      <th>Tgl scan</th>
      <th>Jam scan</th>
      <th>Nama</th>
      <th>Tgl scan</th>
      <th>Jam scan</th>
      <th>Nama</th>
      <th>Tgl scan</th>
      <th>Jam scan</th>
      <th>Nama</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= $data['nama_marketplace'] ?></td>
        <td><?= $data['nama_kurir'] ?></td>
        <td><?= $data['noresi'] ?></td>
        <td><?= $data['nomorpicklist'] ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('Y-m-d', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= empty($data['tanggal_printresi']) ? null : date('H:i', strtotime($data['tanggal_printresi'])) ?></td>
        <td><?= $data['admin_scan'] ?></td>
        <td><?= empty($data['tanggal_resiambilbarang']) ? null : date('Y-m-d', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td><?= empty($data['tanggal_resiambilbarang']) ? null : date('H:i', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td><?= $data['admin_picker'] ?></td>
        <td><?= empty($data['tanggal_packing']) ? null : date('Y-m-d', strtotime($data['tanggal_packing'])) ?></td>
        <td><?= empty($data['tanggal_packing']) ? null : date('H:i', strtotime($data['tanggal_packing'])) ?></td>
        <td><?= $data['admin_packer'] ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('Y-m-d', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= empty($data['tanggal_resikeluar']) ? null : date('H:i', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td><?= $data['admin_ho'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
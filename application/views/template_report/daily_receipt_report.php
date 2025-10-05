<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }

  /* Column colors for different departments */
  .resi-col {
    background-color: #e3f2fd !important; /* Light blue */
  }

  .picker-col {
    background-color: #e8f5e8 !important; /* Light green */
  }

  .packer-col {
    background-color: #fff3e0 !important; /* Light orange */
  }

  .ho-col {
    background-color: #fce4ec !important; /* Light pink */
  }

  /* Darker shades for headers */
  .resi-col-header {
    background-color: #bbdefb !important; /* Medium blue */
  }

  .picker-col-header {
    background-color: #c8e6c9 !important; /* Medium green */
  }

  .packer-col-header {
    background-color: #ffcc02 !important; /* Medium orange */
  }

  .ho-col-header {
    background-color: #f8bbd9 !important; /* Medium pink */
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
      <th class="text-center resi-col-header"><?= number_format($header['total_scan_resi'] - $header['total_pick_resi']) ?></th>
      <th class="text-center picker-col-header"><?= number_format($not_pick_by_receipt_in_percent, 2) ?>%</th>
      <th class="text-center picker-col-header">-</th>
      <th class="text-center packer-col-header"><?= number_format($header['total_scan_resi'] - $header['total_pack_resi']) ?></th>
      <th class="text-center packer-col-header"><?= number_format($not_pack_by_receipt_in_percent, 2) ?>%</th>
      <th class="text-center packer-col-header">-</th>
      <th class="text-center ho-col-header"><?= number_format($header['total_scan_resi'] - $header['total_ho_resi']) ?></th>
      <th class="text-center ho-col-header"><?= number_format($not_ho_by_receipt_in_percent, 2) ?>%</th>
      <th class="text-center ho-col-header">-</th>
    </tr>
    <tr>
      <?php
      $not_pick_by_dept_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pick_resi']) / $header['total_scan_resi'] * 100;
      $not_pack_by_dept_in_percent = $header['total_pick_resi'] == 0 ? 0 : ($header['total_pick_resi'] - $header['total_pack_resi']) / $header['total_pick_resi'] * 100;
      $not_ho_by_dept_in_percent = $header['total_pack_resi'] == 0 ? 0 : ($header['total_pack_resi'] - $header['total_ho_resi']) / $header['total_pack_resi'] * 100;
      ?>
      <th class="text-right" colspan="8">Yang belum dikerjakan dari masing<sup>2</sup> dept (in paket)</th>
      <th class="text-center resi-col-header"><?= number_format($header['total_scan_resi'] - $header['total_pick_resi']) ?></th>
      <th class="text-center picker-col-header"><?= number_format($not_pick_by_dept_in_percent, 2) ?>%</th>
      <th class="text-center picker-col-header">-</th>
      <th class="text-center packer-col-header"><?= number_format($header['total_pick_resi'] - $header['total_pack_resi']) ?></th>
      <th class="text-center packer-col-header"><?= number_format($not_pack_by_dept_in_percent, 2) ?>%</th>
      <th class="text-center packer-col-header">-</th>
      <th class="text-center ho-col-header"><?= number_format($header['total_pack_resi'] - $header['total_ho_resi']) ?></th>
      <th class="text-center ho-col-header"><?= number_format($not_ho_by_dept_in_percent, 2) ?>%</th>
      <th class="text-center ho-col-header">-</th>
    </tr>
    <tr>
      <?php
      $done_pick_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_pick_resi'] / $header['total_scan_resi'] * 100;
      $done_pack_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_pack_resi'] / $header['total_scan_resi'] * 100;
      $done_ho_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_ho_resi'] / $header['total_scan_resi'] * 100;
      ?>
      <th class="text-right" colspan="7">Total yang sedang / sudah dikerjakan</th>
      <th class="text-center resi-col-header"><?= number_format($header['total_scan_resi']) ?></th>
      <th class="text-center picker-col-header"><?= number_format($header['total_pick_resi']) ?></th>
      <th class="text-center picker-col-header"><?= number_format($done_pick_in_percent, 2) ?>%</th>
      <th class="text-center picker-col-header">-</th>
      <th class="text-center packer-col-header"><?= number_format($header['total_pack_resi']) ?></th>
      <th class="text-center packer-col-header"><?= number_format($done_pack_in_percent, 2) ?>%</th>
      <th class="text-center packer-col-header">-</th>
      <th class="text-center ho-col-header"><?= number_format($header['total_ho_resi']) ?></th>
      <th class="text-center ho-col-header"><?= number_format($done_ho_in_percent, 2) ?>%</th>
      <th class="text-center ho-col-header">-</th>
    </tr>
    <tr>
      <th rowspan="2">#</th>
      <th rowspan="2">MP</th>
      <th rowspan="2">Kurir</th>
      <th rowspan="2"># Resi</th>
      <th rowspan="2">Pick list</th>
      <th class="text-center" colspan="3" style="background-color: #bbdefb !important; color: #1565c0 !important;">Resi</th>
      <th class="text-center" colspan="3" style="background-color: #c8e6c9 !important; color: #2e7d32 !important;">Picker</th>
      <th class="text-center" colspan="3" style="background-color: #ffcc02 !important; color: #e65100 !important;">Packer</th>
      <th class="text-center" colspan="3" style="background-color: #f8bbd9 !important; color: #c2185b !important;">HO</th>
    </tr>
    <tr>
      <th class="resi-col-header">Tgl scan</th>
      <th class="resi-col-header">Jam scan</th>
      <th class="resi-col-header">Nama</th>
      <th class="picker-col-header">Tgl scan</th>
      <th class="picker-col-header">Jam scan</th>
      <th class="picker-col-header">Nama</th>
      <th class="packer-col-header">Tgl scan</th>
      <th class="packer-col-header">Jam scan</th>
      <th class="packer-col-header">Nama</th>
      <th class="ho-col-header">Tgl scan</th>
      <th class="ho-col-header">Jam scan</th>
      <th class="ho-col-header">Nama</th>
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
        <td class="resi-col"><?= empty($data['tanggal_printresi']) ? null : date('Y-m-d', strtotime($data['tanggal_printresi'])) ?></td>
        <td class="resi-col"><?= empty($data['tanggal_printresi']) ? null : date('H:i:s', strtotime($data['tanggal_printresi'])) ?></td>
        <td class="resi-col"><?= $data['admin_scan'] ?></td>
        <td class="picker-col"><?= empty($data['tanggal_resiambilbarang']) ? null : date('Y-m-d', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td class="picker-col"><?= empty($data['tanggal_resiambilbarang']) ? null : date('H:i:s', strtotime($data['tanggal_resiambilbarang'])) ?></td>
        <td class="picker-col"><?= $data['admin_picker'] ?></td>
        <td class="packer-col"><?= empty($data['tanggal_packing']) ? null : date('Y-m-d', strtotime($data['tanggal_packing'])) ?></td>
        <td class="packer-col"><?= empty($data['tanggal_packing']) ? null : date('H:i:s', strtotime($data['tanggal_packing'])) ?></td>
        <td class="packer-col"><?= $data['admin_packer'] ?></td>
        <td class="ho-col"><?= empty($data['tanggal_resikeluar']) ? null : date('Y-m-d', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td class="ho-col"><?= empty($data['tanggal_resikeluar']) ? null : date('H:i:s', strtotime($data['tanggal_resikeluar'])) ?></td>
        <td class="ho-col"><?= $data['admin_ho'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Laporan Buka Retur</h2>
<p>Periode: <?= $reportrange ?></p>

<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr style="background-color: #f5f5f5; font-weight: bold;">
      <th>#</th>
      <th>No. Pesanan</th>
      <th>No. Resi</th>
      <th>Market Place</th>
      <th>Kurir</th>
      <th>Tanggal Buka Retur</th>
      <th>Jam Buka Retur</th>
      <th>SKU</th>
      <th>Quantity</th>
      <th>Status Detail</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $i = 1;
    if (!empty($list_data)) :
        foreach ($list_data as $data) : ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= $data['no_pesanan'] ?? '-' ?></td>
            <td><?= $data['resi_buka'] ?? '-' ?></td>
            <td><?= $data['nama_marketplace'] ?? '-' ?></td>
            <td><?= $data['nama_kurir'] ?? '-' ?></td>
            <td><?= empty($data['tanggal_buka_retur']) ? '-' : date('Y-m-d', strtotime($data['tanggal_buka_retur'])) ?></td>
            <td><?= empty($data['tanggal_buka_retur']) ? '-' : date('H:i:s', strtotime($data['tanggal_buka_retur'])) ?></td>
            <td><?= $data['sku'] ?? '-' ?></td>
            <td><?= $data['jumlah'] ?? 0 ?></td>
            <td><?= $data['status_detail_buka'] ?? '-' ?></td>
          </tr>
        <?php endforeach;
    else : ?>
      <tr>
        <td colspan="10" style="text-align: center;">Tidak ada data</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>


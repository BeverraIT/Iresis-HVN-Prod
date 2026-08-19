<style>
  table {
    font-family: "Open Sans", sans-serif;
    font-size: 12px;
  }
</style>

<h2>Log Perubahan Berat Master SKU</h2>

<table class="table table-striped table-bordered">
  <tbody>
    <tr>
      <td>Periode</td>
      <td>:</td>
      <td><?= empty($reportrange) ? 'Seluruh riwayat' : $reportrange ?></td>
    </tr>
    <tr>
      <td>Total perubahan</td>
      <td>:</td>
      <td><?= count($list_data) ?></td>
    </tr>
    <tr>
      <td>Diunduh</td>
      <td>:</td>
      <td><?= date('d-m-Y H:i:s') ?></td>
    </tr>
  </tbody>
</table>

<table class="table table-striped table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Kode SKU</th>
      <th>Nama SKU</th>
      <th>Berat Sebelumnya (g)</th>
      <th>Berat Diperbarui (g)</th>
      <th>Selisih (g)</th>
      <th>Jenis Perubahan</th>
      <th>Oleh</th>
      <th>Tanggal Diubah</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= $data['kode_sku'] ?></td>
        <td><?= $data['nama_sku'] ?: '-' ?></td>
        <td><?= $data['berat_lama'] === null ? '-' : $data['berat_lama'] ?></td>
        <td><?= $data['berat_baru'] === null ? '-' : $data['berat_baru'] ?></td>
        <td><?php
            // Selisih hanya bermakna bila kedua sisi ada; SKU yang baru
            // ditambahkan atau dihapus tidak punya pembanding.
            echo ($data['berat_lama'] === null || $data['berat_baru'] === null)
                ? '-'
                : number_format((float) $data['berat_baru'] - (float) $data['berat_lama'], 2, '.', '');
        ?></td>
        <td><?= $data['aksi'] ?></td>
        <td><?= $data['petugas'] ?: '-' ?></td>
        <td><?= $data['waktu'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

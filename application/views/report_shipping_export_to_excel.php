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
      <td>Grand total</td>
      <td>:</td>
      <td><?= $grand_total ?></td>
    </tr>
  </tbody>
</table>

<table class="table table-striped table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Kurir</th>
      <th>Total Paket</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_data as $data) : ?>
      <tr>
        <td><?= $i++ ?>.</td>
        <td><?= $data['nama_kurir'] ?></td>
        <td><?= $data['total'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
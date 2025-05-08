<style>
  body {
    font-family: "Segoe UI", Frutiger, Tahoma, Helvetica, "Helvetica Neue", Arial, sans-serif;
  }

  table {
    font-size: 12px;
    border-width: 1px;
    border-style: solid;
    border-color: #dddddd;
    border-collapse: collapse;
    margin: 20px 0px 0px 25px;
  }

  caption {
    margin: 0 0 .5em;
    font-size: 1.2em;
    color: #383E4B;
  }

  th {
    font-size: 1.0em;
    text-align: center;
    padding: 0.5em;
    background-color: #DBEAF9;
  }

  td {
    padding: 0.5em;
    vertical-align: top;
    border-width: 1px;
    border-style: solid;
    border-color: #dddddd;
    border-collapse: collapse;
  }

  @media print {
    input.noPrint {
      display: none;
    }
  }

  .center {
    margin-left: auto;
    margin-right: auto;
  }
</style>

<h2>Tanda Terima Kirim </h2>
Periode tanggal : <?= $start_date ?><br>
Sampai dengan: <?= $end_date ?><br><br>
Kurir: <?= $nama_kurir ?><br><br>
Total: <strong><?= count($list_receipt) ?></strong><br><br>

<table class="cell-border center" cellspacing="0">
  <thead>
    <tr>
      <th>No</th>
      <th></th>
      <th>No Resi</th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1;
    foreach ($list_receipt as $receipt) : ?>
      <tr>
        <td><?= $i++ ?></td>
        <td></td>
        <td><?= $receipt['noresi'] ?></td>
      </tr>
    <?php endforeach; ?>
    <tr>
      <td>
        <br>
        <br>
      </td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td>Diserahkan Oleh,</td>
      <td></td>
      <td>Diterima Oleh, </td>
    </tr>
  </tbody>
</table>
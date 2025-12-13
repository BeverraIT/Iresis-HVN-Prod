<style>
  table {
    font-family: Arial, sans-serif;
    font-size: 11px;
    border-collapse: collapse;
    width: 100%;
  }
  
  .header-table {
    width: 100%;
    margin-bottom: 20px;
  }
  
  .header-table td {
    padding: 5px;
  }
  
  .title {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    margin: 20px 0;
  }
  
  .data-table {
    width: 100%;
    border: 1px solid #000;
  }
  
  .data-table th {
    background-color: #d3d3d3;
    border: 1px solid #000;
    padding: 8px;
    text-align: center;
    font-weight: bold;
  }
  
  .data-table td {
    border: 1px solid #000;
    padding: 5px;
    text-align: left;
  }
  
  .data-table td.text-center {
    text-align: center;
  }
  
  .data-table td.text-right {
    text-align: right;
  }
  
  .footer-table {
    width: 100%;
    margin-top: 30px;
  }
  
  .footer-table td {
    padding: 5px;
    vertical-align: top;
  }
</style>

<table class="header-table">
  <tr>
    <td style="width: 50%;">
      <strong>Tanggal :</strong> <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>
    </td>
    <td style="width: 50%; text-align: right;">
      <strong>Nomor :</strong> <?= date('YmdHis') ?>
    </td>
  </tr>
</table>

<div class="title">REQUST PICKER</div>

<table class="data-table">
  <thead>
    <tr>
      <th style="width: 5%;">NO</th>
      <th style="width: 15%;">SKU</th>
      <th style="width: 12%;">JUMLAH RESI</th>
      <th style="width: 12%;">QTY KURANG</th>
      <th style="width: 18%;">MARKET PLACE</th>
      <th style="width: 13%;">TGL CETAK</th>
      <th style="width: 13%;">B. AKHIR KIRIM</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($list_data)) : ?>
      <?php $no = 1; foreach ($list_data as $row) : ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['sku'] ?? '-') ?></td>
          <td class="text-right"><?= number_format($row['jumlah_resi'] ?? 0) ?></td>
          <td class="text-right"><?= number_format($row['total_qty_kurang'] ?? 0) ?></td>
          <td><?= htmlspecialchars($row['marketplace'] ?? '-') ?></td>
          <td class="text-center"><?= !empty($row['tgl_cetak']) ? date('d/m/Y', strtotime($row['tgl_cetak'])) : '-' ?></td>
          <td class="text-center"><?= !empty($row['b_akhir_kirim']) ? date('d/m/Y', strtotime($row['b_akhir_kirim'])) : '-' ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
      <tr>
        <td colspan="7" class="text-center">Tidak ada data</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<table class="footer-table">
  <tr>
    <td style="width: 33%;">
      <strong>Dibuat Oleh</strong><br>
      Tim Display
    </td>
    <td style="width: 33%;">
      <strong>Jam Terima :</strong><br>
      ________________
    </td>
    <td style="width: 33%;">
      <strong>Tim Accounting</strong><br>
      ________________
    </td>
  </tr>
</table>


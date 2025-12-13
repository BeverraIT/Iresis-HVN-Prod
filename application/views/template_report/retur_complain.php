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
      <strong>Tanggal :</strong> <?= date('d/m/Y H:i', strtotime($start_date)) ?> - <?= date('d/m/Y H:i', strtotime($end_date)) ?>
    </td>
    <td style="width: 50%; text-align: right;">
      <strong>Nomor :</strong> <?= date('YmdHis') ?>
    </td>
  </tr>
</table>

<div class="title">LAPORAN RETUR COMPLAIN</div>

<table class="data-table">
  <thead>
    <tr>
      <th style="width: 3%;">NO</th>
      <th style="width: 10%;">NO. RESI</th>
      <th style="width: 12%;">CUSTOMER</th>
      <th style="width: 10%;">MARKETPLACE</th>
      <th style="width: 10%;">TGL COMPLAIN</th>
      <th style="width: 10%;">TGL UPDATE</th>
      <th style="width: 8%;">TIPE</th>
      <th style="width: 10%;">STATUS</th>
      <th style="width: 12%;">CATATAN</th>
      <th style="width: 7%;">NOMINAL REFUND</th>
      <th style="width: 8%;">PERGANTIAN BARANG</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($list_data)) : ?>
      <?php 
      $no = 1; 
      $status_labels = array(
        'TO_DO' => 'To Do',
        'WAITING_CUSTOMER' => 'Waiting Customer',
        'REFUND_DANA' => 'Refund Dana',
        'PERGANTIAN_BARANG' => 'Pergantian Barang',
        'EXPIRED' => 'Expired'
      );
      foreach ($list_data as $row) : 
        $complain_type_label = ucfirst($row['complain_type']);
        if ($row['complain_type'] === 'refund') {
          $complain_type_label = 'Refund Dana';
        } elseif ($row['complain_type'] === 'replacement') {
          $complain_type_label = 'Pergantian Barang';
        }
        $status_label = isset($status_labels[$row['status']]) ? $status_labels[$row['status']] : $row['status'];
      ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['noresi'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['customer_name'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['marketplace'] ?? '-') ?></td>
          <td class="text-center"><?= !empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?></td>
          <td class="text-center"><?= !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-' ?></td>
          <td class="text-center"><?= htmlspecialchars($complain_type_label) ?></td>
          <td class="text-center"><?= htmlspecialchars($status_label) ?></td>
          <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
          <td class="text-right"><?= !empty($row['refund_amount']) ? number_format($row['refund_amount'], 0, ',', '.') : '-' ?></td>
          <td><?= !empty($row['replacement_sku']) ? htmlspecialchars($row['replacement_sku'] . ' (Qty: ' . $row['replacement_qty'] . ')') : '-' ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
      <tr>
        <td colspan="11" class="text-center">Tidak ada data</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<table class="footer-table">
  <tr>
    <td style="width: 50%;">
      <strong>Dibuat Oleh</strong><br>
      Tim CS
    </td>
    <td style="width: 50%; text-align: right;">
      <strong>Tanggal Export :</strong><br>
      <?= date('d/m/Y H:i:s') ?>
    </td>
  </tr>
</table>


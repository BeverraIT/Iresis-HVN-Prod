<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Terima Retur</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Laporan Terima Retur</h2>
    <p>Periode: <?= $start_date ?> s/d <?= $end_date ?></p>
    <?php 
    $data_retur = $list_retur->result();
    $total_quantity = 0;
    foreach ($data_retur as $row) {
        $total_quantity += $row->jumlah ?: 0;
    }
    ?>
    <p><strong>Total Quantity: <?= number_format($total_quantity, 0, ',', '.') ?></strong></p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Pinjaman</th>
                <th>No. Resi</th>
                <th>Market Place</th>
                <th>Kurir</th>
                <th>Tanggal Terima Retur</th>
                <th>Jam Terima Retur</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data_retur) > 0): ?>
                <?php $no = 1; ?>
                <?php foreach ($data_retur as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row->no_pesanan ?: '-' ?></td>
                        <td><?= $row->noresi ?></td>
                        <td><?= $row->nama_marketplace ?: '-' ?></td>
                        <td><?= $row->nama_kurir ?: '-' ?></td>
                        <td><?= date('Y-m-d', strtotime($row->tanggal_resiretur)) ?></td>
                        <td><?= date('H:i:s', strtotime($row->tanggal_resiretur)) ?></td>
                        <td><?= $row->sku ?: '-' ?></td>
                        <td><?= $row->jumlah ?: '0' ?></td>
                        <td><?= $row->status_detail ?: '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>


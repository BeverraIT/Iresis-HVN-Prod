<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Picker</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            font-size: 9px;
        }
        h2, p {
            margin: 10px 0;
        }
        .section-title {
            margin-top: 20px;
            padding: 8px 12px;
            font-weight: bold;
            background: #fff599;
            border: 1px solid #000;
        }
        .table-wrapper {
            overflow-x: auto;
            border: 1px solid #000;
            margin-bottom: 25px;
        }
        table {
            border-collapse: collapse;
            min-width: 2400px;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        .text-left { text-align: left; }
        .summary-text {
            font-weight: bold;
            margin: 10px 0;
        }
        .header-rms { background: #90EE90; font-weight: bold; }
        .header-input { background: #FFA500; font-weight: bold; }
        .header-hms { background: #98FB98; font-weight: bold; }
        .header-orange { background: #FFA500; font-weight: bold; }
        .header-yellow { background: #FFFF00; font-weight: bold; }
        .header-green { background: #90EE90; font-weight: bold; }
        .header-pink { background: #FFB6C1; font-weight: bold; }
        .header-purple { background: #DDA0DD; font-weight: bold; }
        .total-row { background: #FFFFCC; font-weight: bold; }
        .target-high { background: #b5ffb5; }
        .target-mid { background: #fff7a8; }
        .target-low { background: #ffd5d5; }
        .target-negative { background: #ff9fa0; }
    </style>
</head>
<body>

<h2>LAPORAN PICKER</h2>
<p><strong>Periode: <?= date('d/M/y', strtotime($start_date)) ?> - <?= date('d/M/y', strtotime($end_date)) ?> (<?= $num_days ?> hari)</strong></p>

<?php
$sumField = function (array $rows, string $key) {
    $total = 0;
    foreach ($rows as $row) {
        $total += isset($row[$key]) ? (float) $row[$key] : 0;
    }
    return $total;
};

$formatPercent = function ($value, $total, $decimals = 1) {
    if (!$total || $total == 0) {
        return '0%';
    }
    return number_format(($value / $total) * 100, $decimals) . '%';
};

$skuCategories = [
    '1 SKU' => 'paket_1_sku',
    '5 SKU' => 'paket_5_sku',
    '10 SKU' => 'paket_10_sku',
    '20 SKU' => 'paket_20_sku',
    '30 SKU' => 'paket_30_sku',
    '40 SKU' => 'paket_40_sku',
    '50 SKU' => 'paket_50_sku',
    '> 50 SKU' => 'paket_50plus_sku',
];

$sections = [
    'TIM INTI' => $top_pickers_inti ?? [],
    'TIM OTHERS' => $top_pickers_others ?? [],
];
?>

<?php foreach ($sections as $sectionTitle => $rows): ?>
    <div class="section-title"><?= $sectionTitle ?></div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th rowspan="3" class="header-rms">NO</th>
                    <th rowspan="3" class="header-input">AC<br>NO.</th>
                    <th rowspan="3" class="header-input">PICKER</th>
                    <th rowspan="3" class="header-input">NOR<br>MAL</th>
                    <th rowspan="3" class="header-input">S.I.A.<br>SC</th>
                    <th rowspan="3" class="header-input">LEM<br>BUR</th>
                    <th colspan="3" class="header-hms">JAM KERJA</th>
                    <th colspan="4" class="header-orange">TAR GET</th>
                    <th colspan="2" class="header-yellow">RATA2 / JAM</th>
                    <th colspan="3" class="header-yellow">PAKET</th>
                    <th colspan="5" class="header-green">SKU</th>
                    <th colspan="3" class="header-green">QTY</th>
                    <?php foreach ($skuCategories as $label => $key): ?>
                        <th colspan="3" class="header-pink"><?= $label ?></th>
                    <?php endforeach; ?>
                    <th colspan="3" class="header-purple">TOTAL</th>
                </tr>
                <tr>
                    <th rowspan="2" class="header-hms">IN</th>
                    <th rowspan="2" class="header-hms">OUT</th>
                    <th rowspan="2" class="header-hms">TTL</th>
                    <th rowspan="2" class="header-orange">TAR<br>GET</th>
                    <th rowspan="2" class="header-orange">SELISIH</th>
                    <th rowspan="2" class="header-orange">JLH</th>
                    <th rowspan="2" class="header-orange">IN %</th>
                    <th rowspan="2" class="header-yellow">PAKET</th>
                    <th rowspan="2" class="header-yellow">SKU</th>
                    <th rowspan="2" class="header-yellow">JUMLAH</th>
                    <th rowspan="2" class="header-yellow">IN %</th>
                    <th rowspan="2" class="header-yellow">RANK</th>
                    <th rowspan="2" class="header-green">JLH-AW</th>
                    <th rowspan="2" class="header-green">POT.KES</th>
                    <th rowspan="2" class="header-green">JLH-AK</th>
                    <th rowspan="2" class="header-green">IN %</th>
                    <th rowspan="2" class="header-green">RANK</th>
                    <th rowspan="2" class="header-green">JUMLAH</th>
                    <th rowspan="2" class="header-green">IN %</th>
                    <th rowspan="2" class="header-green">RANK</th>
                    <?php foreach ($skuCategories as $label => $key): ?>
                        <th colspan="3" class="header-pink"><?= $label ?></th>
                    <?php endforeach; ?>
                    <th colspan="3" class="header-purple">TOTAL</th>
                </tr>
                <tr>
                    <?php foreach ($skuCategories as $label => $key): ?>
                        <th class="header-pink">JLH</th>
                        <th class="header-pink">IN %</th>
                        <th class="header-pink">RANK</th>
                    <?php endforeach; ?>
                    <th class="header-purple">JLH</th>
                    <th class="header-purple">IN %</th>
                    <th class="header-purple">RANK</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php
                    $totalResi = $sumField($rows, 'total_resi');
                    $totalSkuQty = $sumField($rows, 'total_sku');
                    $totalTarget = $sumField($rows, 'target_resi');
                    $totalSelisih = $sumField($rows, 'selisih');
                    $countRows = count($rows);
                    $avgPaketJam = $countRows ? round($sumField($rows, 'paket_per_jam') / $countRows, 2) : 0;
                    $avgSkuJam = $countRows ? round($sumField($rows, 'sku_per_jam') / $countRows, 2) : 0;
                    $rankSku = 1;
                    $rankQty = 1;
                    $no = 1;
                    ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $selisih = $row['selisih'];
                        if ($selisih >= 0) {
                            $targetClass = 'target-high';
                        } elseif ($selisih >= -50) {
                            $targetClass = 'target-mid';
                        } elseif ($selisih >= -100) {
                            $targetClass = 'target-low';
                        } else {
                            $targetClass = 'target-negative';
                        }
                        $pctCapai = $row['pct_capai'] ?? 0;
                        $pctClass = $pctCapai >= 100 ? 'target-high' : ($pctCapai >= 90 ? 'target-mid' : 'target-low');
                        ?>
                        <tr>
                            <td><?= $no ?></td>
                            <td><?= $row['id_user'] ?></td>
                            <td class="text-left"><?= $row['nama_pegawai'] ?></td>
                            <td>1</td>
                            <td></td>
                            <td></td>
                            <td><?= $row['jam_in'] ?></td>
                            <td><?= $row['jam_out'] ?></td>
                            <td><?= $row['total_jam'] ?></td>
                            <td><?= $row['target_resi'] ?? '-' ?></td>
                            <td class="<?= $targetClass ?>"><?= $row['selisih'] ?></td>
                            <td class="<?= $pctClass ?>"><?= $row['selisih'] ?></td>
                            <td class="<?= $pctClass ?>"><?= number_format($pctCapai, 1) ?>%</td>
                            <td><?= $row['paket_per_jam'] ?? '-' ?></td>
                            <td><?= $row['sku_per_jam'] ?? '-' ?></td>
                            <td><?= $row['total_resi'] ?></td>
                            <td><?= $formatPercent($row['total_resi'], $totalResi) ?></td>
                            <td><?= $no ?></td>
                            <td><?= $row['total_sku'] ?></td>
                            <td>-</td>
                            <td><?= $row['total_sku'] ?></td>
                            <td><?= $formatPercent($row['total_sku'], $totalSkuQty) ?></td>
                            <td><?= $rankSku++ ?></td>
                            <td><?= $row['total_sku'] ?></td>
                            <td><?= $formatPercent($row['total_sku'], $totalSkuQty) ?></td>
                            <td><?= $rankQty++ ?></td>
                            <?php foreach ($skuCategories as $label => $catKey): ?>
                                <?php $catValue = $row[$catKey] ?? 0; ?>
                                <td><?= $catValue ?></td>
                                <td><?= $formatPercent($catValue, $row['total_resi']) ?></td>
                                <td>-</td>
                            <?php endforeach; ?>
                            <td><?= $row['total_resi'] ?></td>
                            <td>100%</td>
                            <td>-</td>
                        </tr>
                        <?php $no++; ?>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="9">JUMLAH <?= $sectionTitle ?></td>
                        <td><?= $totalTarget ?></td>
                        <td><?= $totalSelisih ?></td>
                        <td></td>
                        <td></td>
                        <td><?= $avgPaketJam ?></td>
                        <td><?= $avgSkuJam ?></td>
                        <td><?= $totalResi ?></td>
                        <td>100%</td>
                        <td></td>
                        <td><?= $totalSkuQty ?></td>
                        <td>-</td>
                        <td><?= $totalSkuQty ?></td>
                        <td>100%</td>
                        <td></td>
                        <td><?= $totalSkuQty ?></td>
                        <td>100%</td>
                        <td></td>
                        <?php foreach ($skuCategories as $label => $catKey): ?>
                            <?php $catTotal = $sumField($rows, $catKey); ?>
                            <td><?= $catTotal ?></td>
                            <td><?= $formatPercent($catTotal, $totalResi) ?></td>
                            <td></td>
                        <?php endforeach; ?>
                        <td><?= $totalResi ?></td>
                        <td>100%</td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="53" style="text-align:center; padding: 20px;">Tidak ada data</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<div class="summary-text">RATA2 PER ORANG =&gt;</div>

</body>
</html>


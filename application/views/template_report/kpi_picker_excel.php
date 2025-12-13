<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan KPI Picker</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            font-size: 11px;
        }
        .header-orange {
            background-color: #FFA500;
            font-weight: bold;
        }
        .header-green {
            background-color: #90EE90;
            font-weight: bold;
        }
        .header-yellow {
            background-color: #FFFF00;
            font-weight: bold;
        }
        .total-row {
            background-color: #FFFF00;
            font-weight: bold;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

<h2>LAPORAN PICKER</h2>
<p><strong>Periode:</strong> <?= date('d/M/y', strtotime($start_date)) ?> - <?= date('d/M/y H:i', strtotime($end_date)) ?> (<?= $num_days ?> hari)</strong></p>

<!-- TIM INTI -->
<table>
    <thead>
        <tr>
            <th colspan="3" class="header-yellow">TIM INTI</th>
            <th colspan="<?= $num_days > 1 ? '8' : '7' ?>" class="header-green">PAKET</th>
            <th colspan="4" class="header-green">SKU</th>
            <th colspan="3" class="header-green">QTY</th>
        </tr>
        <tr class="header-orange">
            <th>NO</th>
            <th>AC NO</th>
            <th>PICKER</th>
            <th>JUMLAH</th>
            <?php if ($num_days > 1): ?>
            <th>AVG/HARI</th>
            <?php endif; ?>
            <th>IN %</th>
            <th>RANK</th>
            <th>JLH - AW</th>
            <th>POT. KES</th>
            <th>JLH - AK</th>
            <th>IN %</th>
            <th>RANK</th>
            <th>JUMLAH</th>
            <th>IN %</th>
            <th>RANK</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($dashboard_stats['top_pickers_inti']) && count($dashboard_stats['top_pickers_inti']) > 0): ?>
            <?php 
                $total_resi_inti = 0;
                $total_sku_inti = 0;
                foreach ($dashboard_stats['top_pickers_inti'] as $index => $picker): 
                    $total_resi_inti += $picker['total_resi'];
                    $total_sku_inti += $picker['total_sku'];
                    
                    // Calculate percentage
                    $pct_resi = $dashboard_stats['total_picking'] > 0 ? round(($picker['total_resi'] / $dashboard_stats['total_picking']) * 100, 1) : 0;
                    $pct_sku = $dashboard_stats['total_sku'] > 0 ? round(($picker['total_sku'] / $dashboard_stats['total_sku']) * 100, 1) : 0;
            ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($picker['username'] ?? '-') ?></td>
                    <td class="text-left"><?= htmlspecialchars($picker['nama_pegawai'] ?? '-') ?></td>
                    <!-- PAKET -->
                    <td><?= number_format($picker['total_resi']) ?></td>
                    <?php if ($num_days > 1): ?>
                    <td><?= number_format(round($picker['total_resi'] / $num_days, 1), 1) ?></td>
                    <?php endif; ?>
                    <td><?= $pct_resi ?>%</td>
                    <td><strong>#<?= $index + 1 ?></strong></td>
                    <!-- SKU -->
                    <td><?= number_format($picker['total_sku']) ?></td>
                    <td>-</td>
                    <td><?= number_format($picker['total_sku']) ?></td>
                    <td><?= $pct_sku ?>%</td>
                    <td><strong>#<?= $index + 1 ?></strong></td>
                    <!-- QTY -->
                    <td><?= number_format($picker['total_sku']) ?></td>
                    <td><?= $pct_sku ?>%</td>
                    <td><strong>#<?= $index + 1 ?></strong></td>
                </tr>
            <?php endforeach; ?>
            
            <!-- TOTAL TIM INTI -->
            <tr class="total-row">
                <td colspan="3">JUMLAH TIM INTI</td>
                <!-- PAKET -->
                <td><?= number_format($total_resi_inti) ?></td>
                <?php if ($num_days > 1): ?>
                <td><?= number_format(round($total_resi_inti / $num_days, 1), 1) ?></td>
                <?php endif; ?>
                <td>100%</td>
                <td>-</td>
                <!-- SKU -->
                <td><?= number_format($total_sku_inti) ?></td>
                <td>-</td>
                <td><?= number_format($total_sku_inti) ?></td>
                <td>100%</td>
                <td>-</td>
                <!-- QTY -->
                <td><?= number_format($total_sku_inti) ?></td>
                <td>100%</td>
                <td>-</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="<?= $num_days > 1 ? '15' : '14' ?>" style="background-color: #FFCCCC;">Tidak ada data</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br><br>

<!-- TIM OTHERS -->
<table>
    <thead>
        <tr>
            <th colspan="3" class="header-orange">TIM OTHERS</th>
            <th colspan="<?= $num_days > 1 ? '8' : '7' ?>" class="header-green">PAKET</th>
            <th colspan="4" class="header-green">SKU</th>
            <th colspan="3" class="header-green">QTY</th>
        </tr>
        <tr class="header-orange">
            <th>NO</th>
            <th>AC NO</th>
            <th>PICKER</th>
            <th>JUMLAH</th>
            <?php if ($num_days > 1): ?>
            <th>AVG/HARI</th>
            <?php endif; ?>
            <th>IN %</th>
            <th>RANK</th>
            <th>JLH - AW</th>
            <th>POT. KES</th>
            <th>JLH - AK</th>
            <th>IN %</th>
            <th>RANK</th>
            <th>JUMLAH</th>
            <th>IN %</th>
            <th>RANK</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($dashboard_stats['top_pickers_others']) && count($dashboard_stats['top_pickers_others']) > 0): ?>
            <?php 
                $total_resi_others = 0;
                $total_sku_others = 0;
                $grand_total_resi = $dashboard_stats['total_picking'];
                $grand_total_sku = $dashboard_stats['total_sku'];
                
                foreach ($dashboard_stats['top_pickers_others'] as $index => $picker): 
                    $total_resi_others += $picker['total_resi'];
                    $total_sku_others += $picker['total_sku'];
                    
                    // Calculate percentage
                    $pct_resi = $grand_total_resi > 0 ? round(($picker['total_resi'] / $grand_total_resi) * 100, 1) : 0;
                    $pct_sku = $grand_total_sku > 0 ? round(($picker['total_sku'] / $grand_total_sku) * 100, 1) : 0;
            ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($picker['username'] ?? '-') ?></td>
                    <td class="text-left"><?= htmlspecialchars($picker['nama_pegawai'] ?? '-') ?></td>
                    <!-- PAKET -->
                    <td><?= number_format($picker['total_resi']) ?></td>
                    <?php if ($num_days > 1): ?>
                    <td><?= number_format(round($picker['total_resi'] / $num_days, 1), 1) ?></td>
                    <?php endif; ?>
                    <td><?= $pct_resi ?>%</td>
                    <td><strong>#<?= $index + 1 ?></strong></td>
                    <!-- SKU -->
                    <td><?= number_format($picker['total_sku']) ?></td>
                    <td>-</td>
                    <td><?= number_format($picker['total_sku']) ?></td>
                    <td><?= $pct_sku ?>%</td>
                    <td><strong>#<?= $index + 1 ?></strong></td>
                    <!-- QTY -->
                    <td><?= number_format($picker['total_sku']) ?></td>
                    <td><?= $pct_sku ?>%</td>
                    <td><strong>#<?= $index + 1 ?></strong></td>
                </tr>
            <?php endforeach; ?>
            
            <!-- TOTAL TIM OTHERS -->
            <tr class="total-row">
                <td colspan="3">JUMLAH - OTHERS</td>
                <!-- PAKET -->
                <td><?= number_format($total_resi_others) ?></td>
                <?php if ($num_days > 1): ?>
                <td><?= number_format(round($total_resi_others / $num_days, 1), 1) ?></td>
                <?php endif; ?>
                <td>-</td>
                <td>-</td>
                <!-- SKU -->
                <td><?= number_format($total_sku_others) ?></td>
                <td>-</td>
                <td><?= number_format($total_sku_others) ?></td>
                <td>-</td>
                <td>-</td>
                <!-- QTY -->
                <td><?= number_format($total_sku_others) ?></td>
                <td>-</td>
                <td>-</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="<?= $num_days > 1 ? '15' : '14' ?>" style="background-color: #FFCCCC;">Tidak ada data</td>
            </tr>
            <tr class="total-row">
                <td colspan="3">JUMLAH - OTHERS</td>
                <td>0</td>
                <?php if ($num_days > 1): ?>
                <td>0</td>
                <?php endif; ?>
                <td>-</td>
                <td>-</td>
                <td>0</td>
                <td>-</td>
                <td>0</td>
                <td>-</td>
                <td>-</td>
                <td>0</td>
                <td>-</td>
                <td>-</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<!-- SUMMARY -->
<table style="width: 50%;">
    <tr>
        <td class="header-yellow"><strong>TOTAL PICKING</strong></td>
        <td class="text-right"><strong><?= number_format($dashboard_stats['total_picking'] ?? 0) ?></strong></td>
    </tr>
    <tr>
        <td class="header-yellow"><strong>TOTAL SKU</strong></td>
        <td class="text-right"><strong><?= number_format($dashboard_stats['total_sku'] ?? 0) ?></strong></td>
    </tr>
    <tr>
        <td class="header-yellow"><strong>AVG SKU/RESI</strong></td>
        <td class="text-right"><strong><?= number_format($dashboard_stats['avg_sku_per_picker'] ?? 0, 2) ?></strong></td>
    </tr>
    <tr>
        <td class="header-yellow"><strong>TOTAL ACTIVE PICKERS</strong></td>
        <td class="text-right"><strong><?= number_format($dashboard_stats['total_active_pickers'] ?? 0) ?></strong></td>
    </tr>
</table>

<br>
<p><em>Generated: <?= date('Y-m-d H:i:s') ?></em></p>

</body>
</html>


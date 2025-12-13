<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Picker - <?= date('d/M/Y', strtotime($tanggal)) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        .header-row {
            background-color: #FFA500;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .section-title {
            background-color: #FFA500;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }
        .summary-row {
            background-color: #FFA500;
            font-weight: bold;
        }
        .legend-rms {
            background-color: #00FF00;
            font-weight: bold;
        }
        .legend-input {
            background-color: #FFFF00;
            font-weight: bold;
        }
        .legend-rumus {
            background-color: #00FF00;
            font-weight: bold;
        }
        .col-pink {
            background-color: #FFB6C1;
        }
        .col-yellow {
            background-color: #FFFF99;
        }
        .col-green {
            background-color: #90EE90;
        }
        .col-cyan {
            background-color: #ADD8E6;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-good {
            background-color: #90EE90;
        }
        .bg-warning {
            background-color: #FFFF99;
        }
        .bg-danger {
            background-color: #FFB6C1;
        }
    </style>
</head>
<body>

<?php
// Prepare data
$tim_inti = $performance_data['tim_inti'] ?? array();
$tim_others = $performance_data['tim_others'] ?? array();
$summary = $performance_data['summary'] ?? array();

// Calculate summary for TIM INTI
$tim_inti_total_jam = array_sum(array_column($tim_inti, 'total_jam'));
$tim_inti_total_target = array_sum(array_column($tim_inti, 'target_paket'));
$tim_inti_total_paket = array_sum(array_column($tim_inti, 'jumlah_paket'));
$tim_inti_total_selisih = $tim_inti_total_paket - $tim_inti_total_target;
$tim_inti_avg_capai = $tim_inti_total_target > 0 ? ($tim_inti_total_paket / $tim_inti_total_target) * 100 : 0;

// Calculate summary for TIM OTHERS
$tim_others_total_jam = array_sum(array_column($tim_others, 'total_jam'));
$tim_others_total_target = array_sum(array_column($tim_others, 'target_paket'));
$tim_others_total_paket = array_sum(array_column($tim_others, 'jumlah_paket'));
$tim_others_total_selisih = $tim_others_total_paket - $tim_others_total_target;
$tim_others_avg_capai = $tim_others_total_target > 0 ? ($tim_others_total_paket / $tim_others_total_target) * 100 : 0;

// GRAND TOTAL
$grand_total_picker = count($tim_inti) + count($tim_others);
$grand_total_jam = $tim_inti_total_jam + $tim_others_total_jam;
$grand_total_target = $tim_inti_total_target + $tim_others_total_target;
$grand_total_paket = $tim_inti_total_paket + $tim_others_total_paket;
$grand_total_selisih = $grand_total_paket - $grand_total_target;
$grand_avg_capai = $grand_total_target > 0 ? ($grand_total_paket / $grand_total_target) * 100 : 0;
?>

<!-- LEGEND -->
<table style="margin-bottom: 10px; width: 300px;">
    <tr>
        <td class="legend-rms">RMS</td>
        <td class="legend-input">INPUT</td>
        <td class="legend-input">INPUT</td>
        <td class="legend-input">INPUT</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rumus">RUMUS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rumus">RUMUS</td>
        <td class="legend-rms">RMS</td>
        <td class="legend-rms">RMS</td>
    </tr>
</table>

<!-- HEADER -->
<table style="margin-bottom: 10px;">
    <tr class="header-row">
        <td colspan="3" style="font-size: 16px; text-align: left; padding: 10px;">
            <strong>LAPORAN PICKER</strong>
        </td>
        <td colspan="16" style="text-align: right; padding: 10px;">
            <strong>100%</strong>
        </td>
    </tr>
    <tr>
        <td class="col-yellow font-bold"><?= date('d/M/y', strtotime($tanggal)) ?></td>
        <td class="col-yellow font-bold"><?= $summary['hari'] ?></td>
        <td class="col-green font-bold"><?= $grand_total_picker ?></td>
        <td class="col-green"><?= count($tim_inti) ?></td>
        <td class="col-green"><?= count($tim_others) ?></td>
        <td colspan="2"></td>
        <td class="col-green font-bold"><?= number_format($grand_total_jam, 2) ?></td>
        <td class="col-green font-bold"><?= number_format($grand_total_target) ?></td>
        <td class="col-green font-bold" style="background-color: <?= $grand_total_selisih >= 0 ? '#90EE90' : '#FFB6C1' ?>">
            <?= number_format($grand_total_selisih) ?>
        </td>
        <td class="col-green font-bold"><?= number_format($grand_avg_capai, 1) ?>%</td>
        <td colspan="2"></td>
        <td class="col-green font-bold"><?= number_format($grand_total_paket) ?></td>
        <td class="col-green font-bold">100%</td>
        <td colspan="2"></td>
        <td class="col-green font-bold"><?= number_format(array_sum(array_column(array_merge($tim_inti, $tim_others), 'paket_50_sku'))) ?></td>
        <td class="col-green font-bold">100%</td>
    </tr>
</table>

<!-- TIM INTI TABLE -->
<table>
    <thead>
        <tr class="section-title">
            <td colspan="19" style="padding: 8px; font-size: 14px;">TIM INTI</td>
        </tr>
        <tr style="background-color: #ADD8E6; font-weight: bold; font-size: 10px;">
            <td rowspan="2">NO</td>
            <td rowspan="2">AC NO.</td>
            <td rowspan="2" style="min-width: 150px;">PICKER</td>
            <td rowspan="2">NORMAL</td>
            <td rowspan="2">S,I,A,SC</td>
            <td rowspan="2">LEMBUR</td>
            <td colspan="3">JAM KERJA</td>
            <td rowspan="2">TAR GET</td>
            <td colspan="2">SELISIH</td>
            <td rowspan="2">RATA2<br>/JAM</td>
            <td rowspan="2">RATA2<br>/JAM</td>
            <td colspan="3">PAKET</td>
            <td colspan="3">> 50 SKU</td>
            <td colspan="3">TOTAL</td>
        </tr>
        <tr style="background-color: #ADD8E6; font-weight: bold; font-size: 10px;">
            <td>IN</td>
            <td>OUT</td>
            <td>TTL</td>
            <td>JLH</td>
            <td>IN %</td>
            <td>JUMLAH</td>
            <td>IN %</td>
            <td>RANK</td>
            <td>JLH</td>
            <td>IN %</td>
            <td>RANK</td>
            <td>JLH</td>
            <td>IN %</td>
            <td>RANK</td>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($tim_inti)): ?>
            <?php 
            $rank_by_paket = $tim_inti;
            usort($rank_by_paket, function($a, $b) {
                return $b['jumlah_paket'] - $a['jumlah_paket'];
            });
            $ranking = array();
            foreach ($rank_by_paket as $idx => $p) {
                $ranking[$p['kode_pegawai']] = $idx + 1;
            }
            ?>
            <?php foreach ($tim_inti as $index => $picker): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($picker['username'] ?? $picker['kode_pegawai']) ?></td>
                    <td class="text-left"><?= htmlspecialchars($picker['nama_pegawai']) ?></td>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td><?= $picker['jam_in'] ? date('H:i', strtotime($picker['jam_in'])) : '' ?></td>
                    <td><?= $picker['jam_out'] ? date('H:i', strtotime($picker['jam_out'])) : '' ?></td>
                    <td><?= number_format($picker['total_jam'], 2) ?></td>
                    <td><?= number_format($picker['target_paket']) ?></td>
                    <td style="background-color: <?= $picker['selisih'] >= 0 ? '#90EE90' : '#FFB6C1' ?>">
                        <?= number_format($picker['selisih']) ?>
                    </td>
                    <td><?= number_format($picker['persentase_capai'], 1) ?>%</td>
                    <td><?= number_format($picker['rata_per_jam'], 0) ?></td>
                    <td><?= number_format($picker['rata_per_jam'], 0) ?></td>
                    <td class="font-bold"><?= number_format($picker['jumlah_paket']) ?></td>
                    <td><?= number_format(($tim_inti_total_paket > 0 ? ($picker['jumlah_paket'] / $tim_inti_total_paket) * 100 : 0), 1) ?>%</td>
                    <td><?= isset($ranking[$picker['kode_pegawai']]) ? $ranking[$picker['kode_pegawai']] : '-' ?></td>
                    <td><?= number_format($picker['paket_50_sku']) ?></td>
                    <td><?= number_format(($picker['jumlah_paket'] > 0 ? ($picker['paket_50_sku'] / $picker['jumlah_paket']) * 100 : 0), 1) ?>%</td>
                    <td>-</td>
                    <td class="font-bold"><?= number_format($picker['jumlah_paket']) ?></td>
                    <td><?= number_format($picker['persentase_capai'], 0) ?>%</td>
                    <td><?= isset($ranking[$picker['kode_pegawai']]) ? $ranking[$picker['kode_pegawai']] : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            <!-- SUMMARY TIM INTI -->
            <tr class="summary-row">
                <td colspan="8" class="text-right"><strong>JUMLAH TIM INTI</strong></td>
                <td><strong><?= number_format($tim_inti_total_jam, 2) ?></strong></td>
                <td><strong><?= number_format($tim_inti_total_target) ?></strong></td>
                <td style="background-color: <?= $tim_inti_total_selisih >= 0 ? '#90EE90' : '#FFB6C1' ?>">
                    <strong><?= number_format($tim_inti_total_selisih) ?></strong>
                </td>
                <td><strong><?= number_format($tim_inti_avg_capai, 1) ?>%</strong></td>
                <td><strong><?= $tim_inti_total_jam > 0 ? number_format($tim_inti_total_paket / $tim_inti_total_jam, 0) : 0 ?></strong></td>
                <td><strong><?= count($tim_inti) > 0 ? number_format($tim_inti_total_paket / count($tim_inti) / ($tim_inti_total_jam / count($tim_inti)), 0) : 0 ?></strong></td>
                <td><strong><?= number_format($tim_inti_total_paket) ?></strong></td>
                <td><strong>100%</strong></td>
                <td>-</td>
                <td><strong><?= number_format(array_sum(array_column($tim_inti, 'paket_50_sku'))) ?></strong></td>
                <td>-</td>
                <td>-</td>
                <td><strong><?= number_format($tim_inti_total_paket) ?></strong></td>
                <td><strong>100%</strong></td>
                <td>-</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="23" class="text-center">Tidak ada data TIM INTI</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<!-- TIM OTHERS TABLE -->
<table>
    <thead>
        <tr class="section-title">
            <td colspan="19" style="padding: 8px; font-size: 14px;">TIM OTHERS</td>
        </tr>
        <tr style="background-color: #ADD8E6; font-weight: bold; font-size: 10px;">
            <td rowspan="2">NO</td>
            <td rowspan="2">USER ID</td>
            <td rowspan="2" style="min-width: 150px;">NAMA</td>
            <td rowspan="2">NORMAL</td>
            <td rowspan="2">S,I,A,SC</td>
            <td rowspan="2">LEMBUR</td>
            <td colspan="3">JAM KERJA</td>
            <td rowspan="2">TAR GET</td>
            <td colspan="2">SELISIH</td>
            <td rowspan="2">RATA2<br>/JAM</td>
            <td rowspan="2">RATA2<br>/JAM</td>
            <td colspan="3">PAKET</td>
            <td colspan="3">> 50 SKU</td>
            <td colspan="3">TOTAL</td>
        </tr>
        <tr style="background-color: #ADD8E6; font-weight: bold; font-size: 10px;">
            <td>IN</td>
            <td>OUT</td>
            <td>TTL</td>
            <td>JLH</td>
            <td>IN %</td>
            <td>JUMLAH</td>
            <td>IN %</td>
            <td>RANK</td>
            <td>JLH</td>
            <td>IN %</td>
            <td>RANK</td>
            <td>JLH</td>
            <td>IN %</td>
            <td>RANK</td>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($tim_others)): ?>
            <?php 
            $rank_by_paket_others = $tim_others;
            usort($rank_by_paket_others, function($a, $b) {
                return $b['jumlah_paket'] - $a['jumlah_paket'];
            });
            $ranking_others = array();
            foreach ($rank_by_paket_others as $idx => $p) {
                $ranking_others[$p['kode_pegawai']] = $idx + 1;
            }
            ?>
            <?php foreach ($tim_others as $index => $picker): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($picker['username'] ?? $picker['kode_pegawai']) ?></td>
                    <td class="text-left"><?= htmlspecialchars($picker['nama_pegawai']) ?></td>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td><?= $picker['jam_in'] ? date('H:i', strtotime($picker['jam_in'])) : '' ?></td>
                    <td><?= $picker['jam_out'] ? date('H:i', strtotime($picker['jam_out'])) : '' ?></td>
                    <td><?= number_format($picker['total_jam'], 2) ?></td>
                    <td><?= number_format($picker['target_paket']) ?></td>
                    <td style="background-color: <?= $picker['selisih'] >= 0 ? '#90EE90' : '#FFB6C1' ?>">
                        <?= number_format($picker['selisih']) ?>
                    </td>
                    <td><?= number_format($picker['persentase_capai'], 1) ?>%</td>
                    <td><?= number_format($picker['rata_per_jam'], 0) ?></td>
                    <td><?= number_format($picker['rata_per_jam'], 0) ?></td>
                    <td class="font-bold"><?= number_format($picker['jumlah_paket']) ?></td>
                    <td><?= number_format(($tim_others_total_paket > 0 ? ($picker['jumlah_paket'] / $tim_others_total_paket) * 100 : 0), 1) ?>%</td>
                    <td><?= isset($ranking_others[$picker['kode_pegawai']]) ? $ranking_others[$picker['kode_pegawai']] : '-' ?></td>
                    <td><?= number_format($picker['paket_50_sku']) ?></td>
                    <td><?= number_format(($picker['jumlah_paket'] > 0 ? ($picker['paket_50_sku'] / $picker['jumlah_paket']) * 100 : 0), 1) ?>%</td>
                    <td>-</td>
                    <td class="font-bold"><?= number_format($picker['jumlah_paket']) ?></td>
                    <td><?= number_format($picker['persentase_capai'], 0) ?>%</td>
                    <td><?= isset($ranking_others[$picker['kode_pegawai']]) ? $ranking_others[$picker['kode_pegawai']] : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            <!-- SUMMARY TIM OTHERS -->
            <tr class="summary-row">
                <td colspan="8" class="text-right"><strong>JUMLAH - OTHERS</strong></td>
                <td><strong><?= number_format($tim_others_total_jam, 2) ?></strong></td>
                <td><strong><?= number_format($tim_others_total_target) ?></strong></td>
                <td style="background-color: <?= $tim_others_total_selisih >= 0 ? '#90EE90' : '#FFB6C1' ?>">
                    <strong><?= number_format($tim_others_total_selisih) ?></strong>
                </td>
                <td><strong><?= number_format($tim_others_avg_capai, 1) ?>%</strong></td>
                <td><strong><?= $tim_others_total_jam > 0 ? number_format($tim_others_total_paket / $tim_others_total_jam, 0) : 0 ?></strong></td>
                <td><strong><?= count($tim_others) > 0 ? number_format($tim_others_total_paket / count($tim_others) / ($tim_others_total_jam / count($tim_others)), 0) : 0 ?></strong></td>
                <td><strong><?= number_format($tim_others_total_paket) ?></strong></td>
                <td><strong>100%</strong></td>
                <td>-</td>
                <td><strong><?= number_format(array_sum(array_column($tim_others, 'paket_50_sku'))) ?></strong></td>
                <td>-</td>
                <td>-</td>
                <td><strong><?= number_format($tim_others_total_paket) ?></strong></td>
                <td><strong>100%</strong></td>
                <td>-</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="23" class="text-center">Tidak ada data TIM OTHERS</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<!-- RATA-RATA SUMMARY -->
<table style="width: 600px;">
    <tr>
        <td style="background-color: #f0f0f0; text-align: left; padding: 8px; font-weight: bold;">RATA2 PER ORANG =></td>
        <td class="col-yellow font-bold"><?= number_format($summary['rata_jam_per_orang'], 2) ?></td>
        <td class="font-bold">JAM</td>
        <td class="col-pink font-bold"><?= number_format($summary['rata_paket_per_orang'] - 400, 0) ?></td>
        <td class="font-bold">PAKET</td>
        <td class="col-yellow font-bold"><?= number_format($summary['rata_paket_per_orang'], 0) ?></td>
        <td class="font-bold">PAKET</td>
        <td class="col-yellow font-bold">-</td>
        <td class="font-bold">PAKET</td>
    </tr>
    <tr>
        <td style="background-color: #f0f0f0; text-align: left; padding: 8px; font-weight: bold;">RATA2 / ORG / JAM =></td>
        <td colspan="2"></td>
        <td colspan="2"></td>
        <td class="col-cyan font-bold"><?= number_format($summary['rata_paket_per_jam'], 0) ?></td>
        <td class="font-bold">PAKET / JAM</td>
        <td class="col-cyan font-bold">-</td>
        <td class="font-bold">PKT / JAM</td>
    </tr>
</table>

<br><br>

<!-- FOOTER INFO -->
<p style="font-size: 10px; color: #666;">
    Generated: <?= date('Y-m-d H:i:s') ?><br>
    Total Picker: <?= $grand_total_picker ?> | Total Paket: <?= number_format($grand_total_paket) ?> | Achievement: <?= number_format($grand_avg_capai, 1) ?>%
</p>

</body>
</html>


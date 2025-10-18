<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KPI Reports Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { background-color: #337ab7; color: white; padding: 10px; text-align: center; }
        .section { margin: 20px 0; }
        .section-title { background-color: #f5f5f5; padding: 8px; font-weight: bold; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .number { text-align: right; }
        .center { text-align: center; }
        .summary-card { display: inline-block; width: 200px; margin: 10px; padding: 15px; border: 1px solid #ddd; text-align: center; }
        .summary-card h3 { margin: 0; font-size: 24px; color: #337ab7; }
        .summary-card p { margin: 5px 0 0 0; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KPI Reports Export</h1>
        <p>Date Range: <?= $reportrange ?></p>
        <p>Generated: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <!-- KPI Summary -->
    <div class="section">
        <div class="section-title">KPI Summary</div>
        <div class="summary-card">
            <h3><?= $kpi_data['total_receipts'] ?? 0 ?></h3>
            <p>Total Receipts</p>
        </div>
        <div class="summary-card">
            <h3><?= $kpi_data['shipped_receipts'] ?? 0 ?></h3>
            <p>Shipped Receipts</p>
        </div>
        <div class="summary-card">
            <h3><?= $kpi_data['pending_receipts'] ?? 0 ?></h3>
            <p>Pending Receipts</p>
        </div>
        <div class="summary-card">
            <h3><?= $kpi_data['retur_receipts'] ?? 0 ?></h3>
            <p>Retur Receipts</p>
        </div>
        <div class="summary-card">
            <h3><?= $kpi_data['completion_rate'] ?? 0 ?>%</h3>
            <p>Completion Rate</p>
        </div>
        <div class="summary-card">
            <h3><?= $kpi_data['retur_rate'] ?? 0 ?>%</h3>
            <p>Retur Rate</p>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="section">
        <div class="section-title">Performance Metrics</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Average Processing Time</td>
                    <td class="number"><?= $kpi_data['avg_processing_time'] ?? 0 ?> hours</td>
                    <td>Average time to process receipts</td>
                </tr>
                <tr>
                    <td>Picker Productivity</td>
                    <td class="number"><?= $kpi_data['picker_productivity'] ?? 0 ?> receipts/day</td>
                    <td>Average picker productivity</td>
                </tr>
                <tr>
                    <td>Packer Productivity</td>
                    <td class="number"><?= $kpi_data['packer_productivity'] ?? 0 ?> receipts/day</td>
                    <td>Average packer productivity</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Daily Performance Data -->
    <?php if (isset($kpi_data['daily_performance']) && !empty($kpi_data['daily_performance'])): ?>
    <div class="section">
        <div class="section-title">Daily Performance Trend</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Receipts</th>
                    <th>Shipped</th>
                    <th>Pending</th>
                    <th>Retur</th>
                    <th>Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kpi_data['daily_performance'] as $day): ?>
                <tr>
                    <td><?= $day['date'] ?? '' ?></td>
                    <td class="number"><?= $day['total'] ?? 0 ?></td>
                    <td class="number"><?= $day['shipped'] ?? 0 ?></td>
                    <td class="number"><?= $day['pending'] ?? 0 ?></td>
                    <td class="number"><?= $day['retur'] ?? 0 ?></td>
                    <td class="number"><?= $day['completion_rate'] ?? 0 ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Recommendations -->
    <div class="section">
        <div class="section-title">Recommendations</div>
        <ul>
            <?php if (($kpi_data['completion_rate'] ?? 0) < 95): ?>
            <li><strong>Improve Completion Rate:</strong> Current completion rate is <?= $kpi_data['completion_rate'] ?? 0 ?>%. Target should be above 95%.</li>
            <?php endif; ?>
            
            <?php if (($kpi_data['retur_rate'] ?? 0) > 5): ?>
            <li><strong>Reduce Retur Rate:</strong> Current retur rate is <?= $kpi_data['retur_rate'] ?? 0 ?>%. Target should be below 5%.</li>
            <?php endif; ?>
            
            <?php if (($kpi_data['avg_processing_time'] ?? 0) > 24): ?>
            <li><strong>Improve Processing Time:</strong> Average processing time is <?= $kpi_data['avg_processing_time'] ?? 0 ?> hours. Target should be below 24 hours.</li>
            <?php endif; ?>
            
            <?php if (($kpi_data['picker_productivity'] ?? 0) < 50): ?>
            <li><strong>Increase Picker Productivity:</strong> Current picker productivity is <?= $kpi_data['picker_productivity'] ?? 0 ?> receipts/day. Consider training or process optimization.</li>
            <?php endif; ?>
            
            <?php if (($kpi_data['packer_productivity'] ?? 0) < 50): ?>
            <li><strong>Increase Packer Productivity:</strong> Current packer productivity is <?= $kpi_data['packer_productivity'] ?? 0 ?> receipts/day. Consider training or process optimization.</li>
            <?php endif; ?>
            
            <?php if (($kpi_data['completion_rate'] ?? 0) >= 95 && ($kpi_data['retur_rate'] ?? 0) <= 5): ?>
            <li><strong>Excellent Performance:</strong> All key metrics are within target ranges. Keep up the good work!</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Footer -->
    <div class="section">
        <div class="section-title">Export Information</div>
        <p><strong>Generated by:</strong> <?= $this->session->userdata('user')['username'] ?? 'System' ?></p>
        <p><strong>Export Date:</strong> <?= date('Y-m-d H:i:s') ?></p>
        <p><strong>Data Period:</strong> <?= $reportrange ?></p>
        <p><strong>System:</strong> BEVERRA - Manajemen Resi</p>
    </div>
</body>
</html>
<?php
// Cek akses - hanya user yang memiliki akses ke menu Dashboard KPI
$user = $this->session->userdata('user');
if (!$user || !isset($user['id_user'])) {
    redirect('welcome/restricted');
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Dashboard KPI</strong></h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="<?= base_url('kpi_reports/dashboard') ?>" id="form-filter-kpi">
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
                        <div class="col-md-3 col-xs-12">
                            <input type="text" name="reportrange" id="reportrange" class="form-control" value="<?= !empty($reportrange) ? $reportrange : null ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label"></label>
                        <div class="col-md-2 col-xs-12">
                            <button type="submit" class="btn btn-info" id="btn-search">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <button type="button" class="btn btn-default" onclick="location.reload()">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                </form>
                <hr>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-qrcode fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_scan'] ?? 0) ?></div>
                        <div>Resi Scan</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total Scanned Receipts</span>
                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-shopping-basket fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_picking'] ?? 0) ?></div>
                        <div>Picking</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total Picked Items</span>
                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-cube fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_packing'] ?? 0) ?></div>
                        <div>Packing</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total Packed Items</span>
                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-truck fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_handover'] ?? 0) ?></div>
                        <div>Hand Over</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total Handed Over</span>
                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row">
    <div class="col-lg-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-line-chart"></i> Performance Overview
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="metric-box">
                            <h4>Active Users</h4>
                            <h2 class="text-primary"><?= number_format($dashboard_stats['total_active_users'] ?? 0) ?></h2>
                            <p class="text-muted">Currently working</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-box">
                            <h4>Completion Rate</h4>
                            <h2 class="text-success"><?= number_format($dashboard_stats['completion_rate'] ?? 0, 2) ?>%</h2>
                            <p class="text-muted">Scan to Handover</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-box">
                            <h4>Avg Processing</h4>
                            <h2 class="text-warning"><?= number_format($dashboard_stats['avg_processing_time'] ?? 0, 1) ?> hrs</h2>
                            <p class="text-muted">Average time</p>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Hourly Performance Chart -->
                <div class="chart-container">
                    <canvas id="hourlyPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-pie-chart"></i> Status Distribution
            </div>
            <div class="panel-body">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers -->
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-success">
            <div class="panel-heading">
                <i class="fa fa-trophy"></i> Top 5 Pickers
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dashboard_stats['top_pickers']) && count($dashboard_stats['top_pickers']) > 0): ?>
                                <?php foreach ($dashboard_stats['top_pickers'] as $index => $picker): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($picker['username'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($picker['nama_pegawai'] ?? 'N/A') ?></td>
                                        <td class="text-right">
                                            <span class="badge badge-success"><?= number_format($picker['total_transaksi']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <i class="fa fa-trophy"></i> Top 5 Packers
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dashboard_stats['top_packers']) && count($dashboard_stats['top_packers']) > 0): ?>
                                <?php foreach ($dashboard_stats['top_packers'] as $index => $packer): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($packer['username'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($packer['nama_pegawai'] ?? 'N/A') ?></td>
                                        <td class="text-right">
                                            <span class="badge badge-warning"><?= number_format($packer['total_transaksi']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-info-circle"></i> System Information
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Dashboard Updated:</strong></p>
                        <p><?= date('Y-m-d H:i:s') ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Period:</strong></p>
                        <p><?= $reportrange ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Status:</strong></p>
                        <p><span class="label label-success">System Operational</span></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Access Level:</strong></p>
                        <p><span class="label label-info">Admin & Webmaster Only</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Initialize date range picker - sama seperti Laporan Resi Harian
var report_range = <?= !empty($reportrange) ? '"' . $reportrange . '"' : "null" ?>;

var start = report_range !== null ? moment(report_range.split(" - ")[0]) : moment().startOf('day');
var end = report_range !== null ? moment(report_range.split(" - ")[1]) : moment();

$('#reportrange').daterangepicker({
    timePicker: true,
    timePicker24Hour: true,
    startDate: start,
    endDate: end,
    ranges: {
        'Today': [moment().startOf('day'), moment()],
        'Last 1 Hours': [moment().subtract(1, 'hours'), moment()],
        'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().startOf('day')],
        'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
    },
    locale: {
        format: 'YYYY-MM-DD HH:mm:ss'
    }
});

// Initialize charts
$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    // Hourly Performance Chart
    var hourlyData = <?= json_encode($dashboard_stats['hourly_performance'] ?? []) ?>;
    
    if (hourlyData.length > 0) {
        var hours = hourlyData.map(item => item.hour + ':00');
        var totals = hourlyData.map(item => item.total);
        
        var ctx1 = document.getElementById('hourlyPerformanceChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Transactions per Hour',
                    data: totals,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Transactions'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hour'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Status Distribution Doughnut Chart
    var statusData = [
        <?= $dashboard_stats['total_scan'] ?? 0 ?>,
        <?= $dashboard_stats['total_picking'] ?? 0 ?>,
        <?= $dashboard_stats['total_packing'] ?? 0 ?>,
        <?= $dashboard_stats['total_handover'] ?? 0 ?>
    ];
    
    var ctx2 = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Scan', 'Picking', 'Packing', 'Hand Over'],
            datasets: [{
                data: statusData,
                backgroundColor: [
                    'rgba(66, 139, 202, 0.8)',
                    'rgba(92, 184, 92, 0.8)',
                    'rgba(240, 173, 78, 0.8)',
                    'rgba(91, 192, 222, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}
</script>

<style>
/* Form Control */
.form-control {
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.form-control:focus {
    border-color: #66afe9;
    outline: 0;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
}

.huge {
    font-size: 32px;
    font-weight: bold;
}

.panel-heading {
    padding: 15px;
}

.panel-heading .row {
    display: flex;
    align-items: center;
}

.panel-footer {
    padding: 10px 15px;
    background-color: rgba(0,0,0,0.05);
}

.metric-box {
    text-align: center;
    padding: 15px;
    border-right: 1px solid #e7e7e7;
}

.metric-box:last-child {
    border-right: none;
}

.metric-box h4 {
    margin-top: 0;
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.metric-box h2 {
    margin: 10px 0;
    font-weight: bold;
}

.chart-container {
    position: relative;
    height: 250px;
    margin-top: 20px;
}

.badge {
    padding: 5px 10px;
    font-size: 12px;
    font-weight: bold;
}

.badge-success {
    background-color: #5cb85c;
    color: white;
}

.badge-warning {
    background-color: #f0ad4e;
    color: white;
}

.table > thead > tr > th {
    border-bottom: 2px solid #ddd;
}

.panel-success .panel-heading {
    background-color: #5cb85c;
    color: white;
}

.panel-warning .panel-heading {
    background-color: #f0ad4e;
    color: white;
}

.panel-primary .panel-heading,
.panel-info .panel-heading {
    color: white;
}

@media (max-width: 768px) {
    .metric-box {
        border-right: none;
        border-bottom: 1px solid #e7e7e7;
        margin-bottom: 15px;
    }
    
    .metric-box:last-child {
        border-bottom: none;
    }
}

/* FORCE FIX: Hide any duplicate sidebars */
.page-sidebar ~ .page-sidebar {
    display: none !important;
    visibility: hidden !important;
    position: absolute !important;
    left: -9999px !important;
}

/* Ensure only first sidebar is visible */
body .page-container > .page-sidebar:first-of-type {
    display: block !important;
    visibility: visible !important;
}
</style>

<script>
// DEBUG & FIX: Count sidebars and remove duplicates
$(document).ready(function() {
    console.log('=== DASHBOARD KPI DEBUG START ===');
    
    var sidebarCount = $('.page-sidebar').length;
    var navCount = $('.x-navigation').length;
    
    console.log('Number of .page-sidebar elements:', sidebarCount);
    console.log('Number of .x-navigation elements:', navCount);
    console.log('Expected: 1 sidebar, 2 navigation (1 vertical + 1 horizontal)');
    
    if (sidebarCount > 1) {
        console.error('ERROR: Multiple sidebars detected (' + sidebarCount + ')!');
        console.log('Attempting to fix by removing duplicates...');
        
        // Log each sidebar
        $('.page-sidebar').each(function(index) {
            console.log('Sidebar #' + (index + 1) + ':', this);
            console.log('  Parent:', $(this).parent()[0]);
            console.log('  HTML:', $(this).html().substring(0, 100));
        });
        
        // FORCE FIX: Keep only the first sidebar, remove all others
        $('.page-sidebar:gt(0)').each(function(index) {
            console.log('Removing duplicate sidebar #' + (index + 2));
            $(this).remove();
        });
        
        // Verify fix
        var newCount = $('.page-sidebar').length;
        console.log('After fix: ' + newCount + ' sidebar(s) remaining');
        
        if (newCount === 1) {
            console.log('✓ Fix successful!');
        } else {
            console.error('✗ Fix failed, still ' + newCount + ' sidebars');
        }
    } else if (sidebarCount === 1) {
        console.log('✓ Sidebar count is correct (1)');
    } else {
        console.error('✗ No sidebar found!');
    }
    
    console.log('=== DASHBOARD KPI DEBUG END ===');
});
</script>


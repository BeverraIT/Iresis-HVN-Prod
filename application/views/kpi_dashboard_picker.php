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
                <h3 class="panel-title"><strong>Dashboard KPI Picker</strong></h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="<?= base_url('kpi_reports/dashboard_picker') ?>" id="form-filter-kpi-picker">
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
                        <div class="col-md-3 col-xs-12">
                            <input type="text" name="reportrange" id="reportrange" class="form-control" value="<?= !empty($reportrange) ? $reportrange : null ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label"></label>
                        <div class="col-md-6 col-xs-12">
                            <button type="submit" class="btn btn-info" id="btn-search">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <button type="button" class="btn btn-default" id="btn-refresh-kpi-picker">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-success" id="btn-export-excel">
                                <i class="fa fa-file-excel-o"></i> Export Excel
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
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-shopping-basket fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_picking'] ?? 0) ?></div>
                        <div>Total Picking</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total Picked Receipts</span>
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
                        <i class="fa fa-cubes fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_qty'] ?? 0) ?></div>
                        <div>Total SKU</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total SKU Picked</span>
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
                        <i class="fa fa-line-chart fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['avg_sku_per_picker'] ?? 0, 2) ?></div>
                        <div>Avg SKU/Resi</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Average SKU per Receipt</span>
                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-users fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($dashboard_stats['total_active_pickers'] ?? 0) ?></div>
                        <div>Active Pickers</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <span class="pull-left">Total Active Pickers</span>
                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Chart -->
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart"></i> Hourly Picking Performance
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="hourlyPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-trophy"></i> Top 5 Performance Ranking
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="rankingPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers - TIM INTI -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-success">
            <div class="panel-heading" style="background-color: #5cb85c; color: white;">
                <i class="fa fa-trophy"></i> <strong>TIM INTI - Picker Performance</strong>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr style="background-color: #d9edf7;">
                                <th>#</th>
                                <th>Username</th>
                                <th>Nama Pegawai</th>
                                <th class="text-right">Total Resi</th>
                                <th class="text-right">Total SKU</th>
                                <th class="text-right">Total Qty</th>
                                <th class="text-right">Avg SKU/Resi</th>
                                <th class="text-center">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dashboard_stats['top_pickers_inti']) && count($dashboard_stats['top_pickers_inti']) > 0): ?>
                                <?php foreach ($dashboard_stats['top_pickers_inti'] as $index => $picker): ?>
                                    <?php 
                                        // Determine performance status based on SKU Diversity Index
                                        $avg_sku = $picker['avg_sku_per_resi'] ?? 0;
                                        $total_qty = $picker['total_qty'] ?? 0;
                                        $total_sku_unique = $picker['total_sku_unique'] ?? 0;
                                        
                                        // Calculate SKU diversity (ratio of unique SKUs to total SKUs)
                                        $sku_diversity = $total_qty > 0 ? $total_sku_unique / $total_qty : 0;
                                        
                                        // Performance based on both volume and diversity
                                        if ($avg_sku >= 5 && $sku_diversity >= 0.7) {
                                            $status = 'EXCELLENT';
                                            $status_class = 'primary';
                                        } elseif ($avg_sku >= 4 && $sku_diversity >= 0.5) {
                                            $status = 'GOOD';
                                            $status_class = 'success';
                                        } elseif ($avg_sku >= 3 && $sku_diversity >= 0.3) {
                                            $status = 'FAIR';
                                            $status_class = 'warning';
                                        } elseif ($avg_sku >= 2) {
                                            $status = 'BELOW AVG';
                                            $status_class = 'warning';
                                        } else {
                                            $status = 'POOR';
                                            $status_class = 'danger';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($picker['username'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($picker['nama_pegawai'] ?? 'N/A') ?></td>
                                        <td class="text-right"><?= number_format($picker['total_resi']) ?></td>
                                        <td class="text-right"><?= number_format($picker['total_sku_unique']) ?></td>
                                        <td class="text-right"><?= number_format($picker['total_qty']) ?></td>
                                        <td class="text-right"><?= number_format($picker['avg_sku_per_resi'], 2) ?></td>
                                        <td class="text-center">
                                            <span class="label label-<?= $status_class ?>"><?= $status ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers - TIM OTHERS -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-warning">
            <div class="panel-heading" style="background-color: #f0ad4e; color: white;">
                <i class="fa fa-users"></i> <strong>TIM OTHERS - User Lain yang Bantu Picking</strong>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr style="background-color: #fcf8e3;">
                                <th>#</th>
                                <th>Username</th>
                                <th>Nama Pegawai</th>
                                <th class="text-right">Total Resi</th>
                                <th class="text-right">Total SKU</th>
                                <th class="text-right">Total Qty</th>
                                <th class="text-right">Avg SKU/Resi</th>
                                <th class="text-center">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dashboard_stats['top_pickers_others']) && count($dashboard_stats['top_pickers_others']) > 0): ?>
                                <?php foreach ($dashboard_stats['top_pickers_others'] as $index => $picker): ?>
                                    <?php 
                                        // Determine performance status based on SKU Diversity Index
                                        $avg_sku = $picker['avg_sku_per_resi'] ?? 0;
                                        $total_qty = $picker['total_qty'] ?? 0;
                                        $total_sku_unique = $picker['total_sku_unique'] ?? 0;
                                        
                                        // Calculate SKU diversity (ratio of unique SKUs to total SKUs)
                                        $sku_diversity = $total_qty > 0 ? $total_sku_unique / $total_qty : 0;
                                        
                                        // Performance based on both volume and diversity
                                        if ($avg_sku >= 5 && $sku_diversity >= 0.7) {
                                            $status = 'EXCELLENT';
                                            $status_class = 'primary';
                                        } elseif ($avg_sku >= 4 && $sku_diversity >= 0.5) {
                                            $status = 'GOOD';
                                            $status_class = 'success';
                                        } elseif ($avg_sku >= 3 && $sku_diversity >= 0.3) {
                                            $status = 'FAIR';
                                            $status_class = 'warning';
                                        } elseif ($avg_sku >= 2) {
                                            $status = 'BELOW AVG';
                                            $status_class = 'warning';
                                        } else {
                                            $status = 'POOR';
                                            $status_class = 'danger';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($picker['username'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($picker['nama_pegawai'] ?? 'N/A') ?></td>
                                        <td class="text-right"><?= number_format($picker['total_resi']) ?></td>
                                        <td class="text-right"><?= number_format($picker['total_sku_unique']) ?></td>
                                        <td class="text-right"><?= number_format($picker['total_qty']) ?></td>
                                        <td class="text-right"><?= number_format($picker['avg_sku_per_resi'], 2) ?></td>
                                        <td class="text-center">
                                            <span class="label label-<?= $status_class ?>"><?= $status ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Global state to store current reportrange
window.kpiPickerReportRange = <?= !empty($reportrange) ? '"' . $reportrange . '"' : '"' . date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d H:i:s') . '"' ?>;
window.kpiPickerRetryCount = 0;

// Function to initialize daterangepicker - SAMA SEPERTI LAPORAN RESI HARIAN
function initKpiPickerDaterangepicker() {
    var report_range = window.kpiPickerReportRange;
    
    var start = report_range !== null ? moment(report_range.split(" - ")[0]) : moment().startOf('day');
    var end = report_range !== null ? moment(report_range.split(" - ")[1]) : moment();
    
    var $reportrange = $('#reportrange');
    
    if ($reportrange.length === 0) {
        console.warn('Reportrange element not found, skipping initialization');
        return;
    }
    
    // Destroy existing datepicker if exists
    if ($reportrange.data('daterangepicker')) {
        try {
            $reportrange.data('daterangepicker').remove();
        } catch(e) {
            console.warn('Error removing old daterangepicker:', e);
        }
    }
    
    $reportrange.daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment().startOf('day'), moment()],
            'Last 1 Hours': [moment().subtract(1, 'hours'), moment()],
            'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
            'This Month': [moment().startOf('month'), moment()],
        },
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss'
        }
    }, function(start, end, label) {
        // Callback when range is applied - UPDATE value
        var newRange = start.format('YYYY-MM-DD HH:mm:ss') + ' - ' + end.format('YYYY-MM-DD HH:mm:ss');
        $reportrange.val(newRange);
        
        console.log('✅ Date range selected:', label);
        console.log('✅ New range:', newRange);
        console.log('✅ Input value updated:', $reportrange.val());
    });
    
    // Set initial value in input field immediately after initialization
    $reportrange.val(report_range);
    
    console.log('KPI Picker daterangepicker initialized with:', report_range);
    console.log('Available ranges:', {
        'Today': [moment().startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().format('YYYY-MM-DD HH:mm:ss')],
        'Yesterday': [moment().subtract(1, 'days').startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().subtract(1, 'days').endOf('day').format('YYYY-MM-DD HH:mm:ss')],
        'Last 7 Days': [moment().subtract(6, 'days').startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().format('YYYY-MM-DD HH:mm:ss')],
        'This Month': [moment().startOf('month').format('YYYY-MM-DD HH:mm:ss'), moment().format('YYYY-MM-DD HH:mm:ss')]
    });
}

// Initialize on document ready
$(document).ready(function() {
    initKpiPickerDaterangepicker();
});

// Handle form submission via AJAX to prevent URL change
// Use delegated event to ensure handler persists after content replacement
$(document).off('submit', '#form-filter-kpi-picker').on('submit', '#form-filter-kpi-picker', function(e) {
    e.preventDefault();
    
    // Function to check element and proceed with submission
    function proceedWithSubmission() {
        var $reportrange = $('#reportrange');
        
        // Check if element exists
        if ($reportrange.length === 0) {
            console.error('Reportrange element not found');
            noty({text: 'Form error: Date range element not found', timeout: 3000, layout: 'topRight', type: 'error'});
            return false;
        }
        
        // Use global reportrange value as primary source
        var reportrange = window.kpiPickerReportRange;
        
        // Try to get the latest range from the daterangepicker object if it exists
        try {
            var picker = $reportrange.data('daterangepicker');
            if (picker && picker.startDate && picker.endDate) {
                reportrange = picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss');
                console.log('Retrieved range from picker object:', reportrange);
            }
        } catch(e) {
            console.warn('Error getting range from picker:', e);
        }
        
        // If no range from picker, get from input field
        if (!reportrange) {
            reportrange = $reportrange.val();
            console.log('Retrieved range from input field:', reportrange);
        }
        
        // Update input field if we have a range
        if (reportrange && reportrange.trim() !== '') {
            $reportrange.val(reportrange);
        }
        
        // Validate reportrange
        if (!reportrange || (typeof reportrange === 'string' && reportrange.trim() === '')) {
            noty({text: 'Rentang waktu tidak boleh kosong', timeout: 3000, layout: 'topRight', type: 'error'});
            return false;
        }
        
        console.log('=== KPI PICKER SUBMIT ===');
        console.log('Submitting reportrange:', reportrange);
        console.log('Parsed dates:', {
            start: reportrange.split(' - ')[0],
            end: reportrange.split(' - ')[1]
        });
        
        // Store in global state
        window.kpiPickerReportRange = reportrange;
        
        var btn = $('#btn-search');
        if (btn.length > 0) {
            var originalBtnHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        }
        
        // Show loading
        var loading = "<div style='max-width: 100%; display: flex; justify-content: center; align-items: center; padding: 50px;'><img src='assets/img/LoaderIcon.gif' /></div>";
        $('.page-content-wrap').html(loading);
        
        $.ajax({
            url: '<?= base_url('kpi_reports/dashboard_picker') ?>',
            type: 'POST',
            data: { reportrange: reportrange },
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                if (response.view) {
                    // Replace content with new view
                    $('.page-content-wrap').html(response.view);
                    
                    // Re-initialize plugins (like in plugins.js devScript.formSubmit)
                    if (typeof formElements !== 'undefined' && formElements.init) formElements.init();
                    if (typeof uiElements !== 'undefined' && uiElements.init) uiElements.init();
                    if (typeof templatePlugins !== 'undefined' && templatePlugins.init) templatePlugins.init();
                    
                    // Re-initialize daterangepicker
                    setTimeout(function() {
                        if (typeof initKpiPickerDaterangepicker !== 'undefined') {
                            initKpiPickerDaterangepicker();
                        }
                    }, 200);
                    
                    noty({text: 'Dashboard berhasil di-update', timeout: 2000, layout: 'topRight', type: 'success'});
                }
                if (response.message) {
                    noty({text: response.message, timeout: 3000, layout: 'topRight', type: 'success'});
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error);
                console.error('Response Text:', xhr.responseText);
                
                noty({text: 'Error loading data. Check console for details.', timeout: 3000, layout: 'topRight', type: 'error'});
                
                // Restore original content or show error
                if (xhr.responseText) {
                    $('.page-content-wrap').html(xhr.responseText);
                }
            }
        });
        
        return false;
    }
    
    // Check if element exists, if not wait a bit and retry
    if ($('#reportrange').length === 0) {
        console.log('Element not found, waiting...');
        var retryCount = 0;
        var maxRetries = 10; // 10 retries = 2 seconds total
        
        function waitForElement() {
            if ($('#reportrange').length > 0) {
                console.log('Element found, proceeding...');
                proceedWithSubmission();
            } else if (retryCount < maxRetries) {
                retryCount++;
                console.log('Element not found, retrying... (' + retryCount + '/' + maxRetries + ')');
                setTimeout(waitForElement, 200);
            } else {
                console.error('Element not found after ' + maxRetries + ' attempts');
                noty({text: 'Form error: Date range element not found after multiple attempts', timeout: 3000, layout: 'topRight', type: 'error'});
            }
        }
        
        waitForElement();
    } else {
        proceedWithSubmission();
    }
});

// Refresh button should reload the dashboard section only (no full page refresh)
$(document).off('click', '#btn-refresh-kpi-picker').on('click', '#btn-refresh-kpi-picker', function(e) {
    e.preventDefault();
    $('#form-filter-kpi-picker').trigger('submit');
});

// Export Excel button
$('#btn-export-excel').click(function() {
    var $reportrange = $('#reportrange');
    var reportrange = $reportrange.val();
    
    // If empty, try to get from daterangepicker object
    if (!reportrange || reportrange.trim() === '') {
        try {
            var picker = $reportrange.data('daterangepicker');
            if (picker && picker.startDate && picker.endDate) {
                reportrange = picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss');
                $reportrange.val(reportrange);
                console.log('Retrieved range from picker for export:', reportrange);
            }
        } catch(e) {
            console.warn('Error getting range from picker for export:', e);
        }
    }
    
    // Validate before export
    if (!reportrange || (typeof reportrange === 'string' && reportrange.trim() === '')) {
        noty({text: 'Rentang waktu tidak boleh kosong', timeout: 3000, layout: 'topRight', type: 'error'});
        return false;
    }
    
    var url = '<?= base_url('kpi_reports/export_excel_picker') ?>?reportrange=' + encodeURIComponent(reportrange);
    window.open(url, '_blank');
});

// Initialize charts
$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    createHourlyChart();
    createRankingChart();
}

function createHourlyChart() {
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
                    label: 'Picking per Hour',
                    data: totals,
                    backgroundColor: 'rgba(92, 184, 92, 0.6)',
                    borderColor: 'rgba(92, 184, 92, 1)',
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
                            text: 'Total Picking'
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
}

// Ranking Performance Chart
function createRankingChart() {
        // Combine all pickers and calculate performance scores
        var allPickers = [];
        
        // Add TIM INTI pickers
        <?php if (isset($dashboard_stats['top_pickers_inti']) && count($dashboard_stats['top_pickers_inti']) > 0): ?>
            <?php foreach ($dashboard_stats['top_pickers_inti'] as $picker): ?>
                allPickers.push({
                    name: '<?= htmlspecialchars($picker['nama_pegawai'] ?? $picker['username']) ?>',
                    total_resi: <?= $picker['total_resi'] ?>,
                    total_qty: <?= $picker['total_qty'] ?>,
                    total_sku_unique: <?= $picker['total_sku_unique'] ?>,
                    avg_sku_per_resi: <?= $picker['avg_sku_per_resi'] ?>,
                    tim: 'INTI'
                });
            <?php endforeach; ?>
        <?php endif; ?>
        
        // Add TIM OTHERS pickers
        <?php if (isset($dashboard_stats['top_pickers_others']) && count($dashboard_stats['top_pickers_others']) > 0): ?>
            <?php foreach ($dashboard_stats['top_pickers_others'] as $picker): ?>
                allPickers.push({
                    name: '<?= htmlspecialchars($picker['nama_pegawai'] ?? $picker['username']) ?>',
                    total_resi: <?= $picker['total_resi'] ?>,
                    total_qty: <?= $picker['total_qty'] ?>,
                    total_sku_unique: <?= $picker['total_sku_unique'] ?>,
                    avg_sku_per_resi: <?= $picker['avg_sku_per_resi'] ?>,
                    tim: 'OTHERS'
                });
            <?php endforeach; ?>
        <?php endif; ?>
        
        // Calculate performance score for each picker
        allPickers.forEach(function(picker) {
            var sku_diversity = picker.total_qty > 0 ? picker.total_sku_unique / picker.total_qty : 0;
            
            // Performance score calculation (0-100)
            var volume_score = Math.min(picker.avg_sku_per_resi / 5, 1) * 50; // 50% volume
            var diversity_score = sku_diversity * 50; // 50% diversity
            picker.performance_score = Math.round(volume_score + diversity_score);
        });
        
        // Sort by performance score and get top 5
        allPickers.sort(function(a, b) {
            return b.performance_score - a.performance_score;
        });
        var top5 = allPickers.slice(0, 5);
        
        if (top5.length > 0) {
            var ctx2 = document.getElementById('rankingPerformanceChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: top5.map(function(p, i) { return '#' + (i + 1) + ' ' + p.name; }),
                    datasets: [{
                        label: 'Performance Score',
                        data: top5.map(function(p) { return p.performance_score; }),
                        backgroundColor: [
                            'rgba(255, 215, 0, 0.8)',  // Gold
                            'rgba(192, 192, 192, 0.8)', // Silver
                            'rgba(205, 127, 50, 0.8)',  // Bronze
                            'rgba(92, 184, 92, 0.6)',   // Green
                            'rgba(91, 192, 222, 0.6)'   // Blue
                        ],
                        borderColor: [
                            'rgba(255, 215, 0, 1)',
                            'rgba(192, 192, 192, 1)',
                            'rgba(205, 127, 50, 1)',
                            'rgba(92, 184, 92, 1)',
                            'rgba(91, 192, 222, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Performance Score (0-100)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    var picker = top5[context.dataIndex];
                                    return [
                                        'Score: ' + picker.performance_score + '/100',
                                        'Total Resi: ' + picker.total_resi,
                                        'Avg SKU/Resi: ' + picker.avg_sku_per_resi.toFixed(2),
                                        'Diversity: ' + Math.round((picker.total_sku_unique / picker.total_qty) * 100) + '%',
                                        'Tim: ' + picker.tim
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
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

.chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
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
    .chart-container {
        height: 250px;
    }
}
</style>


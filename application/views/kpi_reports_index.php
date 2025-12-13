<?php
// Cek akses - hanya user yang memiliki akses ke menu KPI Reports
$user = $this->session->userdata('user');
if (!$user || !isset($user['id_user'])) {
    redirect('welcome/restricted');
}
?>

<div class="row">
    <div class="col-md-12">
        <h2><span class="fa fa-line-chart"></span> KPI Reports 
            <span class="pull-right">
                <span class="label label-info">
                    <i class="fa fa-user"></i> <?= $user['username'] ?>
                    <?php if(isset($user['hakakses'])): ?>
                        <small>(<?= $user['hakakses'] == 1 ? 'Admin' : ($user['hakakses'] == 2 ? 'Webmaster' : 'Level ' . $user['hakakses']) ?>)</small>
                    <?php endif; ?>
                </span>
            </span>
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Key Performance Indicators</h3>
                    <div class="pull-right">
                        <small class="text-muted">Access Level: Admin & Webmaster Only</small>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Date Range Picker -->
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal" method="post" action="<?= base_url('kpi_reports') ?>">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Date Range:</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control daterangepicker" name="reportrange" 
                                               value="<?= $reportrange ?>" placeholder="Select date range" />
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="fa fa-search"></span> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                            <span class="fa fa-file-excel-o"></span> Export Excel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- KPI Summary Cards -->
                    <div class="row" id="kpi-summary-cards">
                        <!-- KPI summary cards will be loaded here via AJAX -->
                    </div>

                    <!-- Status Performa Cards -->
                    <div class="row" id="status-performa-cards">
                        <!-- Status performa cards will be loaded here via AJAX -->
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Daily Performance Trend by Status</h3>
                                </div>
                                <div class="panel-body">
                                    <canvas id="dailyPerformanceChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Status Performance Distribution</h3>
                                </div>
                                <div class="panel-body">
                                    <canvas id="statusDistributionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Top Performers</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="top-performers-table">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>User</th>
                                                    <th>Status</th>
                                                    <th>Total Transaksi</th>
                                                    <th>Hari Aktif</th>
                                                    <th>Rata-rata Harian</th>
                                                </tr>
                                            </thead>
                                            <tbody id="top-performers-body">
                                                <!-- Top performers data will be loaded here via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed KPI Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Detailed KPI Data</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="kpi-table">
                                            <thead>
                                                <tr>
                                                    <th>KPI Metric</th>
                                                    <th>Value</th>
                                                    <th>Target</th>
                                                    <th>Status</th>
                                                    <th>Trend</th>
                                                </tr>
                                            </thead>
                                            <tbody id="kpi-table-body">
                                                <!-- KPI data will be loaded here via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Access Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Access Information:</strong> This KPI Reports section is only accessible by Admin and Webmaster users. 
                                All data shown here is confidential and should not be shared with unauthorized personnel.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js FIRST (before using it) - Use specific version -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Check if Chart.js loaded
if (typeof Chart === 'undefined') {
    console.error('Chart.js failed to load! Using fallback...');
    // Fallback: load from alternative CDN
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';
    document.head.appendChild(script);
}

$(document).ready(function() {
    // Initialize date range picker
    $('.daterangepicker').daterangepicker({
        timePicker: true,
        timePickerIncrement: 30,
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss'
        }
    });

    // Load KPI data on page load
    loadKPIData();

    // Load KPI data when date range changes
    $('form').on('submit', function(e) {
        e.preventDefault();
        loadKPIData();
    });
});

function loadKPIData() {
    var startDate = $('input[name="reportrange"]').val().split(' - ')[0];
    var endDate = $('input[name="reportrange"]').val().split(' - ')[1];

    $.ajax({
        url: '<?= base_url("kpi_reports/get_kpi_data") ?>',
        type: 'POST',
        data: {
            start_date: startDate,
            end_date: endDate
        },
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            if (response.success) {
                var data = response.data;
                displayKPICards(data);
                displayKPITable(data);
                displayTopPerformers(data);
                updateCharts(data);
            } else {
                console.error('Error:', response.message);
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading KPI data:', error);
            console.log('Response Text:', xhr.responseText);
            alert('Error loading KPI data. Check console for details.');
        }
    });
}

function displayKPICards(data) {
    // Display KPI Summary Cards
    var summaryCardsHtml = '';
    
    if (data.kpi_summary) {
        summaryCardsHtml += '<div class="col-md-3">';
        summaryCardsHtml += '<div class="panel panel-primary">';
        summaryCardsHtml += '<div class="panel-body text-center">';
        summaryCardsHtml += '<div class="huge">' + (data.kpi_summary.total_status || 0) + '</div>';
        summaryCardsHtml += '<div>Status Aktif</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '<div class="panel-footer">';
        summaryCardsHtml += '<span class="pull-left">Total Status</span>';
        summaryCardsHtml += '<div class="clearfix"></div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';

        summaryCardsHtml += '<div class="col-md-3">';
        summaryCardsHtml += '<div class="panel panel-success">';
        summaryCardsHtml += '<div class="panel-body text-center">';
        summaryCardsHtml += '<div class="huge">' + (data.kpi_summary.total_users || 0) + '</div>';
        summaryCardsHtml += '<div>User Aktif</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '<div class="panel-footer">';
        summaryCardsHtml += '<span class="pull-left">Total User</span>';
        summaryCardsHtml += '<div class="clearfix"></div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';

        summaryCardsHtml += '<div class="col-md-3">';
        summaryCardsHtml += '<div class="panel panel-info">';
        summaryCardsHtml += '<div class="panel-body text-center">';
        summaryCardsHtml += '<div class="huge">' + (data.kpi_summary.total_transactions || 0) + '</div>';
        summaryCardsHtml += '<div>Total Transaksi</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '<div class="panel-footer">';
        summaryCardsHtml += '<span class="pull-left">Packing + Picking</span>';
        summaryCardsHtml += '<div class="clearfix"></div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';

        summaryCardsHtml += '<div class="col-md-3">';
        summaryCardsHtml += '<div class="panel panel-warning">';
        summaryCardsHtml += '<div class="panel-body text-center">';
        summaryCardsHtml += '<div class="huge">' + Math.round(data.kpi_summary.rata_rata_capai || 0) + '%</div>';
        summaryCardsHtml += '<div>Rata-rata Capai</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '<div class="panel-footer">';
        summaryCardsHtml += '<span class="pull-left">Target Achievement</span>';
        summaryCardsHtml += '<div class="clearfix"></div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';
        summaryCardsHtml += '</div>';
    }

    $('#kpi-summary-cards').html(summaryCardsHtml);

    // Display Status Performa Cards
    var statusCardsHtml = '';
    if (data.status_performa && data.status_performa.length > 0) {
        data.status_performa.forEach(function(status, index) {
            var panelClass = 'panel-default';
            var iconClass = 'fa-circle';
            var statusText = 'UNKNOWN';
            
            if (status.status_performa === 'EXCELLENT') {
                panelClass = 'panel-success';
                iconClass = 'fa-check-circle';
                statusText = 'EXCELLENT';
            } else if (status.status_performa === 'GOOD') {
                panelClass = 'panel-info';
                iconClass = 'fa-thumbs-up';
                statusText = 'GOOD';
            } else if (status.status_performa === 'FAIR') {
                panelClass = 'panel-warning';
                iconClass = 'fa-exclamation-triangle';
                statusText = 'FAIR';
            } else if (status.status_performa === 'POOR') {
                panelClass = 'panel-danger';
                iconClass = 'fa-times-circle';
                statusText = 'POOR';
            }

            statusCardsHtml += '<div class="col-md-3">';
            statusCardsHtml += '<div class="panel ' + panelClass + '">';
            statusCardsHtml += '<div class="panel-body text-center">';
            statusCardsHtml += '<div class="huge">' + (status.total_transaksi || 0) + '</div>';
            statusCardsHtml += '<div>' + status.nama_status + '</div>';
            statusCardsHtml += '<div class="text-muted">' + (status.total_user || 0) + ' User</div>';
            statusCardsHtml += '</div>';
            statusCardsHtml += '<div class="panel-footer">';
            statusCardsHtml += '<span class="pull-left"><i class="fa ' + iconClass + '"></i> ' + statusText + '</span>';
            statusCardsHtml += '<span class="pull-right">' + Math.round(status.rata_rata_capai || 0) + '%</span>';
            statusCardsHtml += '<div class="clearfix"></div>';
            statusCardsHtml += '</div>';
            statusCardsHtml += '</div>';
            statusCardsHtml += '</div>';
        });
    } else {
        statusCardsHtml += '<div class="col-md-12">';
        statusCardsHtml += '<div class="alert alert-info text-center">';
        statusCardsHtml += '<i class="fa fa-info-circle"></i> Tidak ada data status performa untuk periode ini.';
        statusCardsHtml += '</div>';
        statusCardsHtml += '</div>';
    }

    $('#status-performa-cards').html(statusCardsHtml);
}

function displayKPITable(data) {
    var tableHtml = '';
    
    var kpis = [
        {name: 'Total Receipts Processed', value: data.total_receipts, target: 'N/A', status: 'info'},
        {name: 'Shipped Receipts', value: data.shipped_receipts, target: 'N/A', status: 'success'},
        {name: 'Pending Receipts', value: data.pending_receipts, target: '0', status: 'warning'},
        {name: 'Retur Receipts', value: data.retur_receipts, target: '< 5%', status: 'danger'},
        {name: 'Completion Rate', value: data.completion_rate + '%', target: '> 95%', status: data.completion_rate >= 95 ? 'success' : 'warning'},
        {name: 'Retur Rate', value: data.retur_rate + '%', target: '< 5%', status: data.retur_rate <= 5 ? 'success' : 'danger'},
        {name: 'Avg Processing Time', value: data.avg_processing_time + ' hours', target: '< 24 hours', status: data.avg_processing_time <= 24 ? 'success' : 'warning'},
        {name: 'Picker Productivity', value: data.picker_productivity + ' receipts/day', target: '> 50', status: data.picker_productivity >= 50 ? 'success' : 'warning'},
        {name: 'Packer Productivity', value: data.packer_productivity + ' receipts/day', target: '> 50', status: data.packer_productivity >= 50 ? 'success' : 'warning'}
    ];

    kpis.forEach(function(kpi) {
        tableHtml += '<tr>';
        tableHtml += '<td>' + kpi.name + '</td>';
        tableHtml += '<td>' + kpi.value + '</td>';
        tableHtml += '<td>' + kpi.target + '</td>';
        tableHtml += '<td><span class="label label-' + kpi.status + '">' + kpi.status.toUpperCase() + '</span></td>';
        tableHtml += '<td><span class="fa fa-arrow-up text-success"></span></td>';
        tableHtml += '</tr>';
    });

    $('#kpi-table-body').html(tableHtml);
}

function displayTopPerformers(data) {
    var tableHtml = '';
    
    if (data.top_performers && data.top_performers.length > 0) {
        data.top_performers.forEach(function(performer, index) {
            tableHtml += '<tr>';
            tableHtml += '<td>' + (index + 1) + '</td>';
            tableHtml += '<td>' + performer.nama_user + '</td>';
            tableHtml += '<td><span class="label label-info">' + performer.nama_status + '</span></td>';
            tableHtml += '<td>' + performer.total_transaksi + '</td>';
            tableHtml += '<td>' + performer.hari_aktif + '</td>';
            tableHtml += '<td>' + Math.round(performer.rata_rata_harian) + '</td>';
            tableHtml += '</tr>';
        });
    } else {
        tableHtml += '<tr><td colspan="6" class="text-center">No data available</td></tr>';
    }

    $('#top-performers-body').html(tableHtml);
}

// Global variables untuk menyimpan chart instances
var dailyPerformanceChartInstance = null;
var statusDistributionChartInstance = null;

function updateCharts(data) {
    // Destroy old charts if exist
    if (dailyPerformanceChartInstance) {
        dailyPerformanceChartInstance.destroy();
    }
    if (statusDistributionChartInstance) {
        statusDistributionChartInstance.destroy();
    }
    
    // Daily Performance Chart by Status
    var ctx1 = document.getElementById('dailyPerformanceChart').getContext('2d');
    
    if (data.daily_chart && data.daily_chart.length > 0) {
        // Group data by status
        var statusGroups = {};
        var allDates = [];
        
        data.daily_chart.forEach(function(item) {
            if (!statusGroups[item.kode_status]) {
                statusGroups[item.kode_status] = {
                    data: []
                };
            }
            if (allDates.indexOf(item.tanggal) === -1) {
                allDates.push(item.tanggal);
            }
        });
        
        // Sort dates
        allDates.sort();
        
        // Fill data for each status
        Object.keys(statusGroups).forEach(function(status) {
            allDates.forEach(function(date) {
                var found = data.daily_chart.find(item => item.kode_status === status && item.tanggal === date);
                statusGroups[status].data.push(found ? found.total_transaksi : 0);
            });
        });

        var datasets = [];
        var colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF8A80', '#80CBC4'];
        var colorIndex = 0;

        Object.keys(statusGroups).forEach(function(status) {
            datasets.push({
                label: status,
                data: statusGroups[status].data,
                borderColor: colors[colorIndex % colors.length],
                backgroundColor: colors[colorIndex % colors.length] + '20',
                tension: 0.1,
                fill: false
            });
            colorIndex++;
        });

        dailyPerformanceChartInstance = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: allDates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Transaksi'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Performance Trend by Status'
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    } else {
        // Show empty chart message
        ctx1.fillStyle = '#999';
        ctx1.font = '16px Arial';
        ctx1.textAlign = 'center';
        ctx1.fillText('No data available', ctx1.canvas.width/2, ctx1.canvas.height/2);
    }

    // Status Performance Distribution Chart
    var ctx2 = document.getElementById('statusDistributionChart').getContext('2d');
    
    if (data.status_distribution && data.status_distribution.length > 0) {
        var labels = data.status_distribution.map(item => item.nama_status);
        var dataValues = data.status_distribution.map(item => item.total_transaksi);
        var colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF8A80', '#80CBC4'];

        statusDistributionChartInstance = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Status Performance Distribution'
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    } else {
        // Show empty chart message
        ctx2.fillStyle = '#999';
        ctx2.font = '16px Arial';
        ctx2.textAlign = 'center';
        ctx2.fillText('No data available', ctx2.canvas.width/2, ctx2.canvas.height/2);
    }
}

function exportToExcel() {
    var form = $('<form>', {
        'method': 'POST',
        'action': '<?= base_url("kpi_reports/export_to_excel") ?>'
    });
    
    form.append($('<input>', {
        'type': 'hidden',
        'name': 'reportrange',
        'value': $('input[name="reportrange"]').val()
    }));
    
    $('body').append(form);
    form.submit();
    form.remove();
}
</script>

<style>
.huge {
    font-size: 40px;
    font-weight: bold;
}

.panel-footer {
    background-color: #f5f5f5;
    border-top: 1px solid #ddd;
    padding: 10px 15px;
}

.panel-footer .pull-left {
    color: #777;
    font-size: 12px;
}

.panel-footer .pull-right {
    color: #777;
    font-size: 12px;
    font-weight: bold;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.panel-body .huge {
    color: #337ab7;
}

.panel-success .huge {
    color: #5cb85c;
}

.panel-info .huge {
    color: #5bc0de;
}

.panel-warning .huge {
    color: #f0ad4e;
}

.panel-danger .huge {
    color: #d9534f;
}

.status-card {
    transition: transform 0.2s;
}

.status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.loading {
    text-align: center;
    padding: 50px;
    color: #999;
}

.loading i {
    font-size: 24px;
    margin-bottom: 10px;
}

.no-data {
    text-align: center;
    padding: 50px;
    color: #999;
}

.no-data i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #ddd;
}

</style>
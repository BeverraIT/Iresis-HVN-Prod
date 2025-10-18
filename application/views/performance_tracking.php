<?php
// Cek akses - hanya user yang memiliki akses ke menu KPI Reports
$user = $this->session->userdata('user');
if (!$user || !isset($user['id_user'])) {
    redirect('welcome/restricted');
}
?>

<div class="page-title">
    <h2><span class="fa fa-tachometer"></span> Performance Tracking</h2>
    <div class="pull-right">
        <span class="label label-info">
            <i class="fa fa-user"></i> <?= $user['username'] ?>
            <?php if(isset($user['hakakses'])): ?>
                <small>(<?= $user['hakakses'] == 1 ? 'Admin' : ($user['hakakses'] == 2 ? 'Webmaster' : 'Level ' . $user['hakakses']) ?>)</small>
            <?php endif; ?>
        </span>
    </div>
</div>

<div class="page-content-wrap">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Real-time Performance Tracking</h3>
                    <div class="pull-right">
                        <small class="text-muted">Live Performance Monitoring</small>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Status Performa Selection -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status Performa Hari Ini:</label>
                                <select id="status-performa" class="form-control">
                                    <option value="">Pilih Status Performa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal:</label>
                                <input type="date" id="tracking-date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Current Performance Card -->
                    <div class="row" id="current-performance">
                        <div class="col-md-12">
                            <div class="alert alert-info text-center">
                                <i class="fa fa-info-circle"></i> Pilih status performa untuk melihat data performa Anda
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Logging -->
                    <div class="row" id="transaction-logging" style="display: none;">
                        <div class="col-md-12">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Log Transaksi</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tipe Transaksi:</label>
                                                <select id="tipe-transaksi" class="form-control">
                                                    <option value="PACKING">PACKING</option>
                                                    <option value="PICKING">PICKING</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Jumlah Resi:</label>
                                                <input type="number" id="jumlah-resi" class="form-control" value="1" min="1">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button id="log-transaction" class="btn btn-success btn-block">
                                                    <i class="fa fa-plus"></i> Log Transaksi
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Summary -->
                    <div class="row" id="performance-summary" style="display: none;">
                        <div class="col-md-3">
                            <div class="panel panel-success">
                                <div class="panel-body text-center">
                                    <div class="huge" id="total-packing">0</div>
                                    <div>Total Packing</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-info">
                                <div class="panel-body text-center">
                                    <div class="huge" id="total-picking">0</div>
                                    <div>Total Picking</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-warning">
                                <div class="panel-body text-center">
                                    <div class="huge" id="total-transaksi">0</div>
                                    <div>Total Transaksi</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-primary">
                                <div class="panel-body text-center">
                                    <div class="huge" id="persentase-capai">0%</div>
                                    <div>Capai Target</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers Today -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Top Performers Hari Ini</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="top-performers-table">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>User</th>
                                                    <th>Status</th>
                                                    <th>Packing</th>
                                                    <th>Picking</th>
                                                    <th>Total</th>
                                                    <th>Capai</th>
                                                </tr>
                                            </thead>
                                            <tbody id="top-performers-body">
                                                <tr>
                                                    <td colspan="7" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Data -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Data Real-time</h3>
                                    <div class="pull-right">
                                        <button id="refresh-data" class="btn btn-sm btn-primary">
                                            <i class="fa fa-refresh"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="realtime-data-table">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>User Aktif</th>
                                                    <th>Total Packing</th>
                                                    <th>Total Picking</th>
                                                    <th>Total Transaksi</th>
                                                    <th>Rata-rata/User</th>
                                                    <th>Capai Target</th>
                                                </tr>
                                            </thead>
                                            <tbody id="realtime-data-body">
                                                <tr>
                                                    <td colspan="7" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load available status performa
    loadAvailableStatus();
    
    // Load top performers
    loadTopPerformers();
    
    // Load real-time data
    loadRealtimeData();
    
    // Event handlers
    $('#status-performa').on('change', function() {
        if ($(this).val()) {
            loadUserPerformance();
            $('#transaction-logging').show();
            $('#performance-summary').show();
        } else {
            $('#transaction-logging').hide();
            $('#performance-summary').hide();
        }
    });
    
    $('#log-transaction').on('click', function() {
        logTransaction();
    });
    
    $('#refresh-data').on('click', function() {
        loadTopPerformers();
        loadRealtimeData();
    });
    
    // Auto refresh every 30 seconds
    setInterval(function() {
        loadTopPerformers();
        loadRealtimeData();
        if ($('#status-performa').val()) {
            loadUserPerformance();
        }
    }, 30000);
});

function loadAvailableStatus() {
    $.ajax({
        url: '<?= base_url("performance_tracking/get_available_status") ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var options = '<option value="">Pilih Status Performa</option>';
                response.data.forEach(function(status) {
                    options += '<option value="' + status.id_statusperforma + '">' + status.nama_status + ' (' + status.kode_status + ')</option>';
                });
                $('#status-performa').html(options);
            }
        },
        error: function() {
            console.error('Error loading available status');
        }
    });
}

function loadUserPerformance() {
    var tanggal = $('#tracking-date').val();
    
    $.ajax({
        url: '<?= base_url("performance_tracking/get_user_performance") ?>',
        type: 'GET',
        data: { tanggal: tanggal },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayUserPerformance(response.data);
            }
        },
        error: function() {
            console.error('Error loading user performance');
        }
    });
}

function displayUserPerformance(data) {
    $('#total-packing').text(data.total_packing || 0);
    $('#total-picking').text(data.total_picking || 0);
    $('#total-transaksi').text(data.total_transaksi || 0);
    
    if (data.user_status && data.user_status.target_harian > 0) {
        var persentase = Math.round((data.total_transaksi / data.user_status.target_harian) * 100);
        $('#persentase-capai').text(persentase + '%');
    } else {
        $('#persentase-capai').text('0%');
    }
}

function logTransaction() {
    var tipe_transaksi = $('#tipe-transaksi').val();
    var jumlah_resi = $('#jumlah-resi').val();
    
    if (!tipe_transaksi || !jumlah_resi) {
        alert('Mohon isi semua field');
        return;
    }
    
    $.ajax({
        url: '<?= base_url("performance_tracking/log_transaction") ?>',
        type: 'POST',
        data: {
            tipe_transaksi: tipe_transaksi,
            jumlah_resi: jumlah_resi
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Transaksi berhasil dicatat');
                loadUserPerformance();
                loadTopPerformers();
                loadRealtimeData();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error logging transaction');
        }
    });
}

function loadTopPerformers() {
    var tanggal = $('#tracking-date').val();
    
    $.ajax({
        url: '<?= base_url("performance_tracking/get_performance_ranking") ?>',
        type: 'GET',
        data: { tanggal: tanggal, limit: 10 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayTopPerformers(response.data);
            }
        },
        error: function() {
            console.error('Error loading top performers');
        }
    });
}

function displayTopPerformers(data) {
    var tableHtml = '';
    
    if (data && data.length > 0) {
        data.forEach(function(performer, index) {
            var persentase = 0;
            if (performer.target_harian > 0) {
                persentase = Math.round((performer.total_transaksi / performer.target_harian) * 100);
            }
            
            tableHtml += '<tr>';
            tableHtml += '<td>' + (index + 1) + '</td>';
            tableHtml += '<td>' + performer.nama_user + '</td>';
            tableHtml += '<td><span class="label label-info">' + performer.nama_status + '</span></td>';
            tableHtml += '<td>' + (performer.total_packing || 0) + '</td>';
            tableHtml += '<td>' + (performer.total_picking || 0) + '</td>';
            tableHtml += '<td>' + performer.total_transaksi + '</td>';
            tableHtml += '<td>' + persentase + '%</td>';
            tableHtml += '</tr>';
        });
    } else {
        tableHtml += '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
    }

    $('#top-performers-body').html(tableHtml);
}

function loadRealtimeData() {
    var tanggal = $('#tracking-date').val();
    
    $.ajax({
        url: '<?= base_url("performance_tracking/get_realtime_data") ?>',
        type: 'GET',
        data: { tanggal: tanggal },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayRealtimeData(response.data);
            }
        },
        error: function() {
            console.error('Error loading realtime data');
        }
    });
}

function displayRealtimeData(data) {
    var tableHtml = '';
    
    if (data && data.length > 0) {
        data.forEach(function(item) {
            tableHtml += '<tr>';
            tableHtml += '<td><span class="label label-primary">' + item.nama_status + '</span></td>';
            tableHtml += '<td>' + item.total_user + '</td>';
            tableHtml += '<td>' + item.total_packing + '</td>';
            tableHtml += '<td>' + item.total_picking + '</td>';
            tableHtml += '<td>' + item.total_transaksi + '</td>';
            tableHtml += '<td>' + item.rata_rata_per_user + '</td>';
            tableHtml += '<td>' + Math.round(item.persentase_capai) + '%</td>';
            tableHtml += '</tr>';
        });
    } else {
        tableHtml += '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
    }

    $('#realtime-data-body').html(tableHtml);
}
</script>

<style>
.huge {
    font-size: 40px;
    font-weight: bold;
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

.panel-primary .huge {
    color: #337ab7;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #999;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #999;
}
</style>

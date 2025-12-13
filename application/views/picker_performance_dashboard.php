<?php
// Cek akses - hanya user yang memiliki akses ke menu Picker Performance
$user = $this->session->userdata('user');
if (!$user || !isset($user['id_user'])) {
    redirect('welcome/restricted');
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Laporan Performa Picker</strong></h3>
            </div>
            <div class="panel-body">
                <!-- Filter Form -->
                <form class="form-horizontal" method="post" id="filterForm">
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
                                <i class="fa fa-search"></i> Cari
                            </button>
                            <button type="button" class="btn btn-primary" onclick="exportToExcel()">
                                <i class="fa fa-download"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                <!-- Summary Statistics -->
                <div class="row">
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <div class="info-box">
                            <div class="info-box-number"><?= date('d M', strtotime($performance_data['summary']['tanggal'] ?? date('Y-m-d'))) ?></div>
                            <div class="info-box-label"><?= $performance_data['summary']['hari'] ?? '-' ?></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <div class="info-box">
                            <div class="info-box-number text-info"><?= number_format($performance_data['summary']['total_picker'] ?? 0) ?></div>
                            <div class="info-box-label">Total Picker</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <div class="info-box">
                            <div class="info-box-number text-primary"><?= number_format($performance_data['summary']['total_paket'] ?? 0) ?></div>
                            <div class="info-box-label">Total Paket</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <div class="info-box">
                            <div class="info-box-number text-warning"><?= number_format($performance_data['summary']['total_target'] ?? 0) ?></div>
                            <div class="info-box-label">Target</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <div class="info-box">
                            <div class="info-box-number <?= ($performance_data['summary']['selisih'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($performance_data['summary']['selisih'] ?? 0) ?>
                            </div>
                            <div class="info-box-label">Selisih</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-6">
                        <div class="info-box">
                            <div class="info-box-number <?= ($performance_data['summary']['persentase_capai'] ?? 0) >= 100 ? 'text-success' : 'text-warning' ?>">
                                <?= number_format($performance_data['summary']['persentase_capai'] ?? 0, 1) ?>%
                            </div>
                            <div class="info-box-label">Achievement</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TIM INTI -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading panel-primary">
                <h3 class="panel-title" style="color: white;">
                    <i class="fa fa-star"></i> <strong>TIM INTI</strong>
                    <span class="badge" style="background: white; color: #337ab7; margin-left: 10px;">
                        <?= count($performance_data['tim_inti'] ?? array()) ?> Picker
                    </span>
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                        <thead class="tim-inti-header">
                            <tr>
                                <th width="30" class="text-center">#</th>
                                <th>Nama Picker</th>
                                <th width="70" class="text-center">Jam IN</th>
                                <th width="70" class="text-center">Jam OUT</th>
                                <th width="60" class="text-center">Jam</th>
                                <th width="70" class="text-center">Target</th>
                                <th width="80" class="text-center">Paket</th>
                                <th width="70" class="text-center">Selisih</th>
                                <th width="80" class="text-center">% Capai</th>
                                <th width="80" class="text-center">Paket/Jam</th>
                                <th width="80" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($performance_data['tim_inti'])): ?>
                                <?php foreach ($performance_data['tim_inti'] as $index => $picker): ?>
                                    <tr class="tim-inti-row">
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($picker['nama_pegawai']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($picker['username'] ?? $picker['kode_pegawai']) ?></small>
                                        </td>
                                        <td class="text-center"><?= $picker['jam_in'] ? date('H:i', strtotime($picker['jam_in'])) : '-' ?></td>
                                        <td class="text-center"><?= $picker['jam_out'] ? date('H:i', strtotime($picker['jam_out'])) : '-' ?></td>
                                        <td class="text-center"><?= number_format($picker['total_jam'] ?? 0, 1) ?></td>
                                        <td class="text-center"><?= number_format($picker['target_paket']) ?></td>
                                        <td class="text-center"><strong><?= number_format($picker['jumlah_paket']) ?></strong></td>
                                        <td class="text-center <?= $picker['selisih'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <strong><?= $picker['selisih'] >= 0 ? '+' : '' ?><?= number_format($picker['selisih']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-<?= $picker['persentase_capai'] >= 100 ? 'success' : ($picker['persentase_capai'] >= 75 ? 'warning' : 'danger') ?>" style="font-size: 11px;">
                                                <?= number_format($picker['persentase_capai'], 0) ?>%
                                            </span>
                                        </td>
                                        <td class="text-center"><?= number_format($picker['rata_per_jam'], 1) ?></td>
                                        <td class="text-center">
                                            <span class="label label-<?= $picker['status_performa'] == 'GOOD' ? 'success' : ($picker['status_performa'] == 'FAIR' ? 'warning' : 'danger') ?>" style="font-size: 10px;">
                                                <?= $picker['status_performa'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Summary Row TIM INTI -->
                                <tr class="summary-row">
                                    <td colspan="2" class="text-right"><strong>TOTAL TIM INTI:</strong></td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center"><strong><?= number_format(array_sum(array_column($performance_data['tim_inti'], 'total_jam')), 1) ?></strong></td>
                                    <td class="text-center"><strong><?= number_format(array_sum(array_column($performance_data['tim_inti'], 'target_paket'))) ?></strong></td>
                                    <td class="text-center"><strong><?= number_format(array_sum(array_column($performance_data['tim_inti'], 'jumlah_paket'))) ?></strong></td>
                                    <td class="text-center <?= array_sum(array_column($performance_data['tim_inti'], 'selisih')) >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <strong><?= array_sum(array_column($performance_data['tim_inti'], 'selisih')) >= 0 ? '+' : '' ?><?= number_format(array_sum(array_column($performance_data['tim_inti'], 'selisih'))) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $total_target_inti = array_sum(array_column($performance_data['tim_inti'], 'target_paket'));
                                        $total_paket_inti = array_sum(array_column($performance_data['tim_inti'], 'jumlah_paket'));
                                        $avg_capai_inti = $total_target_inti > 0 ? ($total_paket_inti / $total_target_inti) * 100 : 0;
                                        ?>
                                        <strong><?= number_format($avg_capai_inti, 0) ?>%</strong>
                                    </td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted">Tidak ada data TIM INTI</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TIM OTHERS -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading panel-warning">
                <h3 class="panel-title" style="color: white;">
                    <i class="fa fa-users"></i> <strong>TIM OTHERS</strong>
                    <span class="badge" style="background: white; color: #f0ad4e; margin-left: 10px;">
                        <?= count($performance_data['tim_others'] ?? array()) ?> User
                    </span>
                    <small style="opacity: 0.95;"> (User non-picker yang membantu)</small>
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                        <thead class="tim-others-header">
                            <tr>
                                <th width="30" class="text-center">#</th>
                                <th>Nama User</th>
                                <th width="70" class="text-center">Jam IN</th>
                                <th width="70" class="text-center">Jam OUT</th>
                                <th width="60" class="text-center">Jam</th>
                                <th width="70" class="text-center">Target</th>
                                <th width="80" class="text-center">Paket</th>
                                <th width="70" class="text-center">Selisih</th>
                                <th width="80" class="text-center">% Capai</th>
                                <th width="80" class="text-center">Paket/Jam</th>
                                <th width="80" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($performance_data['tim_others'])): ?>
                                <?php foreach ($performance_data['tim_others'] as $index => $picker): ?>
                                    <tr class="tim-others-row">
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($picker['nama_pegawai']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($picker['username'] ?? $picker['kode_pegawai']) ?></small>
                                        </td>
                                        <td class="text-center"><?= $picker['jam_in'] ? date('H:i', strtotime($picker['jam_in'])) : '-' ?></td>
                                        <td class="text-center"><?= $picker['jam_out'] ? date('H:i', strtotime($picker['jam_out'])) : '-' ?></td>
                                        <td class="text-center"><?= number_format($picker['total_jam'] ?? 0, 1) ?></td>
                                        <td class="text-center"><?= number_format($picker['target_paket']) ?></td>
                                        <td class="text-center"><strong><?= number_format($picker['jumlah_paket']) ?></strong></td>
                                        <td class="text-center <?= $picker['selisih'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <strong><?= $picker['selisih'] >= 0 ? '+' : '' ?><?= number_format($picker['selisih']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-<?= $picker['persentase_capai'] >= 100 ? 'success' : ($picker['persentase_capai'] >= 75 ? 'warning' : 'danger') ?>" style="font-size: 11px;">
                                                <?= number_format($picker['persentase_capai'], 0) ?>%
                                            </span>
                                        </td>
                                        <td class="text-center"><?= number_format($picker['rata_per_jam'], 1) ?></td>
                                        <td class="text-center">
                                            <span class="label label-<?= $picker['status_performa'] == 'GOOD' ? 'success' : ($picker['status_performa'] == 'FAIR' ? 'warning' : 'danger') ?>" style="font-size: 10px;">
                                                <?= $picker['status_performa'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Summary Row TIM OTHERS -->
                                <tr class="summary-row">
                                    <td colspan="2" class="text-right"><strong>TOTAL TIM OTHERS:</strong></td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center"><strong><?= number_format(array_sum(array_column($performance_data['tim_others'], 'total_jam')), 1) ?></strong></td>
                                    <td class="text-center"><strong><?= number_format(array_sum(array_column($performance_data['tim_others'], 'target_paket'))) ?></strong></td>
                                    <td class="text-center"><strong><?= number_format(array_sum(array_column($performance_data['tim_others'], 'jumlah_paket'))) ?></strong></td>
                                    <td class="text-center <?= array_sum(array_column($performance_data['tim_others'], 'selisih')) >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <strong><?= array_sum(array_column($performance_data['tim_others'], 'selisih')) >= 0 ? '+' : '' ?><?= number_format(array_sum(array_column($performance_data['tim_others'], 'selisih'))) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $total_target_others = array_sum(array_column($performance_data['tim_others'], 'target_paket'));
                                        $total_paket_others = array_sum(array_column($performance_data['tim_others'], 'jumlah_paket'));
                                        $avg_capai_others = $total_target_others > 0 ? ($total_paket_others / $total_target_others) * 100 : 0;
                                        ?>
                                        <strong><?= number_format($avg_capai_others, 0) ?>%</strong>
                                    </td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted">Tidak ada data TIM OTHERS</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rata-rata Summary -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: #5cb85c; color: white;">
                <h3 class="panel-title"><i class="fa fa-bar-chart"></i> <strong>Rata-rata Produktivitas</strong></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="rata-box">
                            <h4>Rata-rata Jam / Orang</h4>
                            <h2 class="text-info"><?= number_format($performance_data['summary']['rata_jam_per_orang'] ?? 0, 1) ?></h2>
                            <p class="text-muted">JAM</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rata-box">
                            <h4>Rata-rata Paket / Orang</h4>
                            <h2 class="text-success"><?= number_format($performance_data['summary']['rata_paket_per_orang'] ?? 0, 0) ?></h2>
                            <p class="text-muted">PAKET</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rata-box">
                            <h4>Rata-rata Paket / Jam</h4>
                            <h2 class="text-warning"><?= number_format($performance_data['summary']['rata_paket_per_jam'] ?? 0, 1) ?></h2>
                            <p class="text-muted">PAKET/JAM</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rata-box">
                            <h4>Total Jam Kerja</h4>
                            <h2 class="text-primary"><?= number_format($performance_data['summary']['total_jam'] ?? 0, 0) ?></h2>
                            <p class="text-muted">JAM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize daterangepicker - sama seperti di Laporan Resi Harian
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

function exportToExcel() {
    var form = $('<form>', {
        'method': 'POST',
        'action': '<?= base_url("picker_performance/export_excel") ?>'
    });
    
    form.append($('<input>', {
        'type': 'hidden',
        'name': 'reportrange',
        'value': $('#reportrange').val()
    }));
    
    $('body').append(form);
    form.submit();
    form.remove();
}
</script>

<style>
/* Form Control Fix */
.form-control {
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
}

.form-control:focus {
    border-color: #66afe9;
    outline: 0;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
}

/* Info Box Styling - Simple and Clean */
.info-box {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
    text-align: center;
    transition: all 0.3s ease;
}

.info-box:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.info-box-number {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.info-box-label {
    font-size: 12px;
    color: #777;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* TIM INTI Styling */
.tim-inti-header {
    background-color: #e8f5e9 !important;
    color: #2e7d32 !important;
    font-weight: bold !important;
}

.tim-inti-row:hover {
    background-color: #f1f8f4 !important;
}

/* TIM OTHERS Styling */
.tim-others-header {
    background-color: #fff8e1 !important;
    color: #f57c00 !important;
    font-weight: bold !important;
}

.tim-others-row:hover {
    background-color: #fffcf5 !important;
}

/* Summary Row Styling */
.summary-row {
    background-color: #f5f5f5 !important;
    font-weight: bold !important;
    border-top: 2px solid #ddd !important;
}

/* Table Styling */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table > thead > tr > th {
    background-color: #f5f5f5;
    font-weight: bold;
    font-size: 11px;
    text-align: center;
    vertical-align: middle !important;
    white-space: nowrap;
    padding: 10px 5px;
}

.table > tbody > tr > td {
    vertical-align: middle !important;
    padding: 8px 5px;
    font-size: 12px;
}

/* Panel Styling */
.panel-default > .panel-heading {
    background-color: #f5f5f5;
    border-color: #ddd;
}

.panel-primary > .panel-heading,
.panel-warning > .panel-heading {
    color: white;
}

.panel-primary > .panel-heading {
    background-color: #337ab7;
}

.panel-warning > .panel-heading {
    background-color: #f0ad4e;
}

.panel-success > .panel-heading {
    background-color: #5cb85c;
    color: white;
}

/* Rata-rata Box */
.rata-box {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    background: #fff;
}

.rata-box h4 {
    font-size: 13px;
    color: #777;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.rata-box h2 {
    font-size: 32px;
    font-weight: bold;
    margin: 5px 0;
}

/* Responsive */
@media (max-width: 768px) {
    .info-box-number {
        font-size: 20px;
    }
    
    .info-box-label {
        font-size: 10px;
    }
    
    .table > thead > tr > th,
    .table > tbody > tr > td {
        font-size: 10px;
        padding: 5px 3px;
    }
}

.table > thead > tr > th {
    background-color: #f5f5f5;
    font-weight: bold;
    vertical-align: middle !important;
}

.table > tbody > tr > td {
    vertical-align: middle !important;
}

.panel-primary .panel-heading,
.panel-info .panel-heading,
.panel-success .panel-heading,
.panel-warning .panel-heading,
.panel-danger .panel-heading {
    color: white;
}

@media print {
    .btn, .panel-heading form {
        display: none;
    }
}
</style>


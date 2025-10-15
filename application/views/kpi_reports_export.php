<?php
// Cek akses - hanya user yang memiliki akses ke menu KPI Reports
$user = $this->session->userdata('user');
if (!$user || !isset($user['id_user'])) {
    redirect('welcome/restricted');
}
?>

<div class="page-title">
    <h2><span class="fa fa-file-excel-o"></span> Export KPI Reports</h2>
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
                    <h3 class="panel-title">Export KPI Data to Excel</h3>
                    <div class="pull-right">
                        <small class="text-muted">Export Performance Data</small>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Date Range Picker -->
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal" method="post" action="<?= base_url('kpi_reports/export') ?>">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Date Range:</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control daterangepicker" name="reportrange" 
                                               value="<?= $reportrange ?>" placeholder="Select date range" />
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success">
                                            <span class="fa fa-file-excel-o"></span> Export to Excel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Export Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h4><i class="fa fa-info-circle"></i> Export Information</h4>
                                <p>This export will include the following data:</p>
                                <ul>
                                    <li><strong>KPI Summary:</strong> Total status, users, transactions, and achievement rates</li>
                                    <li><strong>Status Performance:</strong> Performance data grouped by status (GTL, NDD, NORMAL, etc.)</li>
                                    <li><strong>Top Performers:</strong> User rankings based on total transactions</li>
                                    <li><strong>Daily Performance:</strong> Daily trend data for the selected period</li>
                                    <li><strong>Legacy KPI:</strong> Traditional KPI metrics (completion rate, retur rate, etc.)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Quick Export Options</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="list-group">
                                        <a href="<?= base_url('kpi_reports/export_to_excel') ?>" class="list-group-item">
                                            <h5 class="list-group-item-heading">
                                                <i class="fa fa-calendar"></i> Today's Data
                                            </h5>
                                            <p class="list-group-item-text">Export today's KPI performance data</p>
                                        </a>
                                        <a href="<?= base_url('kpi_reports/export_to_excel?range=yesterday') ?>" class="list-group-item">
                                            <h5 class="list-group-item-heading">
                                                <i class="fa fa-calendar-o"></i> Yesterday's Data
                                            </h5>
                                            <p class="list-group-item-text">Export yesterday's KPI performance data</p>
                                        </a>
                                        <a href="<?= base_url('kpi_reports/export_to_excel?range=this_week') ?>" class="list-group-item">
                                            <h5 class="list-group-item-heading">
                                                <i class="fa fa-calendar-check-o"></i> This Week's Data
                                            </h5>
                                            <p class="list-group-item-text">Export this week's KPI performance data</p>
                                        </a>
                                        <a href="<?= base_url('kpi_reports/export_to_excel?range=this_month') ?>" class="list-group-item">
                                            <h5 class="list-group-item-heading">
                                                <i class="fa fa-calendar-plus-o"></i> This Month's Data
                                            </h5>
                                            <p class="list-group-item-text">Export this month's KPI performance data</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Export Guidelines</h4>
                                </div>
                                <div class="panel-body">
                                    <h5>Data Format:</h5>
                                    <ul>
                                        <li>Excel (.xls) format</li>
                                        <li>Multiple sheets for different data types</li>
                                        <li>Formatted tables with headers</li>
                                        <li>Charts and graphs included</li>
                                    </ul>
                                    
                                    <h5>File Naming:</h5>
                                    <ul>
                                        <li>Format: KPI_Reports_YYYY-MM-DD.xls</li>
                                        <li>Date range included in filename</li>
                                        <li>Timestamp for multiple exports</li>
                                    </ul>
                                    
                                    <h5>Data Security:</h5>
                                    <ul>
                                        <li>Only admin and webmaster can export</li>
                                        <li>Data is filtered by user permissions</li>
                                        <li>Export logs are maintained</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Exports -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Recent Exports</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Export Date</th>
                                                    <th>Date Range</th>
                                                    <th>File Size</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        <i class="fa fa-info-circle"></i> No recent exports found
                                                    </td>
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
    // Initialize date range picker
    $('.daterangepicker').daterangepicker({
        timePicker: true,
        timePickerIncrement: 30,
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss'
        }
    });
});
</script>

<style>
.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: #f5f5f5;
}

.panel-body h5 {
    color: #337ab7;
    margin-top: 15px;
}

.panel-body h5:first-child {
    margin-top: 0;
}

.alert ul {
    margin-bottom: 0;
}

.alert ul li {
    margin-bottom: 5px;
}
</style>
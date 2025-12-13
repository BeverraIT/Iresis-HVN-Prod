<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Laporan Retur</strong></h3>
            </div>

            <div class="panel-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="retur-tabs">
                    <li class="active"><a href="#tab-terima-retur" data-toggle="tab">Terima Retur</a></li>
                    <li><a href="#tab-buka-retur" data-toggle="tab">Buka Retur</a></li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" style="margin-top: 20px;">
                    <!-- TAB 1: Terima Retur -->
                    <div class="tab-pane fade in active" id="tab-terima-retur">
                        <form action="report/retur-receipt-report" class="form-horizontal" method="post" id="form-terima-retur-report">
                            <div class="form-group">
                                <label class="col-md-2 col-xs-12 control-label">Rentang Waktu</label>
                                <div class="col-md-4 col-xs-12">
                                    <input type="text" name="reportrange_terima" id="reportrange-terima" class="form-control" placeholder="Filter Range tanggal" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 col-xs-12 control-label"></label>
                                <div class="col-md-6 col-xs-12">
                                    <button type="button" class="btn btn-info" id="btn-tampilkan-terima"><i class="fa fa-search"></i> Tampilkan</button>
                                    <button type="submit" class="btn btn-success" id="btn-export-terima"><i class="fa fa-file-excel-o"></i> Ekspor ke Excel</button>
                                </div>
                            </div>
                        </form>

                        <hr>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="datatable-terima-retur">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>No. Resi</th>
                                        <th>Market Place</th>
                                        <th>Kurir</th>
                                        <th>Tanggal Terima Retur</th>
                                        <th>Jam Terima Retur</th>
                                        <th>SKU</th>
                                        <th>Quantity</th>
                                        <th>Status Detail</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: Buka Retur -->
                    <div class="tab-pane fade" id="tab-buka-retur">
                        <form action="report/retur-receipt-report" class="form-horizontal" method="post" id="form-buka-retur-report">
                            <div class="form-group">
                                <label class="col-md-2 col-xs-12 control-label">Rentang Waktu</label>
                                <div class="col-md-4 col-xs-12">
                                    <input type="text" name="reportrange_buka" id="reportrange-buka" class="form-control" placeholder="Filter Range tanggal" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 col-xs-12 control-label"></label>
                                <div class="col-md-6 col-xs-12">
                                    <button type="button" class="btn btn-info" id="btn-tampilkan-buka"><i class="fa fa-search"></i> Tampilkan</button>
                                    <button type="submit" class="btn btn-success" id="btn-export-buka"><i class="fa fa-file-excel-o"></i> Ekspor ke Excel</button>
                                </div>
                            </div>
                        </form>

                        <hr>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="datatable-buka-retur">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>No. Resi</th>
                                        <th>Market Place</th>
                                        <th>Kurir</th>
                                        <th>Tanggal Buka Retur</th>
                                        <th>Jam Buka Retur</th>
                                        <th>SKU</th>
                                        <th>Quantity</th>
                                        <th>Status Detail</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // ==================== DATE RANGE PICKERS ====================
    var defaultStart = moment().startOf('day');
    var defaultEnd = moment();

    // Date Range for Terima Retur
    $('#reportrange-terima').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        startDate: defaultStart,
        endDate: defaultEnd,
        ranges: {
            'Hari Ini': [moment().startOf('day'), moment()],
            'Kemarin': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            '7 Hari Terakhir': [moment().subtract(6, 'days').startOf('day'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days').startOf('day'), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
        },
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss',
            separator: ' - ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            customRangeLabel: 'Custom Range'
        },
    });

    // Date Range for Buka Retur
    $('#reportrange-buka').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        startDate: defaultStart,
        endDate: defaultEnd,
        ranges: {
            'Hari Ini': [moment().startOf('day'), moment()],
            'Kemarin': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            '7 Hari Terakhir': [moment().subtract(6, 'days').startOf('day'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days').startOf('day'), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
        },
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss',
            separator: ' - ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            customRangeLabel: 'Custom Range'
        },
    });

    // ==================== DATATABLE: TERIMA RETUR ====================
    var tableTerimaRetur = $('#datatable-terima-retur').DataTable({
        'scrollX': true,
        'pageLength': 10,
        'processing': true,
        'serverSide': true,
        'order': [[4, 'desc']], // Sort by Tanggal Terima Retur
        'lengthMenu': [[10, 25, 50, 100], [10, 25, 50, 100]],
        'ajax': {
            url: 'report/get-terima-retur-report-data',
            type: 'POST',
            data: function(d) {
                var dateRange = $('#reportrange-terima').val();
                if (dateRange) {
                    d.start_date = dateRange.split(" - ")[0];
                    d.end_date = dateRange.split(" - ")[1];
                } else {
                    d.start_date = moment().startOf('day').format('YYYY-MM-DD HH:mm:ss');
                    d.end_date = moment().format('YYYY-MM-DD HH:mm:ss');
                }
            }
        },
        'columns': [
            { data: 'no_pesanan' },
            { data: 'noresi' },
            { data: 'marketplace' },
            { data: 'kurir' },
            { data: 'tanggal_terima' },
            { data: 'jam_terima' },
            { data: 'sku' },
            { data: 'quantity' },
            { data: 'status_detail' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // ==================== DATATABLE: BUKA RETUR ====================
    var tableBukaRetur = $('#datatable-buka-retur').DataTable({
        'scrollX': true,
        'pageLength': 10,
        'processing': true,
        'serverSide': true,
        'order': [[4, 'desc']], // Sort by Tanggal Buka Retur
        'lengthMenu': [[10, 25, 50, 100], [10, 25, 50, 100]],
        'ajax': {
            url: 'report/get-buka-retur-report-data',
            type: 'POST',
            data: function(d) {
                var dateRange = $('#reportrange-buka').val();
                if (dateRange) {
                    d.start_date = dateRange.split(" - ")[0];
                    d.end_date = dateRange.split(" - ")[1];
                } else {
                    d.start_date = moment().startOf('day').format('YYYY-MM-DD HH:mm:ss');
                    d.end_date = moment().format('YYYY-MM-DD HH:mm:ss');
                }
            }
        },
        'columns': [
            { data: 'no_pesanan' },
            { data: 'noresi' },
            { data: 'marketplace' },
            { data: 'kurir' },
            { data: 'tanggal_buka' },
            { data: 'jam_buka' },
            { data: 'sku' },
            { data: 'quantity' },
            { data: 'status_detail' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // ==================== BUTTON HANDLERS ====================
    // Tampilkan Terima Retur
    $('#btn-tampilkan-terima').on('click', function() {
        tableTerimaRetur.ajax.reload();
    });

    // Tampilkan Buka Retur
    $('#btn-tampilkan-buka').on('click', function() {
        tableBukaRetur.ajax.reload();
    });

    // Export Excel Terima Retur
    $('#btn-export-terima').on('click', function(e) {
        e.preventDefault();
        var dateRange = $('#reportrange-terima').val() || (moment().startOf('day').format('YYYY-MM-DD HH:mm:ss') + ' - ' + moment().format('YYYY-MM-DD HH:mm:ss'));
        window.open('report/export-to-excel-terima-retur-report?reportrange=' + encodeURIComponent(dateRange), '_blank');
    });

    // Export Excel Buka Retur
    $('#btn-export-buka').on('click', function(e) {
        e.preventDefault();
        var dateRange = $('#reportrange-buka').val() || (moment().startOf('day').format('YYYY-MM-DD HH:mm:ss') + ' - ' + moment().format('YYYY-MM-DD HH:mm:ss'));
        window.open('report/export-to-excel-buka-retur-report?reportrange=' + encodeURIComponent(dateRange), '_blank');
    });

    // ==================== DELETE HANDLER ====================
    // Handle delete button (using event delegation)
    $(document).on('click', '.btn-delete-retur', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var type = $(this).data('type'); // 'terima' or 'buka'
        
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: url,
                type: 'POST',
                success: function(response) {
                    alert('Data berhasil dihapus');
                    if (type === 'terima') {
                        tableTerimaRetur.ajax.reload();
                    } else {
                        tableBukaRetur.ajax.reload();
                    }
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus data';
                    alert(errorMessage);
                }
            });
        }
    });
});
</script>

<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #datatable-terima-retur th,
    #datatable-buka-retur th {
        white-space: nowrap;
        background-color: #f5f5f5;
        font-weight: bold;
    }
    
    .nav-tabs > li > a {
        font-weight: bold;
        font-size: 14px;
    }
    
    .btn-delete-retur {
        padding: 2px 8px;
        font-size: 12px;
    }
</style>

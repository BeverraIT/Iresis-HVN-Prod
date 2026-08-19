<form action="report/export-to-excel-production-team-report-tab1" class="form-horizontal nojs" method="post" target="_blank">
    <div class="form-group">
        <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
        <div class="col-md-3 col-xs-12">
            <input type="text" name="reportrange" id="reportrange-production-team-tab1" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 col-xs-12 control-label"></label>
        <div class="col-md-2 col-xs-12">
            <button type="button" class="btn btn-info" id="btn-search-production-team-tab1">Tampilkan</button>
            <button type="submit" class="btn btn-primary" id="btn-export-excel-production-team-tab1"><i class="fa fa-download"></i> Ekspor ke Excel</button>
        </div>
    </div>
</form>

<hr>

<!-- Loading Overlay -->
<div id="loading-overlay-tab1" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; margin-bottom: 15px;">
            <span class="sr-only">Loading...</span>
        </div>
        <h4 style="margin: 0; color: #333;">Memuat Data Packer...</h4>
        <p style="margin: 10px 0 0 0; color: #666;">Mohon tunggu sebentar</p>
    </div>
</div>

<div style="position: relative;">
    <table class="table table-striped" id="datatable-production-team-tab1">
        <thead>
        <tr>
            <th colspan="2" style="text-align:right">Grand Total</th>
            <th id="grand-total-tab1">-</th>
        </tr>
        <tr>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Total</th>
        </tr>
        </thead>
    </table>
</div>

<script type="text/javascript">
    $('#reportrange-production-team-tab1').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        startDate: moment().startOf('day'),
        endDate: moment(),
        ranges: {
            'Today': [moment().startOf('day'), moment()],
            'Last 1 Hours': [moment().subtract(1, 'hours'), moment()],
            'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().startOf('day')],
            'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
        },
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss'
        },
    });

    // init for the first time
    var groupColumn = 0;
    var totalColumn = 2;
    var table_production_team_tab1 = $('#datatable-production-team-tab1').DataTable({
        dom: '<if<t>lp>',
        'destroy': true,
    });
    $('#btn-search-production-team-tab1').on('click', function() {
        // Show loading overlay
        $('#loading-overlay-tab1').css('display', 'flex');
        $('#btn-search-production-team-tab1').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        
        table_production_team_tab1 = $('#datatable-production-team-tab1').DataTable({
            dom: '<if<t>lp>',
            'destroy': true,
            'pageLength': 50,
            'processing': true,
            'serverSide': true,
            'order': [
                [0, 'asc']
            ],
            'lengthMenu': [
                [10, 50, 100, 150, 200],
                [10, 50, 100, 150, 200]
            ],
            'ajax': {
                url: 'report/get-production-team-report-data-tab1',
                type: 'POST',
                data: function(d) {
                    d.start_date = $('#reportrange-production-team-tab1').val().split(" - ")[0];
                    d.end_date = $('#reportrange-production-team-tab1').val().split(" - ")[1];
                }
            },
            'initComplete': function() {
                // Hide loading overlay when data loaded
                $('#loading-overlay-tab1').hide();
                $('#btn-search-production-team-tab1').prop('disabled', false).html('Tampilkan');
                
                // Update grand total
                var json = this.api().ajax.json();
                if (json && json.grandTotal !== undefined) {
                    $('#grand-total-tab1').text(json.grandTotal);
                }
            },
            'preDrawCallback': function() {
                // Show loading on pagination/sorting
                $('#loading-overlay-tab1').css('display', 'flex');
            },
            'drawCallback': function() {
                // Hide loading after draw
                $('#loading-overlay-tab1').hide();
                
                // Update grand total setiap kali draw
                var json = this.api().ajax.json();
                if (json && json.grandTotal !== undefined) {
                    $('#grand-total-tab1').text(json.grandTotal);
                }
            }
        });
    });
</script>
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

<table class="table table-striped" id="datatable-production-team-tab1">
    <thead>
    <tr>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Total</th>
    </tr>
    </thead>
</table>

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
        table_production_team_tab1 = $('#datatable-production-team-tab1').DataTable({
            columnDefs: [{
                visible: false,
                targets: groupColumn
            }],
            drawCallback: function(settings) {
                var api = this.api();
                var rows = api.rows({ page: 'current' }).nodes();
                var last = null;
                var groupTotal = 0;

                api.column(groupColumn, { page: 'current' }).data().each(function(group, i) {
                    if (i > 0) {
                        groupTotal += Number(this.cell(i - 1, totalColumn).data());
                    }

                    if (last !== group) {
                        if (last !== null) {
                            $(rows).eq(i).before(`<tr><td align="right"><h5>Total<h5></td><td><h5>${groupTotal}</h5></td></tr>`);
                            groupTotal = 0;
                        }

                        $(rows).eq(i).before(`<tr class="group"><td colspan="2"><h5>${group}<h5></td></tr>`);

                        last = group;
                    }

                    if (i === rows.length - 1) {
                        groupTotal += Number(this.cell(i, totalColumn).data())
                        $(rows).eq(i).after(`<tr><td align="right"><h5>Total<h5></td><td><h5>${groupTotal}</h5></td></tr>`);
                    }
                });
            },

            dom: '<if<t>lp>',
            'destroy': true,
            'pageLength': 50,
            'processing': true,
            'serverSide': true,
            'order': [
                [0, 'asc'],
                [1, 'asc'],
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
        });
        // Order by the grouping
        $('#datatable-production-team-tab1 tbody').on('dblclick', 'tr.group', function () {
            var currentOrder = table_production_team_tab1.order()[0];
            if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
                table_production_team_tab1.order([groupColumn, 'desc']).draw();
            }
            else {
                table_production_team_tab1.order([groupColumn, 'asc']).draw();
            }
        });
    });
</script>
<form action="report/export-to-excel-receipt-report-tab1" class="form-horizontal nojs" method="post" target="_blank">
  <div class="form-group">
    <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
    <div class="col-md-3 col-xs-12">
      <input type="text" name="reportrange" id="reportrange-receipt-process-tab1" class="form-control" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-md-3 col-xs-12 control-label"></label>
    <div class="col-md-2 col-xs-12">
      <button type="button" class="btn btn-info" id="btn-search-receipt-process-tab1">Tampilkan</button>
      <button type="submit" class="btn btn-primary" id="btn-export-excel-receipt-process-tab1"><i class="fa fa-download"></i> Ekspor ke Excel</button>
    </div>
  </div>
</form>

<hr>

<table class="table table-striped" id="datatable-receipt-process-tab1">
  <thead>
    <tr>
      <th colspan="3" style="text-align:right">Grand Total</th>
      <th id="grand_total"></th>
    </tr>
    <tr>
      <th>#</th>
      <th>Tanggal Scan Resi</th>
      <th>Nomor Picklist</th>
      <th>Jumlah Resi</th>
    </tr>
  </thead>
</table>

<script type="text/javascript">
  $('#reportrange-receipt-process-tab1').daterangepicker({
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
  var table_receipt_tab1 = $('#datatable-receipt-process-tab1').DataTable({
    'dom': '<if<t>lp>',
    'destroy': true,
  });
  $('#btn-search-receipt-process-tab1').on('click', function() {
    table_receipt_tab1 = $('#datatable-receipt-process-tab1').DataTable({
      'dom': '<if<t>lp>',
      'destroy': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'order': [
        [2, 'desc']
      ],
      'lengthMenu': [
        [10, 50, 100, 150, 200],
        [10, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'report/get-receipt-report-data-tab1',
        type: 'POST',
        data: function(d) {
          d.start_date = $('#reportrange-receipt-process-tab1').val().split(" - ")[0];
          d.end_date = $('#reportrange-receipt-process-tab1').val().split(" - ")[1];
        }
      },
      'footerCallback': function(row, data, start, end, display) {
        var api = this.api(),
          data;
        var json = api.ajax.json();

        // converting to interger to find total
        var intVal = function(i) {
          return typeof i === 'string' ?
            i.replace(/[\$,]/g, '') * 1 :
            typeof i === 'number' ?
            i : 0;
        };

        $('#grand_total').html(json.grandTotal);
      },
    });
  });
</script>
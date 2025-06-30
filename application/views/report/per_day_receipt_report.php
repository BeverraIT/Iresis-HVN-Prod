<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Laporan resi per hari</strong></h3>
      </div>

      <div class="panel-body">
        <form action="report/receipt-per-day-report" class="form-horizontal" method="post" id="form-report-receipt-per-day">
          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
            <div class="col-md-3 col-xs-12">
              <input type="text" name="reportrange" id="reportrange" class="form-control" value="<?= !empty($reportrange) ? $reportrange : null ?>" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label"></label>
            <div class="col-md-2 col-xs-12">
              <button type="submit" class="btn btn-info" id="btn-search"><i class="fa fa-search"></i> Cari</button>
              <button type="submit" class="btn btn-primary" id="btn-export-excel"><i class="fa fa-download"></i> Ekspor ke Excel</button>
            </div>
          </div>
        </form>

        <hr>

        <table class="table table-striped table-bordered" id="datatable-report-receipt-per-day">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Marketplace</th>
              <th>Kurir</th>
              <th>Nomor Picklist</th>
              <th>Tanggal Scan</th>
              <th>Jam Scan</th>
              <th>Tanggal Pick</th>
              <th>Jam Pick</th>
              <th>Tanggal Packing</th>
              <th>Jam Packing</th>
              <th>Tanggal Resi Keluar</th>
              <th>Jam Resi Keluar</th>
            </tr>
          </thead>
        </table>

      </div>
    </div>
  </div>
</div>

<script>
  var report_range = <?= !empty($reportrange) ? '"' . $reportrange . '"' : "null" ?>;

  var start = report_range !== null ? moment(report_range.split(" - ")[0]) : moment().subtract(1, 'days').startOf('day');
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
    },
  });

  $('#datatable-report-receipt-per-day').DataTable({
    'scrollX': true,
    'pageLength': 10,
    'processing': true,
    'serverSide': true,
    'order': [
      [5, 'asc']
    ],
    'lengthMenu': [
      [10, 50, 100, 150, 200],
      [10, 50, 100, 150, 200]
    ],
    'ajax': {
      url: 'report/get-per-day-receipt-report-data',
      type: 'POST',
      data: function(d) {
        d.start_date = $('#reportrange').val().split(" - ")[0];
        d.end_date = $('#reportrange').val().split(" - ")[1];
      }
    },
  });

  $('#btn-search').on('click', function() {
    $('#form-report-receipt-per-day').removeAttr("target");
    $('#form-report-receipt-per-day').removeClass('nojs');
    $('#form-report-receipt-per-day').attr('action', 'report/per-day-receipt-report');
  })

  $('#btn-export-excel').on('click', function() {
    $('#form-report-receipt-per-day').attr("target", "_blank");
    $('#form-report-receipt-per-day').addClass('nojs');
    $('#form-report-receipt-per-day').attr('action', 'report/export-to-excel-per-day-receipt-report');
  });
</script>
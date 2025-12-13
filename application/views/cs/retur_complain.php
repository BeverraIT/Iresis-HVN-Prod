<style>
  #datatable-retur-complain_wrapper {
    width: 100% !important;
  }
  #datatable-retur-complain {
    width: 100% !important;
  }
  .dataTables_scrollHeadInner,
  .dataTables_scrollBody {
    width: 100% !important;
  }
</style>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Laporan Retur Complain</strong></h3>
      </div>

      <div class="panel-body">
        <form action="cs/retur-complain" class="form-horizontal" method="post" id="form-laporan-retur-complain">
          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Status</label>
            <div class="col-md-3 col-xs-12">
              <select name="status_filter" id="status_filter" class="form-control select">
                <option value="ALL">Semua Status</option>
                <option value="WAITING_CUSTOMER">Waiting Customer</option>
                <option value="REFUND_DANA">Refund Dana</option>
                <option value="PERGANTIAN_BARANG">Pergantian Barang</option>
                <option value="EXPIRED">Expired</option>
              </select>
            </div>
          </div>

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

        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
          <table class="table table-striped table-bordered" id="datatable-retur-complain" style="width: 100%;">
            <thead>
              <tr>
                <th>#</th>
                <th>No. Resi</th>
                <th>Customer</th>
                <th>Marketplace</th>
                <th>Tgl Complain</th>
                <th>Tgl Update</th>
                <th>Tipe</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Nominal Refund</th>
                <th>Pergantian Barang</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
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
      'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
      'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
    },
    locale: {
      format: 'YYYY-MM-DD HH:mm:ss'
    },
  });

  // Inisialisasi DataTable - hanya setelah user submit
  <?php $has_search = $this->input->method() == 'post'; ?>
  
  <?php if($has_search): ?>
  var table = $('#datatable-retur-complain').DataTable({
    'scrollX': true,
    'autoWidth': false,
    'width': '100%',
    'pageLength': 25,
    'processing': true,
    'language': {
      'processing': 'Memproses data...'
    },
    'serverSide': true,
    'order': [[5, 'desc']],
    'lengthMenu': [
      [10, 25, 50, 100, 200],
      [10, 25, 50, 100, 200]
    ],
    'ajax': {
      url: 'cs/get-retur-complain-data',
      type: 'POST',
      data: function(d) {
        d.reportrange = $('#reportrange').val();
        d.status_filter = $('#status_filter').val();
      }
    },
    'columnDefs': [
      { className: 'text-center', targets: [0, 4, 5, 6, 7] }
    ]
  });
  <?php endif; ?>

  $('#btn-search').on('click', function() {
    $('#form-laporan-retur-complain').removeAttr("target");
    $('#form-laporan-retur-complain').removeClass('nojs');
    $('#form-laporan-retur-complain').attr('action', 'cs/retur-complain');
  });
  
  $('#btn-export-excel').on('click', function() {
    $('#form-laporan-retur-complain').attr("target", "_blank");
    $('#form-laporan-retur-complain').addClass('nojs');
    $('#form-laporan-retur-complain').attr('action', 'cs/export-excel-retur-complain');
  });
</script>


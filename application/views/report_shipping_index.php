<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Laporan total pengiriman paket</strong></h3>
      </div>

      <div class="panel-body">
        <form action="report_shipping" class="form-horizontal" method="post" id="form-report-receipt-shipping">
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

        <table class="table table-striped" id="datatable-report-receipt-shipping">
          <thead>
            <tr>
              <th colspan="2" style="text-align:right">Grand Total</th>
              <th><?= empty($grand_total) ? '-' : $grand_total ?></th>
            </tr>
            <tr>
              <th>#</th>
              <th>Kurir</th>
              <th>Total Paket</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($list_data)): ?>
              <?php $i = 1;
              foreach ($list_data as $data): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= $data['nama_kurir'] ?></td>
                  <td><?= $data['total'] ?></td>
                </tr>
              <?php endforeach;  ?>
            <?php else: ?>
              <tr>
                <td colspan="3" class="text-center"><i>Tidak ada data yang ditampilkan</i></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

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
      'Last 1 Hours': [moment().subtract(1, 'hours'), moment()],
      'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().startOf('day')],
      'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
    },
    locale: {
      format: 'YYYY-MM-DD HH:mm:ss'
    },
  });

  $('#btn-search').on('click', function() {
    $('#form-report-receipt-shipping').removeAttr("target");
    $('#form-report-receipt-shipping').removeClass('nojs');
    $('#form-report-receipt-shipping').attr('action', 'report_shipping');
  })

  $('#btn-export-excel').on('click', function() {
    $('#form-report-receipt-shipping').attr("target", "_blank");
    $('#form-report-receipt-shipping').addClass('nojs');
    $('#form-report-receipt-shipping').attr('action', 'report_shipping/export_to_excel');
  });
</script>
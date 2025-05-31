<div class="row">
  <div class="col-md-6 center-block float-none">
    <form action="handover_scan/print_preview" class="form-horizontal nojs" autocomplete="off" method="post" target="_blank">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Resi Keluar Cetak Serah Terima</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Kurir</label>
            <div class="col-md-6 col-xs-12">
              <select name="id_kurir" id="id_kurir" class="form-control select" data-live-search="true">
                <?php foreach ($list_courrier as $courrier) : ?>
                  <option value="<?= $courrier['id_kurir'] ?>"><?= $courrier['nama_kurir'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
            <div class="col-md-6 col-xs-12">
              <input type="text" name="reportrange" id="reportrange" class="form-control" />
            </div>
          </div>

          <div class="tile tile-default" id="div_container_latest_receipt">
            <span id="span_latest_receipt">-</span>
            <p><small id="p_latest_receipt_message">Nomor resi terakhir yang sudah di-scan keluar</small></p>
          </div>

        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-info"><i class="fa fa-print"></i> Cetak</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  var start = moment().startOf('day');
  var end = moment();

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
</script>
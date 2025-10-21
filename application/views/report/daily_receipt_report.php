<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Laporan Resi Harian</strong></h3>
      </div>

      <div class="panel-body">
        <form action="report/receipt-daily-report" class="form-horizontal" method="post" id="form-report-receipt-daily">
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

        <style>
          /* Column colors for different departments */
          .resi-col {
            background-color: #f5f9ff !important; /* Very light blue */
          }

          .picker-col {
            background-color: #f8fff8 !important; /* Very light green */
          }

          .packer-col {
            background-color: #fffbf5 !important; /* Very light orange */
          }

          .ho-col {
            background-color: #fef7f9 !important; /* Very light pink */
          }

          /* Darker shades for headers */
          .resi-col-header {
            background-color: #e3f2fd !important; /* Soft blue */
            color: #1565c0 !important;
          }

          .picker-col-header {
            background-color: #e8f5e8 !important; /* Soft green */
            color: #2e7d32 !important;
          }

          .packer-col-header {
            background-color: #fff3e0 !important; /* Soft orange */
            color: #ef6c00 !important;
          }

          .ho-col-header {
            background-color: #fce4ec !important; /* Soft pink */
            color: #c2185b !important;
          }
        </style>

        <table class="table table-striped table-bordered" id="datatable-report-receipt-daily">
          <thead>
            <tr>
              <?php
              $not_pick_by_receipt_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pick_resi']) / $header['total_scan_resi'] * 100;
              $not_pack_by_receipt_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pack_resi']) / $header['total_scan_resi'] * 100;
              $not_ho_by_receipt_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_ho_resi']) / $header['total_scan_resi'] * 100;
              ?>
              <th class="text-right" colspan="8">Yang belum dikerjakan dari total resi (in paket)</th>
              <th class="text-center resi-col-header"><?= number_format($header['total_scan_resi'] - $header['total_pick_resi']) ?></th>
              <th class="text-center picker-col-header"><?= number_format($not_pick_by_receipt_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
              <th class="text-center packer-col-header"><?= number_format($header['total_scan_resi'] - $header['total_pack_resi']) ?></th>
              <th class="text-center packer-col-header"><?= number_format($not_pack_by_receipt_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
              <th class="text-center ho-col-header"><?= number_format($header['total_scan_resi'] - $header['total_ho_resi']) ?></th>
              <th class="text-center ho-col-header"><?= number_format($not_ho_by_receipt_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
            </tr>
            <tr>
              <?php
              $not_pick_by_dept_in_percent = $header['total_scan_resi'] == 0 ? 0 : ($header['total_scan_resi'] - $header['total_pick_resi']) / $header['total_scan_resi'] * 100;
              $not_pack_by_dept_in_percent = $header['total_pick_resi'] == 0 ? 0 : ($header['total_pick_resi'] - $header['total_pack_resi']) / $header['total_pick_resi'] * 100;
              $not_ho_by_dept_in_percent = $header['total_pack_resi'] == 0 ? 0 : ($header['total_pack_resi'] - $header['total_ho_resi']) / $header['total_pack_resi'] * 100;
              ?>
              <th class="text-right" colspan="8">Yang belum dikerjakan dari masing<sup>2</sup> dept (in paket)</th>
              <th class="text-center resi-col-header"><?= number_format($header['total_scan_resi'] - $header['total_pick_resi']) ?></th>
              <th class="text-center picker-col-header"><?= number_format($not_pick_by_dept_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
              <th class="text-center packer-col-header"><?= number_format($header['total_pick_resi'] - $header['total_pack_resi']) ?></th>
              <th class="text-center packer-col-header"><?= number_format($not_pack_by_dept_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
              <th class="text-center ho-col-header"><?= number_format($header['total_pack_resi'] - $header['total_ho_resi']) ?></th>
              <th class="text-center ho-col-header"><?= number_format($not_ho_by_dept_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
            </tr>
            <tr>
              <?php
              $done_pick_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_pick_resi'] / $header['total_scan_resi'] * 100;
              $done_pack_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_pack_resi'] / $header['total_scan_resi'] * 100;
              $done_ho_in_percent = $header['total_scan_resi'] == 0 ? 0 : $header['total_ho_resi'] / $header['total_scan_resi'] * 100;
              ?>
              <th class="text-right" colspan="7">Total yang sedang / sudah dikerjakan</th>
              <th class="text-center resi-col-header"><?= number_format($header['total_scan_resi']) ?></th>
              <th class="text-center picker-col-header"><?= number_format($header['total_pick_resi']) ?></th>
              <th class="text-center picker-col-header"><?= number_format($done_pick_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
              <th class="text-center packer-col-header"><?= number_format($header['total_pack_resi']) ?></th>
              <th class="text-center packer-col-header"><?= number_format($done_pack_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
              <th class="text-center ho-col-header"><?= number_format($header['total_ho_resi']) ?></th>
              <th class="text-center ho-col-header"><?= number_format($done_ho_in_percent, 2) ?>%</th>
              <th class="text-center">-</th>
            </tr>
            <tr>
              <th rowspan="2">#</th>
              <th rowspan="2">MP</th>
              <th rowspan="2">Kurir</th>
              <th rowspan="2"># Resi</th>
              <th rowspan="2">Pick list</th>
              <th class="text-center" colspan="3" style="background-color: #bbdefb !important; color: #1565c0 !important;">Resi</th>
              <th class="text-center" colspan="4" style="background-color: #c8e6c9 !important; color: #2e7d32 !important;">Picker</th>
              <th class="text-center" colspan="3" style="background-color: #ffcc02 !important; color: #e65100 !important;">Packer</th>
              <th class="text-center" colspan="3" style="background-color: #f8bbd9 !important; color: #c2185b !important;">HO</th>
            </tr>
            <tr>
              <th class="resi-col-header">Tgl scan</th>
              <th class="resi-col-header">Jam scan</th>
              <th class="resi-col-header">Nama</th>
              <th class="picker-col-header">Tgl scan</th>
              <th class="picker-col-header">Jam scan</th>
              <th class="picker-col-header">Nama</th>
              <th class="picker-col-header">Status</th>
              <th class="packer-col-header">Tgl scan</th>
              <th class="packer-col-header">Jam scan</th>
              <th class="packer-col-header">Nama</th>
              <th class="ho-col-header">Tgl scan</th>
              <th class="ho-col-header">Jam scan</th>
              <th class="ho-col-header">Nama</th>
            </tr>
          </thead>
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

  $('#datatable-report-receipt-daily').DataTable({
    'scrollX': true,
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
      url: 'report/get-daily-receipt-report-data',
      type: 'POST',
      data: function(d) {
        d.start_date = $('#reportrange').val().split(" - ")[0];
        d.end_date = $('#reportrange').val().split(" - ")[1];
      }
    },
    'columnDefs': [
      // Resi columns (5, 6, 7) - Light blue
      {
        'targets': [5, 6, 7],
        'className': 'resi-col'
      },
      // Picker columns (8, 9, 10, 11) - Light green
      {
        'targets': [8, 9, 10, 11],
        'className': 'picker-col'
      },
      // Packer columns (12, 13, 14) - Light orange
      {
        'targets': [12, 13, 14],
        'className': 'packer-col'
      },
      // HO columns (15, 16, 17) - Light pink
      {
        'targets': [15, 16, 17],
        'className': 'ho-col'
      }
    ]
  });

  $('#btn-search').on('click', function() {
    $('#form-report-receipt-daily').removeAttr("target");
    $('#form-report-receipt-daily').removeClass('nojs');
    $('#form-report-receipt-daily').attr('action', 'report/daily-receipt-report');
  })
  
  $('#btn-export-excel').on('click', function() {
    $('#form-report-receipt-daily').attr("target", "_blank");
    $('#form-report-receipt-daily').addClass('nojs');
    $('#form-report-receipt-daily').attr('action', 'report/export-to-excel-daily-receipt-report');
  });

</script>
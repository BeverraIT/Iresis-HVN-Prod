<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Laporan Timbangan</strong></h3>
      </div>

      <div class="panel-body">

        <!-- class `nojs`: unduhan Excel harus lewat submit biasa. Tanpa penanda
             itu, handler form global di plugins.js mengubahnya jadi AJAX dan
             berkasnya tidak pernah terunduh. -->
        <form action="timbangan/export-to-excel-timbangan" method="post" id="form_laporan_timbangan" class="nojs" target="_blank">
          <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-4">
              <label class="control-label">Periode</label>
              <input type="text" name="reportrange" id="reportrange" class="form-control" value="<?= $reportrange ?>" readonly />
            </div>

            <div class="col-md-3">
              <label class="control-label">Status</label>
              <select name="status" id="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="ACCEPT">ACCEPT</option>
                <option value="UNDER">UNDER</option>
                <option value="OVER">OVER</option>
              </select>
            </div>

            <div class="col-md-5">
              <label class="control-label">&nbsp;</label><br />
              <button type="button" id="btn_tampilkan" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
              <button type="submit" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Excel</button>
            </div>
          </div>
        </form>

        <table class="table table-striped datatable-timbangan">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Berat Standar</th>
              <th>Berat Aktual</th>
              <th>Selisih</th>
              <th>Status</th>
              <th>Petugas</th>
              <th>Waktu</th>
            </tr>
          </thead>
        </table>

      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  (function() {
    $("#reportrange").daterangepicker({
      timePicker: true,
      timePicker24Hour: true,
      // Samakan dengan rentang bawaan dari controller (7 hari terakhir),
      // supaya membuka kalender tidak diam-diam mengembalikannya ke hari ini.
      startDate: moment().subtract(6, 'days').startOf('day'),
      endDate: moment(),
      ranges: {
        'Hari Ini': [moment().startOf('day'), moment()],
        'Kemarin': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
        '7 Hari Terakhir': [moment().subtract(6, 'days').startOf('day'), moment()],
        '30 Hari Terakhir': [moment().subtract(29, 'days').startOf('day'), moment()],
        'Bulan Ini': [moment().startOf('month'), moment()],
      },
      locale: {
        format: 'YYYY-MM-DD HH:mm:ss',
        separator: ' - ',
      },
    });

    var tabel = $('.datatable-timbangan').DataTable({
      'scrollX': true,
      'pageLength': 25,
      'processing': true,
      'serverSide': true,
      'order': [[7, 'desc']],
      'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
      'columnDefs': [{ 'orderable': false, 'targets': 0 }],
      'ajax': {
        url: 'timbangan/get-data-timbangan',
        type: 'POST',
        data: function(d) {
          d.reportrange = $("#reportrange").val();
          d.status = $("#status").val();
        },
      },
    });

    $("#btn_tampilkan").on('click', function() {
      tabel.ajax.reload();
    });
  })();
</script>

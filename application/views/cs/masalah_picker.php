<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Daftar Masalah Picker</strong></h3>
      </div>

      <div class="panel-body">
        <form action="cs/masalah-picker" class="form-horizontal" method="post" id="form-masalah-picker">
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
            </div>
          </div>
        </form>

        <hr>

        <table class="table table-striped table-bordered" id="datatable-masalah-picker">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>SKU</th>
              <th>Nama Barang</th>
              <th>Qty</th>
              <th>Qty Bermasalah</th>
              <th>Tipe Masalah</th>
              <th>Picker</th>
              <th>Tanggal</th>
              <th style="width: 100px;">Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Masalah Picker -->
<div id="modalDetailMasalah" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Detail Masalah Picker</h4>
      </div>
      <div class="modal-body" id="modalDetailContent">
        <p>Memuat data...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
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
  var table = $('#datatable-masalah-picker').DataTable({
    'scrollX': true,
    'pageLength': 10,
    'processing': true,
    'language': {
      'processing': 'Memproses data...'
    },
    'serverSide': true,
    'order': [[8, 'desc']],
    'lengthMenu': [
      [10, 50, 100, 150, 200],
      [10, 50, 100, 150, 200]
    ],
    'ajax': {
      url: 'cs/get-masalah-picker-data',
      type: 'POST',
      data: function(d) {
        d.reportrange = $('#reportrange').val();
      }
    },
    'columnDefs': [
      { className: 'text-center', targets: [0, 4, 5, 9] },
      { orderable: false, targets: [9] }
    ]
  });

  // Handle detail button
  $(document).on('click', '.btn-detail-masalah', function() {
    var id = $(this).data('id');
    
    $.ajax({
      url: 'cs/get-detail-masalah-picker',
      method: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(response) {
        if (response && response.success) {
          var html = '<table class="table table-bordered">';
          html += '<tr><th width="30%">No. Resi</th><td>' + (response.data.noresi || '-') + '</td></tr>';
          html += '<tr><th>SKU</th><td>' + (response.data.sku || '-') + '</td></tr>';
          html += '<tr><th>Nama Barang</th><td>' + (response.data.nama_barang || '-') + '</td></tr>';
          html += '<tr><th>Quantity</th><td>' + (response.data.qty || 0) + '</td></tr>';
          html += '<tr><th>Quantity Bermasalah</th><td>' + (response.data.qty_bermasalah || 0) + '</td></tr>';
          html += '<tr><th>Tipe Masalah</th><td>' + (response.data.type_masalah || '-') + '</td></tr>';
          html += '<tr><th>SKU Salah</th><td>' + (response.data.sku_salah || '-') + '</td></tr>';
          html += '<tr><th>Picker</th><td>' + (response.data.nama_picker || '-') + '</td></tr>';
          html += '<tr><th>Tanggal Dibuat</th><td>' + (response.data.created || '-') + '</td></tr>';
          if (response.data.updated) {
            html += '<tr><th>Tanggal Diupdate</th><td>' + response.data.updated + '</td></tr>';
          }
          html += '</table>';
          $('#modalDetailContent').html(html);
          $('#modalDetailMasalah').modal('show');
        } else {
          alert('Gagal memuat detail data');
        }
      },
      error: function() {
        alert('Terjadi kesalahan saat memuat detail data');
      }
    });
  });
  <?php endif; ?>

  $('#btn-search').on('click', function() {
    $('#form-masalah-picker').removeAttr("target");
    $('#form-masalah-picker').removeClass('nojs');
    $('#form-masalah-picker').attr('action', 'cs/masalah-picker');
  });
</script>


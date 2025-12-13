<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Laporan Kurangan Picker</strong></h3>
      </div>

      <div class="panel-body">
        <form action="cs/laporan-kurangan-picker" class="form-horizontal" method="post" id="form-laporan-kurangan-picker">
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

        <div class="form-group" id="button-group" style="display: none;">
          <div class="col-md-12">
            <button type="button" id="btn-submit-selected" class="btn btn-success">
              <i class="fa fa-check"></i> Submit Selected
            </button>
            <button type="button" id="btn-select-all" class="btn btn-info">
              <i class="fa fa-check-square"></i> Select All
            </button>
          </div>
        </div>

        <table class="table table-striped table-bordered" id="datatable-laporan-kurangan-picker">
          <thead>
            <tr>
              <th>#</th>
              <th>SKU</th>
              <th>No. Resi</th>
              <th>Marketplace</th>
              <th>Tgl Cetak</th>
              <th>B. Akhir Kirim</th>
              <th>QTY Kurang</th>
              <th style="width: 50px;">Check</th>
              <th style="width: 100px;">Action</th>
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

  // Inisialisasi DataTable - hanya setelah user submit
  <?php $has_search = $this->input->method() == 'post'; ?>
  
  <?php if($has_search): ?>
  var table = $('#datatable-laporan-kurangan-picker').DataTable({
    'scrollX': true,
    'pageLength': 10,
    'processing': true,
    'language': {
      'processing': 'Memproses data...'
    },
    'serverSide': true,
    'order': [[4, 'desc']],
    'lengthMenu': [
      [10, 50, 100, 150, 200],
      [10, 50, 100, 150, 200]
    ],
    'ajax': {
      url: 'cs/get-laporan-kurangan-picker-data',
      type: 'POST',
      data: function(d) {
        d.reportrange = $('#reportrange').val();
      }
    },
    'columnDefs': [
      { className: 'text-center', targets: [0, 6, 7, 8] },
      { orderable: false, targets: [7, 8] }
    ]
  });

  // Show button group when table is loaded
  $('#button-group').show();

  // Handle checkbox select all
  $('#btn-select-all').on('click', function() {
    var isChecked = $(this).data('checked') || false;
    $('.row-select').prop('checked', !isChecked);
    $(this).data('checked', !isChecked);
    $(this).html(isChecked ? '<i class="fa fa-check-square"></i> Select All' : '<i class="fa fa-square"></i> Deselect All');
  });

  // Handle submit per resi (single)
  $(document).on('click', '.btn-submit-resi', function() {
    var noresi = $(this).data('noresi');
    var $btn = $(this);
    
    if (!noresi) {
      alert('Nomor resi tidak ditemukan');
      return;
    }

    showConfirmation('Submit kurangan untuk resi: ' + noresi + '?', function() {
      $btn.prop('disabled', true).text('Processing...');

      $.ajax({
        url: 'cs/submit-kurangan-picker',
        method: 'POST',
        data: {
          noresi: noresi
        },
        dataType: 'json',
        success: function(response) {
          if (response && response.code === 201) {
            showNoty(response.message || 'Data berhasil disubmit', 'success');
            table.ajax.reload(null, false);
          } else {
            showNoty(response.message || 'Gagal menyimpan data', 'error');
          }
        },
        error: function(xhr) {
          var response = {};
          try {
            response = xhr.responseJSON || {};
          } catch(e) {
            response = { message: 'Terjadi kesalahan' };
          }
          showNoty(response.message || 'Terjadi kesalahan saat menyimpan data', 'error');
        },
        complete: function() {
          $btn.prop('disabled', false).html('Submit');
        }
      });
    });
  });

  // Handle submit selected (multiple)
  $('#btn-submit-selected').on('click', function() {
    var selected = [];
    $('.row-select:checked').each(function() {
      selected.push($(this).data('id-detail'));
    });

    if (selected.length === 0) {
      alert('Pilih minimal 1 item untuk di-submit');
      return;
    }

    showConfirmation('Submit ' + selected.length + ' item yang dipilih?', function() {
      var $btn = $('#btn-submit-selected');
      $btn.prop('disabled', true).text('Processing...');

      $.ajax({
        url: 'cs/submit-kurangan-picker',
        method: 'POST',
        data: {
          selected_items: selected
        },
        dataType: 'json',
        success: function(response) {
          if (response && response.code === 201) {
            showNoty(response.message || 'Data berhasil disubmit', 'success');
            table.ajax.reload(null, false);
            $('.row-select').prop('checked', false);
          } else {
            showNoty(response.message || 'Gagal menyimpan data', 'error');
          }
        },
        error: function(xhr) {
          var response = {};
          try {
            response = xhr.responseJSON || {};
          } catch(e) {
            response = { message: 'Terjadi kesalahan' };
          }
          showNoty(response.message || 'Terjadi kesalahan saat menyimpan data', 'error');
        },
        complete: function() {
          $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Submit Selected');
        }
      });
    });
  });
  <?php endif; ?>

  $('#btn-search').on('click', function() {
    $('#form-laporan-kurangan-picker').removeAttr("target");
    $('#form-laporan-kurangan-picker').removeClass('nojs');
    $('#form-laporan-kurangan-picker').attr('action', 'cs/laporan-kurangan-picker');
  });
  
  $('#btn-export-excel').on('click', function() {
    $('#form-laporan-kurangan-picker').attr("target", "_blank");
    $('#form-laporan-kurangan-picker').addClass('nojs');
    $('#form-laporan-kurangan-picker').attr('action', 'cs/export-excel-laporan-kurangan-picker');
  });
</script>

<script>
  var currentConfirmationNoty = null;

  function showConfirmation(message, onConfirm) {
    if (currentConfirmationNoty) {
      currentConfirmationNoty.close();
    }

    currentConfirmationNoty = noty({
      text: message,
      layout: 'center',
      modal: true,
      buttons: [
        {
          addClass: 'btn btn-success btn-clean',
          text: 'Ya',
          onClick: function($noty) {
            $noty.close();
            if (typeof onConfirm === 'function') {
              onConfirm();
            }
            currentConfirmationNoty = null;
          }
        },
        {
          addClass: 'btn btn-danger btn-clean',
          text: 'Batal',
          onClick: function($noty) {
            $noty.close();
            currentConfirmationNoty = null;
          }
        }
      ]
    });
  }

  function showNoty(message, type) {
    noty({
      text: message,
      layout: 'topRight',
      type: type || 'information',
      timeout: 3000
    });
  }
</script>


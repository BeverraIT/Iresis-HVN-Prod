<div class="row">
  <div class="col-md-12">

    <div class="panel panel-default tabs">
      <ul class="nav nav-tabs nav-justified">
        <li class="active"><a href="#tab-terima-retur" data-toggle="tab">Terima Retur</a></li>
        <li><a href="#tab-buka-retur" data-toggle="tab">Buka Retur</a></li>
      </ul>
      <div class="panel-body tab-content">
      <!-- ==================== TAB 1: TERIMA RETUR ==================== -->
      <div class="tab-pane active" id="tab-terima-retur">
            
            <!-- Filter Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-horizontal">
                  <div class="form-group">
                    <label class="col-md-2 control-label">Rentang Waktu</label>
                    <div class="col-md-4">
                      <input type="text" id="rentang_waktu_terima" class="form-control" placeholder="Pilih Rentang Waktu" />
                    </div>
                    <label class="col-md-1 control-label">Kurir</label>
                    <div class="col-md-3">
                      <select id="kurir_terima" class="form-control">
                        <option value="">Semua Kurir</option>
                        <?php foreach ($list_kurir as $kurir): ?>
                          <option value="<?= $kurir->id_kurir ?>"><?= $kurir->nama_kurir ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-8">
                      <button type="button" class="btn btn-primary" id="btn_tampilkan_terima">
                        <i class="fa fa-search"></i> Tampilkan
                      </button>
                      <button type="button" class="btn btn-success" id="btn_export_terima">
                        <i class="fa fa-file-excel-o"></i> Export ke Excel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Table Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered" id="datatable_terima_retur">
                    <thead>
                      <tr>
                        <th>No. Pinjaman</th>
                        <th>No. Resi</th>
                        <th>Market Place</th>
                        <th>Kurir</th>
                        <th>Tanggal Terima</th>
                        <th>Jam Terima</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Status Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

      </div>

      <!-- ==================== TAB 2: BUKA RETUR ==================== -->
      <div class="tab-pane" id="tab-buka-retur">
            
            <!-- Filter Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-horizontal">
                  <div class="form-group">
                    <label class="col-md-2 control-label">Rentang Waktu</label>
                    <div class="col-md-4">
                      <input type="text" id="rentang_waktu_buka" class="form-control" placeholder="Pilih Rentang Waktu" />
                    </div>
                    <label class="col-md-1 control-label">Kurir</label>
                    <div class="col-md-3">
                      <select id="kurir_buka" class="form-control">
                        <option value="">Semua Kurir</option>
                        <?php foreach ($list_kurir as $kurir): ?>
                          <option value="<?= $kurir->id_kurir ?>"><?= $kurir->nama_kurir ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-8">
                      <button type="button" class="btn btn-primary" id="btn_tampilkan_buka">
                        <i class="fa fa-search"></i> Tampilkan
                      </button>
                      <button type="button" class="btn btn-success" id="btn_export_buka">
                        <i class="fa fa-file-excel-o"></i> Export ke Excel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Table Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered" id="datatable_buka_retur">
                    <thead>
                      <tr>
                        <th>No. Pinjaman</th>
                        <th>No. Resi</th>
                        <th>Market Place</th>
                        <th>Kurir</th>
                        <th>Tanggal Buka</th>
                        <th>Jam Buka</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Status Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

      </div>

    </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // ==================== DATERANGEPICKER - TERIMA RETUR ====================
    $('#rentang_waktu_terima').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 1,
        locale: {
            format: 'YYYY-MM-DD HH:mm',
            separator: ' s/d ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Custom',
            weekLabel: 'W',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        },
        startDate: moment().startOf('day'),
        endDate: moment().endOf('day')
    });

    // ==================== DATERANGEPICKER - BUKA RETUR ====================
    $('#rentang_waktu_buka').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 1,
        locale: {
            format: 'YYYY-MM-DD HH:mm',
            separator: ' s/d ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Custom',
            weekLabel: 'W',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        },
        startDate: moment().startOf('day'),
        endDate: moment().endOf('day')
    });

    // ==================== DATATABLE - TERIMA RETUR ====================
    var tableTerimaRetur = $('#datatable_terima_retur').DataTable({
        'scrollX': true,
        'pageLength': 25,
        'processing': true,
        'serverSide': true,
        'order': [[4, 'desc']],
        'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        'ajax': {
            url: 'retur/get-data-terima-retur-laporan',
            type: 'POST',
            data: function(d) {
                var dates = $('#rentang_waktu_terima').val().split(' s/d ');
                d.start_date = dates[0] || '';
                d.end_date = dates[1] || '';
                d.id_kurir = $('#kurir_terima').val();
            }
        },
        'columns': [
            { 'data': 0 }, // No Pinjaman
            { 'data': 1 }, // No Resi
            { 'data': 2 }, // Market Place
            { 'data': 3 }, // Kurir
            { 'data': 4 }, // Tanggal Terima Retur
            { 'data': 5 }, // Jam Terima Retur
            { 'data': 6 }, // SKU
            { 'data': 7 }, // Quantity
            { 'data': 8 }  // Status Detail
        ]
    });

    // ==================== DATATABLE - BUKA RETUR ====================
    var tableBukaRetur = $('#datatable_buka_retur').DataTable({
        'scrollX': true,
        'pageLength': 25,
        'processing': true,
        'serverSide': true,
        'order': [[4, 'desc']],
        'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        'ajax': {
            url: 'retur/get-data-buka-retur-laporan',
            type: 'POST',
            data: function(d) {
                var dates = $('#rentang_waktu_buka').val().split(' s/d ');
                d.start_date = dates[0] || '';
                d.end_date = dates[1] || '';
                d.id_kurir = $('#kurir_buka').val();
            }
        },
        'columns': [
            { 'data': 0 }, // No Pinjaman
            { 'data': 1 }, // No Resi
            { 'data': 2 }, // Market Place
            { 'data': 3 }, // Kurir
            { 'data': 4 }, // Tanggal Buka Retur
            { 'data': 5 }, // Jam Buka Retur
            { 'data': 6 }, // SKU
            { 'data': 7 }, // Quantity
            { 'data': 8 }  // Status Detail
        ]
    });

    // ==================== BUTTON TAMPILKAN - TERIMA RETUR ====================
    $('#btn_tampilkan_terima').on('click', function() {
        tableTerimaRetur.ajax.reload();
    });

    // ==================== BUTTON TAMPILKAN - BUKA RETUR ====================
    $('#btn_tampilkan_buka').on('click', function() {
        tableBukaRetur.ajax.reload();
    });

    // ==================== BUTTON EXPORT - TERIMA RETUR ====================
    $('#btn_export_terima').on('click', function() {
        var dates = $('#rentang_waktu_terima').val().split(' s/d ');
        var start_date = dates[0] || '';
        var end_date = dates[1] || '';
        var id_kurir = $('#kurir_terima').val();
        
        if (!start_date || !end_date) {
            alert('Silakan pilih rentang waktu terlebih dahulu!');
            return;
        }

        var url = 'retur/export-excel-terima-retur?start_date=' + encodeURIComponent(start_date) + '&end_date=' + encodeURIComponent(end_date);
        if (id_kurir) {
            url += '&id_kurir=' + encodeURIComponent(id_kurir);
        }
        window.location.href = url;
    });

    // ==================== BUTTON EXPORT - BUKA RETUR ====================
    $('#btn_export_buka').on('click', function() {
        var dates = $('#rentang_waktu_buka').val().split(' s/d ');
        var start_date = dates[0] || '';
        var end_date = dates[1] || '';
        var id_kurir = $('#kurir_buka').val();
        
        if (!start_date || !end_date) {
            alert('Silakan pilih rentang waktu terlebih dahulu!');
            return;
        }

        var url = 'retur/export-excel-buka-retur?start_date=' + encodeURIComponent(start_date) + '&end_date=' + encodeURIComponent(end_date);
        if (id_kurir) {
            url += '&id_kurir=' + encodeURIComponent(id_kurir);
        }
        window.location.href = url;
    });

    // ==================== TAB CHANGE HANDLER ====================
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.hash === '#tab-terima-retur') {
            tableTerimaRetur.columns.adjust().draw();
        } else if (e.target.hash === '#tab-buka-retur') {
            tableBukaRetur.columns.adjust().draw();
        }
    });
});
</script>

<style>
#rentang_waktu_terima, #rentang_waktu_buka {
    background-color: #fff !important;
    cursor: pointer !important;
    color: #555 !important;
    border: 1px solid #ccc !important;
}

#rentang_waktu_terima:hover, #rentang_waktu_buka:hover {
    border-color: #66afe9 !important;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6) !important;
}
</style>

<style>
.form-group {
    margin-bottom: 20px;
}

.table-responsive {
    margin-top: 20px;
}
</style>


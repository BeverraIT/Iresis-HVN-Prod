<div class="row">
  <div class="col-md-12">

    <div class="panel panel-default tabs">
      <ul class="nav nav-tabs nav-justified">
        <li class="active"><a href="#tab-terima-retur-laporan" data-toggle="tab">Terima Retur</a></li>
        <li><a href="#tab-buka-retur-laporan" data-toggle="tab">Buka Retur</a></li>
      </ul>
      <div class="panel-body tab-content">
      <!-- ==================== TAB 1: TERIMA RETUR ==================== -->
      <div class="tab-pane active" id="tab-terima-retur-laporan">
            
            <!-- Filter Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-horizontal">
                  <div class="form-group">
                    <label class="col-md-2 control-label">Rentang Waktu</label>
                    <div class="col-md-6">
                      <input type="text" id="rentang_waktu_terima" class="form-control" placeholder="*Pilih Rentang Waktu" readonly />
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-primary btn-block" id="btn_tampilkan_terima">
                        Tampilkan
                      </button>
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-success btn-block" id="btn_export_terima">
                        Ekspor ke Excel
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
                        <th>Tanggal Terima Retur</th>
                        <th>Jam Buka Retur</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Status Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td colspan="9" class="text-center">SAMPLE DATA ROW 1</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

      </div>

      <!-- ==================== TAB 2: BUKA RETUR ==================== -->
      <div class="tab-pane" id="tab-buka-retur-laporan">
            
            <!-- Filter Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-horizontal">
                  <div class="form-group">
                    <label class="col-md-2 control-label">Rentang Waktu</label>
                    <div class="col-md-6">
                      <input type="text" id="rentang_waktu_buka" class="form-control" placeholder="*Pilih Rentang Waktu" readonly />
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-primary btn-block" id="btn_tampilkan_buka">
                        Tampilkan
                      </button>
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-success btn-block" id="btn_export_buka">
                        Ekspor ke Excel
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
                        <th>Tanggal Buka Retur</th>
                        <th>Jam Buka Retur</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Status Detail</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td colspan="9" class="text-center">SAMPLE DATA ROW 1</td>
                      </tr>
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
            }
        },
        'columns': [
            { 'data': 0 }, // No Pinjaman
            { 'data': 1 }, // No Resi
            { 'data': 2 }, // Market Place
            { 'data': 3 }, // Kurir
            { 'data': 4 }, // Tanggal Terima Retur
            { 'data': 5 }, // Jam Buka Retur
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
        
        if (!start_date || !end_date) {
            alert('Silakan pilih rentang waktu terlebih dahulu!');
            return;
        }

        window.location.href = 'retur/export-excel-terima-retur?start_date=' + encodeURIComponent(start_date) + '&end_date=' + encodeURIComponent(end_date);
    });

    // ==================== BUTTON EXPORT - BUKA RETUR ====================
    $('#btn_export_buka').on('click', function() {
        var dates = $('#rentang_waktu_buka').val().split(' s/d ');
        var start_date = dates[0] || '';
        var end_date = dates[1] || '';
        
        if (!start_date || !end_date) {
            alert('Silakan pilih rentang waktu terlebih dahulu!');
            return;
        }

        window.location.href = 'retur/export-excel-buka-retur?start_date=' + encodeURIComponent(start_date) + '&end_date=' + encodeURIComponent(end_date);
    });

    // ==================== TAB CHANGE HANDLER ====================
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.hash === '#tab-terima-retur-laporan') {
            tableTerimaRetur.columns.adjust().draw();
        } else if (e.target.hash === '#tab-buka-retur-laporan') {
            tableBukaRetur.columns.adjust().draw();
        }
    });
});
</script>

<style>
.form-group {
    margin-bottom: 20px;
}

.table-responsive {
    margin-top: 20px;
}
</style>

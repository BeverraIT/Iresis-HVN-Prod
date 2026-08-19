<div class="row">

  <!-- ============ IMPOR EXCEL ============ -->
  <div class="col-md-6">
    <form action="timbangan/upload-master-timbangan-action" method="post" enctype="multipart/form-data" class="form-horizontal" id="form_upload_sku">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Impor Master Berat SKU</strong></h3>
        </div>

        <div class="panel-body">
          <p class="text-muted">
            Format file: kolom <strong>A = KODE SKU</strong>, kolom <strong>B = BERAT BARU</strong> (satuan gram).
            Baris pertama dianggap judul kolom. SKU yang sudah ada akan diperbarui beratnya,
            SKU baru akan ditambahkan. Baris dengan berat kosong atau bukan angka dilewati.
          </p>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">File Excel</label>
            <div class="col-md-8 col-xs-12">
              <input id="sku_file" class="form-control" type="file" name="skuFile" accept=".xlsx" />
            </div>
          </div>

          <div class="tile tile-default" id="tile_upload">
            <span id="span_upload">-</span>
            <p><small id="span_upload_detail">Belum ada file yang diproses</small></p>
          </div>
        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-info"><i class="fa fa-upload"></i> Impor</button>
        </div>
      </div>
    </form>
  </div>

  <!-- ============ PENGATURAN GLOBAL ============ -->
  <div class="col-md-6">
    <form action="timbangan/save-setting-timbangan" class="form-horizontal" id="form_setting">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Pengaturan Timbangan</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Toleransi Global (%)</label>
            <div class="col-md-6 col-xs-12">
              <input type="number" step="0.01" min="0" name="TOLERANSI_PERSEN" class="form-control" value="<?= $setting['TOLERANSI_PERSEN'] ?>" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Berat Kemasan (gram)</label>
            <div class="col-md-6 col-xs-12">
              <input type="number" step="0.01" min="0" name="BERAT_KEMASAN" class="form-control" value="<?= $setting['BERAT_KEMASAN'] ?>" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Satuan Indikator</label>
            <div class="col-md-6 col-xs-12">
              <select name="SATUAN_INDIKATOR" class="form-control">
                <option value="g" <?= $setting['SATUAN_INDIKATOR'] == 'g' ? 'selected' : '' ?>>Gram (g)</option>
                <option value="kg" <?= $setting['SATUAN_INDIKATOR'] == 'kg' ? 'selected' : '' ?>>Kilogram (kg)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Baud Rate Default</label>
            <div class="col-md-6 col-xs-12">
              <select name="BAUD_RATE" class="form-control">
                <?php foreach (array(2400, 4800, 9600, 19200, 38400, 57600, 115200) as $baud) : ?>
                  <option value="<?= $baud ?>" <?= $setting['BAUD_RATE'] == $baud ? 'selected' : '' ?>><?= $baud ?> bps</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Interlock</label>
            <div class="col-md-6 col-xs-12">
              <select name="INTERLOCK" class="form-control">
                <option value="1" <?= $setting['INTERLOCK'] == '1' ? 'selected' : '' ?>>Aktif &ndash; hanya ACCEPT yang bisa disimpan</option>
                <option value="0" <?= $setting['INTERLOCK'] == '1' ? '' : 'selected' ?>>Nonaktif &ndash; semua status bisa disimpan</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Simpan Otomatis</label>
            <div class="col-md-6 col-xs-12">
              <select name="AUTO_SIMPAN" class="form-control">
                <option value="1" <?= $setting['AUTO_SIMPAN'] == '1' ? 'selected' : '' ?>>Aktif &ndash; hasil lolos langsung tersimpan &amp; lanjut resi berikutnya</option>
                <option value="0" <?= $setting['AUTO_SIMPAN'] == '1' ? '' : 'selected' ?>>Nonaktif &ndash; operator menekan tombol Simpan</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Jeda Bacaan Stabil (ms)</label>
            <div class="col-md-6 col-xs-12">
              <input type="number" step="100" min="0" name="STABIL_MS" class="form-control" value="<?= $setting['STABIL_MS'] ?>" />
              <span class="help-block"><small>Lama angka indikator harus diam sebelum dinilai lolos / tidak. Terlalu kecil membuat paket yang masih berayun ikut terkunci.</small></span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-5 col-xs-12 control-label">Kode Atasan</label>
            <div class="col-md-6 col-xs-12">
              <input type="password" name="KODE_SUPERVISOR" class="form-control" autocomplete="new-password" placeholder="Kosongkan bila tidak diubah" />
              <span class="help-block"><small>Dipakai atasan untuk membuka kunci saat berat paket tidak lolos verifikasi. Minimal 4 karakter.</small></span>
            </div>
          </div>

          <div class="tile tile-default" id="tile_setting">
            <span id="span_setting">-</span>
          </div>

        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Pengaturan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ============ DAFTAR MASTER ============ -->
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Daftar Berat Standar SKU</strong></h3>
      </div>

      <div class="panel-body">

        <!-- Unduh riwayat perubahan berat. Datanya berasal dari trigger pada
             tabel master, jadi ikut mencatat perubahan lewat menu ini, impor
             Excel, maupun perbaikan langsung di database.

             class `nojs`: unduhan harus lewat submit biasa, kalau tidak
             handler form global di plugins.js mengubahnya jadi AJAX dan
             berkasnya tidak pernah terunduh. -->
        <form action="timbangan/export-to-excel-log-master" method="post" id="form_export_log" class="nojs" target="_blank">
          <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-4">
              <label class="control-label">Periode perubahan</label>
              <input type="text" name="reportrange" id="reportrange_log" class="form-control" readonly />
            </div>

            <div class="col-md-8">
              <label class="control-label">&nbsp;</label><br />
              <button type="submit" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Log Perubahan</button>
              <span class="text-muted" style="margin-left:8px;">
                <small>Berisi berat sebelumnya, berat baru, dan tanggal perubahannya.</small>
              </span>
            </div>
          </div>
        </form>

        <table class="table table-striped datatable-master-timbangan">
          <thead>
            <tr>
              <th>Kode SKU</th>
              <th>Nama SKU</th>
              <th>Berat Standar (g)</th>
              <th>Toleransi</th>
              <th>Terakhir Diubah</th>
              <th>Aksi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ============ MODAL EDIT ============ -->
<div class="modal fade" id="modal_edit_master" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form action="timbangan/save-master-timbangan" id="form_edit_master">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Ubah Berat Standar SKU</h4>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Kode SKU</label>
            <input type="text" name="kode_sku" id="edit_kode_sku" class="form-control" readonly />
          </div>

          <div class="form-group">
            <label>Berat Standar (gram)</label>
            <!-- step 0,01: ada SKU yang beratnya pecahan, mis. MCBTYPEC-4056
                 1,6 g. Dengan step 1, browser menolak angka seperti itu dan
                 beratnya terpaksa dibulatkan -- pada pesanan ratusan pcs
                 pembulatan sekecil itu menggeser berat standar sangat jauh. -->
            <input type="number" step="0.01" min="0" name="berat_standar" id="edit_berat_standar" class="form-control" />
          </div>

          <div class="form-group">
            <label>Toleransi Khusus (%)</label>
            <input type="number" step="0.01" min="0" name="toleransi_persen" id="edit_toleransi" class="form-control" placeholder="Kosongkan untuk ikut toleransi global" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  (function() {
    // Bawaan 30 hari terakhir supaya perubahan terbaru langsung ikut terunduh.
    $("#reportrange_log").daterangepicker({
      timePicker: true,
      timePicker24Hour: true,
      startDate: moment().subtract(29, 'days').startOf('day'),
      endDate: moment(),
      locale: { format: 'YYYY-MM-DD HH:mm:ss', separator: ' - ' },
      ranges: {
        'Hari Ini': [moment().startOf('day'), moment()],
        '7 Hari Terakhir': [moment().subtract(6, 'days').startOf('day'), moment()],
        '30 Hari Terakhir': [moment().subtract(29, 'days').startOf('day'), moment()],
        'Bulan Ini': [moment().startOf('month'), moment()],
        'Seluruh Riwayat': [moment('2020-01-01'), moment()],
      },
    });

    var tabel = $('.datatable-master-timbangan').DataTable({
      'scrollX': true,
      'pageLength': 25,
      'processing': true,
      'serverSide': true,
      'order': [[0, 'asc']],
      'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
      'columnDefs': [{ 'orderable': false, 'targets': 5 }],
      'ajax': {
        url: 'timbangan/get-data-master-timbangan',
        type: 'POST',
      },
    });

    function setTile(id, teks, tipe) {
      $('#tile_' + id).removeClass('tile-default tile-success tile-danger').addClass('tile-' + tipe);
      $('#span_' + id).text(teks);
    }

    // ------------------------------------------------------------------
    // Impor Excel
    // ------------------------------------------------------------------
    $("#form_upload_sku").on('submit', function(e) {
      e.preventDefault();

      var file = $("#sku_file")[0].files[0];

      if (!file) {
        setTile('upload', 'Silakan pilih file Excel terlebih dahulu', 'danger');
        return;
      }

      var form = this;
      var tombol = $(this).find('button[type=submit]');

      tombol.prop('disabled', true);
      setTile('upload', 'Memproses file...', 'default');
      $("#span_upload_detail").text('');

      $.ajax({
        url: form.action,
        type: 'post',
        data: new FormData(form),
        contentType: false,
        processData: false,
        timeout: 600000,
      })
        .done(function(res) {
          setTile('upload', res.message, 'success');
          $("#sku_file").val('');
          tabel.ajax.reload();
        })
        .fail(function(xhr) {
          var pesan = 'Gagal memproses file';
          try { pesan = JSON.parse(xhr.responseText).message; } catch (e) {}
          setTile('upload', pesan, 'danger');
        })
        .always(function() {
          tombol.prop('disabled', false);
        });
    });

    // ------------------------------------------------------------------
    // Pengaturan global
    // ------------------------------------------------------------------
    $("#form_setting").on('submit', function(e) {
      e.preventDefault();

      var tombol = $(this).find('button[type=submit]');
      tombol.prop('disabled', true);

      $.post(this.action, $(this).serialize())
        .done(function(res) {
          setTile('setting', res.message, 'success');
        })
        .fail(function(xhr) {
          var pesan = 'Gagal menyimpan pengaturan';
          try { pesan = JSON.parse(xhr.responseText).message; } catch (e) {}
          setTile('setting', pesan, 'danger');
        })
        .always(function() {
          tombol.prop('disabled', false);
        });
    });

    // ------------------------------------------------------------------
    // Edit per SKU
    // ------------------------------------------------------------------
    $(document).on('click', '.btn-edit-master', function() {
      $("#edit_kode_sku").val($(this).data('kode'));
      $("#edit_berat_standar").val($(this).data('berat'));
      $("#edit_toleransi").val($(this).data('toleransi') === null ? '' : $(this).data('toleransi'));

      $("#modal_edit_master").modal('show');
    });

    $("#form_edit_master").on('submit', function(e) {
      e.preventDefault();

      var tombol = $(this).find('button[type=submit]');
      tombol.prop('disabled', true);

      $.post(this.action, $(this).serialize())
        .done(function() {
          $("#modal_edit_master").modal('hide');
          tabel.ajax.reload(null, false);
        })
        .fail(function(xhr) {
          var pesan = 'Gagal menyimpan';
          try { pesan = JSON.parse(xhr.responseText).message; } catch (e) {}
          alert(pesan);
        })
        .always(function() {
          tombol.prop('disabled', false);
        });
    });
  })();
</script>

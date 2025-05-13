<div class="row">
  <div class="col-md-8 center-block float-none">
    <form action="receipt/save_reprint_receipt" class="form-horizontal" id="form_scan_resi" enctype="multipart/form-data" autocomplete="off">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Print Ulang Resi</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Alasan Hilang</label>
            <div class="col-md-8 col-xs-12">
              <select name="alasan" id="alasan" class="form-control select" data-live-search="true">
                <option value=""></option>
                <?php foreach ($list_reason as $reason) : ?>
                  <option value="<?= $reason['id'] ?>"><?= $reason['paramvalue1'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group hidden" id="div_foto">
            <label class="col-md-3 col-xs-12 control-label">Foto</label>
            <div class="col-md-8 col-xs-12">
              <input type="file" class="fileinput" multiple name="images[]" id="images" data-filename-placement="inside" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Keterangan</label>
            <div class="col-md-8 col-xs-12">
              <textarea name="keterangan" class="form-control" rows="5"></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Resi</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" name="noresi" id="noresi" class="form-control" />
            </div>
          </div>

          <div class="tile tile-default" id="div_container_latest_receipt">
            <span id="span_latest_receipt">-</span>
            <p><small id="p_latest_receipt_message">Nomor resi terakhir yang sudah di-scan</small></p>
          </div>

        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-info">Submit</button>
          <button type="reset" class="btn btn-primary pull-right">Reset</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  var jvalidate = $("#form_scan_resi").validate({
    ignore: [],
    rules: {
      alasan: {
        required: true,
      },
      noresi: {
        required: true,
      },
    },
    submitHandler: function(form) {
      var formData = new FormData(form);

      $.each($('#images')[0].files, function(i, file) {
        formData.append('images-' + i, file);
      });

      form.noresi.disabled = true;

      $.ajax({
        url: form.action,
        type: 'post',
        data: Object.fromEntries(formData),
        success: function(data) {},
        error: function(data) {},
      }).done(function(response) {
        $("#span_latest_receipt").text(form.noresi.value);
        $("#div_container_latest_receipt").removeClass("tile-danger").addClass("tile-default");

        document.getElementById('audio-alert').play();

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      }).fail(function(response) {
        $("#span_latest_receipt").text(form.noresi.value);
        $("#div_container_latest_receipt").removeClass("tile-default").addClass("tile-danger");

        document.getElementById('audio-fail').play();

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      });
      return false; // required to block normal submit since you used ajax
    }
  });

  $('#alasan').on('change', function() {
    const id_alasan = this.value;

    if (id_alasan === '5') { // condition for PAKET SALAH KERANGJANG OLEH KARYAWAN
      $('#div_foto').removeClass('hidden');
    } else {
      $('#div_foto').addClass('hidden');
    }
  });
</script>
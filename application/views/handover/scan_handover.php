<div class="row">
  <div class="col-md-6 center-block float-none">
    <form action="handover/save-handover" class="form-horizontal" id="form_scan_handover" autocomplete="off">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Scan Resi Keluar</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Total scan resi Anda hari ini</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" id="total_scan" value="<?= $total_scan ?>" class="form-control" disabled />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Komputer</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" class="form-control" value="<?= $nama_komputer ?>" readonly />
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
            <p><small id="p_latest_receipt_message">Nomor resi terakhir yang sudah di-scan keluar</small></p>
          </div>

        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-info">Submit</button>
          <a href="handover_scan/print" class="btn btn-default link pull-right"><i class="fa fa-print"></i> Cetak Tanda Terima</a>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  $("#noresi").focus();
  var total_scan = document.getElementById('total_scan');

  const __COURRIER_AUDIO = {
    '': '',
  };

  var jvalidate = $("#form_scan_handover").validate({
    ignore: [],
    rules: {
      noresi: {
        required: true,
      },
    },
    submitHandler: function(form) {
      var formData = new FormData(form);

      form.noresi.disabled = true;

      $.ajax({
        url: form.action,
        type: 'post',
        data: Object.fromEntries(formData),
        success: function(data) {},
        error: function(data) {},
      }).done(function(response) {
        $("#div_container_latest_receipt").removeClass("tile-danger").addClass("tile-default");
        $("#span_latest_receipt").text(form.noresi.value);
        $("#p_latest_receipt_message").text("Nomor resi terakhir yang sudah di-scan Packer");

        const courrier_code = form.noresi.value.substring(0, 2);
        const courrier_code_2 = form.noresi.value.substring(0, 3);
        switch (courrier_code) {
          case 'JP':
          case 'JX':
          case 'JO':
          case 'JZ':
          case 'TJ':
          case '20':
            document.getElementById('audio-jnt').play();
            break;

          case 'SP':
            document.getElementById('audio-shopee').play();
            break;

          case 'JN':
          case 'LX':
          case 'NL':
            document.getElementById('audio-lazada').play();
            break;

          case 'JT':
          case 'TL':
            document.getElementById('audio-jne').play();
            break;

          case '00':
          case 'TK':
            if (courrier_code_2 === 'TKP') {
              document.getElementById('audio-rekomen').play();
            } else {
              document.getElementById('audio-sicepat').play();
            }
            break;

          case 'NJ':
            document.getElementById('audio-ninja').play();
            break;

          case 'IN':
          case '24':
          case 'GT':
            document.getElementById('audio-instant').play();
            break;
        }

        total_scan.value = Number(total_scan.value) + 1;

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      }).fail(function(error) {
        var response = JSON.parse(error.responseText);

        $("#span_latest_receipt").text(form.noresi.value);
        $("#div_container_latest_receipt").removeClass("tile-default").addClass("tile-danger");
        $("#p_latest_receipt_message").text(response.message);

        if (error.status === 400) { // already handover
          document.getElementById('audio-error').play();
        } else {
          document.getElementById('audio-fail').play();
        }

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      });
      return false; // required to block normal submit since you used ajax
    }
  });
</script>
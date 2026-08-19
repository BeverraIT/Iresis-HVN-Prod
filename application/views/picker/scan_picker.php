<div class="row">
  <div class="col-md-6 center-block float-none">
    <form action="picker/save-scan-picker" class="form-horizontal" id="form_scan_picker" autocomplete="off">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Scan Picker</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Total scan resi Anda hari ini</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" id="total_scan" value="<?= $total_scan ?>" class="form-control" disabled />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Nama Picker</label>
            <div class="col-md-8 col-xs-12">
              <select name="id_pegawaipicker" id="id_pegawaipicker" class="form-control select" data-live-search="true">
                <?php foreach ($list_picker as $picker) : ?>
                  <option value="<?= $picker['id_pegawai'] ?>"><?= $picker['nama_pegawai'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Status Performa</label>
            <div class="col-md-8 col-xs-12">
              <select name="status_performa" id="status_performa" class="form-control select" data-live-search="true" style="font-weight: bold;">
                <option value="" style="font-weight: bold;" selected disabled>-- Select Status Performa --</option>
                <?php foreach ($list_status_performa as $status) : ?>
                  <option value="<?= $status['kode_status'] ?>" style="font-weight: bold;">
                    <?= $status['status_name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
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
            <p><small id="p_latest_receipt_message">Nomor resi terakhir yang sudah di-scan Picker</small></p>
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
  $("#noresi").focus();
  var total_scan = document.getElementById('total_scan');

  // Function untuk process request directly (Parallel)
  function sendRequest(url, formData, noresiValue) {
    $.ajax({
      url: url,
      type: 'post',
      data: formData,
      processData: false, // Don't process the files
      contentType: false, // Set content type to false as jQuery will tell the server its a query string request
      timeout: 10000,
      cache: false,
      success: function(data) {
          // Success feedback
          $("#div_container_latest_receipt").removeClass("tile-danger").addClass("tile-default");
          $("#span_latest_receipt").text(noresiValue);
          $("#p_latest_receipt_message").text("Nomor resi terakhir yang sudah di-scan Picker");

          // Play success sound
          if (document.getElementById('audio-alert')) {
            document.getElementById('audio-alert').play();
          }
      },
      error: function(xhr, status, error) {
          // Error feedback
          var response = {};
          try {
            response = JSON.parse(xhr.responseText);
          } catch (e) {
            response.message = "Terjadi kesalahan pada server";
          }

          // Rollback counter on error
          total_scan.value = Number(total_scan.value) - 1;

          $("#span_latest_receipt").text(noresiValue);
          $("#div_container_latest_receipt").removeClass("tile-default").addClass("tile-danger");
          $("#p_latest_receipt_message").text(response.message);

          // Play error sound
          if (document.getElementById('audio-fail')) {
            document.getElementById('audio-fail').play();
          }
      }
    });
  }

  $('#id_pegawaipicker').on('change', function() {
    $("#noresi").focus();
  });

  var jvalidate = $("#form_scan_picker").validate({
    ignore: [],
    rules: {
      id_pegawaipicker: {
        required: true,
      },
      status_performa: {
        required: true,
      },
      noresi: {
        required: true,
      },
    },
    submitHandler: function(form) {
      var formData = new FormData(form);
      var noresiValue = form.noresi.value;

      // Immediate feedback - update UI
      $("#div_container_latest_receipt").removeClass("tile-danger").addClass("tile-default");
      $("#span_latest_receipt").text(noresiValue);
      $("#p_latest_receipt_message").text("Dalam antrian...");
      
      // Increment counter immediately
      total_scan.value = Number(total_scan.value) + 1;

      // Submit immediately
      sendRequest(form.action, formData, noresiValue);

      // Reset form immediately untuk scan berikutnya
      form.noresi.value = "";
      form.noresi.focus();
      
      return false;
    }
  });

  // Process KPI queue every 30 seconds (optimized)
  var kpiQueueInterval = setInterval(function() {
    $.ajax({
      url: 'picker/process-kpi-queue',
      type: 'post',
      timeout: 5000, // 5 second timeout
      cache: false, // Disable cache for real-time data
      success: function(response) {
        // KPI queue processed successfully
      },
      error: function(xhr, status, error) {
        // Silent fail - KPI processing is not critical
        if (status === 'timeout') {
          console.warn('KPI queue processing timeout');
        }
      }
    });
  }, 30000); // 30 seconds
  
  // Clear interval when page unloads
  $(window).on('beforeunload', function() {
    clearInterval(kpiQueueInterval);
  });
</script>
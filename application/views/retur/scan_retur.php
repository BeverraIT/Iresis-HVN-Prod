<div class="row">
  <div class="col-md-12">

    <div class="panel panel-default tabs">
      <ul class="nav nav-tabs nav-justified">
        <li class="active"><a href="#tab-terima-retur" data-toggle="tab">Terima Retur</a></li>
        <li><a href="#tab-buka-retur" data-toggle="tab">Buka Retur</a></li>
      </ul>
      <div class="panel-body tab-content">
          <!-- ==================== TAB 1: TERIMA RETUR ==================== -->
          <div class="tab-pane fade in active" id="tab-terima-retur">
            <div class="row">
              <div class="col-md-6 center-block float-none">
                <form action="retur/save-retur" class="form-horizontal" id="form_scan_terima_retur" autocomplete="off">
                  <div class="panel panel-default">
                    <div class="panel-body">

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Total scan resi Anda hari ini</label>
                        <div class="col-md-8 col-xs-12">
                          <input type="text" id="total_scan_terima" value="<?= isset($total_scan_terima) ? $total_scan_terima : 0 ?>" class="form-control" disabled />
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Status</label>
                        <div class="col-md-8 col-xs-12">
                          <input type="text" class="form-control" value="Terima Retur" readonly />
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Resi</label>
                        <div class="col-md-8 col-xs-12">
                          <input type="text" name="hasil_scan" id="noresi_terima" class="form-control" />
                        </div>
                      </div>

                      <div class="tile tile-default" id="div_container_latest_receipt_terima">
                        <span id="span_latest_receipt_terima">-</span>
                        <p><small id="p_latest_receipt_message_terima">Nomor resi terakhir yang sudah di-scan</small></p>
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
          </div>

          <!-- ==================== TAB 2: BUKA RETUR ==================== -->
          <div class="tab-pane fade" id="tab-buka-retur">
            <div class="row">
              <div class="col-md-6 center-block float-none">
                <form action="retur/save-buka-retur" class="form-horizontal" id="form_scan_buka_retur" autocomplete="off">
                  <div class="panel panel-default">
                    <div class="panel-body">

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Total scan resi Anda hari ini</label>
                        <div class="col-md-8 col-xs-12">
                          <input type="text" id="total_scan_buka" value="<?= isset($total_scan_buka) ? $total_scan_buka : 0 ?>" class="form-control" disabled />
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Status</label>
                        <div class="col-md-8 col-xs-12">
                          <input type="text" name="status_buka" id="status_buka" class="form-control" value="Buka Retur" readonly />
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Status Detail</label>
                        <div class="col-md-8 col-xs-12">
                          <select name="status_detail_buka" id="status_detail_buka" class="form-control" required>
                            <option value="" selected disabled>*List Of Value*</option>
                            <option value="REJECT">REJECT</option>
                            <option value="REFUND">REFUND</option>
                            <option value="PENUKARAN_BERES">PENUKARAN BERES</option>
                            <option value="KURANG">KURANG</option>
                            <option value="KURANG_DARI_PEMBELI">KURANG DARI PEMBELI</option>
                            <option value="KE_DISPLAY">KE DISPLAY</option>
                            <option value="BUKAN_BARANG_KITA">BUKAN BARANG KITA</option>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="col-md-4 col-xs-12 control-label">Resi</label>
                        <div class="col-md-8 col-xs-12">
                          <input type="text" name="hasil_scan_buka" id="noresi_buka" class="form-control" />
                        </div>
                      </div>

                      <div class="tile tile-default" id="div_container_latest_receipt_buka">
                        <span id="span_latest_receipt_buka">-</span>
                        <p><small id="p_latest_receipt_message_buka">Nomor resi terakhir yang sudah di-scan</small></p>
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
      </div>
    </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // ==================== AUTO FOCUS ON LOAD ====================
    $("#noresi_terima").focus();

    var total_scan_terima = document.getElementById('total_scan_terima');
    var total_scan_buka = document.getElementById('total_scan_buka');

    // ==================== REQUEST QUEUE - TERIMA RETUR ====================
    var requestQueueTerima = [];
    var isProcessingTerima = false;

    function processQueueTerima() {
        if (isProcessingTerima || requestQueueTerima.length === 0) {
            return;
        }

        isProcessingTerima = true;
        var request = requestQueueTerima.shift();
        
        $.ajax({
            url: request.url,
            type: 'post',
            data: request.data,
            timeout: 10000,
            cache: false,
            success: function(data) {
                request.success(data);
                isProcessingTerima = false;
                processQueueTerima();
            },
            error: function(xhr, status, error) {
                request.error(xhr, status, error);
                isProcessingTerima = false;
                processQueueTerima();
            }
        });
    }

    // ==================== REQUEST QUEUE - BUKA RETUR ====================
    var requestQueueBuka = [];
    var isProcessingBuka = false;

    function processQueueBuka() {
        if (isProcessingBuka || requestQueueBuka.length === 0) {
            return;
        }

        isProcessingBuka = true;
        var request = requestQueueBuka.shift();
        
        $.ajax({
            url: request.url,
            type: 'post',
            data: request.data,
            timeout: 10000,
            cache: false,
            success: function(data) {
                request.success(data);
                isProcessingBuka = false;
                processQueueBuka();
            },
            error: function(xhr, status, error) {
                request.error(xhr, status, error);
                isProcessingBuka = false;
                processQueueBuka();
            }
        });
    }

    // ==================== AUTO FOCUS AFTER STATUS DETAIL CHANGE ====================
    $('#status_detail_buka').on('change', function() {
        $("#noresi_buka").focus();
    });

    // ==================== FORM VALIDATION & SUBMIT: TERIMA RETUR ====================
    var jvalidateTerima = $("#form_scan_terima_retur").validate({
        ignore: [],
        rules: {
            hasil_scan: {
                required: true,
            },
        },
        submitHandler: function(form) {
            var formData = new FormData(form);
            var noresiValue = $("#noresi_terima").val();

            // Immediate feedback - update UI
            $("#div_container_latest_receipt_terima").removeClass("tile-danger").addClass("tile-default");
            $("#span_latest_receipt_terima").text(noresiValue);
            $("#p_latest_receipt_message_terima").text("Dalam antrian...");
            
            // Increment counter immediately
            total_scan_terima.value = Number(total_scan_terima.value) + 1;

            // Add to queue
            requestQueueTerima.push({
                url: form.action,
                data: Object.fromEntries(formData),
                noresiValue: noresiValue,
                success: function(data) {
                    // Success feedback
                    $("#div_container_latest_receipt_terima").removeClass("tile-danger").addClass("tile-default");
                    $("#span_latest_receipt_terima").text(noresiValue);
                    $("#p_latest_receipt_message_terima").text("Nomor resi terakhir yang sudah di-scan");

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
                    total_scan_terima.value = Number(total_scan_terima.value) - 1;

                    $("#span_latest_receipt_terima").text(noresiValue);
                    $("#div_container_latest_receipt_terima").removeClass("tile-default").addClass("tile-danger");
                    $("#p_latest_receipt_message_terima").text(response.message);

                    // Play error sound
                    if (document.getElementById('audio-fail')) {
                        document.getElementById('audio-fail').play();
                    }
                }
            });

            // Process queue
            processQueueTerima();

            // Reset form immediately for next scan
            $("#noresi_terima").val("");
            $("#noresi_terima").focus();
            
            return false;
        }
    });

    // ==================== FORM VALIDATION & SUBMIT: BUKA RETUR ====================
    var jvalidateBuka = $("#form_scan_buka_retur").validate({
        ignore: [],
        rules: {
            status_detail_buka: {
                required: true,
            },
            hasil_scan_buka: {
                required: true,
            },
        },
        submitHandler: function(form) {
            var formData = new FormData(form);
            var noresiValue = $("#noresi_buka").val();

            // Immediate feedback - update UI
            $("#div_container_latest_receipt_buka").removeClass("tile-danger").addClass("tile-default");
            $("#span_latest_receipt_buka").text(noresiValue);
            $("#p_latest_receipt_message_buka").text("Dalam antrian...");
            
            // Increment counter immediately
            total_scan_buka.value = Number(total_scan_buka.value) + 1;

            // Add to queue
            requestQueueBuka.push({
                url: form.action,
                data: Object.fromEntries(formData),
                noresiValue: noresiValue,
                success: function(data) {
                    // Success feedback
                    $("#div_container_latest_receipt_buka").removeClass("tile-danger").addClass("tile-default");
                    $("#span_latest_receipt_buka").text(noresiValue);
                    $("#p_latest_receipt_message_buka").text("Nomor resi terakhir yang sudah di-scan");

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
                    total_scan_buka.value = Number(total_scan_buka.value) - 1;

                    $("#span_latest_receipt_buka").text(noresiValue);
                    $("#div_container_latest_receipt_buka").removeClass("tile-default").addClass("tile-danger");
                    $("#p_latest_receipt_message_buka").text(response.message);

                    // Play error sound
                    if (document.getElementById('audio-fail')) {
                        document.getElementById('audio-fail').play();
                    }
                }
            });

            // Process queue
            processQueueBuka();

            // Reset form immediately for next scan
            $("#noresi_buka").val("");
            $("#noresi_buka").focus();
            
            return false;
        }
    });

    // ==================== TAB CHANGE HANDLER ====================
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.hash === '#tab-terima-retur') {
            $('#noresi_terima').focus();
        } else if (e.target.hash === '#tab-buka-retur') {
            $('#noresi_buka').focus();
        }
    });
});
</script>

<style>
.center-block {
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.float-none {
    float: none !important;
}
</style>

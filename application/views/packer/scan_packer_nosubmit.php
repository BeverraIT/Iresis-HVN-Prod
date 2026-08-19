<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <!-- title form -->
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Scan Packer (Direct Submit)</strong></h3>
      </div>

      <!-- search input by noresi -->
      <div class="panel-body">
        <form action="packer/save_nosubmit" method="post" class="form-horizontal" autocomplete="off" id="form-scan-packer">
          <div class="form-group">
            <div class="col-md-12">
              <div class="alert alert-info" role="alert">
                <strong>Mode Cepat:</strong> Scan barcode resi. Sistem akan otomatis memproses (Submit) tanpa konfirmasi.
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <div class="col-md-12">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  name="noresi"
                  id="noresi"
                  placeholder="Scan Nomor Resi disini..."
                  style="font-size: 20px; height: 50px;"
                />
                  <span class="input-group-btn" id="button-group">
                    <button class="btn btn-success" type="submit" style="height: 50px;">
                      <i class="fa fa-arrow-right"></i> Process
                    </button>
                  </span>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="panel-body">
          <div class="row">
                <div class="col-md-12 text-center">
                    <h3>Total Scan Hari Ini: <span id="total_scan_display"><?= $total_scan ?></span></h3>
                </div>
          </div>
          
          <div class="row" id="last-scan-container" style="display:none; margin-top: 20px;">
                <div class="col-md-12">
                    <div class="alert alert-success" id="last-scan-alert">
                        <strong>Berhasil!</strong> Resi <span id="last_resi"></span> telah diproses.
                    </div>
                </div>
          </div>
      </div>

      <div class="panel-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div><strong>Picker: <?= $nama_picker ?></strong></div>
                </div>
                <div class="col-md-4 text-center">
                    <div><strong>Komputer: <?= $komputer_packer ?></strong></div>
                </div>
                <div class="col-md-4 text-center">
                     <!-- Info lain -->
                </div>
            </div>
      </div>

      <!-- Success Auto Popup Modal -->
      <div id="successModal" class="custom-popup-overlay" style="display: none;">
        <div class="custom-popup-box success-popup">
          <div class="panel panel-default">
            <div class="panel-heading" style="background-color: #5cb85c; color: white;">
              <h3 class="panel-title">
                <i class="fa fa-check-circle"></i> <strong>Success!</strong>
              </h3>
            </div>
            <div class="panel-body">
              <p id="successMessage" style="font-size: 16px; margin: 20px 0;">Data berhasil disubmit!</p>
              <div class="progress" style="margin: 10px 0;">
                <div id="progressBar" class="progress-bar progress-bar-success" role="progressbar" style="width: 100%; transition: width 1s linear;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Auto Popup Modal -->
      <div id="errorModal" class="custom-popup-overlay" style="display: none;">
        <div class="custom-popup-box error-popup">
          <div class="panel panel-default">
            <div class="panel-heading" style="background-color: #d9534f; color: white;">
              <h3 class="panel-title">
                <i class="fa fa-times-circle"></i> <strong>Error!</strong>
              </h3>
            </div>
            <div class="panel-body">
              <p id="errorMessage" style="font-size: 16px; margin: 20px 0;">Terjadi kesalahan!</p>
              <div class="progress" style="margin: 10px 0;">
                <div id="errorProgressBar" class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%; transition: width 1s linear;"></div>
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
      // Focus input on load
      $('#noresi').focus();
      
      // Keep focus on input if user clicks away (unless they select text)
      // Optional: might be annoying if they try to click other things
      /*
      $(document).on('click', function(e) {
          if(!$(e.target).is('input, a, button')) {
             $('#noresi').focus();
          }
      });
      */

      $('#form-scan-packer').on('submit', function(e) {
          e.preventDefault();
          
          const noresi = $('#noresi').val().trim();
          
          if (!noresi) {
              return;
          }

          const form = this;
          const formData = new FormData(form);

          $.ajax({
              url: 'packer/save_nosubmit', // Call the new controller method
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: 'json',
              success: function(response) {
                  // Handle Success
                  if (response && (response.message === '<?= SUCCESS_SAVE_DATA ?>' || response.code === 201)) { // Check message constant or code
                       playAudio('audio-alert');
                       
                       // Show Success Modal
                       $('#successMessage').text("Resi " + noresi + " berhasil diproses.");
                       $('#successModal').fadeIn();
                       setTimeout(function() {
                         $('#successModal').fadeOut();
                       }, 1000); // 1 second for speed

                       // Update Last Scanned info
                       $('#last_resi').text(noresi);
                       $('#last-scan-container').show();

                       // Update total scan count
                       let currentTotal = parseInt($('#total_scan_display').text()) || 0;
                       $('#total_scan_display').text(currentTotal + 1);

                  } else if (response && response.code === 200) {
                       // Nothing to save (already scanned?)
                       playAudio('audio-error'); // Or warning
                       showError(response.message || "Tidak ada perubahan.");
                  } else {
                       // Other success but maybe warning?
                        playAudio('audio-alert');
                        $('#successMessage').text(response.message);
                        $('#successModal').fadeIn();
                        setTimeout(function() {$('#successModal').fadeOut();}, 1500);
                  }

                  // Clear input and focus
                  $('#noresi').val('').focus();
              },
              error: function(xhr, status, error) {
                  // Handle Error
                  playAudio('audio-fail');
                  
                  let errorMsg = "Terjadi kesalahan koneksi.";
                  try {
                      const resp = JSON.parse(xhr.responseText);
                      if(resp.message) errorMsg = resp.message;
                  } catch(e) {
                      console.log("Error parsing error response", e);
                  }
                  
                  showError(errorMsg);
                  
                  // Sort of clear input to allow retry, or keep it to let user correct it? 
                  // Usually better to select it so typing replaces it.
                  $('#noresi').select().focus();
              }
          });
      });

      function showError(msg) {
          $('#errorMessage').text(msg);
          $('#errorModal').fadeIn();
          setTimeout(function() {
            $('#errorModal').fadeOut();
          }, 3000);
      }

      function playAudio(id) {
          const audio = document.getElementById(id);
          if (audio) {
              audio.currentTime = 0;
              audio.play().catch(e => console.log("Audio play failed", e));
          }
      }
  });
</script>

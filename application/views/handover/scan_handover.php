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
        $("#p_latest_receipt_message").text("Nomor resi terakhir yang sudah di-scan keluar");

        const courrier_code = form.noresi.value.substring(0, 2);
        const courrier_code_2 = form.noresi.value.substring(0, 3);
        let audioPlayed = false;
        
        // Helper function untuk memutar audio dengan error handling
        function playAudio(audioId) {
          try {
            var audio = document.getElementById(audioId);
            if (audio) {
              audio.currentTime = 0; // Reset audio ke awal
              var playPromise = audio.play();
              if (playPromise !== undefined) {
                playPromise.catch(function(error) {
                  console.log('Audio play failed:', error);
                });
              }
              return true;
            }
          } catch (e) {
            console.log('Error playing audio:', e);
          }
          return false;
        }
        
        switch (courrier_code) {
          case 'JP':
          case 'JX':
          case 'JO':
          case 'JZ':
          case 'TJ':
          case '20':
            audioPlayed = playAudio('audio-jnt');
            break;

          case 'SP':
            audioPlayed = playAudio('audio-shopee');
            break;

          case 'JN':
          case 'LX':
          case 'NL':
            audioPlayed = playAudio('audio-lazada');
            break;

          case 'JT':
          case 'TL':
            audioPlayed = playAudio('audio-jne');
            break;

          case '00':
          case 'TK':
            if (courrier_code_2 === 'TKP') {
              audioPlayed = playAudio('audio-rekomen');
            } else {
              audioPlayed = playAudio('audio-sicepat');
            }
            break;

          case 'NJ':
            audioPlayed = playAudio('audio-ninja');
            break;

          case 'IN':
          case '24':
          case 'GT':
            audioPlayed = playAudio('audio-instant');
            break;
        }

        if (!audioPlayed) {
          playAudio('audio-alert');
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

        // Helper function untuk memutar audio dengan error handling
        function playAudioError(audioId) {
          try {
            var audio = document.getElementById(audioId);
            if (audio) {
              audio.currentTime = 0;
              var playPromise = audio.play();
              if (playPromise !== undefined) {
                playPromise.catch(function(error) {
                  console.log('Audio play failed:', error);
                });
              }
            }
          } catch (e) {
            console.log('Error playing audio:', e);
          }
        }
        
        if (error.status === 400) { // already handover
          playAudioError('audio-error');
        } else {
          playAudioError('audio-fail');
        }

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      });
      return false; // required to block normal submit since you used ajax
    }
  });
</script>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <!-- title form -->
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Scan Kurangan Picker</strong></h3>
      </div>

      <!-- search input by noresi -->
      <div class="panel-body">
        <form action="picker/kurangan-picker" method="post" class="form-horizontal" autocomplete="off">
          <div class="form-group">
            <div class="col-md-12">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  name="noresi"
                  id="noresi"
                  placeholder="Nomor resi"
                />
                  <span class="input-group-btn" id="button-group">
                    <button class="btn btn-default" type="submit">
                      <i class="fa fa-search"></i> Cari
                    </button>
                  </span>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- table untuk data resi -->
      <?php if (!empty($noresi)) : ?>
          <div class="col-md-12" id="result-info">
              <div id="button-footer">
                  <div class="text-left" style="margin-top: 10px;">
                      <button id="submit-kurangan" class="btn btn-success mb-2" style="margin-bottom: 10px;">
                          <strong>Submit</strong>
                      </button>
                      <button id="btn-reset" class="btn btn-warning mb-2" style="margin-bottom: 10px;">
                          <strong>Reset</strong>
                      </button>
                  </div>
              </div>
              <div class="row justify-content-center">
                  <div class="col-md-3">
                  </div>
                  <div class="col-md-2">
                      <input type="text"
                             class="form-control text-center"
                             readonly
                             value="No Resi:"
                             style="
                                background-color: transparent;
                                border: none;
                                color: black;
                                font-weight: bold;
                                cursor: text;"
                      />
                  </div>
                  <div class="col-md-4">
                      <input type="text"
                             name="noresi"
                             id="noresi-detail"
                             class="form-control text-center mx-auto"
                             readonly
                             value="<?= $noresi ?>"
                             style="
                                background-color: transparent;
                                color: black;
                                font-weight: bold;
                                cursor: text;"
                      />
                  </div>
                  <div class="col-md-3">
                  </div>
              </div>
          </div>
          <div class="panel-body" id="table-kurangan-picker">
            <table class="table table-striped datatable-kurangan-picker">
              <thead>
                <tr>
                  <th>#</th>
                  <th>SKU</th>
                  <th>Nama Barang</th>
                  <th>QTY</th>
                  <th>QTY Kurang</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="text-center">Loading data...</td>
                </tr>
              </tbody>
            </table>
          </div>
      <?php endif; ?>

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
              <p id="successMessage" style="font-size: 16px; margin: 20px 0;">Data kurangan berhasil disimpan!</p>
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
  var table;
  var noresi;

  $(document).ready(function() {
    // Focus pada input noresi saat halaman dimuat
    $('#noresi').focus();

    // Initialize DataTable jika ada noresi
    <?php if (!empty($noresi)) : ?>
    var noresiTbl = "<?= htmlspecialchars($noresi, ENT_QUOTES, 'UTF-8') ?>";
    
    table = $('.datatable-kurangan-picker').DataTable({
      'scrollX': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'order': [[0, 'asc']],
      'lengthMenu': [
        [10, 50, 100, 150, 200],
        [10, 50, 100, 150, 200]
      ],
      'columnDefs': [
        { width: '5%', targets: 0 },
        { width: '20%', targets: 1 },
        { width: '35%', targets: 2 },
        { width: '15%', targets: 3 },
        { width: '25%', targets: 4 },
        { className: 'text-center', targets: [0, 3, 4] }
      ],
      'ajax': {
        url: 'picker/get-kurangan-picker-data/' + noresiTbl,
        type: 'POST',
      },
    });
    <?php endif; ?>

    // Handle submit kurangan
    $('#submit-kurangan').on('click', function() {
      const noresi = $('#noresi-detail').val();

      if (!noresi || noresi.trim() === '') {
        $('#errorMessage').text("Nomor resi tidak boleh kosong!");
        $('#errorModal').fadeIn();
        setTimeout(function() {
          $('#errorModal').fadeOut();
        }, 2000);
        return;
      }

      // Collect data from table
      var items = [];
      $('.datatable-kurangan-picker tbody tr').each(function() {
        var $row = $(this);
        var idDetail = $row.find('.qty_kurang').data('id-detail');
        var qtyKurang = parseInt($row.find('.qty_kurang').val()) || 0;

        if (idDetail) {
          items.push({
            id_detail_resi: idDetail,
            status_kurangan: 'Ya', // Langsung set ke 'Ya'
            qty_kurang: qtyKurang
          });
        }
      });

      if (items.length === 0) {
        $('#errorMessage').text("Tidak ada data untuk disimpan!");
        $('#errorModal').fadeIn();
        setTimeout(function() {
          $('#errorModal').fadeOut();
        }, 2000);
        return;
      }

      // Disable button
      $('#submit-kurangan').prop('disabled', true).text('Menyimpan...');

      // Submit via AJAX
      $.ajax({
        url: 'picker/save-kurangan-picker',
        method: 'POST',
        data: {
          noresi: noresi,
          items: items
        },
        dataType: 'json',
        success: function(response) {
          if (response && response.code === 201) {
            $('#successMessage').text(response.message || 'Data kurangan berhasil disimpan!');
            $('#successModal').fadeIn();
            
            // Play success sound if available
            if (typeof document.getElementById('audio-alert') !== 'undefined') {
              document.getElementById('audio-alert').play();
            }

            setTimeout(function() {
              $('#successModal').fadeOut();
              // Reset form
              $('#result-info').hide();
              $('#table-kurangan-picker').hide();
              $('#noresi').val('').focus();
            }, 1500);
          } else {
            $('#errorMessage').text((response && response.message) || 'Gagal menyimpan data!');
            $('#errorModal').fadeIn();
            setTimeout(function() {
              $('#errorModal').fadeOut();
            }, 2000);
          }
        },
        error: function(xhr, status, error) {
          let errorText = 'Terjadi kesalahan saat memproses data!';
          
          try {
            let response = JSON.parse(xhr.responseText);
            if (response.message) {
              errorText = response.message;
            }
          } catch (e) {
            errorText = xhr.responseText || errorText;
          }

          $('#errorMessage').text(errorText);
          $('#errorModal').fadeIn();
          
          // Play error sound if available
          if (typeof document.getElementById('audio-fail') !== 'undefined') {
            document.getElementById('audio-fail').play();
          }

          setTimeout(function() {
            $('#errorModal').fadeOut();
          }, 2000);
        },
        complete: function() {
          $('#submit-kurangan').prop('disabled', false).text('Submit');
        }
      });
    });

    // Handle reset button
    const $resultInfo = $('#result-info');
    const $table = $('#table-kurangan-picker');
    const $footer = $('#button-footer');
    
    $('#btn-reset').on('click', function() {
      $resultInfo.hide();
      $table.hide();
      $footer.hide();
      $('#noresi').val('').focus();
      
      // Destroy DataTable if exists
      if (table) {
        table.destroy();
        table = null;
      }
    });
  });
</script>

<style>
  .custom-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
  }

  .custom-popup-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    text-align: center;
  }

  #result-info {
    display: <?= empty($noresi) ? 'none' : 'block' ?>;
  }

  /* Style untuk success modal */
  .success-popup {
    text-align: center;
  }

  .success-popup .panel-heading {
    background-color: #5cb85c;
    color: white;
  }

  .success-popup .progress {
    height: 5px;
    background-color: #f5f5f5;
  }

  .success-popup .progress-bar {
    background-color: #5cb85c;
  }

  /* Style untuk error modal */
  .error-popup {
    text-align: center;
  }

  .error-popup .panel-heading {
    background-color: #d9534f;
    color: white;
  }

  .error-popup .progress {
    height: 5px;
    background-color: #f5f5f5;
  }

  .error-popup .progress-bar {
    background-color: #d9534f;
  }

  table.dataTable tbody td {
    vertical-align: middle;
  }

  .qty_kurang {
    min-width: 120px;
  }
</style>

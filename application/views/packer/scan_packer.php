<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <!-- title form -->
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Scan Resi Packer</strong></h3>
      </div>

      <!-- search input by noresi -->
      <div class="panel-body">
        <form action="packer/scan-packer" method="post" class="form-horizontal" autocomplete="off">
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
                      <button id="submit-selected" class="btn btn-success mb-2" style="margin-bottom: 10px;">
                          <strong>Submit</strong>
                      </button>
                      <button id="btn-reset" class="btn btn-warning mb-2" style="margin-bottom: 10px;">
                          <strong>Reset</strong>
                      </button>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-4 text-center" style="border-right: 1px solid #ccc;">
                      <div><strong>Total Scan: <?= $total_scan ?></strong></div>
                  </div>
                  <div class="col-md-4 text-center" style="border-right: 1px solid #ccc;">
                      <div><strong>Picker: <?= $nama_picker ?></strong></div>
                  </div>
                  <div class="col-md-4 text-center">
                      <div><strong>Komputer: <?= $komputer_packer ?></strong></div>
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
          <div class="panel-body" id="table-scan-packer">
            <table class="table table-striped datatable-masalah-picker">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Foto</th>
                  <th>Nama Barang</th>
                  <th>SKU</th>
                  <th>Quantity</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="text-center">No details</td>
                </tr>
              </tbody>
            </table>
          </div>
      <?php endif; ?>

      <!-- modal pop up untuk masalah picker -->
      <div id="masalahPickerModal" class="custom-popup-overlay" style="display: none;">
        <div class="custom-popup-box col-md-6 center-block float-none">
          <form action="packer/masalah-picker-save" method="post" class="form-horizontal" id="from_scan_packer" autocomplete="off">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <strong>Submit Masalah Picker</strong>
                </h3>
              </div>

              <div class="panel-body">

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">Nama Picker</label>
                  <div class="col-md-8 col-xs-12">
                    <input type="text" id="modal_nama_picker" class="form-control" readonly />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">SKU</label>
                  <div class="col-md-8 col-xs-12">
                    <input type="text" id="modal_sku" class="form-control" readonly />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">Quantity</label>
                  <div class="col-md-8 col-xs-12">
                    <input type="text" id="modal_qty" class="form-control" readonly />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">No Rak</label>
                  <div class="col-md-8 col-xs-12">
                    <input type="text" id="modal_no_rak" class="form-control" readonly />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">Action</label>
                  <div class="col-md-8 col-xs-12">
                    <select name="type_masalah" id="type_masalah" class="form-control selectpicker" data-live-search="true">
                      <?php foreach ($list_type_masalah as $masalah) : ?>
                        <option value="<?= $masalah['id_typemasalah'] ?>"><?= $masalah['type_masalah'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">Quantity Bermasalah</label>
                  <div class="col-md-8 col-xs-12">
                    <div id="qty_bermasalah_container">
                      <select name="qty_bermasalah_level1" id="qty_bermasalah_level1" class="form-control qty-dropdown" style="margin-bottom: 5px;">
                        <option value="">-- Pilih Range --</option>
                        <option value="1">1</option>
                        <option value="10">10</option>
                        <option value="100">100</option>
                        <option value="1000">1000</option>
                      </select>
                      <select name="qty_bermasalah_level2" id="qty_bermasalah_level2" class="form-control qty-dropdown hidden" style="margin-bottom: 5px;">
                        <option value="">-- Pilih --</option>
                      </select>
                      <select name="qty_bermasalah_level3" id="qty_bermasalah_level3" class="form-control qty-dropdown hidden" style="margin-bottom: 5px;">
                        <option value="">-- Pilih --</option>
                      </select>
                      <select name="qty_bermasalah_level4" id="qty_bermasalah_level4" class="form-control qty-dropdown hidden" style="margin-bottom: 5px;">
                        <option value="">-- Pilih --</option>
                      </select>
                      <input type="hidden" name="qty_bermasalah" id="qty_bermasalah" value="1" required />
                    </div>
                    <small class="help-block text-muted">Pilih quantity bermasalah dari dropdown (1-1000)</small>
                  </div>
                </div>

                <div class="form-group hidden" id="div_sku_salah">
                  <label class="col-md-3 col-xs-12 control-label">SKU Salah</label>
                  <div class="col-md-8 col-xs-12">
                    <input type="text" name="sku_salah" id="sku_salah" class="form-control" />
                  </div>
                </div>

                <div class="hidden form-group">
                  <label class="col-md-3 col-xs-12 control-label">Noresi</label>
                  <div class="col-md-8 col-xs-12">
                    <input type="text" value="<?= $noresi ?>" name="noresi" id="noresi" class="form-control" />
                  </div>
                </div>

                <div class="tile tile-default" id="div_container_latest_receipt">
                  <span id="span_latest_receipt">-</span>
                  <p><small id="p_latest_receipt_message">Nomor SKU terakhir yang sudah di-scan</small></p>
                </div>

                <button type="submit" class="btn btn-info btn-submit-popup">Submit</button>
                <button type="reset" class="btn btn-primary">Reset</button>
                <button type="button" class="btn btn-default btn-cancel-popup">Cancel</button>

              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal buat preview foto -->
      <div id="fotoModal" class="custom-popup-overlay" style="display: none;">
        <div class="modal-content-custom">
          <button type="button" id="closeModal" class="close-button">&times;</button>
          <div class="modal-header">
            <h5 class="modal-title">Preview Foto</h5>
          </div>
          <div class="modal-body" style="text-align: center;">
            <img id="previewFoto" src="" style="max-width: 100%; max-height: 70vh;">
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
  var table;
  var idPrintResi;
  var noresi;
  var sku;
  var qty;

  $().ready(function() {

    // agar saat tampilkan halaman, kursor langsung muncul di form input noresi
    $(document).ready(function() {
      $('#noresi').focus();
    });

    // menampilkan data list sku by noresi yang di input
    var table = $(document).ready(function() {

      let noresiTbl = "<?= isset($noresi) ? htmlspecialchars($noresi, ENT_QUOTES, 'UTF-8') : '' ?>";

      table = $('.datatable-masalah-picker').DataTable({
        'scrollX': true,
        'pageLength': 10,
        'processing': true,
        'serverSide': true,
        'order': [
          [2, 'desc']
        ],
        'lengthMenu': [
          [10, 50, 100, 150, 200],
          [10, 50, 100, 150, 200]
        ],
        'columnDefs': [
          { width: '5%', targets: 0 },
          { width: '20%', targets: 1 },
          { width: '35%', targets: 2 },
          { width: '10%', targets: 3 },
          { width: '10%', targets: 4 },
          { width: '20%', targets: 5 },
          { className: 'text-center', targets: [0, 1, 2, 3, 4, 5] }
        ],
        'ajax': {
          url: 'packer/get-scan-packer-data/' + noresiTbl,
          type: 'POST',
        },
      });
    });

    // ====================== modal masalah picker ======================

    // tampilkan modal masalah picker
    $(document).on('click', '.saveMasalahPickera', function() {
      alert('Fitur ini belum bisa digunakan');
    });

    $(document).on('click', '.saveMasalahPicker', function() {

      event.preventDefault();

      $('#masalahPickerModal').show();

      hargajualpcs = $(this).data("hargajualpcs");
      idPrintResi = $(this).data("id");
      noresi = $(this).data("noresi");

      // Get values from data attributes
      const namapicker = $(this).data("nama-picker");
      sku = $(this).data("sku");
      qty = $(this).data("qty");
      const noRak = $(this).data("no-rak");

      $('#modal_nama_picker').val(namapicker);
      $('#modal_sku').val(sku);
      $('#modal_qty').val(qty);
      $('#modal_no_rak').val(noRak);

      // Reset cascading dropdowns
      $('#qty_bermasalah_level1').val('').trigger('change');
      $('#qty_bermasalah_level2').addClass('hidden').val('');
      $('#qty_bermasalah_level3').addClass('hidden').val('');
      $('#qty_bermasalah_level4').addClass('hidden').val('');
      $('#qty_bermasalah').val(1); // Default value

    });

    // tutup modal masalah picker
    $(document).on('click', '.btn-cancel-popup', function() {
      // Reset form saat cancel
      $("#from_scan_packer")[0].reset();
      $('#div_sku_salah').addClass('hidden');
      $('#qty_bermasalah_level1').val('').trigger('change');
      $('#qty_bermasalah_level2').addClass('hidden').val('');
      $('#qty_bermasalah_level3').addClass('hidden').val('');
      $('#qty_bermasalah_level4').addClass('hidden').val('');
      $('#qty_bermasalah').val(1);
      
      $('#masalahPickerModal').hide();
      $("#div_container_latest_receipt").removeClass("tile-danger").addClass("tile-default");
    });

    // ini kondisi ketika memilih tipe masalah pada modal masalah picker
    $('#type_masalah').on('change', function() {
      // const id_alasan = this.value;
      const id_typemasalah = $(this).val();

      // console.log("typemasalah", id_typemasalah);

      if (id_typemasalah === "4") { // condition for PAKET SALAH KERANGJANG OLEH KARYAWAN
        $('#div_sku_salah').removeClass('hidden');
      } else {
        $('#div_sku_salah').addClass('hidden');
      }
    });

    // Cascading dropdown untuk Quantity Bermasalah
    $('#qty_bermasalah_level1').on('change', function() {
      const level1Value = parseInt($(this).val()) || 0;
      const level2 = $('#qty_bermasalah_level2');
      const level3 = $('#qty_bermasalah_level3');
      const level4 = $('#qty_bermasalah_level4');
      
      // Reset semua level
      level2.addClass('hidden').val('');
      level3.addClass('hidden').val('');
      level4.addClass('hidden').val('');
      
      if (level1Value === 0) {
        $('#qty_bermasalah').val(1);
        return;
      }
      
      if (level1Value === 1) {
        // Jika pilih 1, langsung set nilai
        $('#qty_bermasalah').val(1);
        return;
      }
      
      // Generate options untuk level 2
      level2.removeClass('hidden').html('<option value="">-- Pilih --</option>');
      
      if (level1Value === 10) {
        // Untuk range 10: tampilkan 1, 2, 3, ..., 10
        for (let i = 1; i <= 10; i++) {
          level2.append(`<option value="${i}">${i}</option>`);
        }
      } else if (level1Value === 100) {
        // Untuk range 100: tampilkan 10, 20, 30, ..., 100
        for (let i = 10; i <= 100; i += 10) {
          level2.append(`<option value="${i}">${i}</option>`);
        }
      } else if (level1Value === 1000) {
        // Untuk range 1000: tampilkan 100, 200, 300, ..., 1000 (ratusan)
        for (let i = 100; i <= 1000; i += 100) {
          level2.append(`<option value="${i}">${i}</option>`);
        }
      }
    });

    $('#qty_bermasalah_level2').on('change', function() {
      const level1Value = parseInt($('#qty_bermasalah_level1').val()) || 0;
      const level2Value = parseInt($(this).val()) || 0;
      const level3 = $('#qty_bermasalah_level3');
      const level4 = $('#qty_bermasalah_level4');
      
      // Reset level 3 dan 4
      level3.addClass('hidden').val('');
      level4.addClass('hidden').val('');
      
      if (level2Value === 0) {
        $('#qty_bermasalah').val(1);
        return;
      }
      
      // Jika level1 adalah 10 dan level2 dipilih, langsung set nilai
      if (level1Value === 10) {
        $('#qty_bermasalah').val(level2Value);
        return;
      }
      
      // Generate options untuk level 3
      level3.removeClass('hidden').html('<option value="">-- Pilih --</option>');
      
      if (level1Value === 100) {
        // Jika level2 adalah 10, tampilkan 1-10
        // Jika level2 adalah 20, tampilkan 11-20, dst
        const start = level2Value === 10 ? 1 : level2Value - 9;
        const end = level2Value;
        for (let i = start; i <= end; i++) {
          level3.append(`<option value="${i}">${i}</option>`);
        }
        // Set default ke nilai terendah dari range
        $('#qty_bermasalah').val(start);
      } else if (level1Value === 1000) {
        // Jika level2 adalah 100, tampilkan 100 (hanya satu pilihan)
        // Jika level2 adalah 200, tampilkan 100, 110, 120, ..., 200 (puluhan)
        // Jika level2 adalah 300, tampilkan 100, 110, 120, ..., 300 (puluhan)
        const start = 100;
        const end = level2Value;
        for (let i = start; i <= end; i += 10) {
          level3.append(`<option value="${i}">${i}</option>`);
        }
        // Set default ke nilai terendah dari range
        $('#qty_bermasalah').val(start);
      }
    });

    $('#qty_bermasalah_level3').on('change', function() {
      const level1Value = parseInt($('#qty_bermasalah_level1').val()) || 0;
      const level3Value = parseInt($(this).val()) || 0;
      const level4 = $('#qty_bermasalah_level4');
      
      // Reset level 4
      level4.addClass('hidden').val('');
      
      if (level3Value === 0) {
        $('#qty_bermasalah').val(1);
        return;
      }
      
      // Jika level1 adalah 100, langsung set nilai (tidak perlu level 4)
      if (level1Value === 100) {
        $('#qty_bermasalah').val(level3Value);
        return;
      }
      
      // Jika level1 adalah 1000, generate level 4 (satuan)
      if (level1Value === 1000) {
        level4.removeClass('hidden').html('<option value="">-- Pilih --</option>');
        // Jika level3 adalah 100, tampilkan 100-109
        // Jika level3 adalah 110, tampilkan 111-120, dst
        // Jika level3 adalah 120, tampilkan 121-130, dst
        const start = level3Value === 100 ? 100 : level3Value - 9;
        const end = level3Value === 100 ? 109 : level3Value;
        for (let i = start; i <= end; i++) {
          level4.append(`<option value="${i}">${i}</option>`);
        }
        // Set default ke nilai terendah dari range
        $('#qty_bermasalah').val(start);
      }
    });

    $('#qty_bermasalah_level4').on('change', function() {
      const level4Value = parseInt($(this).val()) || 0;
      if (level4Value > 0) {
        $('#qty_bermasalah').val(level4Value);
      }
    });

    // aksi button submit dari modal masalah picker, simpan ke tblmasalahpicker per sku
    $(document).on('click', '.btn-submit-popup', function () {

      var jvalidate = $("#from_scan_packer").validate({
        ignore: [],
        rules: {
          type_masalah: {
            required: true,
          },
          qty_bermasalah: {
            required: true,
            min: 1,
            max: 1000,
            number: true
          },
          sku_salah: {
            required: function() {
              return $('#type_masalah').val() === '4';
            }
          }
        },
        messages: {
          type_masalah: {
            required: 'Tipe masalah harus dipilih'
          },
          qty_bermasalah: {
            required: 'Quantity bermasalah harus diisi',
            min: 'Quantity bermasalah minimal 1',
            max: 'Quantity bermasalah maksimal 1000',
            number: 'Quantity bermasalah harus berupa angka'
          },
          sku_salah: {
            required: 'SKU Salah harus diisi untuk tipe masalah ini'
          }
        },
        submitHandler: function(form) {
          var formData = new FormData(form);

          // formData.append("noresi", noresi);
          formData.append("sku", sku);
          formData.append("qty", qty);
          formData.append("id_printresi", idPrintResi);

          form.noresi.disabled = true;

          $.ajax({
            url: form.action,
            type: 'post',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
              // Parse response if it's a string
              if (typeof response === 'string') {
                try {
                  response = JSON.parse(response);
                } catch (e) {
                  console.error('Error parsing response:', e);
                }
              }

              if (response && response.message) {
                $("#span_latest_receipt").text(sku);
                $("#div_container_latest_receipt").removeClass("tile-danger").addClass("tile-success");

                if (document.getElementById('audio-alert')) {
                  document.getElementById('audio-alert').play();
                }

                // Show success message
                $('#successMessage').text(response.message);
                $('#successModal').fadeIn();
                setTimeout(function() {
                  $('#successModal').fadeOut();
                }, 2000);

                // Reset form
                $("#from_scan_packer")[0].reset();
                $('#div_sku_salah').addClass('hidden');
                // Reset cascading dropdowns
                $('#qty_bermasalah_level1').val('').trigger('change');
                $('#qty_bermasalah_level2').addClass('hidden').val('');
                $('#qty_bermasalah_level3').addClass('hidden').val('');
                $('#qty_bermasalah_level4').addClass('hidden').val('');
                $('#qty_bermasalah').val(1);

                // Close modal
                $('#masalahPickerModal').hide();

                // Reload table to reflect changes
                if (typeof table !== 'undefined' && table) {
                  table.ajax.reload(null, false);
                }
              }
            },
            error: function(xhr, status, error) {
              let errorMessage = 'Terjadi kesalahan saat menyimpan data';

              try {
                // Try to parse error response
                let responseText = xhr.responseText || '';
                let jsonMatch = responseText.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                  let errorResponse = JSON.parse(jsonMatch[0]);
                  if (errorResponse.message) {
                    errorMessage = errorResponse.message;
                  }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                  errorMessage = xhr.responseJSON.message;
                }
              } catch (e) {
                console.error('Error parsing error response:', e);
              }

              $("#span_latest_receipt").text(errorMessage);
              $("#div_container_latest_receipt").removeClass("tile-default tile-success").addClass("tile-danger");

              // Show error message
              $('#errorMessage').text(errorMessage);
              $('#errorModal').fadeIn();
              setTimeout(function() {
                $('#errorModal').fadeOut();
              }, 3000);

              if (document.getElementById('audio-fail')) {
                document.getElementById('audio-fail').play();
              }
            }
          });
          return false; // required to block normal submit since you used ajax
        }
      });

    });

    // ====================== modal masalah picker ======================

    // ====================== modal foto ======================

    // event ketika klik gambar foto yang ada di tabel
    $(document).on('click', '.foto-preview', function() {
      var fotoUrl = $(this).data('foto');

        if (fotoUrl && fotoUrl.trim() !== '') {
            $('#previewFoto').attr('src', fotoUrl);   // ✅ Set the image source
            $('#fotoModal').fadeIn();                 // ✅ Show the modal
        } else {
            alert('Foto tidak tersedia!');
        }
    });

    // event ketika klik button lihat foto (for backward compatibility)
    $(document).on('click', '.lihat-foto', function() {
      var fotoUrl = $(this).data('foto');

        if (fotoUrl && fotoUrl.trim() !== '') {
            $('#previewFoto').attr('src', fotoUrl);   // ✅ Set the image source
            $('#fotoModal').fadeIn();                 // ✅ Show the modal
        } else {
            alert('Foto tidak tersedia!');
        }
    });

    // Tombol close
    $(document).on('click', '#closeModal', function() {
      $('#fotoModal').hide();
    });

    // ====================== modal foto ======================

    // Select/Deselect semua checkbox saat klik #select-all
    $(document).on('change', '#select-all', function() {
      var isChecked = $(this).is(':checked');
      $('.row-select').prop('checked', isChecked);
    });

    // Kalau ada 1 checkbox di-uncheck manual, #select-all juga ikut uncheck
    $(document).on('change', '.row-select', function() {
      if (!$(this).is(':checked')) {
        $('#select-all').prop('checked', false);
      } else {
        // Kalau semua checkbox ke-check, otomatis select-all ke-check juga
        if ($('.row-select:checked').length === $('.row-select').length) {
          $('#select-all').prop('checked', true);
        }
      }
    });

    // Handle submit selected, simpan ke tblpacking
    $('#submit-selected').on('click', function() {
      const noresi = $('#noresi-detail').val();

      if (!noresi || noresi.trim() === '') {
          // Show error popup for empty noresi
          $('#errorMessage').text("Nomor resi tidak boleh kosong!");
          $('#errorModal').fadeIn();
          setTimeout(function() {
            $('#errorModal').fadeOut();
          }, 1000);
          return;
      }

      // Kirim pakai Ajax (status_performa otomatis dari session di controller)
      $.ajax({
        url: 'packer/save-packer', // Ganti sesuai route kamu
        method: 'POST',
        data: { 
          noresi: noresi
        },
        success: function(response) {
          // alert("Data berhasil disubmit!");
          $('#successModal').fadeIn();

          setTimeout(function() {
            $('#successModal').fadeOut();
          }, 1000);

            $resultInfo.hide();
            $table.hide();
            $footer.hide();
            $('#noresi').focus();
        },
        error: function(xhr, status, error) {
          // Show error popup instead of alert
          let errorText = 'Terjadi kesalahan saat memproses data!';

          // Try to parse JSON response and extract message
          try {
            let response = JSON.parse(xhr.responseText);
            if (response.message) {
              errorText = response.message;
            }
          } catch (e) {
            // If not JSON, use responseText directly or default message
            errorText = xhr.responseText || errorText;
          }

          $('#errorMessage').text(errorText);
          $('#errorModal').fadeIn();

          setTimeout(function() {
            $('#errorModal').fadeOut();
          }, 1000);
        }
      });
    });

    const $resultInfo = $('#result-info');
    const $table = $('#table-scan-packer');
    const $footer = $('#button-footer');
    $('#btn-reset').on('click', function () {
        $resultInfo.hide();
        $table.hide();
        $footer.hide();
        $('#noresi').focus();
    });
  })

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

  .custom-popup-buttons {
    margin-top: 15px;
  }

  .custom-popup-buttons button {
    margin: 0 5px;
  }

  table.dataTable tbody td {
    vertical-align: middle;
    padding-top: 30px;
    padding-bottom: 30px;
  }

  .modal-content-custom {
    position: relative;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
  }

  /* .modal-content-custom {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    } */

    .close-button {
      position: absolute;
      top: -15px;
      right: -2px;
      font-size: 34px;
      background: none;
      border: none;
      color: #333;
      cursor: pointer;
      z-index: 10;
  }

  .close-button:hover {
    color: red;
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
</style>

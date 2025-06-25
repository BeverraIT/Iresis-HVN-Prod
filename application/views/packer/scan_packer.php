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
                  value="<?= isset($noresi) ? htmlspecialchars($noresi) : '' ?>"
                  <?= isset($noresi) && $noresi !== '' ? 'disabled' : '' ?>
                />
                  <span class="input-group-btn" id="button-group">
                    <?php if (isset($noresi) && $noresi !== ''): ?>
                        <button type="button" id="btn-reset" class="btn btn-warning">
                          <i class="fa fa-refresh"></i> Reset
                        </button>
                    <?php else: ?>
                        <button class="btn btn-default" type="submit">
                          <i class="fa fa-search"></i> Cari
                        </button>
                    <?php endif; ?>
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
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-4 text-center" style="border-right: 1px solid #ccc;">
                      <div><strong><?= $total_scan ?></strong></div>
                  </div>
                  <div class="col-md-4 text-center" style="border-right: 1px solid #ccc;">
                      <div><strong><?= $nama_picker ?></strong></div>
                  </div>
                  <div class="col-md-4 text-center">
                      <div><strong><?= $komputer_picker ?></strong></div>
                  </div>
              </div>
          </div>
      <div class="panel-body" id="table-scan-packer">
        <table class="table table-striped datatable-masalah-picker">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Barang</th>
              <th>SKU</th>
              <th>Quantity</th>
              <th>Foto</th>
              <th>Aksi</th>
            </tr>
          </thead>
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
                  <strong>Save Masalah Picker</strong>
                </h3>
              </div>

              <div class="panel-body">

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
                    <select name="qty_bermasalah" id="qty_bermasalah" class="form-control select" data-live-search="true">
                      <?php for ($number = 1; $number <= 30; $number++) : ?>
                        <option value=<?= $number ?>>
                          <?= $number ?>
                        </option>
                      <?php endfor; ?>
                    </select>
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

              </div>

              <div class="panel-footer">
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

    </div>
  </div>
</div>

<script type="text/javascript">
  var table;
  var hargajualpcs;
  var idPrintResi;
  var noresi;
  var sku;
  var qty;
  var row;
  var sku_lihat_foto;

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
          { width: '45%', targets: 1 },
          { width: '10%', targets: 2 },
          { width: '10%', targets: 3 },
          { width: '10%', targets: 4 },
          { width: '10%', targets: 5 },
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
    $(document).on('click', '.saveMasalahPicker', function() {

      event.preventDefault();

      $('#masalahPickerModal').show();

      hargajualpcs = $(this).data("hargajualpcs");
      idPrintResi = $(this).data("id");
      noresi = $(this).data("noresi");
      sku = $(this).data("sku");
      qty = $(this).data("qty");
      row = $(this).closest("tr");

      // supaya bisa cari dengan ketik
      $('.selectpicker').selectpicker('render');

    });

    // tutup modal masalah picker
    $(document).on('click', '.btn-cancel-popup', function() {
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

    // aksi button submit dari modal masalah picker, simpan ke tblmasalahpicker per sku
    $(document).on('click', '.btn-submit-popup', function () {

      var jvalidate = $("#from_scan_packer").validate({
        ignore: [],
        rules: {
          type_masalah: {
            required: true,
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
            success: function(data) {},
            error: function(data) {},
          }).done(function(response) {
            $("#span_latest_receipt").text(sku);

            document.getElementById('audio-alert').play();

            $('#masalahPickerModal').hide();
            // $('.custom-popup-overlay').fadeOut();
            // table.ajax.reload(null, false);
            // row.fadeOut(500, function() { $(this).remove(); });

          }).fail(function(response) {

            $("#div_container_latest_receipt").removeClass("tile-default").addClass("tile-danger");

            document.getElementById('audio-fail').play();
          });
          return false; // required to block normal submit since you used ajax
        }
      });

    });

    // ====================== modal masalah picker ======================

    // ====================== modal foto ======================

    // event ketika klik button lihat foto
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
      const noresi = $('#noresi').val();

      if (!noresi || noresi.trim() === '') {
          alert("Nomor resi tidak boleh kosong!");
          return;
      }

      // Kirim pakai Ajax
      $.ajax({
        url: 'packer/save-packer', // Ganti sesuai route kamu
        method: 'POST',
        data: { noresi: noresi },
        success: function(response) {
          alert("Data berhasil disubmit!");
          table.ajax.reload(null, false); // reload data tanpa reset page
        },
        error: function(xhr, status, error) {
          alert(xhr.responseText);
        }
      });
    });

    const $noresi = $('#noresi');
    const $buttonGroup = $('#button-group');
    const $resultInfo = $('#result-info');
    const $table = $('#table-scan-packer');
    const $footer = $('#button-footer');
    $('#btn-reset').on('click', function () {
        // Enable the input
        $noresi.prop('disabled', false);
        // Remove the hidden input if it exists
        $('#hidden-noresi').remove()
        // Change button to "Cari"
        updateToCariButton();
        // Hide the result info section
        $resultInfo.hide();
        $table.hide();
        $footer.hide();
    });

    // Optional: also check if the field becomes enabled manually
    $noresi.on('input propertychange change', function () {
        if (!$noresi.prop('disabled')) {
            updateToCariButton();
        }
    });

    function updateToCariButton() {
        $buttonGroup.html(`
      <button class="btn btn-default" type="submit">
        <i class="fa fa-search"></i> Cari
      </button>
    `);
    }
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
</style>

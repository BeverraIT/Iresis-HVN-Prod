<style>
  .complain-form .form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .complain-form .form-group {
    margin-bottom: 15px;
  }

  @media (max-width: 1400px) {
    .complain-form .col-md-6 {
      width: 100% !important;
      float: none !important;
    }
  }
  
  /* Tab styling improvements */
  .nav-tabs > li > a {
    font-weight: 600;
    font-size: 14px;
    padding: 10px 20px;
    transition: all 0.3s ease;
  }
  
  .nav-tabs > li.active > a,
  .nav-tabs > li.active > a:focus,
  .nav-tabs > li.active > a:hover {
    border-top-color: #565249;
    background-color: #fff;
    color: #333;
  }
  
  /* Panel body padding */
  .panel-body.tab-content {
    padding: 20px;
    background-color: #fff;
  }
  
  
  /* Form styling improvements */
  .complain-form {
    padding: 10px 0;
  }
  
  .complain-form .btn {
    margin-right: 10px;
    border-radius: 4px;
  }

  /* Sync button styling */
  .btn-sync-resi {
    min-width: 40px;
  }

  .btn-sync-resi i {
    transition: transform 0.3s ease;
  }

  .btn-sync-resi:hover i {
    transform: rotate(180deg);
  }

  .btn-sync-resi:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
</style>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default tabs">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Proses Retur Complain</strong></h3>
      </div>

      <ul class="nav nav-tabs nav-justified" role="tablist">
        <li class="active"><a href="#tab_refund" role="tab" data-toggle="tab">Refund Dana</a></li>
        <li><a href="#tab_replacement" role="tab" data-toggle="tab">Pergantian Barang</a></li>
      </ul>

      <div class="panel-body tab-content">
          <!-- Refund Dana -->
          <div class="tab-pane active" id="tab_refund">
            <form id="form-refund-complain" class="complain-form" autocomplete="off" style="padding: 10px 0;">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>No. Resi</label>
                    <div class="input-group">
                      <input type="text" name="noresi" id="noresi_refund" class="form-control" placeholder="Masukkan nomor resi" required />
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-sync-resi" data-form="refund" title="Sync data resi">
                          <i class="fa fa-refresh"></i>
                        </button>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nama Customer</label>
                    <input type="text" name="customer_name" id="customer_name_refund" class="form-control" placeholder="Nama customer" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Marketplace</label>
                    <select name="marketplace" id="marketplace_refund" class="form-control select">
                      <option value="">- Pilih Marketplace -</option>
                      <?php foreach ($list_marketplace as $marketplace) : ?>
                        <option value="<?= $marketplace['nama_marketplace'] ?>"><?= $marketplace['nama_marketplace'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>No. Pesanan</label>
                    <input type="text" name="no_pesanan" id="no_pesanan_refund" class="form-control" placeholder="Nomor pesanan" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>SKU (Asli)</label>
                    <input type="text" name="sku" id="sku_refund" class="form-control" placeholder="SKU barang asli" readonly />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Qty (Asli)</label>
                    <input type="number" name="qty" id="qty_refund" class="form-control" placeholder="Jumlah barang asli" readonly />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nominal Refund</label>
                    <input type="number" name="refund_amount" class="form-control" placeholder="Contoh: 150000" min="0" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bank / E-Wallet</label>
                    <input type="text" name="refund_bank" class="form-control" placeholder="Nama bank atau e-wallet" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>No. Rekening / ID</label>
                    <input type="text" name="refund_account" class="form-control" placeholder="Nomor rekening / ID" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Refund</button>
                  <button type="reset" class="btn btn-default">Reset</button>
                </div>
              </div>
            </form>
          </div>

          <!-- Pergantian Barang -->
          <div class="tab-pane" id="tab_replacement">
            <form id="form-replacement-complain" class="complain-form" autocomplete="off" style="padding: 10px 0;">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>No. Resi</label>
                    <div class="input-group">
                      <input type="text" name="noresi" id="noresi_replacement" class="form-control" placeholder="Masukkan nomor resi" required />
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-sync-resi" data-form="replacement" title="Sync data resi">
                          <i class="fa fa-refresh"></i>
                        </button>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nama Customer</label>
                    <input type="text" name="customer_name" id="customer_name_replacement" class="form-control" placeholder="Nama customer" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Marketplace</label>
                    <select name="marketplace" id="marketplace_replacement" class="form-control select">
                      <option value="">- Pilih Marketplace -</option>
                      <?php foreach ($list_marketplace as $marketplace) : ?>
                        <option value="<?= $marketplace['nama_marketplace'] ?>"><?= $marketplace['nama_marketplace'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>No. Pesanan</label>
                    <input type="text" name="no_pesanan" id="no_pesanan_replacement" class="form-control" placeholder="Nomor pesanan" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>SKU (Asli)</label>
                    <input type="text" name="sku" id="sku_replacement" class="form-control" placeholder="SKU barang asli" readonly />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Qty (Asli)</label>
                    <input type="number" name="qty" id="qty_replacement" class="form-control" placeholder="Jumlah barang asli" readonly />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>SKU Pengganti</label>
                    <input type="text" name="replacement_sku" class="form-control" placeholder="SKU barang pengganti" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Qty Pengganti</label>
                    <input type="number" name="replacement_qty" class="form-control" min="1" placeholder="Jumlah barang pengganti" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Pergantian</button>
                  <button type="reset" class="btn btn-default">Reset</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  // Sync receipt info when sync button is clicked
  function syncReceiptInfo(formType) {
    var $noresiInput, $customerInput, $marketplaceSelect, $noPesananInput, $skuInput, $qtyInput, $syncBtn;
    
    if (formType === 'refund') {
      $noresiInput = $('#noresi_refund');
      $customerInput = $('#customer_name_refund');
      $marketplaceSelect = $('#marketplace_refund');
      $noPesananInput = $('#no_pesanan_refund');
      $skuInput = $('#sku_refund');
      $qtyInput = $('#qty_refund');
      $syncBtn = $('#form-refund-complain .btn-sync-resi');
    } else if (formType === 'replacement') {
      $noresiInput = $('#noresi_replacement');
      $customerInput = $('#customer_name_replacement');
      $marketplaceSelect = $('#marketplace_replacement');
      $noPesananInput = $('#no_pesanan_replacement');
      $skuInput = $('#sku_replacement');
      $qtyInput = $('#qty_replacement');
      $syncBtn = $('#form-replacement-complain .btn-sync-resi');
    } else {
      return;
    }
    
    var noresi = $noresiInput.val().trim().toUpperCase();
    
    if (!noresi || noresi.length < 3) {
      showNoty('Masukkan nomor resi terlebih dahulu', 'warning');
      return;
    }

    // Disable button and show loading
    $syncBtn.prop('disabled', true);
    $syncBtn.find('i').removeClass('fa-refresh').addClass('fa-spinner fa-spin');

    $.ajax({
      url: 'retur/get-receipt-info',
      method: 'POST',
      data: { noresi: noresi },
      dataType: 'json',
      success: function(response) {
        if (response.success && response.data) {
          // Fill customer name
          if (response.data.customer_name) {
            $customerInput.val(response.data.customer_name);
          }
          
          // Fill marketplace
          if (response.data.marketplace) {
            $marketplaceSelect.val(response.data.marketplace).trigger('change');
          }
          
          // Fill no pesanan
          if (response.data.no_pesanan) {
            $noPesananInput.val(response.data.no_pesanan);
          }
          
          // Fill SKU and QTY (for both refund and replacement forms)
          if ($skuInput && $qtyInput) {
            if (response.data.sku) {
              $skuInput.val(response.data.sku);
            }
            if (response.data.qty) {
              $qtyInput.val(response.data.qty);
            }
          }
          
          showNoty('Data resi berhasil di-sync', 'success');
        } else {
          showNoty(response.message || 'Data resi tidak ditemukan', 'warning');
        }
      },
      error: function(xhr) {
        var message = 'Terjadi kesalahan saat sync data';
        try {
          var response = xhr.responseJSON || JSON.parse(xhr.responseText);
          if (response && response.message) {
            message = response.message;
          }
        } catch (e) {
          // Use default message
        }
        showNoty(message, 'error');
      },
      complete: function() {
        // Re-enable button and restore icon
        $syncBtn.prop('disabled', false);
        $syncBtn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-refresh');
      }
    });
  }

  // Setup sync button click handlers
  $('.btn-sync-resi').on('click', function() {
    var formType = $(this).data('form');
    syncReceiptInfo(formType);
  });

  $('#form-refund-complain').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation(); // Prevent plugins.js formSubmit handler
    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');

    $btn.prop('disabled', true).text('Menyimpan...');

    $.ajax({
      url: 'retur/save-refund-complain',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'text', // Changed to text to handle parsing manually
      success: function(responseText) {
        try {
          // Try to extract JSON from response (in case there's HTML before JSON)
          var jsonMatch = responseText.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            responseText = jsonMatch[0];
          }
          
          var response = JSON.parse(responseText);
          
          if (response && response.message) {
            showNoty(response.message, 'success');
            $form.trigger('reset');
          }
        } catch (e) {
          console.error('JSON parse error:', e);
          console.error('Response text:', responseText);
          showNoty('Terjadi kesalahan saat memproses response', 'error');
        }
      },
      error: function(xhr) {
        var response = {};
        try {
          var responseText = xhr.responseText || '';
          var jsonMatch = responseText.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            response = JSON.parse(jsonMatch[0]);
          } else if (xhr.responseJSON) {
            response = xhr.responseJSON;
          }
        } catch (e) {
          console.error('Error parsing error response:', e);
        }
        showNoty(response.message || 'Terjadi kesalahan', 'error');
      },
      complete: function() {
        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Refund');
      }
    });
  });

  $('#form-replacement-complain').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation(); // Prevent plugins.js formSubmit handler
    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');

    $btn.prop('disabled', true).text('Menyimpan...');

    $.ajax({
      url: 'retur/save-replacement-complain',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'text', // Changed to text to handle parsing manually
      success: function(responseText) {
        try {
          // Try to extract JSON from response (in case there's HTML before JSON)
          var jsonMatch = responseText.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            responseText = jsonMatch[0];
          }
          
          var response = JSON.parse(responseText);
          
          if (response && response.message) {
            showNoty(response.message, 'success');
            $form.trigger('reset');
          }
        } catch (e) {
          console.error('JSON parse error:', e);
          console.error('Response text:', responseText);
          showNoty('Terjadi kesalahan saat memproses response', 'error');
        }
      },
      error: function(xhr) {
        var response = {};
        try {
          var responseText = xhr.responseText || '';
          var jsonMatch = responseText.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            response = JSON.parse(jsonMatch[0]);
          } else if (xhr.responseJSON) {
            response = xhr.responseJSON;
          }
        } catch (e) {
          console.error('Error parsing error response:', e);
        }
        showNoty(response.message || 'Terjadi kesalahan', 'error');
      },
      complete: function() {
        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Pergantian');
      }
    });
  });

  // Flag to prevent multiple simultaneous requests
  var isUpdatingStatus = false;
  
  $(document).off('change', '.complain-status-select').on('change', '.complain-status-select', function() {
    // Prevent multiple simultaneous requests
    if (isUpdatingStatus) {
      return;
    }
    
    var id = $(this).data('id');
    var status = $(this).val();
    var $select = $(this);
    var oldStatus = $select.data('old-status');

    if (!id || !status) {
      return;
    }

    // Check if status actually changed
    if (oldStatus === status) {
      return;
    }

    // Store old status to prevent duplicate requests
    $select.data('old-status', status);
    isUpdatingStatus = true;
    $select.prop('disabled', true);

    $.ajax({
      url: 'retur/update-complain-status',
      method: 'POST',
      data: {
        id: id,
        status: status
      },
      dataType: 'text', // Changed to text to handle parsing manually
      success: function(responseText) {
        try {
          // Try to extract JSON from response (in case there's HTML before JSON)
          var jsonMatch = responseText.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            responseText = jsonMatch[0];
          }
          
          var response = JSON.parse(responseText);
          
          if (response && response.message) {
            showNoty(response.message, 'success');
          }
        } catch (e) {
          console.error('JSON parse error:', e);
          console.error('Response text:', responseText);
          showNoty('Terjadi kesalahan saat memproses response', 'error');
          // Reset select to old value on error
          $select.val(oldStatus);
        }
      },
      error: function(xhr) {
        var response = {};
        try {
          var responseText = xhr.responseText || '';
          var jsonMatch = responseText.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            response = JSON.parse(jsonMatch[0]);
          } else if (xhr.responseJSON) {
            response = xhr.responseJSON;
          }
        } catch (e) {
          console.error('Error parsing error response:', e);
        }
        showNoty(response.message || 'Terjadi kesalahan', 'error');
        // Reset select to old value on error
        $select.val(oldStatus);
        // Don't reload on error to prevent loop
      },
      complete: function() {
        isUpdatingStatus = false;
        $select.prop('disabled', false);
      }
    });
  });

  function showNoty(message, type) {
    noty({
      text: message,
      layout: 'topRight',
      type: type || 'information',
      timeout: 3000
    });
  }
</script>


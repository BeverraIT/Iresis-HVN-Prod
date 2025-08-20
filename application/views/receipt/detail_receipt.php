<div class="row">
  <div class="col-md-8 center-block float-none">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Cari Detail Resi</strong></h3>
      </div>

      <div class="panel-body">

        <form action="receipt/detail-receipt" method="post" class="form-horizontal" autocomplete="off">
          <div class="form-group">
            <div class="col-md-12">
              <div class="input-group">
                <input type="text" class="form-control" name="noresi" id="noresi" placeholder="Nomor resi" />
                <span class="input-group-btn">
                  <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
                </span>
              </div>
            </div>
          </div>

          <div class="tile tile-default mb-0" id="div_container_latest_receipt">
            <span id="span_latest_receipt"><?= !empty($receipt) ? $receipt['noresi'] : '-' ?></span>
          </div>
        </form>

      </div>

      <div class="panel-footer">
        <?php if (!empty($receipt)) : ?>
          <div class="row">
            <div class="col-md-6">
              <table class="table table-striped">
                <tbody>
                  <tr>
                      <td align="right">Nomor Pesanan :</td>
                      <td><strong>
                        <?php if (!empty($receipt_items)) : ?>
                          <?php
                          $unique_orders = array_unique(array_column($receipt_items, 'no_pesanan'));
                          echo implode(', ', $unique_orders);
                          ?>
                        <?php else : ?>
                          -
                        <?php endif; ?>
                      </strong></td>
                  </tr>
                  <tr>
                      <td align="right">Status Pesanan :</td>
                      <td><strong><?= $receipt['status_pesanan'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">SKU :</td>
                      <td>
                        <?php if (!empty($receipt_items)) : ?>
                          <table class="table table-condensed table-bordered" style="margin: 0;">
                            <thead>
                              <tr>
                                <th>SKU</th>
                                <th>Kuantitas</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($receipt_items as $item) : ?>
                              <tr>
                                <td><strong><?= $item['sku'] ?></strong></td>
                                <td><strong><?= $item['jumlah'] ?></strong></td>
                              </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        <?php else : ?>
                          <strong>-</strong>
                        <?php endif; ?>
                      </td>
                  </tr>
                  <tr>
                      <td align="right">Marketplace :</td>
                      <td><strong><?= $receipt['nama_marketplace'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Kurir / Ekspedisi :</td>
                      <td><strong><?= $receipt['nama_kurir'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Tanggal Proses Resi :</td>
                      <td><strong><?= $receipt['tanggal_printresi'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Tanggal Batas Kirim :</td>
                      <td><strong><?= $receipt['tanggal_bataskirim'] ?></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-striped">
                <tbody>
                  <tr>
                      <td align="right">Nama Picker :</td>
                      <td><strong><?= $receipt['picker'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Tanggal Picker :</td>
                      <td><strong><?= $receipt['tanggal_resiambilbarang'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Nama Packer :</td>
                      <td><strong><?= $receipt['packer'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Tanggal Packing :</td>
                      <td><strong><?= $receipt['tanggal_packing'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Nomor Komputer Packer :</td>
                      <td><strong><?= $receipt['komputer_packer_no'] ?></strong></td>
                  </tr>
                  <tr>
                    <td align="right">Tanggal Serah :</td>
                    <td><strong><?= $receipt['tanggal_resikeluar'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Tanggal Scan Jadi :</td>
                      <td><strong><?= $receipt['tanggal_cetak'] ?></strong></td>
                  </tr>
                  <tr>
                      <td align="right">Tanggal Retur :</td>
                      <td><strong><?= $receipt['tanggal_retur'] ?></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        <?php else : ?>
          <p class="text-center"><small><i>Resi tidak ditemukan</i></small></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $("#noresi").focus();
</script>
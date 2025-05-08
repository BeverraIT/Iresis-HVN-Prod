<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Resync SKU</h3>
      </div>
      <div class="panel-body">
        <?php if ($resync_status['paramvalue1'] == '1') : ?>
          <div class="alert alert-warning" role="alert">
            <strong>Perhatian!</strong> <?= MESSAGE_RESYNC_STATUS_RUNNING ?>
          </div>
        <?php else : ?>
          <form action="sku/do_resync" class="form-horizontal" method="post" autocomplete="off">
            <div class="form-group">
              <!-- <label class="col-md-3 col-xs-12 control-label"></label> -->
              <div class="col-md-4 col-xs-12">
                <label class="check"><input type="checkbox" name="sync_stock" value="1" class="icheckbox" /> Sync stock juga dari Jubelio?</label>
              </div>
            </div>

            <div class="form-group">
              <!-- <label class="col-md-3 col-xs-12 control-label"></label> -->
              <div class="col-md-4 col-xs-12">
                <button type="submit" class="btn btn-info"><i class="fa fa-refresh"></i> Sync ulang SKU Jubelio</button>
              </div>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
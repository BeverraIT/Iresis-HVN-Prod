<div class="row">
    <div class="col-md-12">
        <form action="logaktivitas/save" class="form-horizontal" method="post">
            <input type="hidden" name="id" value="<?= empty($logaktivitas) ? '' : $logaktivitas['id'] ?>" />

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong><?= $action ?></strong></h3>
                </div>

                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Nama Pegawai</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" disabled="disabled" class="form-control" value="<?= empty($logaktivitas) ? $nama_anda : $logaktivitas['username'] ?>"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Kuantiti dan cek kan yang diturunkan</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="qty" class="form-control" value="<?= empty($logaktivitas) ? '' : json_decode($logaktivitas['data'],true)['qty']??'' ?>" require />
                        </div>
                    </div>
                    <?php if(!empty($logaktivitas["updated"])){?>
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Pengubah Terakhir</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" disabled="disabled" class="form-control" value="<?= empty($logaktivitas) ? '' : $logaktivitas['username'] ?>"/>
                        </div>
                    </div>
                    <?php }?>
                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-info">Submit</button>
                    <button type="reset" class="btn btn-primary">Reset</button>

                    <a href="logaktivitas" type="reset" class="btn btn-default pull-right link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
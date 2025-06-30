<div class="row">
    <div class="col-md-12">
        <form action="marketplace/save" class="form-horizontal" method="post">
            <input type="hidden" name="id_marketplace" value="<?= empty($marketplace) ? '0' : $marketplace['id_marketplace'] ?>" />

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong><?= $action ?></strong></h3>
                </div>

                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Nama marketplace</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="nama_marketplace" class="form-control" value="<?= empty($marketplace) ? '' : $marketplace['nama_marketplace'] ?>" require />
                        </div>
                    </div>

                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-info">Submit</button>
                    <button type="reset" class="btn btn-primary">Reset</button>

                    <a href="marketplace" type="reset" class="btn btn-default pull-right link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
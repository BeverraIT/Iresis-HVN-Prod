<div class="row">
    <div class="col-md-12">
        <form action="employee/save" class="form-horizontal" method="post">
            <input type="hidden" name="kode_pegawai" value="<?= empty($employee) ? '0' : $employee['kode_pegawai'] ?>" />

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong><?= $action ?></strong></h3>
                </div>

                <div class="panel-body">

                    <?php if (!empty($employee)) : ?>
                        <div class="form-group">
                            <label class="col-md-3 col-xs-12 control-label">Kode</label>
                            <div class="col-md-4 col-xs-12">
                                <input type="text" name="kode_pegawai" class="form-control" value="<?= empty($employee) ? '' : $employee['kode_pegawai'] ?>" readonly />
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Nama</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="nama_pegawai" class="form-control" value="<?= empty($employee) ? '' : $employee['nama_pegawai'] ?>" require />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Status</label>
                        <div class="col-md-4 col-xs-12">
                            <select name="status_aktif" class="form-control select">
                                <option></option>
                                <?php foreach ($list_status as $status) : ?>
                                    <option value="<?= $status ?>" <?= !empty($employee) && ($employee['status_aktif'] == $status) ? 'selected' : '' ?>><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-info">Submit</button>
                    <button type="reset" class="btn btn-primary">Reset</button>

                    <a href="employee" type="reset" class="btn btn-default pull-right link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <form action="save-user" class="form-horizontal" method="post">
            <input type="hidden" name="id_user" value="<?= empty($user) ? '0' : $user['id_user'] ?>" />

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong><?= $action ?></strong></h3>
                </div>

                <div class="panel-body">

                    <h4>Informasi Pengguna</h4>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Username</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="username" class="form-control" value="<?= empty($user) ? '' : $user['username'] ?>" require />
                        </div>
                    </div>

                    <?php if (empty($user)) : ?>
                        <div class="form-group">
                            <label class="col-md-3 col-xs-12 control-label">Password</label>
                            <div class="col-md-4 col-xs-12">
                                <div class="input-group">
                                    <input type="text" name="password" class="form-control" />
                                    <span class="input-group-btn">
                                        <button id="btn_generate_password" type="button" class="btn btn-default"><i class="fa fa-gears"></i> </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Name</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="name" class="form-control" value="<?= empty($user) ? '' : $user['name'] ?>" require />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Email</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="email" class="form-control" value="<?= empty($user) ? '' : $user['email'] ?>" require />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Role</label>
                        <div class="col-md-4 col-xs-12">
                            <select name="hakakses" class="form-control select">
                                <option></option>
                                <?php foreach ($list_role as $role) : ?>
                                    <option value="<?= $role['id_hakakses'] ?>" <?= !empty($user) && ($user['hakakses'] == $role['id_hakakses']) ? 'selected' : '' ?>><?= $role['akses'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label"></label>
                        <div class="col-md-4 col-xs-12">
                            <label class="check"><input type="checkbox" name="bypass" class="icheckbox" value="1" <?= (!empty($user) && $user['bypass']) ? 'checked' : null ?> /> Bypass password saat login?</label>
                        </div>
                    </div>

                    <hr>

                    <h4>Informasi pegawai</h4>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Nama Pegawai</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="nama_pegawai" class="form-control" value="<?= empty($employee) ? '' : $employee['nama_pegawai'] ?>" require <?= empty($user) ? '' : 'disabled' ?> />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Status</label>
                        <div class="col-md-4 col-xs-12">
                            <select name="status_aktif" class="form-control select" <?= empty($user) ? '' : 'disabled' ?>>
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

                    <a href="user" type="reset" class="btn btn-default pull-right link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
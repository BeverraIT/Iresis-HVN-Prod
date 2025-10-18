<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $action ?></h3>
    </div>
    <div class="panel-body">
        <form action="status_performa/save" method="POST" class="form-horizontal">
            <input type="hidden" name="id" value="<?= isset($status['id']) ? $status['id'] : '' ?>">
            
            <div class="form-group">
                <label class="col-md-3 control-label">Kode Status <span class="text-danger">*</span></label>
                <div class="col-md-6">
                    <input type="text" name="kode_status" class="form-control" 
                           value="<?= isset($status['kode_status']) ? $status['kode_status'] : '' ?>" 
                           placeholder="Contoh: PACKER_GTL, PICKER_NORMAL"
                           required>
                    <span class="help-block">Format: ROLE_NAMASTSTATUS (huruf besar, gunakan underscore)</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Role <span class="text-danger">*</span></label>
                <div class="col-md-6">
                    <select name="role" class="form-control" required>
                        <option value="">-- Pilih Role --</option>
                        <?php foreach ($list_role as $role): ?>
                            <option value="<?= $role ?>" <?= (isset($status['role']) && $status['role'] == $role) ? 'selected' : '' ?>>
                                <?= $role ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Nama Status <span class="text-danger">*</span></label>
                <div class="col-md-6">
                    <input type="text" name="status_name" class="form-control" 
                           value="<?= isset($status['status_name']) ? $status['status_name'] : '' ?>" 
                           placeholder="Contoh: GTL, NDD, NORMAL"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Deskripsi</label>
                <div class="col-md-6">
                    <textarea name="deskripsi" class="form-control" rows="3" 
                              placeholder="Deskripsi status performa"><?= isset($status['deskripsi']) ? $status['deskripsi'] : '' ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Target Harian <span class="text-danger">*</span></label>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="number" name="target_harian" class="form-control" 
                               value="<?= isset($status['target_harian']) ? $status['target_harian'] : '50' ?>" 
                               min="1" max="500"
                               required>
                        <span class="input-group-addon">resi/hari</span>
                    </div>
                    <span class="help-block">Target minimal resi per hari untuk status ini</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Status Aktif</label>
                <div class="col-md-6">
                    <label class="check">
                        <input type="checkbox" name="isactive" value="1" 
                               <?= (!isset($status['isactive']) || $status['isactive'] == 1) ? 'checked' : '' ?>>
                        <span class="checkbox-material"><span class="check"></span></span>
                        Aktif (akan muncul di pilihan login dan dashboard)
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">
                        <span class="fa fa-save"></span> Simpan
                    </button>
                    <a href="status_performa" class="btn btn-default">
                        <span class="fa fa-arrow-left"></span> Kembali
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.help-block {
    color: #999;
    font-size: 12px;
    margin-top: 5px;
}
.text-danger {
    color: #d9534f;
}
</style>

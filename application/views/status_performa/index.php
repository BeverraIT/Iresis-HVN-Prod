<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Master Status Performa</h3>
        <div class="btn-group pull-right">
            <a href="status_performa/edit" class="btn btn-success btn-sm">
                <span class="fa fa-plus"></span> Tambah Status Performa
            </a>
        </div>
    </div>
    <div class="panel-body">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?= $message['title'] ?></strong> <?= $message['message'] ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Kode Status</th>
                        <th width="10%">Role</th>
                        <th width="20%">Nama Status</th>
                        <th width="25%">Deskripsi</th>
                        <th width="10%">Target Harian</th>
                        <th width="10%">Status</th>
                        <th width="5%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list_status)): ?>
                        <?php $no = 1; foreach ($list_status as $status): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="label label-primary"><?= $status['kode_status'] ?></span></td>
                                <td><span class="label label-<?= $status['role'] == 'PACKER' ? 'info' : 'warning' ?>"><?= $status['role'] ?></span></td>
                                <td><?= $status['status_name'] ?></td>
                                <td><?= $status['deskripsi'] ?></td>
                                <td class="text-center">
                                    <strong><?= $status['target_harian'] ?></strong> resi/hari
                                </td>
                                <td class="text-center">
                                    <?php if ($status['isactive']): ?>
                                        <span class="label label-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="label label-default">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="status_performa/edit/<?= $status['id'] ?>" class="btn btn-primary btn-xs" title="Edit">
                                            <span class="fa fa-edit"></span>
                                        </a>
                                        <a href="status_performa/delete/<?= $status['id'] ?>" 
                                           class="btn btn-danger btn-xs" 
                                           title="Delete"
                                           onclick="return confirm('Yakin ingin menghapus status ini?')">
                                            <span class="fa fa-trash"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.panel-heading .btn-group {
    margin-top: -5px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Log aktivitas</h3>
            </div>
            <div class="panel-body">
                <p>
                    <a href="logaktivitas/edit" class="btn btn-info link"><i class="fa fa-plus"></i> Tambah log aktivitas baru</a>
                </p>
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pegawai</th>
                            <th>Tipe</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Terakhir Diubah</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_logaktivitas as $logaktivitas): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $logaktivitas['username'] ?></td>
                            <td><?= $logaktivitas['activitytype'] ?></td>
                            <td><?= $logaktivitas['created'] ?></td>
                            <td><?= $logaktivitas['isactive']==TRUE?'Aktif':'Tidak Aktif' ?></td>
                            <td><?= $logaktivitas['updated']==null?'':$logaktivitas['updated'].' oleh '.$logaktivitas['updatedbyusername'] ?></td>
                            <td class="text-center">
                                <a href="logaktivitas/edit/<?= $logaktivitas['id'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                                <a href="logaktivitas/delete/<?= $logaktivitas['id'] ?>" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

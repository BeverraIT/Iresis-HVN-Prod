<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Master Pegawai</h3>
            </div>
            <div class="panel-body">
                <!-- <p>
                    <a href="employee/edit" class="btn btn-info link"><i class="fa fa-plus"></i> Tambah pegawai baru</a>
                </p> -->
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Kode Pegawai</th>
                            <th>Nama Pegawai</th>
                            <th>Status Aktif</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_employee as $employee): ?>
                        <tr>
                            <td><?= $employee['kode_pegawai'] ?></td>
                            <td><?= $employee['nama_pegawai'] ?></td>
                            <td><?= $employee['status_aktif'] ?></td>
                            <td class="text-center">
                                <a href="employee/edit/<?= $employee['kode_pegawai'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                                <a href="employee/delete/<?= $employee['kode_pegawai'] ?>" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

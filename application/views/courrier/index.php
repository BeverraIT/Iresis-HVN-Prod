<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Master Kurir</h3>
            </div>
            <div class="panel-body">
                <p>
                    <a href="courrier/edit" class="btn btn-info link"><i class="fa fa-plus"></i> Tambah kurir baru</a>
                </p>
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kurir</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_courrier as $courrier): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $courrier['nama_kurir'] ?></td>
                            <td class="text-center">
                                <a href="courrier/edit/<?= $courrier['id_kurir'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                                <a href="courrier/delete/<?= $courrier['id_kurir'] ?>" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

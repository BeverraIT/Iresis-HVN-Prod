<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Master Marketplace</h3>
            </div>
            <div class="panel-body">
                <p>
                    <a href="marketplace/edit" class="btn btn-info link"><i class="fa fa-plus"></i> Tambah marketplace baru</a>
                </p>
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Marketplace</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_marketplace as $marketplace): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $marketplace['nama_marketplace'] ?></td>
                            <td class="text-center">
                                <a href="marketplace/edit/<?= $marketplace['id_marketplace'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                                <a href="marketplace/delete/<?= $marketplace['id_marketplace'] ?>" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">List of role</h3>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Access name</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_role as $role): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $role['akses'] ?></td>
                            <td class="text-center">
                                <a href="access/edit-access/<?= $role['id_hakakses'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">List of users</h3>
            </div>
            <div class="panel-body">
                <p>
                    <a href="user/add_user" class="btn btn-info link"><i class="fa fa-plus"></i> Add new user</a>
                </p>
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Last login</th>
                            <th>Created</th>
                            <th class="text-center">Bypass?</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_user as $user): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $user['username'] ?></td>
                            <td><?= $user['name'] ?></td>
                            <td><?= $user['akses'] ?></td>
                            <td><?= $user['lastlogin'] ?></td>
                            <td><?= $user['created'] ?></td>
                            <td class="text-center"><?= $user['bypass'] ? '<i class="fa fa-check"></i>' : null ?></td>
                            <td class="text-center">
                                <a href="user/edit_user/<?= $user['id_user'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                                <a href="user/generate_password_user/<?= $user['id_user'] ?>" class="btn btn-default link"><i class="fa fa-gears"></i> </a>
                                <a href="user/delete_user/<?= $user['id_user'] ?>" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

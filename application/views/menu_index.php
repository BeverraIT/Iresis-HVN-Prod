<link href="<?= base_url() ?>assets/css/jstree/style.min.css" rel="stylesheet">

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">List of menu access</h3>
            </div>
            <div class="panel-body">
                <p>
                    <a href="add-menu" class="btn btn-info link"><i class="fa fa-plus"></i> Add new menu</a>
                </p>
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Menu name</th>
                            <th>Parent</th>
                            <th>Uri</th>
                            <th>Sortorder</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($list_menu as $menu): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $menu['name'] ?></td>
                            <td><?= $menu['parentname'] ?></td>
                            <td><?= $menu['uri'] ?></td>
                            <td><?= $menu['sortorder'] ?></td>
                            <td class="text-center">
                                <a href="edit-menu-<?= $menu['id'] ?>" class="btn btn-success link"><i class="fa fa-edit"></i> </a>
                                <a href="delete-menu-<?= $menu['id'] ?>" class="btn btn-danger confirm"><i class="fa fa-trash-o"></i> </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Menu in tree view</h3>
            </div>
            <div class="panel-body">

                <div id="menu_tree" view>
                    <ul>
                        <?= $menu_tree; ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

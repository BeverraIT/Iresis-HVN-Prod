<link href="<?= base_url() ?>assets/css/jstree/style.min.css" rel="stylesheet">

<style>
    .hide {
        display:none;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <form action="access/save-access" class="form-horizontal" method="post">
            <input type="hidden" name="roleid" value="<?= empty($role) ? '0' : $role['id_hakakses'] ?>" />
            <div id="jstree_input"></div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong><?= $action ?></strong> : <?= $role['akses'] ?></h3>
                </div>

                <div class="panel-body">

                    <div id="menu_tree">
                        <ul>
                            <?= $menu_tree; ?>
                        </ul>
                    </div>

                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-info">Submit</button>

                    <a href="access" type="reset" class="btn btn-default pull-right link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

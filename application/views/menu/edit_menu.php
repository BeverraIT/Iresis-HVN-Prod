<div class="row">
    <div class="col-md-12">
        <form action="menu/save-menu" class="form-horizontal" method="post" autocomplete="off">
            <input type="hidden" name="id" value="<?= empty($menu) ? '0' : $menu['id'] ?>" />

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong><?= $action ?></strong></h3>
                </div>

                <div class="panel-body">
                
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Parent</label>
                        <div class="col-md-4 col-xs-12">
                            <select name="parentid" class="form-control select">
                                <?php foreach ($list_parent as $parent): ?>
                                <option value="<?= $parent['id'] ?>" <?= !empty($menu) && ($menu['parentid'] == $parent['id']) ? 'selected' : '' ?>><?= $parent['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Menu name</label>
                        <div class="col-md-4 col-xs-12">
                            <input type="text" name="name" class="form-control" value="<?= empty($menu) ? '' : $menu['name'] ?>" require />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Uri</label>
                        <div class="col-md-4 col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon">/</span>
                                <input type="text" name="uri" class="form-control" value="<?= empty($menu) ? '' : $menu['uri'] ?>" />
                            </div>
                            <span class="help-block">Leave it blank if it is as a menu parent</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Icon</label>
                        <div class="col-md-2 col-xs-12">
                            <input type="text" name="icon" class="form-control" value="<?= empty($menu) ? '' : $menu['icon'] ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Sortorder</label>
                        <div class="col-md-2 col-xs-6">
                            <input type="text" name="sortorder" class="form-control" value="<?= empty($menu) ? '' : $menu['sortorder'] ?>" require />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Description</label>
                        <div class="col-md-6 col-xs-12">
                            <textarea name="description" class="form-control" rows="5"><?= empty($menu) ? '' : $menu['description'] ?></textarea>
                        </div>
                    </div>

                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-info">Submit</button>
                    <button type="reset" class="btn btn-primary">Reset</button>

                    <a href="menu" type="reset" class="btn btn-default pull-right link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
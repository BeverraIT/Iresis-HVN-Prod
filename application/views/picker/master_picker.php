<div class="row">
  <div class="col-md-12">
    <form action="picker/save-master-picker" class="form-horizontal" method="post" id="form_picking_picker">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Tambah / Edit Picker</strong></h3>
        </div>

        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Pegawai</label>
            <div class="col-md-3 col-xs-12">
              <select name="id_pegawai" class="form-control select" data-live-search="true">
                <option></option>
                <?php foreach ($list_employee as $employee) : ?>
                  <option value="<?= $employee['kode_pegawai'] ?>"><?= $employee['nama_pegawai'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Status Aktif</label>
            <div class="col-md-2 col-xs-12">
              <select name="status_aktif" id="status_aktif" class="form-control select">
                <option></option>
                <?php foreach ($list_status as $status) : ?>
                  <option value="<?= $status ?>"><?= strtoupper($status) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-info">Submit</button>
          <button type="reset" class="btn btn-primary">Reset</button>
        </div>
      </div>
    </form>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Daftar Picker</strong></h3>
      </div>

      <div class="panel-body">
        <table class="table table-striped datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Pegawai</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1;
            foreach ($list_picker as $picker) : ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= $picker['nama_pegawai'] ?></td>
                <td><?= $picker['status_aktif'] ?></td>
                <td>
                  <a href="picker/delete_master_picker/<?= $picker['id_namaambilbarang'] ?>" class="btn btn-danger confirm" onClick="notyConfirm(event);"><i class="fa fa-trash-o"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var jvalidate = $("#form_picking_picker").validate({
    ignore: [],
    rules: {
      id_pegawai: {
        required: true,
      },
      status_aktif: {
        required: true,
      },
    }
  });
</script>
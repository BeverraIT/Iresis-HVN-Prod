<?php $bg_colors = ['#ffb3ba', '#ffffba', '#bae1ff'];  ?>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Daftar SKU</h3>
      </div>
      <div class="panel-body">
        <table class="table table-hover datatable-sku">
          <thead>
            <tr>
              <th rowspan="2">#</th>
              <th rowspan="2">Kode</th>
              <th rowspan="2">Name</th>
              <th rowspan="2">Harga rata<sup>2</sup></th>
              <?php foreach ($list_location as $key => $value) : ?>
                <th style="background: <?= $bg_colors[$key] ?>" colspan="4" class="text-center"><?= $value['paramvalue1'] ?></th>
              <?php endforeach; ?>
              <th rowspan="2" class="text-center">Action</th>
            </tr>
            <tr>
              <?php foreach ($list_location as $key => $value) : ?>
                <th style="background: <?= $bg_colors[$key] ?>" width="5%">On hand</th>
                <th style="background: <?= $bg_colors[$key] ?>" width="5%">On order</th>
                <th style="background: <?= $bg_colors[$key] ?>" width="5%">Reserved</th>
                <th style="background: <?= $bg_colors[$key] ?>" width="5%">Available</th>
              <?php endforeach; ?>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modal_edit_sku" tabindex="-1" role="dialog" aria-labelledby="largeModalHead" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="sku/save_sku_location_stock" class="form-horizontal" id="form-content-edit-sku" method="post" autocomplete="off">
      <input type="hidden" name="id" value="" />
      <input type="hidden" name="item_id" value="" />

      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="largeModalHead">Update stok item</h4>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Kode</label>
            <div class="col-md-3 col-xs-12">
              <input type="text" name="item_code" class="form-control" value="" disabled />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Nama</label>
            <div class="col-md-6 col-xs-12">
              <input type="text" name="item_name" class="form-control" value="" disabled />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Gambar</label>
            <div class="col-md-6 col-xs-12">
              <img id="thumbnail" src="" alt="">
            </div>
          </div>

          <hr>

          <div class="row">
            <?php foreach ($list_location as $key => $value) : ?>
              <div class="col-md-4 col-xs-12">
                <p><span class="badge" style="background: <?= $bg_colors[$key] ?>; color: #000"><?= $value['paramvalue1'] ?></span></p>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">On hand</label>
                  <div class="col-md-6 col-xs-12">
                    <input type="number" name="<?= $value['paramvalue2'] ?>_on_hand" class="form-control" value="" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">On order</label>
                  <div class="col-md-6 col-xs-12">
                    <input type="number" name="<?= $value['paramvalue2'] ?>_on_order" class="form-control" value="" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">Reserved</label>
                  <div class="col-md-6 col-xs-12">
                    <input type="number" name="<?= $value['paramvalue2'] ?>_reserved" class="form-control" value="" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 col-xs-12 control-label">Available</label>
                  <div class="col-md-6 col-xs-12">
                    <input type="number" name="<?= $value['paramvalue2'] ?>_available" class="form-control" value="" />
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Submit</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    var table = $('.datatable-sku').DataTable({
      'scrollX': true,
      'pageLength': 20,
      'processing': true,
      'serverSide': true,
      'order': [
        [1, 'asc']
      ],
      'lengthMenu': [
        [20, 50, 100, 150, 200],
        [20, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'sku/get_data',
        type: 'POST',
      },
      "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        $('td', nRow).eq(4).css({"background": "<?= $bg_colors[0] ?>"}).addClass('text-right');
        $('td', nRow).eq(5).css({"background": "<?= $bg_colors[0] ?>"}).addClass('text-right');
        $('td', nRow).eq(6).css({"background": "<?= $bg_colors[0] ?>"}).addClass('text-right');
        $('td', nRow).eq(7).css({"background": "<?= $bg_colors[0] ?>"}).addClass('text-right');

        $('td', nRow).eq(8).css({"background": "<?= $bg_colors[1] ?>"}).addClass('text-right');
        $('td', nRow).eq(9).css({"background": "<?= $bg_colors[1] ?>"}).addClass('text-right');
        $('td', nRow).eq(10).css({"background": "<?= $bg_colors[1] ?>"}).addClass('text-right');
        $('td', nRow).eq(11).css({"background": "<?= $bg_colors[1] ?>"}).addClass('text-right');

        $('td', nRow).eq(12).css({"background": "<?= $bg_colors[2] ?>"}).addClass('text-right');
        $('td', nRow).eq(13).css({"background": "<?= $bg_colors[2] ?>"}).addClass('text-right');
        $('td', nRow).eq(14).css({"background": "<?= $bg_colors[2] ?>"}).addClass('text-right');
        $('td', nRow).eq(15).css({"background": "<?= $bg_colors[2] ?>"}).addClass('text-right');
      },
    });

    table.on('draw', function() {
      $(this).find('.btn-edit-sku').on('click', function() {
        var id = $(this).data('id');

        $.ajax({
          url: 'sku/get_sku_location_stock/' + id,
          type: 'get',
          dataType: 'JSON',
        }).done(function(response) {
          var sku = response.data;

          $('input[name="id"]').val(sku.id);
          $('input[name="item_id"]').val(sku.item_id);
          $('input[name="item_code"]').val(sku.item_code);
          $('input[name="item_name"]').val(sku.item_name);

          $('#thumbnail').attr("src", sku.thumbnail);

          for (const [key, value] of Object.entries(sku.list_skulocationstock)) {
            $('input[name="' + key + '"]').val(value);
          }

          $('#modal_edit_sku').modal('show');
        });
      });
    });

    $('#form-content-edit-sku').on('submit', function() {
      var form = $(this);

      $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(),
        dataType: 'JSON',
      }).done(function(response) {
        noty({text: response.message, timeout: 3000, layout: 'topRight', type: 'information'});
      }).fail(function(response) {
        noty({text: response.message, timeout: 3000, layout: 'topRight', type: 'danger'});
      });

      table.ajax.reload(null, false);

      $('#modal_edit_sku').modal('hide');
      $('#form-content-edit-sku').trigger("reset");

      return false;
    });
  });
</script>
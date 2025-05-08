<div class="row">
  <div class="col-md-6 center-block float-none">
    <form action="receipt_delete/delete" class="form-horizontal" id="form_delete_resi" autocomplete="off">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Hapus Resi</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Resi</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" name="noresi" id="noresi" class="form-control" />
            </div>
          </div>

          <div class="tile tile-default" id="div_container_deleted_receipt">
            <span id="span_delete_receipt">-</span>
            <p><small id="p_latest_receipt_message">Nomor resi yang sudah dihapus</small></p>
          </div>

        </div>

        <div class="panel-footer">
          <button type="submit" class="btn btn-info"><i class="fa fa-trash-o"></i> Hapus</button>
          <button type="reset" class="btn btn-primary pull-right">Reset</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  $("#noresi").focus();
  var jvalidate = $("#form_delete_resi").validate({
    ignore: [],
    rules: {
      noresi: {
        required: true,
      },
    },
    submitHandler: function(form) {
      var formData = new FormData(form);

      form.noresi.disabled = true;

      $.ajax({
        url: form.action,
        type: 'post',
        data: Object.fromEntries(formData),
        success: function(data) {},
        error: function(data) {},
      }).done(function(response) {
        $("#span_delete_receipt").text(form.noresi.value);
        $("#div_container_deleted_receipt").removeClass("tile-danger").addClass("tile-default");
        $("#p_latest_receipt_message").text("Nomor resi yang sudah dihapus");

        document.getElementById('audio-alert').play();

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      }).fail(function(error) {
        var response = JSON.parse(error.responseText);

        $("#span_delete_receipt").text(form.noresi.value);
        $("#div_container_deleted_receipt").removeClass("tile-default").addClass("tile-danger");
        $("#p_latest_receipt_message").text(response.message);

        document.getElementById('audio-fail').play();

        form.noresi.value = "";
        form.noresi.disabled = false;
        form.noresi.focus();
      });
      return false; // required to block normal submit since you used ajax
    }
  });
</script>
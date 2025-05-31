<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Cari Resi Packer</strong></h3>
      </div>

      <div class="panel-body">
        <table class="table table-striped datatable-packer">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Packer</th>
              <th>Tanggal Packing</th>
              <th>Jam Packing</th>
              <th>Keterangan</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('.datatable-packer').DataTable({
      'scrollX': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'order': [
        [3, 'desc'],
      ],
      'lengthMenu': [
        [10, 50, 100, 150, 200],
        [10, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'packer/get-data-packer',
        type: 'POST',
      },
    });
  });
</script>